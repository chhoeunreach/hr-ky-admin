@extends('layouts.master')

@section('title', 'AI Job Description Summary')
@section('action', 'Job Description Confirmation')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @php $jd = $interview->jobDescription; @endphp
        <form method="post" action="{{ route('admin.staff-evaluations.interviews.summary.update', $interview) }}" class="card mb-3">
            @csrf
            @method('PUT')
            <div class="card-header"><h6 class="card-title mb-0">AI Job Description Summary</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">តួនាទី / Job Title</label>
                    <input class="form-control" name="job_title" value="{{ $jd->job_title }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">គោលបំណងការងារ / Main Purpose</label>
                    <textarea class="form-control" name="main_purpose" rows="3">{{ $jd->main_purpose }}</textarea>
                </div>
                <div class="row">
                    @foreach([
                        'responsibilities' => 'ការងារសំខាន់ៗ',
                        'daily_tasks' => 'ការងារប្រចាំថ្ងៃ',
                        'weekly_tasks' => 'ការងារប្រចាំសប្តាហ៍',
                        'monthly_tasks' => 'ការងារប្រចាំខែ',
                        'kpis' => 'KPI / Target',
                        'required_skills' => 'ជំនាញត្រូវការ',
                        'required_knowledge' => 'ចំណេះដឹងត្រូវការ',
                        'tools' => 'ប្រព័ន្ធ / Tools',
                        'common_problems' => 'បញ្ហាត្រូវដោះស្រាយ',
                        'customer_responsibilities' => 'ទំនួលខុសត្រូវអតិថិជន',
                        'reporting_responsibilities' => 'ការរាយការណ៍',
                        'leadership_responsibilities' => 'Leadership',
                        'special_responsibilities' => 'Special Responsibilities',
                    ] as $field => $label)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ $label }}</label>
                            <textarea class="form-control" name="{{ $field }}" rows="5">{{ implode("\n", $jd->{$field} ?? []) }}</textarea>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-outline-primary">Save Edit</button>
            </div>
        </form>
        <form method="post" action="{{ route('admin.staff-evaluations.interviews.confirm', $interview) }}">
            @csrf
            <button class="btn btn-success">Confirm & Generate Evaluation</button>
            <a class="btn btn-secondary" href="{{ route('admin.staff-evaluations.interviews.show', $interview) }}">Ask AI to Improve</a>
        </form>
    </section>
@endsection
