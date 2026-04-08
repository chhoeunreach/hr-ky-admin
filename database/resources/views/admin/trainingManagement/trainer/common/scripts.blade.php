<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $('#attachment').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })




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


        $('#awarded_date_np').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

    });

    $('.toggleStatus').change(function (event) {
        event.preventDefault();
        let status = $(this).prop('checked') === true ? 1 : 0;
        let href = $(this).attr('href');
        Swal.fire({
            title: '{{ __('index.change_status_confirm') }}',
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


    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });

    $('#branch_id').change(function() {
        let selectedBranchId = $('#branch_id option:selected').val();

        let departmentId = "{{  $trainerDetail->department_id ?? '' }}";
        console.log('selected department '+departmentId);
        $('#department_id').empty();
        if (selectedBranchId) {
            $.ajax({
                type: 'GET',
                url: "{{ url('admin/departments/get-All-Departments') }}" + '/' + selectedBranchId ,
            }).done(function(response) {

                if(!departmentId){
                    $('#department_id').append('<option disabled  selected >{{ __('index.select_department') }}</option>');
                }
                response.data.forEach(function(data) {
                    $('#department_id').append('<option ' + ((data.id == departmentId) ? "selected" : '') + ' value="'+data.id+'" >'+data.dept_name+'</option>');
                });
            });
        }
        getEmployee();
    }).trigger('change');


    function getEmployee(){
        let defaultDepartmentId = "{{ $trainerDetail->department_id ?? '' }}";

        let departmentId =  $('#department_id option:selected').val() || defaultDepartmentId;

        let employeeId = "{{  $trainerDetail->employee_id ?? '' }}";

        $('#employee_id').empty();
        if (departmentId) {
            $.ajax({
                type: 'GET',
                url: "{{ url('admin/users/get-all-employees') }}" + '/' + departmentId ,
            }).done(function(response) {

                if(!employeeId){
                    $('#employee_id').append('<option disabled  selected >{{ __('index.select_employee') }}</option>');
                }
                response.data.forEach(function(data) {
                    $('#employee_id').append('<option ' + ((data.id == employeeId) ? "selected" : '') + ' value="'+data.id+'" >'+data.name+'</option>');
                });
            });
        }
    }
    $('#department_id').change(function() {

        getEmployee();

    }).trigger('change');

    $('#trainer_type').change(function (){

        let internalType = @json($internal);
        let externalType = @json($external);
        let selectedType= $('#trainer_type option:selected').val();

        if(selectedType === internalType){
            $('.externalTrainer').find('input').val('');

            $('.externalTrainer').addClass('d-none');
            $('.internalTrainer').removeClass('d-none');
        }else if(selectedType === externalType){
            $('.internalTrainer').find('select').val('');

            $('.internalTrainer').addClass('d-none');
            $('.externalTrainer').removeClass('d-none');

        }
    })

    document.addEventListener('DOMContentLoaded', function() {

        const inputFields = document.querySelectorAll('input:not([type="submit"]), select');


        inputFields.forEach(input => {
            input.addEventListener('keypress', function(e) {

                if (e.key === 'Enter') {
                    e.preventDefault();


                    const currentIndex = Array.from(inputFields).indexOf(this);
                    const nextInput = inputFields[currentIndex + 1];


                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            });
        });
    });
</script>
