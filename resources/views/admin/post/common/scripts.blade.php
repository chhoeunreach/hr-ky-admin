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

        // Show employee list in modal with modern UI and live search
        let currentModalEmployees = [];

        $(document).on('click', '.showEmployeeBtn, #showEmployee', function (e) {
            e.preventDefault();
            const postName = $(this).data('post-name') || 'Post';
            let employeeData = $(this).data('employee');

            if (typeof employeeData === 'string') {
                try {
                    employeeData = JSON.parse(employeeData);
                } catch (err) {
                    employeeData = [];
                }
            }

            currentModalEmployees = Array.isArray(employeeData) ? employeeData : [];
            $('.modal-post-subtitle').text(`Position: ${postName}`);
            $('.modal-employee-count').text(`${currentModalEmployees.length} ${currentModalEmployees.length === 1 ? 'Employee' : 'Employees'}`);
            $('#modalEmployeeSearch').val('');
            renderModalEmployees(currentModalEmployees);
            $('#showEmployees').modal('show');
        });

        const renderModalEmployees = (list) => {
            const container = $('.employeeList');
            container.empty();

            if (!currentModalEmployees || currentModalEmployees.length === 0) {
                $('.postEmptyCase').removeClass('d-none');
                $('.modalSearchEmpty').addClass('d-none');
                return;
            }

            $('.postEmptyCase').addClass('d-none');

            if (!list || list.length === 0) {
                $('.modalSearchEmpty').removeClass('d-none');
                return;
            }

            $('.modalSearchEmpty').addClass('d-none');

            list.forEach(function (data) {
                const avatar = data.avatar
                    ? '{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH) }}/' + data.avatar
                    : '{{ asset("assets/images/img.png") }}';
                const profileUrl = data.id ? `{{ url("admin/users") }}/${data.id}` : '#';

                const cardHtml = `
                    <div class="col-md-6 col-12 employee-item" data-name="${String(data.name || '').toLowerCase()}">
                        <div class="card h-100 border shadow-sm p-3 bg-white" style="border-radius: 12px; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative flex-shrink-0">
                                    <img src="${avatar}" alt="${data.name}" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-1 text-truncate fw-semibold text-dark">${data.name || 'Unknown'}</h6>
                                    <span class="badge bg-light text-secondary border" style="font-size: 11px;">Employee ID: #${data.id || 'N/A'}</span>
                                </div>
                                <a href="${profileUrl}" class="btn btn-sm btn-outline-secondary rounded-circle p-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" title="View Profile">
                                    <i class="link-icon" data-feather="external-link" style="width: 14px; height: 14px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                container.append(cardHtml);
            });

            if (window.feather) {
                feather.replace();
            }
        };

        $('#modalEmployeeSearch').on('input', function () {
            const query = $(this).val().trim().toLowerCase();
            if (!query) {
                renderModalEmployees(currentModalEmployees);
                return;
            }

            const filtered = currentModalEmployees.filter(emp =>
                String(emp.name || '').toLowerCase().includes(query)
            );
            renderModalEmployees(filtered);
        });



        const branchSelect = $('#branch_id');
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
                    const isReceptionist = data.dept_name && data.dept_name.includes('អ្នកទទួលភ្ញៀវ');
                    let label = data.dept_name;

                    if ((isReceptionist || nameCounts[normalizedName] > 1) && data.branch_name && !data.dept_name.includes(data.branch_name)) {
                        label = `${data.dept_name.replace(/\s*-\s*$/, '')} - ${data.branch_name}`;
                    }

                    $('#department_id').append(`<option value="${data.id}" ${selectedDepartmentIds.includes(String(data.id)) ? 'selected' : ''}>${label}</option>`);
                });

                $('#department_id').trigger('change.select2');
            } catch (error) {
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                $('#department_id').trigger('change.select2');
            }
        };

        const initializeDropdowns = async () => {
            if (branchSelect.is('select')) {
                branchSelect.on('change', async () => {
                    const newBranchId = branchSelect.val();
                    await loadDepartments(newBranchId);
                });

                const initialBranches = branchSelect.val() || branchId || (defaultBranchId ? [String(defaultBranchId)] : []);
                if (initialBranches && initialBranches.length > 0) {
                    await loadDepartments(initialBranches);
                }
            } else {
                const hiddenVal = branchSelect.val() || defaultBranchId;
                if (hiddenVal) {
                    await loadDepartments([String(hiddenVal)]);
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

        // CSV Export handler
        $(document).on('click', '#exportPostCsvBtn', function () {
            const rows = $('#postTable tbody tr[data-post-name]');
            if (!rows.length) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Data',
                        text: 'There are no position records to export.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('There are no position records to export.');
                }
                return;
            }

            let csvContent = 'Position Name,Departments,Branches,Total Employees,Status\n';
            rows.each(function () {
                const clean = (val) => `"${(val || '').toString().replace(/"/g, '""')}"`;
                const postName = clean($(this).data('post-name'));
                const depts = clean($(this).data('departments'));
                const branches = clean($(this).data('branches'));
                const employees = clean($(this).data('employees'));
                const status = clean($(this).data('status'));
                csvContent += `${postName},${depts},${branches},${employees},${status}\n`;
            });

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `Positions_Export_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });

        initPostListControls();
    });

</script>
