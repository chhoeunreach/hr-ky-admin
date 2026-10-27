        <div class="card" id="employeeSalaryResults" aria-busy="false">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">{{ __('index.employee_salaries') }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-salary-refresh>
                        <i class="link-icon" data-feather="refresh-cw"></i> {{ __('index.refresh') }}
                    </button>
                @can('create_salary')
                    <a href="{{ route('admin.employee-salaries.import-csv.show') }}" class="btn btn-primary btn-sm">
                        <i class="link-icon" data-feather="upload"></i> {{ __('index.csv_import') }}
                    </a>
                @endcan
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="name_direction" form="employeeSalaryFilters" value="{{ $filterParameters['name_direction'] ?? 'asc' }}">
                @include('admin.payroll.employeeSalary.common.toolkit')
                @php
                    $rowOffset = method_exists($employeeLists, 'firstItem') ? ($employeeLists->firstItem() ?? 1) : 1;
                @endphp
                <div class="table-responsive salary-table-scroll">
                    <table id="employeeSalaryTable" class="table align-middle salary-professional-table">
                        <thead>
                        <tr>
                            @foreach($salaryColumns as $column => $label)
                                <th data-salary-column="{{ $column }}" data-default-visible="{{ in_array($column, $defaultSalaryColumns, true) ? '1' : '0' }}"
                                    @if($column === 'employee') aria-sort="{{ ($filterParameters['name_direction'] ?? 'asc') === 'desc' ? 'descending' : 'ascending' }}" @endif
                                    @if(!in_array($column, $defaultSalaryColumns, true)) hidden @endif
                                    @class(['text-end' => in_array($column, ['salary', 'basic_salary', 'allowance'], true)])>
                                    @if($column === 'employee')
                                        <a id="salaryNameSort" class="salary-name-sort" data-salary-sort="{{ ($filterParameters['name_direction'] ?? 'asc') === 'desc' ? 'asc' : 'desc' }}"
                                           href="{{ route('admin.employee-salaries.index', array_merge(request()->except(['page', 'export', 'columns', 'orientation']), ['name_direction' => ($filterParameters['name_direction'] ?? 'asc') === 'desc' ? 'asc' : 'desc'])) }}">
                                            {{ $label }} <span aria-hidden="true">{{ ($filterParameters['name_direction'] ?? 'asc') === 'desc' ? '↓' : '↑' }}</span>
                                        </a>
                                    @else
                                        {{ $label }}
                                    @endif
                                </th>
                            @endforeach
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeLists as $key => $value)
                                @php $savedSalary = $employeeSalaries->get($value->employee_id); @endphp
                                <tr data-salary-row>
                                    <td hidden>{{ $rowOffset + $key }}</td>
                                    <td>
                                        @can('employee.profile.view')
                                            <a class="salary-employee-profile" href="{{ route('admin.employees.profile.show', ['employee' => $value->employee_id, 'tab' => 'salary']) }}">
                                        @else
                                            <div class="salary-employee-profile">
                                        @endcan
                                            <img class="salary-employee-avatar" src="{{ $value->avatar_url }}" alt="" width="52" height="52" loading="lazy"
                                                 style="--employee-avatar-ring: {{ \App\Support\EmployeeAvatar::borderColor($value->employee_id) }};"
                                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">
                                            <span class="salary-employee-identity">
                                                <span class="fw-semibold">{{ $value->employee_name }}</span>
                                                <small class="text-muted" title="{{ __('index.username') }}">{{ $value->username ?: '—' }}</small>
                                            </span>
                                        @can('employee.profile.view')
                                            </a>
                                        @else
                                            </div>
                                        @endcan
                                    </td>
                                    <td>{{ $value->working_life }}</td>
                                    <td class="text-end">
                                        @if($savedSalary)
                                            <strong>{{ \App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol() }}{{ number_format((float) $savedSalary->annual_salary / ($savedSalary->payment_type === 'weekly' ? 52 : 12), 2) }}</strong>

                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end salary-amount">{{ $savedSalary ? number_format((float) ($savedSalary->payment_type === 'weekly' ? $savedSalary->weekly_basic_salary : $savedSalary->monthly_basic_salary), 2) : '—' }}</td>
                                    <td class="text-end salary-amount">{{ $savedSalary ? number_format((float) ($savedSalary->payment_type === 'weekly' ? $savedSalary->weekly_fixed_allowance : $savedSalary->monthly_fixed_allowance), 2) : '—' }}</td>
                                    <td>{{ $savedSalary ? __('index.'.$savedSalary->payment_type) : '—' }}</td>
                                    <td><span class="badge rounded-pill {{ $savedSalary ? 'bg-success' : 'bg-light text-secondary border' }}">{{ __($savedSalary ? 'index.salary_setup_ready' : 'index.salary_setup_missing') }}</span></td>
                                    <td class="text-center" hidden>{{ in_array($value->marital_status, ['single', 'married'], true) ? __('index.marital_status_' . $value->marital_status) : ucfirst($value->marital_status ?? '') }}</td>
{{--                        <td>{{ number_format($value->salary) }}</td>--}}
                            <td class="text-center" hidden>
                                @if($value->account_id)
                                <select class="form-select form-select-sm salary-cycle-select"
                                        name="salary_cycle"
                                        data-employee="{{$value->employee_id}}"
                                        data-current="{{$value->salary_cycle}}"
                                        aria-label="{{ __('index.salary_cycle') }}"
                                        @cannot('change_salary_cycle') disabled @endcannot
                                >
                                    @if(!$value->salary_cycle)
                                        <option value="" selected disabled>{{ __('index.select_salary_cycle') }}</option>
                                    @endif
                                    @foreach(\App\Models\EmployeeAccount::SALARY_CYCLE as $salaryCycle)
                                            <option value="{{$salaryCycle}}" {{$value->salary_cycle == $salaryCycle ? 'selected' : '' }}>
                                                {{ __('index.' . $salaryCycle) }}
                                            </option>
                                    @endforeach
                                </select>
                                @can('change_salary_cycle')
                                    <form method="POST" class="salary-cycle-form d-none" data-url-template="{{ route('admin.employee-salaries.update-salary-cycle', [$value->employee_id, '__CYCLE__']) }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endcan
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center" hidden>{{ $value->salary_group_name ?: '—' }}</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="payslipDropdown{{ $value->employee_id }}"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="{{ __('index.action') }}"
                                    aria-label="{{ __('index.action') }}"
                                ><i data-feather="more-vertical" style="width:16px;height:16px;"></i></button>

                                <div class="dropdown-menu p-0" aria-labelledby="payslipDropdown{{ $value->employee_id }}">
                                    <ul class="list-unstyled mb-0">
                                        @php
                                            $employeeSalaryStatus = $savedSalary ? 1 : 0
                                        @endphp

                                        @if($employeeSalaryStatus == 0)
                                            @can('add_salary')
                                                <li>
                                                    <a class="dropdown-item py-2" data-open-employee-form data-employee-form-title="{{ __('index.add_salary') }} · {{ $value->employee_name }}" title="{{ __('index.add_salary') }}"
                                                        href="{{ route('admin.employee-salaries.add', $value->employee_id) }}">
                                                        @lang('index.add_salary')
                                                    </a>
                                                </li>
                                            @endcan
                                        @else
                                            @can('edit_salary')
                                                <li>
                                                    <a class="dropdown-item py-2" data-open-employee-form data-employee-form-title="{{ __('index.edit_salary') }} · {{ $value->employee_name }}" title="{{ __('index.edit_salary') }}"
                                                        href="{{ route('admin.employee-salaries.edit-salary', $value->employee_id) }}">
                                                        {{ __('index.edit_salary') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('salary_increment')
                                                <li>
                                                    <a class="dropdown-item py-2" data-open-employee-form data-employee-form-title="{{ __('index.increase_salary') }} · {{ $value->employee_name }}" title="{{ __('index.increase_salary') }}"
                                                        href="{{route('admin.employee-salaries.increase-salary',$value->employee_id)}}">
                                                        {{ __('index.increase_salary') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('show_salary_history')
                                                <li>
                                                    <a href="{{route('admin.employee-salaries.salary-revise-history.show',$value->employee_id)}}"
                                                        class="dropdown-item py-2" data-open-salary-history
                                                        title="{{ __('index.salary_review_history') }}">
                                                        {{ __('index.salary_review_history') }}
                                                    </a>
                                                </li>
                                            @endcan
                                                @can('delete_salary')
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#employeeSalaryDeleteModal"
                                                                data-delete-url="{{ route('admin.employee-salaries.delete-salary', $value->employee_id) }}" data-employee-name="{{ $value->employee_name }}">{{ __('index.delete') }}</button>
                                                    </li>
                                                @endcan
                                        @endif

                                    </ul>
                                </div>
                            </td>
                            @empty
                                <tr>
                                    <td colspan="{{ count($defaultSalaryColumns) + 1 }}" data-salary-empty>
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
                <div class="salary-table-footer">
                    <span>{{ __('index.showing') }} {{ $employeeLists->count() ? $rowOffset : 0 }} {{ __('index.to') }} {{ $employeeLists->count() ? $rowOffset + $employeeLists->count() - 1 : 0 }} {{ __('index.of') }} {{ method_exists($employeeLists, 'total') ? $employeeLists->total() : $employeeLists->count() }} {{ __('index.entries') }}</span>
                    @if(method_exists($employeeLists, 'links')){{ $employeeLists->links() }}@endif
                </div>
            </div>
        </div>
