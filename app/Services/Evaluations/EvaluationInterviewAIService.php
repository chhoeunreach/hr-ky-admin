<?php

namespace App\Services\Evaluations;

use App\Models\EvaluationInterview;
use Illuminate\Support\Str;

class EvaluationInterviewAIService
{
    public function firstQuestion(): string
    {
        return 'សូមពិពណ៌នាខ្លីៗថា បុគ្គលិកនេះមានតួនាទីអ្វី និងធ្វើការងារអ្វីជាចម្បង?';
    }

    public function next(EvaluationInterview $interview): array
    {
        $messages = $interview->messages()->oldest()->get();
        $answers = $messages->where('role', 'manager')->pluck('message')->values();
        $context = Str::lower($answers->implode(' '));
        $askedCount = $messages->where('role', 'ai')->count();

        $summary = $this->summarize($interview, $answers->all());

        if ($answers->count() >= 5 && $this->hasEnoughInformation($summary, $context)) {
            return [
                'status' => 'ready',
                'summary' => $summary,
            ];
        }

        $questions = [
            'daily' => 'តើការងារប្រចាំថ្ងៃសំខាន់ៗរបស់បុគ្គលិកនេះមានអ្វីខ្លះ?',
            'expected' => 'តើលទ្ធផលអ្វីដែលអ្នករំពឹងពីបុគ្គលិកនេះក្នុងរយៈពេលវាយតម្លៃនេះ?',
            'kpi' => 'តើមាន KPI ឬ Target អ្វីខ្លះដែលត្រូវវាស់វែង?',
            'problem' => 'តើបញ្ហាអ្វីខ្លះដែលបុគ្គលិកនេះត្រូវដោះស្រាយជាប្រចាំ?',
            'skill' => 'តើជំនាញ ឧបករណ៍ ឬប្រព័ន្ធអ្វីខ្លះដែលបុគ្គលិកនេះត្រូវប្រើឱ្យបានល្អ?',
            'customer' => 'តើបុគ្គលិកនេះមានទំនួលខុសត្រូវចំពោះអតិថិជន ឬ Channel Online អ្វីខ្លះ?',
            'report' => 'តើបុគ្គលិកនេះត្រូវរាយការណ៍អ្វីខ្លះ និងរាយការណ៍ទៅអ្នកណា?',
            'teamwork' => 'តើការសហការជាមួយក្រុម ឬផ្នែកផ្សេងៗត្រូវវាស់វែងយ៉ាងដូចម្តេច?',
        ];

        if ($this->looksLikeLeader($context)) {
            $questions['leadership'] = 'តើតួនាទីដឹកនាំ ការបង្រៀន ឬការគ្រប់គ្រងក្រុមរបស់បុគ្គលិកនេះមានអ្វីខ្លះ?';
        }

        foreach ($questions as $key => $question) {
            if (!$this->contextCovers($key, $context) && $askedCount < 10) {
                return [
                    'status' => 'need_more_information',
                    'next_question' => $question,
                ];
            }
        }

        return [
            'status' => 'ready',
            'summary' => $summary,
        ];
    }

