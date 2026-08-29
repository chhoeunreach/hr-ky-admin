<?php

namespace App\Services\Evaluations;

use App\Models\EmployeeEvaluation;
use App\Models\EvaluationJobDescription;

class EmployeeEvaluationAIService
{
    public function generateQuestions(EvaluationJobDescription $jobDescription): array
    {
        $questions = [];
        $jobItems = array_values(array_filter(array_merge(
            $jobDescription->responsibilities ?? [],
            $jobDescription->daily_tasks ?? [],
            $jobDescription->weekly_tasks ?? [],
            $jobDescription->monthly_tasks ?? [],
            $jobDescription->kpis ?? [],
            $jobDescription->customer_responsibilities ?? [],
            $jobDescription->reporting_responsibilities ?? [],
            $jobDescription->special_responsibilities ?? [],
        )));

        foreach (array_slice($jobItems, 0, 18) as $item) {
            $questions[] = [
                'section' => 'Job Performance',
                'question_kh' => 'តើបុគ្គលិកអាចអនុវត្ត "' . $item . '" បានល្អតាមការរំពឹងទុកដែរឬទេ?',
                'question_en' => 'Does the employee perform "' . $item . '" according to expectations?',
                'question_type' => 'score_1_5',
                'weight' => 6,
                'max_score' => 5,
                'reason' => 'This item comes directly from the confirmed job description.',
            ];
        }

        $general = [
            ['Responsibility', 'តើបុគ្គលិកមានទំនួលខុសត្រូវចំពោះការងារដែលបានប្រគល់ឱ្យដែរឬទេ?', 'Does the employee take responsibility for assigned work?'],
            ['Discipline', 'តើបុគ្គលិកគោរពវិន័យ ពេលវេលា និងនីតិវិធីការងារដែរឬទេ?', 'Does the employee follow discipline, time, and work procedures?'],
            ['Teamwork', 'តើបុគ្គលិកសហការជាមួយក្រុម និងផ្នែកពាក់ព័ន្ធបានល្អដែរឬទេ?', 'Does the employee cooperate well with the team and related departments?'],
            ['Communication', 'តើការប្រាស្រ័យទាក់ទងរបស់បុគ្គលិកមានភាពច្បាស់លាស់ និងគួរសមដែរឬទេ?', 'Is the employee communication clear and professional?'],
            ['Problem Solving', 'តើបុគ្គលិកដោះស្រាយបញ្ហាការងារបានសមស្របដែរឬទេ?', 'Does the employee solve work problems appropriately?'],
            ['Learning & Improvement', 'តើបុគ្គលិករៀនសូត្រ និងកែលម្អការងាររបស់ខ្លួនជាបន្តបន្ទាប់ដែរឬទេ?', 'Does the employee continue learning and improving?'],
        ];

        if (!empty($jobDescription->leadership_responsibilities)) {
            $general[] = ['Leadership', 'តើបុគ្គលិកអាចដឹកនាំ បង្រៀន ឬគាំទ្រក្រុមបានល្អដែរឬទេ?', 'Does the employee lead, train, or support the team well?'];
        }

        foreach ($general as [$section, $kh, $en]) {
            $questions[] = [
                'section' => $section,
                'question_kh' => $kh,
                'question_en' => $en,
                'question_type' => 'score_1_5',
                'weight' => 4,
                'max_score' => 5,
                'reason' => 'General performance behavior that supports this role.',
            ];
        }

        if (count($questions) < 15) {
            foreach (['តើគុណភាពការងាររបស់បុគ្គលិកស្ថិតក្នុងកម្រិតដែលអាចទុកចិត្តបានដែរឬទេ?', 'តើបុគ្គលិកបញ្ចប់ការងារទាន់ពេល និងមិនចាំបាច់តាមដានញឹកញាប់ដែរឬទេ?', 'តើបុគ្គលិករក្សាភាពត្រឹមត្រូវនៃព័ត៌មាន និងឯកសារដែរឬទេ?'] as $kh) {
                $questions[] = [
                    'section' => 'Work Quality',
                    'question_kh' => $kh,
                    'question_en' => null,
                    'question_type' => 'score_1_5',
                    'weight' => 4,
                    'max_score' => 5,
                    'reason' => 'Baseline quality requirement.',
                ];
            }
        }

        return ['questions' => $this->normalizeWeights(array_slice($questions, 0, 30))];
    }

    public function normalizeWeights(array $questions): array
    {
        $total = array_sum(array_map(fn ($question) => (float) ($question['weight'] ?? 0), $questions));
        $total = $total > 0 ? $total : 1;
        $running = 0;
        $lastIndex = count($questions) - 1;

        foreach ($questions as $index => $question) {
            $weight = round(((float) ($question['weight'] ?? 0) / $total) * 100, 2);
            if ($index === $lastIndex) {
                $weight = round(100 - $running, 2);
            }
            $questions[$index]['weight'] = $weight;
            $questions[$index]['max_score'] = (int) ($question['max_score'] ?? 5);
            $questions[$index]['question_type'] = $question['question_type'] ?? 'score_1_5';
            $running += $weight;
        }

        return $questions;
    }

    public function calculate(EmployeeEvaluation $evaluation, array $answers): array
    {
        $applicableWeight = 0;
        foreach ($answers as $answer) {
            if (empty($answer['is_na'])) {
                $applicableWeight += (float) $answer['weight'];
            }
        }

        $score = 0;
        foreach ($answers as $answer) {
            if (!empty($answer['is_na']) || $applicableWeight <= 0) {
                continue;
            }
            $normalizedWeight = ((float) $answer['weight'] / $applicableWeight) * 100;
            $score += (((float) $answer['score']) / max(1, (float) $answer['max_score'])) * $normalizedWeight;
        }

        $score = round($score, 2);

        return [
            'total_score' => $score,
            'result_label' => $this->resultLabel($score),
        ];
    }

    public function generateSummary(EmployeeEvaluation $evaluation): array
    {
        $evaluation->load(['answers', 'template.jobDescription']);
        $strong = $evaluation->answers->where('is_na', false)->where('score', '>=', 4)->pluck('question_kh')->take(4)->values()->all();
        $weak = $evaluation->answers->where('is_na', false)->where('score', '<=', 2)->pluck('question_kh')->take(4)->values()->all();

        return [
            'strengths' => $strong ?: ['បុគ្គលិកមានការបំពេញការងារល្អនៅផ្នែកសំខាន់ៗ។'],
            'areas_for_improvement' => $weak ?: ['បន្តរក្សាគុណភាពការងារ និងពង្រឹងការតាមដានលទ្ធផល។'],
            'training_skills' => ['ជំនាញតាមតួនាទី', 'ការទំនាក់ទំនង', 'ការដោះស្រាយបញ្ហា'],
            'next_month_goals' => ['កែលម្អលទ្ធផលតាម KPI', 'បន្ថយកំហុស', 'រាយការណ៍វឌ្ឍនភាពជាប្រចាំ'],
            'improvement_plan_suggestion' => 'AI ផ្តល់សំណើសម្រាប់ការអភិវឌ្ឍប៉ុណ្ណោះ។ ការសម្រេចចិត្ត HR/ការងារត្រូវធ្វើដោយអ្នកគ្រប់គ្រង។',
        ];
    }

    private function resultLabel(float $score): string
    {
        return match (true) {
            $score >= 90 => 'ល្អឥតខ្ចោះ',
            $score >= 80 => 'ល្អណាស់',
            $score >= 70 => 'ល្អ',
            $score >= 60 => 'ត្រូវអភិវឌ្ឍ',
            default => 'ត្រូវកែលម្អ',
        };
    }
}
