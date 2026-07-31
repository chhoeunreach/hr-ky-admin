@extends('layouts.master')
@section('title', __('index.advance_salary'))
@section('action', __('index.create'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.payroll.advanceSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.create') }} {{ __('index.advance_salary') }}</h6>
            </div>

            <div class="card-body">
                <form class="forms-sample"
                      action="{{ route('admin.advance-salaries.store') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option selected disabled>{{ __('index.select_employee') }}</option>
                                @foreach($employees ?? [] as $employee)
                                    <option value="{{ $employee->id }}"
                                            data-branch-id="{{ $employee->branch_id }}"
                                            data-branch-name="{{ $employee->branch->name ?? '' }}"
                                            data-department-id="{{ $employee->department_id }}"
                                            data-department-name="{{ $employee->department->dept_name ?? '' }}"
                                        {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ ucfirst($employee->name) }}{{ $employee->username ? ' (' . $employee->username . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="branch_display" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="branch_display"
                                   value="{{ old('branch_name') }}"
                                   placeholder="{{ __('index.select_branch') }}"
                                   readonly>
                            <input type="hidden" id="branch_id" name="branch_id" value="{{ old('branch_id') }}">
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="department_display" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="department_display"
                                   value="{{ old('department_name') }}"
                                   placeholder="{{ __('index.select_department') }}"
                                   readonly>
                            <input type="hidden" id="department_id" name="department_id" value="{{ old('department_id') }}">
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="requested_amount" class="form-label">{{ __('index.requested_amount') }} <span style="color: red">*</span></label>
                            <input type="number"
                                   min="10"
                                   step="0.01"
                                   class="form-control"
                                   id="requested_amount"
                                   name="requested_amount"
                                   value="{{ old('requested_amount') }}"
                                   required
                                   autocomplete="off"
                                   placeholder="{{ __('index.requested_amount') }}">
                            @error('requested_amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-8 mb-4">
                            <label for="description" class="form-label">{{ __('index.description') }} <span style="color: red">*</span></label>
                            <textarea class="form-control" name="description" id="description" rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 mb-4">
                            <label for="image-uploadify" class="form-label">{{ __('index.attachments') }}</label>
                            <input id="image-uploadify"
                                   type="file"
                                   name="documents[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls"
                                   multiple />
                            @error('documents')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @error('documents.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 text-end">
                            <a href="{{ route('admin.advance-salaries.index') }}" class="btn btn-secondary me-2">
                                {{ __('index.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="link-icon" data-feather="plus"></i>
                                {{ __('index.create') }} {{ __('index.advance_salary') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/imageuploadify.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#employee_id').select2();
            $("#image-uploadify").imageuploadify();

            const syncEmployeeBranchAndDepartment = () => {
                const selectedEmployee = $('#employee_id').find(':selected');
                const branchId = selectedEmployee.data('branch-id') || '';
                const branchName = selectedEmployee.data('branch-name') || '';
                const departmentId = selectedEmployee.data('department-id') || '';
                const departmentName = selectedEmployee.data('department-name') || '';

                $('#branch_id').val(branchId);
                $('#branch_display').val(branchName);
                $('#department_id').val(departmentId);
                $('#department_display').val(departmentName);
            };

            $('#employee_id').on('change', syncEmployeeBranchAndDepartment);
            syncEmployeeBranchAndDepartment();
        });
    </script>
@endsection
