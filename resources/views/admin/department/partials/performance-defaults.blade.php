<details class="mt-3">
    <summary class="text-primary">{{ __('index.department_defaults_preview') }}</summary>
    <h6 class="mt-3">{{ __('index.responsibilities') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>{{ __('index.title') }}</th><th>{{ __('index.description') }}</th><th>{{ __('index.target') }}</th><th>{{ __('index.department_defaults_weight') }}</th></tr></thead>
            <tbody>
            @foreach($defaults['responsibilities'] as $responsibility)
                <tr>
                    <td class="text-wrap">{{ $responsibility['title'] }}</td>
                    <td class="text-wrap" style="min-width: 240px">{{ $responsibility['description'] }}</td>
                    <td class="text-wrap">{{ $responsibility['kpi_target'] }}</td>
                    <td>{{ $responsibility['weight'] }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h6 class="mt-3">{{ __('index.department_defaults_kpis') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>{{ __('index.name') }}</th><th>{{ __('index.department_defaults_measurement') }}</th><th>{{ __('index.target') }}</th><th>{{ __('index.department_defaults_weight') }}</th></tr></thead>
            <tbody>
            @foreach($defaults['kpis'] as $kpi)
                <tr>
                    <td class="text-wrap">{{ $kpi['name'] }}</td>
                    <td class="text-wrap" style="min-width: 260px; white-space: pre-line">{{ $kpi['description'] }}</td>
                    <td>{{ $kpi['target_value'] }}%</td>
                    <td>{{ $kpi['weight'] }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h6 class="mt-3">{{ __('index.evaluation_criteria') }}</h6>
    <p class="small text-muted">{{ __('index.department_evaluation_defaults_help') }}</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>{{ __('index.criteria') }}</th><th>{{ __('index.standard') }}</th><th>{{ __('index.max') }}</th></tr></thead>
            <tbody>
            @foreach($defaults['evaluation_criteria'] as $criterion)
                <tr>
                    <td class="text-wrap">{{ $criterion['criteria'] }}</td>
                    <td class="text-wrap" style="min-width: 260px; white-space: pre-line">{{ $criterion['description'] }}</td>
                    <td>{{ $criterion['max_score'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</details>
