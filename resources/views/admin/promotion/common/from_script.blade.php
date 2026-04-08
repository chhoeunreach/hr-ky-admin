<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){



        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.nepali_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

    });

    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });

    $(document).ready(function () {
        const loadDepartments = async () => {
            const selectedBranchId = $('#branch_id').val();
            if (!selectedBranchId) return;

            try {
                $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                $('#post_id').empty().append('<option selected disabled>{{ __("index.select_post") }}</option>');

                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length === 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }

                response.data.forEach(data => {
                    $('#department_id').append(`<option value="${data.id}">${data.dept_name}</option>`);
                });
            } catch (error) {
                console.error('Error loading departments:', error);
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const loadEmployeesAndPosts = async () => {
            const selectedDepartmentId = $('#department_id').val();
            if (!selectedDepartmentId) return;

            try {
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                $('#post_id').empty().append('<option selected disabled>{{ __("index.select_post") }}</option>');

                const response = await fetch(`{{ url('admin/promotion/get-employees-posts') }}/${selectedDepartmentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                const data = await response.json();

                console.log(data);
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        $('#employee_id').append(`<option value="${user.id}">${user.name}</option>`);
                    });
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }

                if (data.posts && data.posts.length > 0) {
                    data.posts.forEach(post => {
                        $('#post_id').append(`<option value="${post.id}">${post.post_name}</option>`);
                    });
                } else {
                    $('#post_id').append('<option disabled>{{ __("index.no_posts_found") }}</option>');
                }
            } catch (error) {
                $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                $('#post_id').append('<option disabled>{{ __("index.error_loading_posts") }}</option>');
            }
        };

        const loadEmployeeData = async () => {
            const selectedEmployeeId = $('#employee_id').val();
            if (!selectedEmployeeId) return;
            let oldPostId = "{{  $promotionDetail->old_post_id ?? '' }}";

            try {

                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/transfer/get-user-data') }}" + '/' + selectedEmployeeId,
                }).done(function (data) {


                    $('#old_post_id').append('<option ' + ((data.id == oldPostId) ? "selected" : '') + ' value="' + data.post_id + '" >' + data.post + '</option>');
                });


            } catch (error) {

            }
        };
        // Load departments when branch is selected
        $('#branch_id').change(loadDepartments);

        // Load employees and posts when department is selected
        $('#department_id').change(loadEmployeesAndPosts);

        $('#employee_id').change(loadEmployeeData);
    });

    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });

</script>
