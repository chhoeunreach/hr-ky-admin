<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\DCardEmployee;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DCardPrintController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request): Factory|View|Application
    {
        $this->authorize('list_employee');

        $branchColumns = ['id', 'name'];
        $hasBranchLogo = Schema::hasColumn('branches', 'logo');
        $hasBranchPaymentQrCodes = Schema::hasColumn('branches', 'payment_qr_codes');

        if ($hasBranchLogo) {
            $branchColumns[] = 'logo';
        }

        if ($hasBranchPaymentQrCodes) {
            $branchColumns[] = 'payment_qr_codes';
        }

        $employees = User::with([
                'branch:' . implode(',', $branchColumns),
                'department:id,dept_name',
                'post:id,post_name',
                'company:id,name,logo,address,phone',
            ])
            ->select([
                'id',
                'employee_code',
                'name',
                'english_name',
                'email',
                'phone',
                'avatar',
                'branch_id',
                'department_id',
                'post_id',
                'company_id',
                'joining_date',
            ])
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get()
            ->map(function (User $employee) use ($hasBranchLogo, $hasBranchPaymentQrCodes) {
                return [
                    'id' => 'user-' . $employee->id,
                    'record_id' => $employee->id,
                    'source' => 'user',
                    'employee_code' => $employee->employee_code ?: sprintf('EMP-%04d', $employee->id),
                    'name' => $employee->name,
                    'english_name' => $employee->english_name,
                    'position_khmer' => $employee->post?->post_name,
                    'position_english' => $employee->post?->post_name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'photo_url' => $employee->avatar
                        ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar)
                        : asset('assets/images/img.png'),
                    'branch_id' => $employee->branch_id,
                    'branch' => $employee->branch?->name,
                    'branch_logo_url' => $hasBranchLogo && $employee->branch?->logo
                        ? asset(Branch::UPLOAD_PATH . $employee->branch->logo)
                        : null,
                    'department_id' => $employee->department_id,
                    'department' => $employee->department?->dept_name,
                    'post_id' => $employee->post_id,
                    'post' => $employee->post?->post_name,
                    'joining_date' => $this->dateForInput($employee->joining_date),
                    'emergency_contact' => '',
                    'blood_type' => '',
                    'khqr_account_id' => '',
                    'company' => $employee->company?->name,
                    'company_address' => $employee->company?->address,
                    'company_phone' => $employee->company?->phone,
                    'payment_qr_codes' => collect($hasBranchPaymentQrCodes ? ($employee->branch?->payment_qr_codes ?? []) : [])
                        ->map(fn ($qrCode) => [
                            'payment_name' => $qrCode['payment_name'] ?? '',
                            'qr_code_url' => !empty($qrCode['qr_code'])
                                ? asset(Branch::UPLOAD_PATH . $qrCode['qr_code'])
                                : null,
                        ])
                        ->filter(fn ($qrCode) => $qrCode['payment_name'] && $qrCode['qr_code_url'])
                        ->values()
                        ->all(),
                ];
            });

        if (Schema::hasTable('d_card_employees')) {
            $customEmployees = DCardEmployee::orderBy('employee_code')->get();
            $customEmployeesByCode = $customEmployees->keyBy('employee_code');

            $employees = $employees
                ->map(function (array $employee) use ($customEmployeesByCode) {
                    $customEmployee = $customEmployeesByCode->get($employee['employee_code']);

                    if (! $customEmployee) {
                        return $employee;
                    }

                    return $this->mergeDCardOverride($employee, $customEmployee);
                })
                ->concat(
                    $customEmployees
                        ->reject(fn (DCardEmployee $employee) => $employees->contains('employee_code', $employee->employee_code))
                        ->map(fn (DCardEmployee $employee) => $this->transformDCardEmployee($employee))
                )
                ->values();
        }

        $company = Company::select(['id', 'name', 'logo', 'address', 'phone'])->first();
        $companyLogo = AppHelper::getCompanyLogo()
            ? asset(Company::UPLOAD_PATH . AppHelper::getCompanyLogo())
            : null;

        return view('admin.dCardPrint.index', compact('employees', 'company', 'companyLogo'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('list_employee');

        $employee = DCardEmployee::where('employee_code', $request->input('employee_code'))->first();

        if ($employee) {
            $employee->update($this->validatedData($request, $employee));
        } else {
            $employee = DCardEmployee::create($this->validatedData($request));
        }

        return response()->json([
            'employee' => $this->transformDCardEmployee($employee->fresh()),
        ]);
    }

    public function update(Request $request, DCardEmployee $employee): JsonResponse
    {
        $this->authorize('list_employee');

        $employee->update($this->validatedData($request, $employee));

        return response()->json([
            'employee' => $this->transformDCardEmployee($employee->fresh()),
        ]);
    }

    public function destroy(DCardEmployee $employee): JsonResponse
    {
        $this->authorize('list_employee');

        $employee->delete();

        return response()->json(['deleted' => true]);
    }

    private function validatedData(Request $request, ?DCardEmployee $employee = null): array
    {
        $data = $request->validate([
            'employee_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('d_card_employees', 'employee_code')->ignore($employee?->id),
            ],
            'name_khmer' => ['required', 'string', 'max:255'],
            'name_english' => ['nullable', 'string', 'max:255'],
            'position_khmer' => ['nullable', 'string', 'max:255'],
            'position_english' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:50'],
            'khqr_account_id' => ['nullable', 'string', 'max:255'],
            'profile_photo_url' => ['nullable', 'string', 'max:2048'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        unset($data['profile_photo']);

        if ($request->hasFile('profile_photo')) {
            if ($employee?->profile_photo_url && str_contains($employee->profile_photo_url, '/storage/d-card-employees/')) {
                $oldPath = str($employee->profile_photo_url)->after('/storage/')->toString();
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('profile_photo')->store('d-card-employees/photos', 'public');
            $data['profile_photo_url'] = asset('storage/' . $path);
        }

        return $data;
    }

    private function transformDCardEmployee(DCardEmployee $employee): array
    {
        return [
            'id' => 'dcard-' . $employee->id,
            'record_id' => $employee->id,
            'source' => 'dcard',
            'employee_code' => $employee->employee_code,
            'name' => $employee->name_khmer,
            'english_name' => $employee->name_english,
            'position_khmer' => $employee->position_khmer,
            'position_english' => $employee->position_english,
            'post' => $employee->position_khmer ?: $employee->position_english,
            'branch_id' => null,
            'department' => $employee->department,
            'department_id' => null,
            'branch' => $employee->branch,
            'post_id' => null,
            'joining_date' => $this->dateForInput($employee->joining_date),
            'emergency_contact' => $employee->emergency_contact,
            'blood_type' => $employee->blood_type,
            'khqr_account_id' => $employee->khqr_account_id,
            'phone' => $employee->phone,
            'email' => $employee->email,
            'photo_url' => $employee->profile_photo_url ?: asset('assets/images/img.png'),
            'branch_logo_url' => $this->branchLogoUrl($employee->branch),
            'company' => null,
            'company_address' => null,
            'company_phone' => null,
            'payment_qr_codes' => [],
        ];
    }

    private function mergeDCardOverride(array $employee, DCardEmployee $override): array
    {
        $overrideBranch = $override->branch ?: null;
        $overrideDepartment = $override->department ?: null;
        $overridePost = ($override->position_khmer ?: $override->position_english) ?: null;

        return [
            ...$employee,
            'id' => 'dcard-' . $override->id,
            'record_id' => $override->id,
            'source' => 'dcard',
            'employee_code' => $override->employee_code ?: $employee['employee_code'],
            'name' => $override->name_khmer ?: $employee['name'],
            'english_name' => $override->name_english ?: $employee['english_name'],
            'position_khmer' => $override->position_khmer ?: $employee['position_khmer'],
            'position_english' => $override->position_english ?: $employee['position_english'],
            'post_id' => $overridePost && $overridePost !== $employee['post'] ? null : ($employee['post_id'] ?? null),
            'post' => $overridePost ?: $employee['post'],
            'department_id' => $overrideDepartment && $overrideDepartment !== $employee['department'] ? null : ($employee['department_id'] ?? null),
            'department' => $overrideDepartment ?: $employee['department'],
            'branch_id' => $overrideBranch && $overrideBranch !== $employee['branch'] ? null : ($employee['branch_id'] ?? null),
            'branch' => $overrideBranch ?: $employee['branch'],
            'joining_date' => $this->dateForInput($override->joining_date) ?: $employee['joining_date'],
            'emergency_contact' => $override->emergency_contact ?: $employee['emergency_contact'],
            'blood_type' => $override->blood_type ?: $employee['blood_type'],
            'khqr_account_id' => $override->khqr_account_id ?: $employee['khqr_account_id'],
            'phone' => $override->phone ?: $employee['phone'],
            'email' => $override->email ?: $employee['email'],
            'photo_url' => $override->profile_photo_url ?: $employee['photo_url'],
            'branch_logo_url' => $this->branchLogoUrl($override->branch) ?: $employee['branch_logo_url'],
        ];
    }

    private function dateForInput(mixed $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return Carbon::parse($date)->format('Y-m-d');
    }

    private function branchLogoUrl(?string $branchName): ?string
    {
        if (empty($branchName) || ! Schema::hasColumn('branches', 'logo')) {
            return null;
        }

        $logo = Branch::where('name', $branchName)->value('logo');

        return $logo ? asset(Branch::UPLOAD_PATH . $logo) : null;
    }
}
