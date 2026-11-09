<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('body').on('click', '.leaveRequestUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let remark = $(this).data('remark');
            let leaveRequestId = $(this).data('id');
            let leaveTypeId = $(this).data('leave-type-id');

            $('.modal-title').html('Leave Status Update');
            $('#updateLeaveStatus').attr('action',url)
            $('#updateStatus').val(status === 'rejected' ? 'rejected' : 'approved')
            $('#remark').val(remark)
            $('#updateLeaveType').val(leaveTypeId)

            $('#previousApprovers').html('');
            $.ajax({
                url: `/admin/leave-request/get-approvers/${leaveRequestId}`,
                method: 'GET',
                success: function (response) {
                    console.log(response.data.admin_data)
                    if (response.success) {
                        let approversData = '';



                        response.data.approval_data.forEach(function (approver) {
                            approversData += `
                        <div class="approver-details">
                            <p><b>Approver:</b> ${approver.approved_by_name}</p>
                            <p><b>Status:</b> ${approver.status}</p>
                            <p><b>Remark:</b> ${approver.reason}</p>
                        </div>
                        <hr>`;
                        });

                        if(response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== ''){
                            approversData += `
                                <div class="approver-details">
                                    <p><b>Status:</b>  ${response.data.admin_data.status}</p>
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>`;
                            if(response.data.admin_data.message !== ''){
                                approversData += ` <p>(${ response.data.admin_data.message})</p>`;
                            }



                            approversData += ` </div>`;
                        }
                        $('#previousApprovers').html(approversData);
                    }
                }
            });
            $('#statusUpdate').modal('show');
        });

        const refreshLeaveRequestList = function ({resetPage = false} = {}) {
            const url = new URL(window.location.href);
            if (resetPage) url.searchParams.delete('page');
            return $.get(url.href).then(function (html) {
                const page = $('<div>').append($.parseHTML(html));
                const listBlock = page.find('#leave-request-list-block');
                const pagination = page.find('#leave-request-pagination');

                if (!listBlock.length || !pagination.length) {
                    return $.Deferred().reject({
                        responseJSON: { message: 'Unable to refresh the leave request list.' }
                    }).promise();
                }

                $('#leave-request-list-block').replaceWith(listBlock);
                $('#leave-request-pagination').replaceWith(pagination);
                const refreshed = document.getElementById('leave-request-list-block');
                const count = document.querySelector('[data-directory-count="leaveRequestTable"]');
                if (count && refreshed?.dataset.directoryTotal !== undefined) count.textContent = Number(refreshed.dataset.directoryTotal).toLocaleString();
                document.dispatchEvent(new CustomEvent('directory:updated'));
                if (resetPage) window.history.replaceState({}, '', url.href);

                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });
        };

        document.addEventListener('employee-request:updated', function (event) {
            if (event.detail?.requestType !== 'leave') return;
            refreshLeaveRequestList({resetPage: true}).fail(function (error) {
                Swal.fire(
                    'Refresh required',
                    error.responseJSON?.message || 'The request was saved, but the list could not be refreshed. Please reload the page.',
                    'warning'
                );
            });
        });

        $('body').on('submit', '#updateLeaveStatus', function (event) {
            event.preventDefault();

            const form = $(this);
            const submitButton = form.find('[type="submit"]');
            const originalButtonText = submitButton.html();

            submitButton.prop('disabled', true).html('Updating...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                }
            }).done(function (response) {
                $('#statusUpdate').modal('hide');

                refreshLeaveRequestList().done(function () {
                    Swal.fire('Success', response.message, 'success');
                }).fail(function (error) {
                    Swal.fire(
                        'Refresh failed',
                        error.responseJSON?.message || 'The request was updated, but the list could not be refreshed.',
                        'warning'
                    );
                });
            }).fail(function (error) {
                const validationErrors = error.responseJSON?.errors;
                const message = validationErrors
                    ? Object.values(validationErrors).flat().join('<br>')
                    : error.responseJSON?.message || 'Unable to update the leave request.';

                Swal.fire('Error', message, 'error');
            }).always(function () {
                submitButton.prop('disabled', false).html(originalButtonText);
            });
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#requestedBy').val('');
            $('#leaveType').val('');
            $('#month').val('');
            $('#status').val('');
            $('#year').val('');
        })



        $('body').on('click','.show-approval-info', function() {
            let leaveRequestId = $(this).data('id');
            $('#approversList').html('');
            $.ajax({
                url: `/admin/leave-request/get-approvers/${leaveRequestId}`,
                method: 'GET',
                success: function (response) {
                    console.log(response.data);
                    if (response.success) {
                        let approversData = '';
                            response.data.approval_data.forEach(function (approver) {
                            approversData += `
                                    <div class="approver-details">
                                        <p><b>Approver:</b> ${approver.approved_by_name}</p>
                                        <p><b>Status:</b> ${approver.status}</p>
                                        <p><b>Remark:</b> ${approver.reason}</p>
                                    </div>
                                    <hr>`;
                            });

                        if(response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== ''){
                            approversData += `
                                <div class="approver-details">
                                    <p><b>Status:</b>  ${response.data.admin_data.status}</p>
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>`;
                                    if(response.data.admin_data.message !== ''){
                                        approversData += `<p>${ response.data.admin_data.message}</p>`;
                                       }

                            approversData += `</div>`;
                        }
                        $('#approversList').html(approversData);
                    }
                }
            });
            $('#approvalInfoModal').modal('show');
        });



        $("#department_id").select2({});
        $("#branch_id").select2({});
        $("#requestedBy").select2({});
        $("#leaveType").select2({});

        const departmentId = @json((string) ($filterParameters['department_id'] ?? ''));
        const employeeId = @json((string) ($filterParameters['requested_by'] ?? ''));
        const branchId = @json((string) ($filterParameters['branch_id'] ?? ''));
        const employeeLabel = $('#requestedBy option:selected').text();
        const allDepartments = @json(__('index.all_departments'));
        const allEmployees = @json(__('index.all_employees'));
        let departmentSequence = 0, employeeSequence = 0;
        const reset = (selector, label) => $(selector).empty().append(new Option(label, ''));

        const loadEmployees = async (restoreId = '') => {
            const sequence = ++employeeSequence;
            const selectedDepartment = $('#department_id').val();
            if (!restoreId) reset('#requestedBy', allEmployees);
            if (!selectedDepartment) return;
            try {
                const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${encodeURIComponent(selectedDepartment)}`, {headers: {Accept: 'application/json'}});
                if (!response.ok) throw new Error('Unable to load employees');
                const data = await response.json();
                if (sequence !== employeeSequence) return;
                reset('#requestedBy', allEmployees);
                (data.data || []).forEach(employee => {
                    const label = employee.username ? `${employee.name} (${employee.username})` : employee.name;
                    $('#requestedBy').append(new Option(label, employee.id, false, String(employee.id) === restoreId));
                });
                $('#requestedBy').trigger('change.select2');
            } catch (_) {
                if (sequence === employeeSequence) $('#requestedBy').append(new Option(@json(__('index.error_loading_employees')), '', false, false)).find('option:last').prop('disabled', true);
            }
        };
        const loadDepartments = async (selectedBranch, restoreDepartment = '', restoreEmployee = '') => {
            const sequence = ++departmentSequence;
            ++employeeSequence;
            if (!restoreDepartment) reset('#department_id', allDepartments);
            if (!restoreEmployee) reset('#requestedBy', allEmployees);
            if (!selectedBranch) return;
            try {
                const response = await $.get(`{{ url('admin/departments/get-All-Departments') }}/${encodeURIComponent(selectedBranch)}`);
                if (sequence !== departmentSequence) return;
                reset('#department_id', allDepartments);
                (response.data || []).forEach(department => $('#department_id').append(new Option(department.dept_name, department.id, false, String(department.id) === restoreDepartment)));
                $('#department_id').trigger('change.select2');
                if ($('#department_id').val()) await loadEmployees(restoreEmployee);
                else if (restoreEmployee) reset('#requestedBy', allEmployees).append(new Option(employeeLabel, restoreEmployee, true, true));
            } catch (_) {
                if (sequence === departmentSequence) $('#department_id').append(new Option(@json(__('index.error_loading_department')), '', false, false)).find('option:last').prop('disabled', true);
            }
        };
        $('#branch_id').on('change', () => loadDepartments($('#branch_id').val()));
        $('#department_id').on('change', () => loadEmployees());
        if (branchId) loadDepartments(branchId, departmentId, employeeId);
        else if (departmentId) loadEmployees(employeeId);
    });
</script>
