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

            $('.modal-title').html('Leave Status Update');
            $('#updateLeaveStatus').attr('action',url)
            $('#status').val(status)
            $('#remark').val(remark)

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
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>
                                    <p>(${ response.data.admin_data.message})</p>
                                </div>`;
                        }
                        $('#previousApprovers').html(approversData);
                    }
                }
            });
            $('#statusUpdate').modal('show');
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
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>
                                    <p>(${ response.data.admin_data.message})</p>
                                </div>`;
                        }
                        $('#approversList').html(approversData);
                    }
                }
            });
            $('#approvalInfoModal').modal('show');
        });

    });


</script>
