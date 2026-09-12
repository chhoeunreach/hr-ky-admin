<script>

    $(document).ready(function () {
        $("#department_id").select2({
            placeholder: $("#department_id").data('placeholder') || "{{ __('index.select_department') }}",
            allowClear: true,
            width: '100%'
        });
        $("#branch_id").select2({
            placeholder: $("#branch_id").data('placeholder') || "{{ __('index.select_branch') }}",
            allowClear: true,
            width: '100%'
        });
        $("#is_active").select2({});
        $("#per_page").select2({minimumResultsForSearch: Infinity});
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle status change handler
        $(document).on('change', '.toggleStatus', function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __("index.change_status_confirmation") }}',
                showDenyButton: true,
                confirmButtonText: `{{ __("index.yes") }}`,
                denyButtonText: `{{ __("index.no") }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    // Revert checkbox state if user clicks 'No'
                    (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                }
            });
        });

        // Delete post confirmation handler
        $(document).on('click', '.deletePost', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __("index.delete_post_confirmation") }}',
                showDenyButton: true,
                confirmButtonText: `{{ __("index.yes") }}`,
                denyButtonText: `{{ __("index.no") }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        // Show employee list in modal
        $('body').on('click', '#showEmployee', function (e) {
            e.preventDefault();
            let employee = $(this).data('employee');
            $('.employee').remove();
            $('.modal-title').html('{{ __("index.employee_list_title") }}');
            if (employee.length > 0) {
                $('.postEmptyCase').addClass('d-none');
                employee.forEach(function (data) {
                    let avatar = data.avatar ? '{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH) }}' + '/' + data.avatar : '{{ asset('assets/images/img.png') }}';
                    $('.employeeList').append(
                        '<div class="col-lg-6 d-flex align-items-center mb-3 employee">' +
                        '<img class="rounded-circle w-25 me-2 employeeImage" ' + 'style="object-fit: cover" ' +
                        'src="' + avatar + '" ' +
                        'alt="profile">' +
                        '<span class="employeeName">' + data.name + '</span>' +
                        '</div>'
                    );
                });
            } else {
                $('.postEmptyCase').removeClass('d-none');
            }
            $('#showEmployees').modal('show');
        }).trigger("change");



        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = @json($filterParameters['branch_id'] ?? ($selectedBranchIds ?? (isset($postDetail) ? [$postDetail->branch_id] : [])));
        const selectedDepartmentIds = @json(collect(old('dept_id', $selectedDepartmentIds ?? (isset($postDetail) ? [$postDetail->dept_id] : ($filterParameters['department_id'] ?? []))))->filter()->map(fn ($id) => (string) $id)->all());


        const loadDepartments = async (selectedBranchValue) => {
            const selectedBranchIds = Array.isArray(selectedBranchValue)
                ? selectedBranchValue.filter(Boolean)
                : (selectedBranchValue ? [selectedBranchValue] : []);

            $('#department_id').empty();
            $('#department_id').trigger('change.select2');

            if (selectedBranchIds.length === 0) return;

            try {
                const responses = await Promise.all(selectedBranchIds.map(async (selectedBranchId) => {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });

                    const branchName = $(`#branch_id option[value="${selectedBranchId}"]`).text().trim();
                    return (response.data || []).map((department) => ({
                        ...department,
                        branch_name: branchName,
                    }));
                }));

                const departments = responses.flat();

                if (departments.length === 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    $('#department_id').trigger('change.select2');
                    return;
                }

                const nameCounts = departments.reduce((counts, department) => {
                    const name = String(department.dept_name || '').trim().toLowerCase();
                    counts[name] = (counts[name] || 0) + 1;
                    return counts;
                }, {});

                departments.forEach(data => {
                    const normalizedName = String(data.dept_name || '').trim().toLowerCase();
                    const label = nameCounts[normalizedName] > 1 && data.branch_name
                        ? `${data.dept_name} - ${data.branch_name}`
                        : data.dept_name;
                    $('#department_id').append(`<option value="${data.id}" ${selectedDepartmentIds.includes(String(data.id)) ? 'selected' : ''}>${label}</option>`);
                });

                $('#department_id').trigger('change.select2');
            } catch (error) {
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                $('#department_id').trigger('change.select2');
            }
        };


        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadDepartments(newBranchId);

                });

                // Trigger initial load if branch is selected
                if (selectedBranchId) {
                    $('#branch_id').trigger('change');
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);

                }
            }

        };

        // Initialize everything
        initializeDropdowns();

        let postSearchTimer = null;
        let postListController = null;
        let postListRequestId = 0;

        const initPostListControls = () => {
            const perPage = $('#per_page');

            if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                perPage.select2({minimumResultsForSearch: Infinity});
            }
        };

        const replacePostResults = (doc) => {
            const currentTableBody = document.querySelector('#postTable tbody');
            const nextTableBody = doc.querySelector('#postTable tbody');
            const currentPagination = document.querySelector('#postListSection .dataTables_paginate');
            const nextPagination = doc.querySelector('#postListSection .dataTables_paginate');

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

        const refreshPostList = () => {
            const form = document.getElementById('postFilterForm');
            const listSection = document.getElementById('postListSection');
            const tableBody = document.querySelector('#postTable tbody');

            if (!form || !listSection) {
                return;
            }

            const params = new URLSearchParams(new FormData(form));
            const requestUrl = `${form.action}?${params.toString()}`;
            const requestId = ++postListRequestId;

            if (postListController) {
                postListController.abort();
            }

            postListController = new AbortController();

            if (tableBody) {
                tableBody.style.opacity = '0.6';
            }

            fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: postListController.signal
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== postListRequestId) {
                        return;
                    }

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (!replacePostResults(doc)) {
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
                    if (requestId === postListRequestId) {
                        if (tableBody) {
                            tableBody.style.opacity = '1';
                        }
                        postListController = null;
                    }
                });
        };

        $(document).on('submit', '#postFilterForm', function (event) {
            event.preventDefault();
            refreshPostList();
        });

        $(document).on('change', '#per_page', function () {
            refreshPostList();
        });

        $(document).on('input', '#postListSearch', function () {
            const searchInput = document.getElementById('postSearch');
            if (searchInput) {
                searchInput.value = this.value;
            }

            clearTimeout(postSearchTimer);
            postSearchTimer = setTimeout(() => {
                refreshPostList();
            }, 300);
        });

        $(document).on('click', '#postListSection .pagination a', function (event) {
            event.preventDefault();

            const tableBody = document.querySelector('#postTable tbody');
            const requestUrl = this.href;
            const requestId = ++postListRequestId;

            if (postListController) {
                postListController.abort();
            }

            postListController = new AbortController();

            if (tableBody) {
                tableBody.style.opacity = '0.6';
            }

            fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: postListController.signal
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== postListRequestId) {
                        return;
                    }

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (!replacePostResults(doc)) {
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
                    if (requestId === postListRequestId) {
                        if (tableBody) {
                            tableBody.style.opacity = '1';
                        }
                        postListController = null;
                    }
                });
        });

        initPostListControls();
    });

</script>
