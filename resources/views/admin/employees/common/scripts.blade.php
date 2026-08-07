<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce.js') }}"></script>
<script src="{{ asset('assets/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('assets/jquery-validation/additional-methods.min.js') }}"></script>

<script>
    $(document).ready(function () {

        $("#department").select2({});
        $("#branch").select2({});
        $("#post").select2({});
        $("#supervisor").select2({});
        $("#employment_type").select2({});
        $("#officeTime").select2({});
        $("#per_page").select2({minimumResultsForSearch: Infinity});
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on('click', '.changePassword', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $('.modal-title').html('{{ __('index.user_change_password') }}');
            $('#changePassword').attr('action', url);
            $('#statusUpdate').modal('show');
        });

        $(document).on('change', '.toggleStatus', function (event) {
            event.preventDefault();
            const toggle = $(this);
            const row = toggle.closest('tr');
            let status = $(this).prop('checked') == true ? 1 : 0;
            let href = $(this).attr('href');

            Swal.fire({
                title: '{{ __('index.confirm_change_status') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    toggle.prop('disabled', true);

                    fetch(href, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(@json(__('message.something_went_wrong')));
                            }

                            return response.json();
                        })
                        .then((response) => {
                            const updatedStatus = Number(response.is_active);
                            toggle.prop('checked', updatedStatus === 1);

                            const activeFilter = $('#is_active').val();
                            if (activeFilter !== '' && Number(activeFilter) !== updatedStatus) {
                                row.fadeOut(180, function () {
                                    $(this).remove();
                                });
                            }
                        })
                        .catch((error) => {
                            (status === 0) ? toggle.prop('checked', true) : toggle.prop('checked', false);

                            Swal.fire({
                                icon: 'error',
                                title: error.message,
                                padding: '10px 50px 10px 50px',
                            });
                        })
                        .finally(() => {
                            toggle.prop('disabled', false);
                        });
                } else if (result.isDenied) {
                    (status === 0) ? toggle.prop('checked', true) : toggle.prop('checked', false)
                }
            })
        });

        $(document).on('change', '.toggleHolidayCheckIn', function (event) {
            event.preventDefault();
            const toggle = $(this);
            let status = $(this).prop('checked') == true ? 1 : 0;
            let href = $(this).attr('href');

            Swal.fire({
                title: '{{ __('index.confirm_change_holiday_checkin') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    (status === 0) ? toggle.prop('checked', true) : toggle.prop('checked', false)
                }
            })
        });

        $(document).on('click', '.deleteEmployee', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_delete_employee') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $(document).on('click', '.forceLogOut', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_force_logout') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $(document).on('click', '.changeWorkPlace', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_change_workplace') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        let employeeSearchTimer = null;
        let employeeListController = null;
        let employeeListRequestId = 0;

        const initEmployeeListControls = () => {
            const perPage = $('#per_page');

            if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                perPage.select2({minimumResultsForSearch: Infinity});
            }
        };

        const replaceEmployeeResults = (doc) => {
            const currentTableBody = document.querySelector('#employeeTable tbody');
            const nextTableBody = doc.querySelector('#employeeTable tbody');
            const currentPagination = document.querySelector('#employeeListSection .dataTables_paginate');
            const nextPagination = doc.querySelector('#employeeListSection .dataTables_paginate');

            if (!currentTableBody || !nextTableBody) {
                return false;
            }

            currentTableBody.innerHTML = nextTableBody.innerHTML;

            if (currentPagination && nextPagination) {
                currentPagination.innerHTML = nextPagination.innerHTML;
            }

            if (window.feather) {
                feather.replace();
            }

            return true;
        };

        const refreshEmployeeList = (options = {}) => {
            const form = document.getElementById('employeeFilterForm');
            const listSection = document.getElementById('employeeListSection');
            const tableBody = document.querySelector('#employeeTable tbody');

            if (!form || !listSection) {
                form?.submit();
                return;
            }

            const params = new URLSearchParams(new FormData(form));
            const requestUrl = `${form.action}?${params.toString()}`;
            const requestId = ++employeeListRequestId;

            if (employeeListController) {
                employeeListController.abort();
            }

            employeeListController = new AbortController();

            if (tableBody) {
                tableBody.style.opacity = '0.6';
            }

            fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: employeeListController.signal
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== employeeListRequestId) {
                        return;
                    }

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (!replaceEmployeeResults(doc)) {
                        window.location.href = requestUrl;
                        return;
                    }

                    window.history.replaceState({}, '', requestUrl);
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    form.submit();
                })
                .finally(() => {
                    if (requestId === employeeListRequestId) {
                        if (tableBody) {
                            tableBody.style.opacity = '1';
                        }
                        employeeListController = null;
                    }
                });
        };

        $(document).on('submit', '#employeeFilterForm', function (event) {
            event.preventDefault();
            refreshEmployeeList();
        });

        $(document).on('change', '#per_page', function () {
            refreshEmployeeList();
        });

        $(document).on('input', '#employeeListSearch', function () {
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = this.value;
            }

            clearTimeout(employeeSearchTimer);
            employeeSearchTimer = setTimeout(() => {
                refreshEmployeeList({focusSearch: true});
            }, 300);
        });

        $(document).on('click', '#employeeListSection .pagination a', function (event) {
            event.preventDefault();

            const form = document.getElementById('employeeFilterForm');
            const listSection = document.getElementById('employeeListSection');
            const tableBody = document.querySelector('#employeeTable tbody');
            const requestUrl = this.href;
            const requestId = ++employeeListRequestId;

            if (!form || !listSection) {
                window.location.href = requestUrl;
                return;
            }

            if (employeeListController) {
                employeeListController.abort();
            }

            employeeListController = new AbortController();
            if (tableBody) {
                tableBody.style.opacity = '0.6';
            }

            fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: employeeListController.signal
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== employeeListRequestId) {
                        return;
                    }

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (!replaceEmployeeResults(doc)) {
                        window.location.href = requestUrl;
                        return;
                    }

                    window.history.replaceState({}, '', requestUrl);
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    window.location.href = requestUrl;
                })
                .finally(() => {
                    if (requestId === employeeListRequestId) {
                        if (tableBody) {
                            tableBody.style.opacity = '1';
                        }
                        employeeListController = null;
                    }
                });
        });

        initEmployeeListControls();


    });
    $(document).on('click', '#export_employee', function (e) {
        e.preventDefault();
        let route = $(this).data('href');

        // Create a form data object with all current filter values
        let filtered_params = {
            employee_name: $('#employeeName').val(),
            search: $('#search').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            branch_id: $('#branch').val(),
            department_id: $('#department').val(),
            post_id: $('#post').val(),
            is_active: $('#is_active').val(),
            per_page: $('#per_page').val(),
            action: 'export'  // This should match what the controller is checking for
        };

        let queryString = $.param(filtered_params);
        let url = route + '?' + queryString;
        window.open(url, '_blank');
    });
    function getEmployeeFilterParam() {
        return {
            employee_name: $('#employeeName').val(),
            search: $('#search').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            branch_id: $('#branch').val(),
            department_id: $('#department').val(),
            post_id: $('#post').val(),
            is_active: $('#is_active').val(),
            per_page: $('#per_page').val()
        };
    }


    function capitalize(str) {
        strVal = '';
        str = str.split(' ');
        for (let chr = 0; chr < str.length; chr++) {
            strVal += str[chr].substring(0, 1).toUpperCase() + str[chr].substring(1, str[chr].length) + ' ';
        }
        return strVal;
    }

    $('#employeeDetail').validate({
        rules: {
            name: { required: true },
            address: { required: true },
            email: { required: true },
            role_id: { required: true },
            username: { required: true },
        },
        messages: {
            name: {
                required: "{{ __('index.enter_name') }}",
            },
            address: {
                required: "{{ __('index.enter_address') }}"
            },
            email: {
                required: "{{ __('index.enter_valid_email') }}"
            },
            role_id: {
                required: "{{ __('index.select_role') }}"
            },
            username: {
                required: "{{ __('index.enter_username') }}"
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('div').append(error);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
            $(element).removeClass('is-valid');
            $(element).siblings().addClass("text-danger").removeClass("text-success");
            $(element).siblings().find('span .input-group-text').addClass("bg-danger").removeClass("bg-success");
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
            $(element).addClass('is-valid');
            $(element).siblings().addClass("text-success").removeClass("text-danger");
            $(element).find('span .input-group-prepend').addClass("bg-success").removeClass("bg-danger");
            $(element).siblings().find('span .input-group-text').addClass("bg-success").removeClass("bg-danger");
        }
    });

    $('#avatar').change(function () {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('image-preview');
        const file = input.files[0];
        const reader = new FileReader();
        reader.addEventListener('load', function () {
            preview.src = reader.result;
        }, false);
        if (file) {
            reader.readAsDataURL(file);
        }

    });



    // branch wise department, office_time etc
    $(document).ready(function () {
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        let selectedDepartmentId = "{{ $userDetail->department_id ?? $filterParameters['department_id'] ?? old('department_id') }}";
        let selectedOfficeTimeId = "{{ isset($userDetail) ? $userDetail['office_time_id'] : old('office_time_id') }}";
        let selectedSupervisorId = "{{ isset($userDetail) ? $userDetail['supervisor_id'] : old('supervisor_id') }}";
        const employeeId = "{{ isset($userDetail) ? $userDetail['id'] : '' }}";
        let selectedPostId = "{{ $userDetail->post_id ?? $filterParameters['post_id'] ?? old('post_id') }}";

        let branchLoadRequestId = 0;
        let departmentLoadRequestId = 0;

        const loadDepartmentsAndOfficeTime = async (branchId) => {
            const requestId = ++branchLoadRequestId;

            // Reset dependent dropdowns before loading the new branch data
            $('#department').empty().append('<option value="" selected>{{ __('index.select_department') }}</option>').prop('disabled', true);
            $('#officeTime').empty().append('<option value="" selected>{{ __('index.select_office_time') }}</option>');
            $('#post').empty().append('<option value="" selected>{{ __('index.select_post') }}</option>').prop('disabled', true);
            $('#supervisor').empty().append('<option value="" selected>{{ __('index.select_supervisor') }}</option>');
            $('#department, #post').trigger('change.select2');

            if (!branchId) return;

            try {
                const [departmentResponse, branchResponse] = await Promise.all([
                    $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${branchId}`,
                    }),
                    $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/transfer/get-user-transfer-branch-data') }}/${branchId}`,
                    })
                ]);

                if (requestId !== branchLoadRequestId) return;

                // Departments
                const departments = departmentResponse.data || [];
                if (departments.length > 0) {
                    departments.forEach(department => {
                        $('#department').append(`<option ${department.id == selectedDepartmentId ? 'selected' : ''} value="${department.id}">${department.dept_name}</option>`);
                    });
                    $('#department').prop('disabled', false);
                } else {
                    $('#department').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }

                // Office Times
                if (branchResponse.officeTimes && branchResponse.officeTimes.length > 0) {
                    branchResponse.officeTimes.forEach(shift => {
                        $('#officeTime').append(`<option ${shift.id == selectedOfficeTimeId ? 'selected' : ''} value="${shift.id}">${shift.opening_time} - ${shift.closing_time}</option>`);
                    });
                } else {
                    $('#officeTime').append('<option disabled>{{ __("index.office_time_not_found") }}</option>');
                }

                $('#department').trigger('change.select2');
                $('#officeTime').trigger('change.select2');

                if ($('#department').val()) {
                    $('#department').trigger('change');
                }
            } catch (error) {
                $('#department').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                $('#officeTime').append('<option disabled>{{ __("index.error_loading_office_times") }}</option>');
            }
        };

        const loadSupervisorAndPosts = async () => {
            const requestId = ++departmentLoadRequestId;
            const selectedDepartmentId = $('#department').val();

            $('#supervisor').empty().append('<option value="" selected>{{ __('index.select_supervisor') }}</option>');
            $('#post').empty().append('<option value="" selected>{{ __('index.select_post') }}</option>').prop('disabled', true);
            $('#post').trigger('change.select2');

            if (!selectedDepartmentId) return;

            try {
                const [supervisorResponse, postResponse] = await Promise.all([
                    fetch(`{{ url('admin/transfer/get-user-transfer-department-data') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    }),
                    fetch(`{{ url('admin/posts/get-All-posts') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    })
                ]);

                let supervisorData = await supervisorResponse.json();
                let postData = await postResponse.json();

                if (requestId !== departmentLoadRequestId) return;

                // Supervisors
                if (supervisorData.supervisors && supervisorData.supervisors.length > 0) {
                    supervisorData.supervisors.forEach(user => {
                        if (employeeId != user.id){
                            $('#supervisor').append(`<option ${user.id == selectedSupervisorId ? 'selected' : ''} value="${user.id}">${user.name}</option>`);
                        }
                    });
                } else {
                    $('#supervisor').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }

                // Posts
                const posts = postData.data || [];
                if (posts.length > 0) {
                    posts.forEach(post => {
                        $('#post').append(`<option ${post.id == selectedPostId ? 'selected' : ''} value="${post.id}">${post.post_name}</option>`);
                    });
                    $('#post').prop('disabled', false);
                } else {
                    $('#post').append('<option disabled>{{ __("index.no_posts_found") }}</option>');
                }

                $('#supervisor').trigger('change.select2');
                $('#post').trigger('change.select2');
            } catch (error) {
                $('#supervisor').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                $('#post').append('<option disabled>{{ __("index.error_loading_posts") }}</option>');
            }
        };

        const loadLeaveTypes = async () => {
            const gender = $('#gender').val();
            const branch = isAdmin ? $('#branch').val() : defaultBranchId;
            if (!gender || !branch) {
                $('#leave-types-table').html('');
                return;
            }

            // Preserve existing values before reloading
            let existing = {};
            $('#leave-types-table tr').each(function() {
                const idInput = $(this).find('input[name^="leave_type_id"]');
                if (idInput.length) {
                    const id = idInput.val();
                    existing[id] = {
                        days: $(this).find('input[name^="days"]').val(),
                        active: $(this).find('input[name^="is_active"]').is(':checked') ? 1 : 0
                    };
                }
            });

            try {
                const response = await $.ajax({
                    url: `{{ url('admin/leaves/get-gender-leave-types') }}/${branch}/${gender}`,
                    method: 'GET',
                });

                const leaveTypes = response.leaveTypes || [];
                let tableBody = '';

                if (leaveTypes.length) {
                    leaveTypes.forEach((leaveType, index) => {
                        tableBody += `
                <tr>
                    <td>
                        ${capitalize(leaveType.name)}
                        <input type="hidden" name="leave_type_id[${index}]" value="${leaveType.id}">
                    </td>
                    <td>
                        <input type="number" min="0" class="form-control leave-days"
                               value=""
                               oninput="validity.valid || (value='');"
                               placeholder="{{ __('index.total_leave_days') }}"
                               name="days[${index}]">
                        <span class="error-message" style="display: none; color: red;">{{ __('index.required_field') }}</span>
                    </td>
                    <td>
                        <input class="me-1 is-active-checkbox" type="checkbox"
                               name="is_active[${index}]" value="1">{{ __('index.is_active') }}
                        </td>
                    </tr>`;
                    });
                } else {
                    tableBody = '<tr><td colspan="3">{{ __("index.no_leave_types_found") }}</td></tr>';
                }

                $('#leave-types-table').html(tableBody);

                // Restore preserved values to matching leave types
                $('#leave-types-table tr').each(function() {
                    const idInput = $(this).find('input[name^="leave_type_id"]');
                    if (idInput.length) {
                        const id = idInput.val();
                        if (existing[id]) {
                            $(this).find('input[name^="days"]').val(existing[id].days);
                            if (existing[id].active) {
                                $(this).find('input[name^="is_active"]').prop('checked', true);
                            }
                        }
                    }
                });

                // Dispatch event after update (assuming this is needed for listeners)
                document.dispatchEvent(new CustomEvent('leaveTypesUpdated'));

            } catch (error) {
                console.error('Error fetching leave types:', error);
                $('#leave-types-table').html('<tr><td colspan="3">{{ __("index.error_loading_leave_types") }}</td></tr>');
            }
        };

        // Capitalize helper used in leave types
        const capitalize = (str) => {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        };

        $('#department').on('change', function () {
            if ($(this).val() != selectedDepartmentId) {
                selectedSupervisorId = '';
                selectedPostId = '';
            }

            loadSupervisorAndPosts();
        });
        $('#gender').on('change', loadLeaveTypes);

        if (isAdmin) {
            $('#branch').on('change', () => {
                selectedDepartmentId = '';
                selectedOfficeTimeId = '';
                selectedSupervisorId = '';
                selectedPostId = '';
                loadDepartmentsAndOfficeTime($('#branch').val());
                loadLeaveTypes();
            });
        }

        // Initial load for the selected/pre-assigned branch
        loadDepartmentsAndOfficeTime(isAdmin ? $('#branch').val() : defaultBranchId);
        loadLeaveTypes();

        document.addEventListener('leaveTypesUpdated', attachEventListeners);


        const leaveForm = document.getElementById('employeeDetail');
        const leaveAllocatedInput = document.getElementById('leave_allocated');
        let leaveDaysInputs = document.querySelectorAll('.leave-days');
        let isActiveCheckboxes = document.querySelectorAll('.is-active-checkbox');
        const errorMessage = document.getElementById('error-message');

        // Disable HTML5 validation to let JavaScript handle it
        // Check if required elements exist
        if (!leaveForm || !leaveAllocatedInput || !errorMessage || !leaveDaysInputs.length) {
            console.error('Required form elements are missing.');
            return;
        }

        // Disable HTML5 validation to let JavaScript handle it
        leaveForm.setAttribute('novalidate', true);

        function displayError(element, message) {
            if (!element) return;
            console.log('Displaying error for element:', element, 'Message:', message); // Debug
            element.classList.add('text-danger');
            element.textContent = message;
            element.style.display = 'block';
        }

        function hideError(element) {
            if (!element) return;
            console.log('Hiding error for element:', element); // Debug
            element.classList.remove('text-danger');
            element.textContent = '';
            element.style.display = 'none';
        }

        function validateForm(event) {
            let totalDays = 0;
            let isValid = true;

            // Calculate total leave days
            leaveDaysInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                totalDays += value;
            });

            // Check if allocated leave is less than total leave days
            const allocatedValue = parseInt(leaveAllocatedInput.value) || 0;
            console.log('Validating: Total Days:', totalDays, 'Allocated:', allocatedValue); // Debug
            if (allocatedValue < totalDays) {
                displayError(errorMessage, 'Allocated leave cannot be less than the total leave days.');
                leaveAllocatedInput.classList.add('text-danger');
                isValid = false;
            }else if(allocatedValue > totalDays){
                displayError(errorMessage, 'Allocated leave cannot be more than the total leave days.');
                leaveAllocatedInput.classList.add('text-danger');
                isValid = false;
            } else {
                hideError(errorMessage);
                leaveAllocatedInput.classList.remove('text-danger');
            }

            // Validate leave days inputs when allocated leave is greater than 0
            leaveDaysInputs.forEach((input, index) => {
                const value = input.value.trim();
                const errorElement = input.nextElementSibling;

                if (allocatedValue > 0 && !value) {
                    displayError(errorElement, 'This field is required when leave is allocated.');
                    input.classList.add('text-danger');
                    input.classList.remove('is-valid');
                    console.log('Invalid input:', input); // Debug
                    isValid = false;
                } else {
                    hideError(errorElement);
                    input.classList.remove('text-danger');
                    if (value) input.classList.add('is-valid');
                }
            });

            if (!isValid && event) {
                event.preventDefault();
                console.log('Form submission prevented, isValid:', isValid); // Debug
            }

            return isValid;
        }

        function setRequiredAttribute() {
            const allocatedValue = parseInt(leaveAllocatedInput.value) || 0;
            console.log('setRequiredAttribute called, Allocated Value:', allocatedValue); // Debug
            leaveDaysInputs.forEach((input, index) => {
                const value = input.value.trim();
                const errorElement = input.nextElementSibling;
                const isActiveCheckbox = isActiveCheckboxes[index];

                console.log(`Checking input ${index}: Value: ${value}, Allocated: ${allocatedValue}`); // Debug
                if (allocatedValue > 0 && !value) {
                    displayError(errorElement, 'This field is required when leave is allocated.');
                    input.classList.add('text-danger');
                    input.classList.remove('is-valid');
                } else {
                    hideError(errorElement);
                    input.classList.remove('text-danger');
                    if (value) input.classList.add('is-valid');
                }
            });
        }

        // Function to attach event listeners to leave days inputs and checkboxes
        function attachEventListeners() {
            leaveDaysInputs = document.querySelectorAll('.leave-days');
            isActiveCheckboxes = document.querySelectorAll('.is-active-checkbox');
            console.log('Attaching event listeners to', leaveDaysInputs.length, 'inputs'); // Debug

            leaveDaysInputs.forEach((input, index) => {
                input.addEventListener('input', function () {
                    console.log('Input changed:', input.value); // Debug
                    const isActiveCheckbox = isActiveCheckboxes[index];
                    if (!input.value.trim()) {
                        isActiveCheckbox.checked = false;
                    }
                    setRequiredAttribute();
                });
            });

            isActiveCheckboxes.forEach((checkbox, index) => {
                checkbox.addEventListener('change', function () {
                    console.log('Checkbox changed:', checkbox.checked); // Debug
                    setRequiredAttribute();
                });
            });
        }

        // Initial event listeners
        leaveAllocatedInput.addEventListener('input', setRequiredAttribute);
        leaveForm.addEventListener('submit', validateForm);
        attachEventListeners();

        // Initial validation
        setRequiredAttribute();
    });



@if(\App\Helpers\AppHelper::ifDateInBsEnabled())
    $('.joiningDate').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 20,
        readOnlyInput: true,
        disableAfter: "2089-12-30",
    });
    $('.birthDate').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 50,
        readOnlyInput: true,
        disableAfter: "2089-12-30",
    });
    @endif
</script>
