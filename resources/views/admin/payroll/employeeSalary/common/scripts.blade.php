
<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $(document).ready(function () {
        $('#branch_id').select2();
        $('#department_id').select2();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.error').hide();

        $('body').on('change', '.salary-cycle-select', function () {
            const select = this;
            const salaryCycle = select.value;
            const currentCycle = select.dataset.current;
            const form = select.closest('td').querySelector('.salary-cycle-form');
            if (!form || salaryCycle === currentCycle) return;
            Swal.fire({
                title: '{{ __('index.confirm_change_cycle') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = form.dataset.urlTemplate.replace('__CYCLE__', encodeURIComponent(salaryCycle));
                    form.submit();
                } else {
                    select.value = currentCycle;
                }
            })
        })

        $('.generatePayroll').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_generate_payroll') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })

        $('.deleteEmployeeSalaryForm').on('submit', function (event) {
            event.preventDefault();
            const form = this;
            Swal.fire({
                title: '{{ __('index.delete') }} {{ __('index.employee_salary') }}?',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                // width:'1000px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        })



    });


    $(document).ready(function () {
        const loadDepartments = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;
            const departmentId = @json($filterParameters['department_id'] ?? old('department_id'));

            const department = $('#department_id');
            department.empty().append('<option value="">{{ __('index.all') }}</option>');
            if (!selectedBranchId) {
                department.trigger('change.select2');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (response.data && response.data.length > 0) {
                    response.data.forEach(item => {
                        department.append(new Option(item.dept_name, item.id, false, String(item.id) === String(departmentId)));
                    });
                }
                department.trigger('change.select2');

            } catch (error) {
                department.append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };



        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').on('change', loadDepartments);
            $('#branch_id').trigger('change');
        } else {
            loadDepartments();
        }
    });

</script>
