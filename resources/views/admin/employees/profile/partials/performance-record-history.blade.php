<details>
    <summary>{{ __('index.print_record_details') }}</summary>
    <div class="row g-3 mt-1">
        @foreach(['old_values' => 'asset_activity_before', 'new_values' => 'asset_activity_after'] as $property => $heading)
            <div class="col-md-6">
                <strong>{{ __('index.'.$heading) }}</strong>
                <dl class="small mt-2">
                    @foreach(['title' => 'title', 'name' => 'name', 'description' => 'description', 'kpi_target' => 'kpi_target', 'target_value' => 'target', 'actual_value' => 'actual', 'unit' => 'unit', 'weight' => 'weight', 'achievement_percentage' => 'performance_achievement', 'score' => 'score', 'manager_comment' => 'manager_comment', 'status' => 'status', 'start_date' => 'start_date', 'end_date' => 'end_date'] as $field => $label)
                        @if(array_key_exists($field, $log->{$property} ?? []))
                            <dt>{{ __('index.'.$label) }}</dt>
                            <dd style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $log->{$property}[$field] ?? '—' }}</dd>
                        @endif
                    @endforeach
                </dl>
            </div>
        @endforeach
    </div>
</details>