    public function summarize(EvaluationInterview $interview, array $answers): array
    {
        $employee = $interview->employee;
        $position = $employee?->post?->post_name ?? 'Employee';
        $text = trim(implode("\n", $answers));
        $items = $this->extractItems($text);

        return [
            'job_title' => $position,
            'main_purpose' => $answers[0] ?? 'បំពេញការងារតាមតួនាទី និងគោលដៅរបស់ផ្នែក។',
            'responsibilities' => array_slice($items, 0, 8),
            'daily_tasks' => $this->filterItems($items, ['daily', 'រាល់ថ្ងៃ', 'ប្រចាំថ្ងៃ', 'comment', 'message', 'customer', 'អតិថិជន']),
            'weekly_tasks' => $this->filterItems($items, ['weekly', 'week', 'ប្រចាំសប្តាហ៍', 'សប្តាហ៍', 'report']),
            'monthly_tasks' => $this->filterItems($items, ['monthly', 'month', 'ប្រចាំខែ', 'ខែ', 'target']),
            'kpis' => $this->filterItems($items, ['kpi', 'target', 'score', 'time', 'accuracy', 'response', 'missed', 'គោលដៅ', 'ពិន្ទុ']),
            'required_skills' => $this->filterItems($items, ['skill', 'system', 'tool', 'excel', 'facebook', 'tiktok', 'ជំនាញ', 'ប្រព័ន្ធ']),
            'required_knowledge' => $this->filterItems($items, ['knowledge', 'policy', 'procedure', 'ចំណេះដឹង', 'នីតិវិធី']),
            'tools' => $this->filterItems($items, ['tool', 'system', 'excel', 'facebook', 'tiktok', 'telegram', 'pos', 'ប្រព័ន្ធ']),
            'common_problems' => $this->filterItems($items, ['problem', 'issue', 'complaint', 'បញ្ហា', 'តវ៉ា']),
            'customer_responsibilities' => $this->filterItems($items, ['customer', 'client', 'comment', 'message', 'page', 'tiktok', 'អតិថិជន']),
            'reporting_responsibilities' => $this->filterItems($items, ['report', 'summary', 'daily report', 'រាយការណ៍']),
            'leadership_responsibilities' => $this->looksLikeLeader(Str::lower($text))
                ? $this->filterItems($items, ['lead', 'leader', 'train', 'manage', 'supervisor', 'ដឹកនាំ', 'បង្រៀន'])
                : [],
            'special_responsibilities' => $this->filterItems($items, ['special', 'vip', 'project', 'assignment', 'ពិសេស']),
        ];
    }

    private function hasEnoughInformation(array $summary, string $context): bool
    {
        return count($summary['responsibilities']) >= 3
            && (count($summary['kpis']) >= 1 || Str::contains($context, ['kpi', 'target', 'គោលដៅ']))
            && (count($summary['daily_tasks']) >= 1 || count($summary['customer_responsibilities']) >= 1);
    }

    private function contextCovers(string $key, string $context): bool
    {
        $keywords = [
            'daily' => ['daily', 'ប្រចាំថ្ងៃ', 'រាល់ថ្ងៃ', 'comment', 'message'],
            'expected' => ['expect', 'result', 'goal', 'រំពឹង', 'លទ្ធផល'],
            'kpi' => ['kpi', 'target', 'score', 'គោលដៅ', 'ពិន្ទុ'],
            'problem' => ['problem', 'issue', 'complaint', 'បញ្ហា'],
            'skill' => ['skill', 'tool', 'system', 'ជំនាញ', 'ប្រព័ន្ធ'],
            'customer' => ['customer', 'client', 'comment', 'message', 'អតិថិជន'],
            'report' => ['report', 'រាយការណ៍'],
            'teamwork' => ['team', 'collaborate', 'ក្រុម', 'សហការ'],
            'leadership' => ['lead', 'leader', 'manage', 'train', 'ដឹកនាំ', 'បង្រៀន'],
        ];

        return Str::contains($context, $keywords[$key] ?? [$key]);
    }

    private function looksLikeLeader(string $context): bool
    {
        return Str::contains($context, ['leader', 'lead', 'manager', 'supervisor', 'train', 'manage staff', 'ដឹកនាំ', 'បង្រៀន', 'មេក្រុម']);
    }

    private function extractItems(string $text): array
    {
        $parts = preg_split('/[\r\n•,;។]+/u', $text) ?: [];
        $items = collect($parts)
            ->map(fn ($item) => trim($item, " \t\n\r\0\x0B-–—:"))
            ->filter(fn ($item) => mb_strlen($item) >= 4)
            ->unique()
            ->values()
            ->all();

        return $items ?: ['បំពេញការងារតាមតួនាទី', 'រក្សាគុណភាពការងារ', 'សហការជាមួយក្រុម'];
    }

    private function filterItems(array $items, array $keywords): array
    {
        $filtered = collect($items)
            ->filter(fn ($item) => Str::contains(Str::lower($item), $keywords))
            ->values()
            ->all();

        return $filtered ?: array_slice($items, 0, min(3, count($items)));
    }
}
