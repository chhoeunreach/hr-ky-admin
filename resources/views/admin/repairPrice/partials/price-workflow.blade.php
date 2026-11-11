<div class="repair-price-workflow" data-repair-workflow>
    <input type="hidden" name="price_entry_mode" value="single">
    <div class="repair-entry-modes" role="group" aria-label="{{ __('repair.entry_mode') }}">
        <button type="button" class="repair-entry-mode is-active" data-entry-mode="single" aria-pressed="true">
            <strong>{{ __('repair.single_entry') }}</strong><span>{{ __('repair.single_entry_help') }}</span>
        </button>
        <button type="button" class="repair-entry-mode" data-entry-mode="bulk" aria-pressed="false">
            <strong>{{ __('repair.bulk_entry') }}</strong><span>{{ __('repair.bulk_entry_help') }}</span>
        </button>
    </div>
    <ol class="repair-workflow-steps" aria-label="{{ __('repair.price_setup') }}">
        @foreach(['service_parts', 'device_models_step', 'prices_step', 'review_step'] as $step => $label)
            <li><button type="button" data-go-step="{{ $step + 1 }}" @if($step === 0) aria-current="step" @endif><span>{{ $step + 1 }}</span>{{ __('repair.'.$label) }}</button></li>
        @endforeach
    </ol>
    <div class="repair-workflow-context small text-muted" aria-live="polite" data-workflow-context
         data-label="{{ __('repair.selection_summary') }}"></div>
</div>
