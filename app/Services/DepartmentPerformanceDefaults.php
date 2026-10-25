<?php

namespace App\Services;

use App\Models\Department;
use App\Models\EmployeeJobResponsibility;
use App\Models\EmployeeKpi;
use App\Models\EmployeeProfileAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepartmentPerformanceDefaults
{
    private ?array $catalog = null;

    public function catalog(): array
    {
        return $this->catalog ??= json_decode(file_get_contents(resource_path('data/department-performance-defaults.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function match(string $name): ?string
    {
        $name = mb_strtolower(trim($name));
        $best = null;
        $length = 0;
        foreach ($this->catalog() as $key => $profile) {
            foreach ($profile['aliases'] as $alias) {
                $alias = mb_strtolower($alias);
                $matches = preg_match('/^[a-z]{1,3}$/', $alias)
                    ? preg_match('/(?<![a-z])'.preg_quote($alias, '/').'(?![a-z])/i', $name)
                    : str_contains($name, $alias);
                if ($matches && mb_strlen($alias) > $length) {
                    $best = $key; $length = mb_strlen($alias);
                }
            }
        }
        return $best;
    }

    public function forName(string $departmentName, string $position = ''): array
    {
        $positionKey = $this->match($position);
        $key = ($positionKey && $positionKey !== 'general' ? $positionKey : $this->match($departmentName)) ?? 'general';
        $profile = $this->catalog()[$key];
        $weights = [25, 20, 20, 15, 10, 10];
        $responsibilities = $kpis = $evaluationCriteria = [];
        foreach ($profile['items'] as $index => $item) {
            $formula = $item['formula'] ?? 'ចំនួនករណីដែលបានបញ្ចប់ត្រឹមត្រូវតាមលក្ខខណ្ឌ និងពេលវេលាដែលបានព្រមព្រៀង ÷ ចំនួនករណីដែលត្រូវអនុវត្តក្នុងខែ × ១០០';
            $measurement = $item['description']."\nរយៈពេលវាស់វែង៖ ប្រចាំខែ។\nរូបមន្ត៖ ".$formula."។\nភស្តុតាង៖ បញ្ជីការងារ កំណត់ត្រាប្រព័ន្ធ ឯកសារអនុម័ត និងលទ្ធផលត្រួតពិនិត្យ។\nបើគ្មានករណីត្រូវវាស់វែង ត្រូវទុកលទ្ធផលនិងពិន្ទុទទេ។";
            $responsibilities[] = [
                'title' => $item['title'], 'description' => $item['description'],
                'kpi_target' => 'យ៉ាងតិច '.$item['target'].'% ក្នុងមួយខែ',
                'weight' => $weights[$index], 'status' => 'active',
            ];
            $evaluationCriteria[] = [
                'criteria' => $item['title'],
                'description' => $item['description']."\nគោលដៅយោង៖ យ៉ាងតិច ".$item['target']."% ក្នុងរយៈពេលវាយតម្លៃ។\nភស្តុតាង៖ ពិនិត្យកំណត់ត្រាការងារ លទ្ធផលដែលបានអនុម័ត និងមតិអ្នកពាក់ព័ន្ធ។\nការផ្តល់ពិន្ទុ៖ លទ្ធផលជាក់ស្តែង ÷ គោលដៅ × ពិន្ទុអតិបរមា ដោយមិនលើស ".$weights[$index]." ពិន្ទុ។ បើគ្មានភស្តុតាងគ្រប់គ្រាន់ ត្រូវស្នើព័ត៌មានបន្ថែម មិនត្រូវសន្មតលទ្ធផល។",
                'max_score' => $weights[$index],
            ];
            $kpis[] = [
                'name' => 'អត្រាសម្រេច៖ '.$item['title'], 'description' => $measurement,
                'target_value' => $item['target'], 'actual_value' => null, 'unit' => 'percentage',
                'weight' => $weights[$index], 'achievement_percentage' => null, 'score' => null,
            ];
        }
        return ['key' => $key, 'label' => $profile['label'], 'responsibilities' => $responsibilities, 'kpis' => $kpis, 'evaluation_criteria' => $evaluationCriteria];
    }

    public function forEmployee(User $employee): array
    {
        $employee->loadMissing(['department', 'post']);
        return $this->forName($employee->department?->dept_name ?? '', $employee->post?->post_name ?? '');
    }

    public function applyToEmployee(User $employee): array
    {
        return DB::transaction(function () use ($employee) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $preset = $this->forEmployee($employee);
            $counts = ['responsibilities' => 0, 'kpis' => 0];
            // Preserve existing assignments, targets, scores and custom weight totals.
            if (!EmployeeJobResponsibility::where('employee_id', $employee->id)->exists()) {
                foreach ($preset['responsibilities'] as $data) {
                    $record = EmployeeJobResponsibility::create($data + ['employee_id' => $employee->id, 'assigned_by' => auth()->id(), 'start_date' => now()->toDateString()]);
                    $this->audit($employee, $record, 'responsibility', $preset['key']);
                    $counts['responsibilities']++;
                }
            }
            if (!EmployeeKpi::where('employee_id', $employee->id)->exists()) {
                foreach ($preset['kpis'] as $data) {
                    $record = EmployeeKpi::create($data + ['employee_id' => $employee->id, 'created_by' => auth()->id()]);
                    $this->audit($employee, $record, 'kpi', $preset['key']);
                    $counts['kpis']++;
                }
            }
            return $counts;
        });
    }

    public function applyToDepartment(Department $department): array
    {
        $counts = ['employees' => 0, 'responsibilities' => 0, 'kpis' => 0];
        $department->employees()->with(['department', 'post'])->orderBy('users.id')->chunkById(100, function ($employees) use (&$counts) {
            foreach ($employees as $employee) {
                $created = $this->applyToEmployee($employee);
                $counts['employees']++;
                $counts['responsibilities'] += $created['responsibilities'];
                $counts['kpis'] += $created['kpis'];
            }
        });
        return $counts;
    }

    private function audit(User $employee, $record, string $module, string $preset): void
    {
        EmployeeProfileAuditLog::create([
            'employee_id' => $employee->id, 'module' => $module, 'action' => 'create_department_defaults',
            'record_id' => $record->id, 'new_values' => $record->toArray() + ['default_preset' => $preset],
            'performed_by' => auth()->id(), 'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 65535), 'created_at' => now(),
        ]);
    }
}
