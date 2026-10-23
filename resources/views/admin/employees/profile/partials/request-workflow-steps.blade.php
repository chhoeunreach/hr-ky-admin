<ol class="employee-request-steps" aria-label="{{ __('index.request_workflow') }}">
    @foreach([
        1 => ['request_step_create', 'request_step_create_help'],
        2 => ['request_step_save', 'request_step_save_help'],
        3 => ['request_step_print', 'request_step_print_help'],
    ] as $step => [$title, $help])
        <li class="{{ $step < $currentStep ? 'is-complete' : ($step === $currentStep ? 'is-current' : '') }}" @if($step === $currentStep) aria-current="step" @endif>
            <span class="employee-request-step-number" aria-hidden="true">{{ $step < $currentStep ? '✓' : $step }}</span>
            <div><strong>{{ __('index.' . $title) }}</strong><span>{{ __('index.' . $help) }}</span></div>
        </li>
    @endforeach
</ol>
