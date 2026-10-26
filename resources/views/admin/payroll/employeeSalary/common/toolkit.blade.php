<div class="salary-table-toolkit" id="employeeSalaryToolkit" data-copy-success="{{ __('index.copied') }}" data-copy-error="{{ __('index.copy_failed') }}">
    <div class="salary-table-length">
        <label for="salaryRows">{{ __('index.show') }}</label>
        <select class="form-select form-select-sm" id="salaryRows" name="per_page" form="employeeSalaryFilters">
            @foreach(['10', '25', '50', '100', 'all'] as $size)
                <option value="{{ $size }}" @selected((string) $filterParameters['per_page'] === $size)>{{ $size === 'all' ? __('index.all') : $size }}</option>
            @endforeach
        </select>
        <span>{{ __('index.entries') }}</span>
    </div>
    <div class="salary-table-tools" role="group" aria-label="{{ __('index.salary_table_tools') }}">
        @can('export_salary')
            <button class="btn btn-outline-secondary btn-sm" type="button" data-salary-copy><i data-feather="copy"></i>{{ __('index.copy') }}</button>
            @foreach(['csv' => 'export_csv', 'excel' => 'export_excel'] as $format => $label)
                <a class="btn btn-outline-secondary btn-sm" data-salary-export="{{ $format }}" href="{{ request()->fullUrlWithQuery(['export' => $format, 'page' => null]) }}"><i data-feather="file-text"></i>{{ __('index.'.$label) }}</a>
            @endforeach
            <a class="btn btn-outline-secondary btn-sm" data-salary-export="print" href="{{ request()->fullUrlWithQuery(['export' => 'print', 'page' => null]) }}" target="_blank" rel="noopener"><i data-feather="printer"></i>{{ __('index.print') }}</a>
        @endcan
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i data-feather="columns"></i>{{ __('index.column_visibility') }}</button>
            <div class="dropdown-menu salary-column-menu">
                <div data-salary-columns></div>
                <hr class="dropdown-divider">
                <button type="button" class="dropdown-item small" data-salary-reset-columns>{{ __('index.salary_reset_columns') }}</button>
            </div>
        </div>
        @can('export_salary')
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="file-text"></i>{{ __('index.export_pdf') }}</button>
                <div class="dropdown-menu">
                    @foreach(['landscape', 'portrait'] as $orientation)
                        <a class="dropdown-item" data-salary-export="pdf" data-orientation="{{ $orientation }}" href="{{ request()->fullUrlWithQuery(['export' => 'pdf', 'orientation' => $orientation, 'page' => null]) }}" target="_blank" rel="noopener">{{ __('index.'.$orientation) }}</a>
                    @endforeach
                </div>
            </div>
        @endcan
    </div>
    <div class="salary-table-search">
        <label for="salarySearch" class="visually-hidden">{{ __('index.search') }}</label>
        <input type="search" class="form-control form-control-sm" id="salarySearch" name="q" form="employeeSalaryFilters" value="{{ $filterParameters['q'] }}" maxlength="200" placeholder="{{ __('index.search') }}…">
    </div>
</div>
<p class="salary-toolkit-status small text-muted" role="status" aria-live="polite" data-salary-status></p>
