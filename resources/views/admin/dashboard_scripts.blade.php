<script>
    $('document').ready(function(){
        const summaryDetailModalElement = document.getElementById('summaryDetailModal');
        const summaryDetailModal = summaryDetailModalElement ? new bootstrap.Modal(summaryDetailModalElement) : null;
        const dashboardQuickLeaveModalElement = document.getElementById('dashboardQuickLeaveModal');
        const dashboardQuickLeaveModal = dashboardQuickLeaveModalElement ? new bootstrap.Modal(dashboardQuickLeaveModalElement) : null;
        const dashboardQuickLeaveUserId = document.getElementById('dashboardQuickLeaveUserId');
        const dashboardQuickLeaveDate = document.getElementById('dashboardQuickLeaveDate');
        const dashboardQuickLeaveType = document.getElementById('dashboardQuickLeaveType');
        const dashboardQuickLeaveReason = document.getElementById('dashboardQuickLeaveReason');
        const dashboardQuickLeaveSubmit = document.getElementById('dashboardQuickLeaveSubmit');
        const dashboardQuickLeaveLabel = document.getElementById('dashboardQuickLeaveModalLabel');
        const dashboardQuickLeaveHelpText = document.getElementById('dashboardQuickLeaveHelpText');
        const dashboardLeaveStatusModalElement = document.getElementById('dashboardLeaveStatusUpdate');
        const dashboardLeaveStatusModal = dashboardLeaveStatusModalElement ? new bootstrap.Modal(dashboardLeaveStatusModalElement) : null;
        let dashboardSummaryCurrentDate = '';
        let dashboardSummaryCurrentDateDisplay = '';

        const resetDashboardQuickLeaveOptions = (message = 'Loading leave types...') => {
            if (!dashboardQuickLeaveType) {
                return;
            }

            dashboardQuickLeaveType.innerHTML = `<option value="">${message}</option>`;
            dashboardQuickLeaveType.disabled = true;
            if (dashboardQuickLeaveSubmit) {
                dashboardQuickLeaveSubmit.disabled = true;
            }
        };

        $(document).on('click', '.summary-trigger', function () {
            if (!summaryDetailModal) {
                return;
            }

            const entityIds = String($(this).data('entity-ids') || '')
                .split(',')
                .map((value) => Number(value.trim()))
                .filter((value) => !Number.isNaN(value) && value > 0);

            if (!entityIds.length) {
                return;
            }

            $('#summaryDetailModalLabel').text('Summary Detail');
            $('#summaryDetailTableBody').empty();
            $('#summaryDetailEmpty').addClass('d-none').text('No records found.');
            $('#summaryDetailLoading').removeClass('d-none');
            summaryDetailModal.show();

            $.ajax({
                type: 'POST',
                url: '{{ route('admin.dashboard.summary-detail') }}',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    scope: $(this).data('summary-scope'),
                    metric: $(this).data('summary-metric'),
                    entity_name: $(this).data('entity-name'),
                    entity_ids: entityIds
                },
                success: function (response) {
                    $('#summaryDetailModalLabel').text(response.title || 'Summary Detail');
                    const isPendingLeaveMetric = response.metric === 'active_employee_pending_request';
                    const canQuickLeave = Boolean(response.can_quick_leave);
                    const canUpdateLeaveRequest = Boolean(response.can_update_leave_request);
                    dashboardSummaryCurrentDate = response.current_date || '';
                    dashboardSummaryCurrentDateDisplay = response.current_date_display || response.current_date || '';

                    if (!response.rows || !response.rows.length) {
                        $('#summaryDetailEmpty').removeClass('d-none');
                        return;
                    }

                    const rowsHtml = response.rows.map((row) => `
                        <tr>
                            <td>${row.name ?? 'N/A'}</td>
                            <td>${row.employee_code ?? 'N/A'}</td>
                            <td>${row.email ?? 'N/A'}</td>
                            <td>${row.branch ?? 'N/A'}</td>
                            <td>${row.department ?? 'N/A'}</td>
                            <td>${row.status ?? 'N/A'}</td>
                            <td>
                                <div class="summary-quick-actions">
                                    ${row.pending_leave_request_id
                                        ? `
                                            ${canUpdateLeaveRequest
                                                ? `<a href="#" class="btn btn-outline-warning btn-sm dashboard-leave-request-update"
                                                        data-href="${row.leave_update_url ?? '#'}"
                                                        data-status="approved"
                                                        data-remark=""
                                                        data-id="${row.pending_leave_request_id}">
                                                        Approve / Reject
                                                   </a>`
                                                : ''}
                                            <a href="${row.pending_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">View Pending Leave</a>
                                          `
                                        : `${canQuickLeave
                                                ? `<a href="#" class="btn btn-outline-warning btn-sm dashboard-quick-leave-trigger"
                                                        data-user-id="${row.id}"
                                                        data-user-name="${row.name ?? 'Employee'}"
                                                        data-fetch-url="${row.leave_types_url ?? '#'}">
                                                        Quick Leave
                                                   </a>`
                                                : (isPendingLeaveMetric ? `<a href="${row.pending_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">View Pending Leave</a>` : '')
                                            }`
                                    }
                                    <a href="${row.chat_url ?? '#'}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">Quick Chat</a>
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    $('#summaryDetailTableBody').html(rowsHtml);
                },
                error: function () {
                    $('#summaryDetailEmpty').removeClass('d-none').text('Unable to load detail right now.');
                },
                complete: function () {
                    $('#summaryDetailLoading').addClass('d-none');
                }
            });
        });

        $(document).on('click', '.dashboard-quick-leave-trigger', function (event) {
            event.preventDefault();

            if (!dashboardQuickLeaveModal) {
                return;
            }

            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');
            const fetchUrl = $(this).data('fetch-url');

            dashboardQuickLeaveUserId.value = userId;
            dashboardQuickLeaveDate.value = dashboardSummaryCurrentDate;
            dashboardQuickLeaveReason.value = '';
            dashboardQuickLeaveLabel.textContent = `Quick Leave: ${userName}`;
            dashboardQuickLeaveHelpText.textContent = `Create an already approved leave for ${dashboardSummaryCurrentDateDisplay || 'today'}.`;

            resetDashboardQuickLeaveOptions();
            dashboardQuickLeaveModal.show();

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    const leaveTypes = data.leaveTypes || data.leveTypes || [];

                    if (!leaveTypes.length) {
                        resetDashboardQuickLeaveOptions('No leave types available');
                        dashboardQuickLeaveHelpText.textContent = 'No leave types are available for this employee.';
                        return;
                    }

                    dashboardQuickLeaveType.disabled = false;
                    dashboardQuickLeaveType.innerHTML = '<option value="">Select leave type</option>';

                    leaveTypes.forEach((leaveType) => {
                        const option = document.createElement('option');
                        option.value = leaveType.id;
                        option.textContent = leaveType.name;
                        dashboardQuickLeaveType.appendChild(option);
                    });

                    const preferredType = leaveTypes.find((leaveType) => {
                        const typeName = String(leaveType.name || '').toLowerCase();
                        return typeName.includes('day off') || typeName.includes('leave');
                    });

                    dashboardQuickLeaveType.value = String(preferredType?.id || leaveTypes[0].id);
                    if (dashboardQuickLeaveSubmit) {
                        dashboardQuickLeaveSubmit.disabled = false;
                    }
                })
                .catch(() => {
                    resetDashboardQuickLeaveOptions('Unable to load leave types');
                    dashboardQuickLeaveHelpText.textContent = 'Unable to load leave types right now. Please try again.';
                });
        });

        $(document).on('click', '.dashboard-leave-request-update', function (event) {
            event.preventDefault();

            if (!dashboardLeaveStatusModal) {
                return;
            }

            const url = $(this).data('href');
            const status = $(this).data('status');
            const remark = $(this).data('remark');
            const leaveRequestId = $(this).data('id');

            $('#dashboardUpdateLeaveStatus').attr('action', url);
            $('#dashboardLeaveStatus').val(status || 'approved');
            $('#dashboardLeaveRemark').val(remark || '');
            $('#dashboardPreviousApprovers').html('');

            $.ajax({
                url: `/admin/leave-request/get-approvers/${leaveRequestId}`,
                method: 'GET',
                success: function (response) {
                    if (!response.success) {
                        return;
                    }

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

                    if (response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== '') {
                        approversData += `
                            <div class="approver-details">
                                <p><b>Status:</b> ${response.data.admin_data.status}</p>
                                <p><b>Admin Remark:</b> ${response.data.admin_data.remark}</p>
                                ${response.data.admin_data.message !== '' ? `<p>(${response.data.admin_data.message})</p>` : ''}
                            </div>`;
                    }

                    $('#dashboardPreviousApprovers').html(approversData);
                }
            });

            dashboardLeaveStatusModal.show();
        });

        $('.errorStartWorking').hide();

        $('.errorStopWorking').hide();

        $('.successStartWorking').hide();

        $('.successStopWorking').hide();

        function showLoader() {
            $('#loader').show();
        }

        function hideLoader() {
            $("#loader").hide();
        }

        setInterval(drawClock, 1000);

        function drawClock(){
            let now = new Date();
            let hr = now.getHours();
            let min = now.getMinutes();
            let sec = now.getSeconds();
            let hr_rotation = 30 * hr + min / 2;
            let min_rotation = 6 * min;
            let sec_rotation = 6 * sec;
            hour.style.transform = `rotate(${hr_rotation}deg)`;
            minute.style.transform = `rotate(${min_rotation}deg)`;
            second.style.transform = `rotate(${sec_rotation}deg)`;

            // display weekday and date
            // const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            // const weekday = weekdays[now.getDay()];
            // const date = now.toLocaleDateString();
            //
            // const dateDiv = document.getElementById('date');
            // dateDiv.innerText = `${weekday}, ${date}`;
        }

        let tasksChart = new Chart(document.getElementById("tasksChart"), {
            type: 'pie',
            data: {
                labels: [ translatedStrings.pending,
                    translatedStrings.on_hold,
                    translatedStrings.in_progress,
                    translatedStrings.completed,
                    translatedStrings.cancelled
                ],
                datasets: [{
                    label: 'Task state',
                    type: 'doughnut',
                    backgroundColor: ["#7ee5e5","#f77eb9","#4d8af0","#00ff00","#FF0000"],
                    borderColor: [
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)'
                    ],

                    data: [
                        {{$taskPieChartData['not_started']}},
                        {{$taskPieChartData['on_hold']}},
                        {{$taskPieChartData['in_progress']}},
                        {{$taskPieChartData['completed']}},
                        {{$taskPieChartData['cancelled']}}
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: 'Task Pie Chart'
                    }
                }
            }
        });

        let ctx = document.getElementById('projectChart')?.getContext('2d');
        let labels = [
            translatedStrings.pending,
            translatedStrings.on_hold,
            translatedStrings.in_progress,
            translatedStrings.completed,
            translatedStrings.cancelled
        ];
        let barColors = ["#7ee5e5","#f77eb9","#4d8af0","green",'red'];
        let barData = [
            {{$projectCardDetail['not_started']}},
            {{$projectCardDetail['on_hold']}},
            {{$projectCardDetail['in_progress']}},
            {{$projectCardDetail['completed']}},
            {{$projectCardDetail['cancelled']}}
        ];
        let myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels ,
                datasets: [{
                    label: 'Project',
                    backgroundColor: barColors,
                    data: barData,
                    borderWidth: 1,
                    borderRadius: 10,
                    borderSkipped: true,
                }],

            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                },
                plugins: {
                    legend: {
                        position: 'none',
                    },
                    title: {
                        display: false,
                        text: 'Project Bar Chart'
                    }
                },
                barThickness: 50,

            }
        });

        $("#startWorkingBtn").click(function(e) {
            e.preventDefault();
            showLoader();
            let url = $(this).attr('href');
            let audioUrl = $(this).data('audio');

            getLocation().then(function (position) {
                let params = {
                    lat: position.latitude,
                    long: position.longitude
                };
                let queryString = $.param(params);
                let urlWithParams = url + "?" + queryString
                $.ajax({
                    type: "get",
                    url: urlWithParams,
                    success: function (response) {
                        $('#startWorkingBtn').addClass('d-none');
                        $('#checkInTime').text(response.data.check_in_at);
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.successStartWorking').show();
                        $('.successStartWorkingMessage').text(response.message);
                        $('div.alert.alert-success').not('.alert-important').delay(500).slideUp(900);
                        let audio = new Audio(audioUrl);
                        audio.play();
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            let errorObj = JSON.parse(jqXHR.responseText);
                            let errorMessage = "Error: " + errorObj.message;
                            $('#flashAttendanceMessage').removeClass('d-none');
                            $('.errorStartWorking').show();
                            $('.errorStartWorkingMessage').text(errorMessage);
                            $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                        } else {
                            let errorMessage = "Error: " + errorThrown;
                            $('#flashAttendanceMessage').removeClass('d-none');
                            $('.errorStartWorking').show();
                            $('.errorStartWorkingMessage').text(errorMessage);
                            $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                        }
                        location.reload();
                    },
                    complete: function () {
                        hideLoader();
                    }
                });
            }).catch(function (error) {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text("Error occurred while retrieving location: "+error.message);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });

        $("#stopWorkingBtn").click(function(e){
            e.preventDefault();
            showLoader();
            let url = $(this).attr('href');
            let audioUrl = $(this).data('audio');
            getLocation().then(function (position) {
                let params = {
                    lat: position.latitude,
                    long: position.longitude
                };
                let queryString = $.param(params);
                let urlWithParams = url + "?" + queryString

                $.ajax({
                    type: "get",
                    url: urlWithParams,
                success: function(response){
                    let audio = new Audio(audioUrl);
                    audio.play();
                    $('#stopWorkingBtn').addClass('d-none');
                    $('#checkOutTime').text(response.data.check_out_at);
                    $('#flashAttendanceMessage').removeClass('d-none');
                    $('.successStopWorking').show();
                    $('.successStopWorkingMessage').text(response.message);
                    $('div.alert.alert-success').not('.alert-important').delay(500).slideUp(900);
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 400) {
                        let errorObj = JSON.parse(jqXHR.responseText);
                        let errorMessage = "Error: " + errorObj.message;
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.errorStopWorking').show();
                        $('.errorStopWorkingMessage').text(errorMessage);
                        $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                    } else {
                        let errorMessage = "Error: " + errorThrown;
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.errorStopWorking').show();
                        $('.errorStopWorkingMessage').text(errorMessage);
                        $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                    }
                    location.reload();
                },
                complete: function() {
                    hideLoader();
                }
            });
            }).catch(function (error) {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text("Error occurred while retrieving location: "+error.message);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });

        function getLocation() {
            if (navigator.geolocation) {
                return new Promise(function(resolve, reject) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        let latitude = position.coords.latitude;
                        let longitude = position.coords.longitude;

                        resolve({ latitude: latitude, longitude: longitude });
                    }, function(error) {
                        reject(error);
                    });
                });
            } else {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text('Geolocation is not supported by this browser.');
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            }
        }
    });



</script>
