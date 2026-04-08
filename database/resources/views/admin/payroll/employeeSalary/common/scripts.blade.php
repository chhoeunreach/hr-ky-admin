
<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.error').hide();

        $('body').on('change','#salaryCycle',function (event) {
            event.preventDefault();
            let salaryCycle = $(this).val()
            let employeeId = $(this).data('employee')
            let currentCycle = $(this).data('current')
            let url = "{{url('admin/employee-salaries/update-cycle')}}" +'/' + employeeId + '/' + salaryCycle;
            Swal.fire({
                title: '{{ __('index.confirm_change_cycle') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }else{
                    $(this).val(currentCycle);
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

        $('.deleteEmployeeSalary').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_delete_payroll') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                // width:'1000px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })


        const fiscalYearSelect = document.getElementById('fiscal_year_id');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        fiscalYearSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const startDate = selectedOption.getAttribute('data-start-date');
            const endDate = selectedOption.getAttribute('data-end-date');

            if (startDate) {
                dateFromInput.value = startDate;
            }
            if (endDate) {
                dateToInput.value = endDate;
            }
        });
    });


</script>
