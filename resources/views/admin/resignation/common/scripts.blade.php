<script>
    $('document').ready(function(){

        const selectLanguage = {
            noResults: () => @json(__('index.resignation_no_results')),
            searching: () => @json(__('index.resignation_searching')),
            errorLoading: () => @json(__('index.resignation_employee_load_error')),
        };
        $('#branch_id, #employee_id, #department_id').select2({language: selectLanguage});


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_confirmation') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '.resignationStatusUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let reason = $(this).data('reason');

            $('#statusUpdate .modal-title').text(@json(__('index.resignation_status_update')));
            $('#updateResignationStatus').attr('action',url)
            $('#status').val(status)
            $('#admin_remark').val(reason)


            $('#statusUpdate').modal('show');
        });


        if ($('.nepaliDate').length && $.fn.nepaliDatePicker) {
            $('.nepaliDate').nepaliDatePicker({
                language: 'english', dateFormat: 'YYYY-MM-DD', ndpYear: true,
                ndpMonth: true, ndpYearCount: 20, disableAfter: '2089-12-30',
            });
        }

        const initialBranchId = @json((string) old('branch_id', $filterParameters['branch_id'] ?? $resignationDetail->branch_id ?? auth()->user()?->branch_id ?? ''));
        const initialDepartmentId = @json((string) old('department_id', $filterParameters['department_id'] ?? $resignationDetail->department_id ?? ''));
        const initialEmployeeId = @json((string) old('employee_id', $filterParameters['employee_id'] ?? $resignationDetail->employee_id ?? ''));
        const form = document.getElementById('resignationForm');
        let pendingLoads = 0;
        let departmentLoadId = 0;
        let employeeLoadId = 0;
        let informationLoadId = 0;
        let letterEmployeeId = initialEmployeeId;
        const setLoading = delta => {
            pendingLoads += delta;
            form?.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = pendingLoads > 0; });
        };
        const resetSelect = (selector, label) => {
            $(selector).empty().append(new Option(label, '', true, true));
            $(selector).find('option:first').prop('disabled', true);
            $(selector).trigger('change.select2');
        };
        const status = document.getElementById('resignationEmployeeStatus');
        const clearEmployeeDetails = () => {
            informationLoadId++;
            letterEmployeeId = '';
            document.querySelectorAll('[data-letter-employee-field]').forEach(field => { field.value = ''; });
            if (status) status.textContent = '';
        };
        const updateLetterEmployee = async () => {
            const selectedId = String($('#employee_id').val() || '');
            const fields = document.querySelectorAll('[data-letter-employee-field]');
            if (!fields.length || (selectedId && selectedId === letterEmployeeId)) return;
            clearEmployeeDetails();
            if (!selectedId) return;
            const loadId = ++informationLoadId;
            setLoading(1);
            if (status) status.textContent = @json(__('index.resignation_employee_loading'));
            try {
                const response = await fetch(@json(url('admin/resignation/employee-information')) + '/' + encodeURIComponent(selectedId), {
                    headers: {'Accept': 'application/json'}, credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Employee information unavailable');
                const result = await response.json();
                if (loadId !== informationLoadId || String($('#employee_id').val() || '') !== selectedId) return;
                fields.forEach(field => {
                    const value = result.data[field.dataset.letterEmployeeField] || '';
                    field.value = field.dataset.letterEmployeeField === 'gender' ? value.toLowerCase() : value;
                });
                letterEmployeeId = selectedId;
                if (status) status.textContent = '';
            } catch (error) {
                if (loadId === informationLoadId && status) status.textContent = @json(__('index.resignation_employee_load_error'));
            } finally {
                setLoading(-1);
            }
        };

        const loadDepartments = async (branchId, selectedDepartmentId = '') => {
            const loadId = ++departmentLoadId;
            resetSelect('#department_id', @json(__('index.select_department')));
            $('#department_id').prop('disabled', true);
            if (!branchId) return;
            setLoading(1);
            try {
                const response = await fetch(@json(url('admin/departments/get-All-Departments')) + '/' + encodeURIComponent(branchId), {headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error('Departments unavailable');
                const result = await response.json();
                if (loadId !== departmentLoadId) return;
                (result.data || []).forEach(department => {
                    $('#department_id').append(new Option(department.dept_name, department.id, false, String(department.id) === selectedDepartmentId));
                });
                if (!result.data?.length) resetSelect('#department_id', @json(__('index.no_departments_found')));
            } catch (error) {
                if (loadId === departmentLoadId) resetSelect('#department_id', @json(__('index.error_loading_departments')));
            } finally {
                if (loadId === departmentLoadId) $('#department_id').prop('disabled', false).trigger('change.select2');
                setLoading(-1);
            }
        };
        const loadEmployees = async (selectedEmployeeId = '') => {
            const departmentId = $('#department_id').val();
            const loadId = ++employeeLoadId;
            resetSelect('#employee_id', @json(__('index.select_employee')));
            $('#employee_id').prop('disabled', true);
            if (!departmentId) return;
            setLoading(1);
            try {
                const response = await fetch(@json(url('admin/employees/get-all-employees')) + '/' + encodeURIComponent(departmentId), {headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error('Employees unavailable');
                const result = await response.json();
                if (loadId !== employeeLoadId) return;
                (result.data || []).forEach(employee => {
                    $('#employee_id').append(new Option(employee.name, employee.id, false, String(employee.id) === selectedEmployeeId));
                });
                if (!result.data?.length) resetSelect('#employee_id', @json(__('index.no_employees_found')));
                await updateLetterEmployee();
            } catch (error) {
                if (loadId === employeeLoadId) resetSelect('#employee_id', @json(__('index.error_loading_employees')));
            } finally {
                if (loadId === employeeLoadId) $('#employee_id').prop('disabled', false).trigger('change.select2');
                setLoading(-1);
            }
        };
        $('#employee_id').on('change', updateLetterEmployee);
        $('#department_id').on('change', () => {
            clearEmployeeDetails();
            loadEmployees();
        });
        $('#branch_id').on('change', async () => {
            const branchId = $('#branch_id').val();
            employeeLoadId++;
            resetSelect('#employee_id', @json(__('index.select_employee')));
            $('#employee_id').prop('disabled', true);
            clearEmployeeDetails();
            await loadDepartments(branchId);
        });
        const initializeDropdowns = async () => {
            $('#employee_id').prop('disabled', true);
            await loadDepartments(initialBranchId, initialDepartmentId);
            if ($('#branch_id').length && String($('#branch_id').val() || '') !== initialBranchId) return;
            // Restore saved selections only during the initial load.
            if (initialDepartmentId) {
                $('#department_id').val(initialDepartmentId).trigger('change.select2');
                await loadEmployees(initialEmployeeId);
            }
        };
        // Avoid browser-dependent date ordering by matching the server's final-day rule.
        const requestDate = document.getElementById('resignation_date');
        const lastDay = document.getElementById('last_working_day');
        if (requestDate?.type === 'date' && lastDay) {
            const updateLastDayMinimum = () => {
                if (!requestDate.value) { lastDay.removeAttribute('min'); return; }
                const nextDay = new Date(requestDate.value + 'T00:00:00Z');
                nextDay.setUTCDate(nextDay.getUTCDate() + 1);
                lastDay.min = nextDay.toISOString().slice(0, 10);
            };
            requestDate.addEventListener('change', updateLastDayMinimum);
            updateLastDayMinimum();
        }
        const remark = document.getElementById('admin_remark');
        if (remark) {
            const updateRemarkRequired = () => { remark.required = ['approved', 'onReview', 'rejected'].includes($('#status').val()); };
            $('#status').on('change', updateRemarkRequired);
            updateRemarkRequired();
        }
        const attachment = document.getElementById('document');
        const selectedFile = document.getElementById('resignationSelectedFile');
        attachment?.addEventListener('change', () => {
            if (selectedFile) selectedFile.textContent = attachment.files?.[0]?.name || @json(__('index.resignation_no_file'));
        });
        form?.addEventListener('invalid', event => {
            const field = event.target;
            field.setCustomValidity('');
            if (field.validity.valueMissing) {
                field.setCustomValidity(@json(__('index.resignation_required_field')));
            } else if (field.id === 'last_working_day' && field.validity.rangeUnderflow) {
                field.setCustomValidity(@json(__('index.resignation_last_day_error')));
            } else if (!field.validity.valid) {
                field.setCustomValidity(@json(__('index.resignation_invalid_field')));
            }
        }, true);
        ['input', 'change'].forEach(type => {
            form?.addEventListener(type, event => event.target.setCustomValidity?.(''));
        });
        form?.addEventListener('submit', event => {
            if (pendingLoads > 0) event.preventDefault();
        });
        initializeDropdowns();
    });
</script>
