@extends('layouts.master')

@section('title', 'AI Job Description Interview')
@section('action', 'AI Interview')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <style>
            .ai-eval-layout { display: grid; grid-template-columns: 280px 1fr; gap: 18px; }
            .ai-eval-panel { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #fff; }
            .ai-message { border-radius: 8px; padding: 12px; margin-bottom: 10px; max-width: 84%; }
            .ai-message.ai { background: #eef6ff; color: #0f172a; }
            .ai-message.manager { background: #ecfdf5; color: #064e3b; margin-left: auto; }
            @media (max-width: 767.98px) { .ai-eval-layout { grid-template-columns: 1fr; } .ai-message { max-width: 100%; } }
        </style>
        <div class="ai-eval-layout">
            <aside class="ai-eval-panel">
                <h6>{{ $interview->employee->english_name ?: $interview->employee->name }}</h6>
                <p class="text-muted mb-2">{{ $interview->employee->employee_code ?: $interview->employee->username }}</p>
                <div><strong>Position:</strong> {{ $interview->employee->post?->post_name ?: 'N/A' }}</div>
                <div><strong>Department:</strong> {{ $interview->employee->department?->dept_name ?: 'N/A' }}</div>
                <div><strong>Branch:</strong> {{ $interview->employee->branch?->name ?: 'N/A' }}</div>
                <div><strong>Period:</strong> {{ $interview->evaluation_period }}</div>
                <hr>
                <span class="badge bg-{{ $interview->status === 'ready' ? 'success' : 'primary' }}">{{ ucfirst($interview->status) }}</span>
            </aside>
            <main class="ai-eval-panel">
                <h6>AI Job Description Interview</h6>
                <div class="mb-3">
                    @foreach($interview->messages as $message)
                        <div class="ai-message {{ $message->role === 'ai' ? 'ai' : 'manager' }}">
                            <strong>{{ $message->role === 'ai' ? 'AI' : 'Manager' }}</strong>
                            <div>{{ $message->message }}</div>
                        </div>
                    @endforeach
                </div>
                @if($interview->status === 'ready')
                    <a href="{{ route('admin.staff-evaluations.interviews.summary', $interview) }}" class="btn btn-success mb-3">Review Job Description</a>
                    <form method="post" action="{{ route('admin.staff-evaluations.interviews.answer', $interview) }}">
                        @csrf
                        <label class="form-label">Ask AI to Improve / Add More Detail</label>
                        <textarea class="form-control mb-3" name="answer" rows="4" placeholder="បន្ថែមព័ត៌មានអំពីតួនាទី KPI ឬការងារដែល AI មិនទាន់យល់ច្បាស់..." required></textarea>
                        <button class="btn btn-outline-primary">Continue Interview</button>
                    </form>
                @else
                    <form method="post" action="{{ route('admin.staff-evaluations.interviews.answer', $interview) }}">
                        @csrf
                        <label class="form-label">Manager Answer</label>
                        <textarea class="form-control mb-3" name="answer" rows="5" required autofocus></textarea>
                        <button class="btn btn-primary">Continue</button>
                    </form>
                @endif
            </main>
        </div>
    </section>
@endsection
