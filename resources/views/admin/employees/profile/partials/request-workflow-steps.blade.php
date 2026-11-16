<ol class="employee-request-steps" aria-label="{{ $workflowLabel ?? __('index.request_workflow') }}">
    @foreach([
        1 => ['create', 'create_help'],
        2 => ['save', 'save_help'],
        3 => ['print', 'print_help'],
    ] as $step => [$stepKey, $helpKey])
        <li class="{{ $step < $currentStep ? 'is-complete' : ($step === $currentStep ? 'is-current' : '') }}" @if($step === $currentStep) aria-current="step" @endif>
            <span class="employee-request-step-number" aria-hidden="true">{{ $step < $currentStep ? '✓' : $step }}</span>
            @php($prefix = $stepTranslationPrefix ?? 'request_step')
            <div><strong>{{ __('index.' . $prefix . '_' . $stepKey) }}</strong><span>{{ __('index.' . $prefix . '_' . $helpKey) }}</span></div>
        </li>
    @endforeach
</ol>
