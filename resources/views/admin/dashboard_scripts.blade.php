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
        const dashboardI18n = {
            summaryDetail: @json(__('index.summary_detail')),
            noRecordsFound: @json(__('index.no_records_found')),
            approveReject: @json(__('index.approve_reject')),
            viewTimeLeave: @json(__('index.view_time_leave')),
            viewPendingLeave: @json(__('index.view_pending_leave')),
            viewLeaveRequests: @json(__('index.view_leave_requests')),
            quickLeave: @json(__('index.quick_leave')),
            quickChat: @json(__('index.quick_chat')),
            employee: @json(__('index.employee')),
            loadingLeaveTypes: @json(__('index.loading_leave_types')),
            noLeaveTypesAvailable: @json(__('index.no_leave_types_available')),
            noLeaveTypesAvailableEmployee: @json(__('index.no_leave_types_available_employee')),
            selectLeaveType: @json(__('index.select_leave_type')),
            unableLoadLeaveTypes: @json(__('index.unable_load_leave_types')),
            unableLoadLeaveTypesTryAgain: @json(__('index.unable_load_leave_types_try_again')),
            createApprovedLeaveForDate: @json(__('index.create_approved_leave_for_date')),
            today: @json(__('index.today')),
            unableLoadDetailNow: @json(__('index.unable_load_detail_now')),
        };

        const resetDashboardQuickLeaveOptions = (message = dashboardI18n.loadingLeaveTypes) => {
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

            $('#summaryDetailModalLabel').text(dashboardI18n.summaryDetail);
            $('#summaryDetailTableBody').empty();
            $('#summaryDetailEmpty').addClass('d-none').text(dashboardI18n.noRecordsFound);
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
                    $('#summaryDetailModalLabel').text(response.title || dashboardI18n.summaryDetail);
                    const isPendingLeaveMetric = response.metric === 'active_employee_pending_request';
                    const isPendingTimeLeaveMetric = response.metric === 'active_employee_time_leave_request';
                    const isCurrentMonthLeaveMetric = response.metric === 'current_month_leave_request';
                    const isCurrentMonthTimeLeaveMetric = response.metric === 'current_month_time_leave_request';
                    const canQuickLeave = Boolean(response.can_quick_leave);
                    const canUpdateLeaveRequest = Boolean(response.can_update_leave_request);
                    const canUpdateTimeLeave = Boolean(response.can_update_time_leave);
                    dashboardSummaryCurrentDate = response.current_date || '';
                    dashboardSummaryCurrentDateDisplay = response.current_date_display || response.current_date || '';

                    if (!response.rows || !response.rows.length) {
                        $('#summaryDetailEmpty').removeClass('d-none');
                        return;
                    }

                    const rowsHtml = response.rows.map((row) => {
                        let actionsHtml = '';

                        if (isPendingTimeLeaveMetric && row.pending_time_leave_request_id) {
                            actionsHtml = `
                                ${canUpdateTimeLeave
                                    ? `<a href="#" class="btn btn-outline-warning btn-sm dashboard-time-leave-request-update"
                                            data-href="${row.time_leave_update_url ?? '#'}"
                                            data-status="approved"
                                            data-remark="">
                                            ${dashboardI18n.approveReject}
                                       </a>`
                                    : ''}
                                <a href="${row.pending_time_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewTimeLeave}</a>
                            `;
                        } else if (row.pending_leave_request_id) {
                            actionsHtml = `
                                ${canUpdateLeaveRequest
                                    ? `<a href="#" class="btn btn-outline-warning btn-sm dashboard-leave-request-update"
                                            data-href="${row.leave_update_url ?? '#'}"
                                            data-status="approved"
                                            data-remark=""
                                            data-id="${row.pending_leave_request_id}">
                                            ${dashboardI18n.approveReject}
                                       </a>`
                                    : ''}
                                <a href="${row.pending_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewPendingLeave}</a>
                            `;
                        } else if (isCurrentMonthLeaveMetric) {
                            actionsHtml = `<a href="${row.leave_requests_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewLeaveRequests}</a>`;
                        } else if (isCurrentMonthTimeLeaveMetric) {
                            actionsHtml = `<a href="${row.time_leave_requests_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewTimeLeave}</a>`;
                        } else if (canQuickLeave) {
                            actionsHtml = `
                                <a href="#" class="btn btn-outline-warning btn-sm dashboard-quick-leave-trigger"
                                    data-user-id="${row.id}"
                                    data-user-name="${row.name ?? dashboardI18n.employee}"
                                    data-fetch-url="${row.leave_types_url ?? '#'}">
                                    ${dashboardI18n.quickLeave}
                                </a>
                            `;
                        } else if (isPendingLeaveMetric) {
                            actionsHtml = `<a href="${row.pending_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewPendingLeave}</a>`;
                        } else if (isPendingTimeLeaveMetric) {
                            actionsHtml = `<a href="${row.pending_time_leave_url ?? '#'}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.viewTimeLeave}</a>`;
                        }

                        return `
                            <tr>
                                <td>${row.name ?? 'N/A'}</td>
                                <td>${row.employee_code ?? 'N/A'}</td>
                                <td>${row.email ?? 'N/A'}</td>
                                <td>${row.branch ?? 'N/A'}</td>
                                <td>${row.department ?? 'N/A'}</td>
                                <td>${row.status ?? 'N/A'}</td>
                                <td>
                                    <div class="summary-quick-actions">
                                        ${actionsHtml}
                                        <a href="${row.chat_url ?? '#'}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">${dashboardI18n.quickChat}</a>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');

                    $('#summaryDetailTableBody').html(rowsHtml);
                },
                error: function () {
                    $('#summaryDetailEmpty').removeClass('d-none').text(dashboardI18n.unableLoadDetailNow);
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
            dashboardQuickLeaveLabel.textContent = `${dashboardI18n.quickLeave}: ${userName}`;
            dashboardQuickLeaveHelpText.textContent = dashboardI18n.createApprovedLeaveForDate.replace(':date', dashboardSummaryCurrentDateDisplay || dashboardI18n.today);

            resetDashboardQuickLeaveOptions();
            dashboardQuickLeaveModal.show();

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    const leaveTypes = data.leaveTypes || data.leveTypes || [];

                    if (!leaveTypes.length) {
                        resetDashboardQuickLeaveOptions(dashboardI18n.noLeaveTypesAvailable);
                        dashboardQuickLeaveHelpText.textContent = dashboardI18n.noLeaveTypesAvailableEmployee;
                        return;
                    }

                    dashboardQuickLeaveType.disabled = false;
                    dashboardQuickLeaveType.innerHTML = `<option value="">${dashboardI18n.selectLeaveType}</option>`;

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
                    resetDashboardQuickLeaveOptions(dashboardI18n.unableLoadLeaveTypes);
                    dashboardQuickLeaveHelpText.textContent = dashboardI18n.unableLoadLeaveTypesTryAgain;
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

        $(document).on('click', '.dashboard-time-leave-request-update', function (event) {
            event.preventDefault();

            if (!dashboardLeaveStatusModal) {
                return;
            }

            const url = $(this).data('href');
            const status = $(this).data('status');
            const remark = $(this).data('remark');

            $('#dashboardUpdateLeaveStatus').attr('action', url);
            $('#dashboardLeaveStatus').val(status || 'approved');
            $('#dashboardLeaveRemark').val(remark || '');
            $('#dashboardPreviousApprovers').html('');

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

        drawClock();
        setInterval(drawClock, 1000);

        function drawClock(){
            let now = new Date();
            let hr = now.getHours();
            let min = now.getMinutes();
            let sec = now.getSeconds();
            let hr_rotation = 30 * hr + min / 2;
            let min_rotation = 6 * min;
            let sec_rotation = 6 * sec;

            const hourEl = document.getElementById('hour');
            const minuteEl = document.getElementById('minute');
            const secondEl = document.getElementById('second');
            if (hourEl) hourEl.style.transform = `rotate(${hr_rotation}deg)`;
            if (minuteEl) minuteEl.style.transform = `rotate(${min_rotation}deg)`;
            if (secondEl) secondEl.style.transform = `rotate(${sec_rotation}deg)`;

            const digitalTimeEl = document.getElementById('digitalClockTime');
            const digitalAmPmEl = document.getElementById('digitalClockAmPm');
            if (digitalTimeEl) {
                let h = hr % 12 || 12;
                let m = min < 10 ? '0' + min : min;
                let s = sec < 10 ? '0' + sec : sec;
                digitalTimeEl.textContent = `${h < 10 ? '0' + h : h}:${m}:${s}`;
                if (digitalAmPmEl) {
                    digitalAmPmEl.textContent = hr >= 12 ? 'PM' : 'AM';
                }
            }
        }

        const themeColor = getComputedStyle(document.documentElement).getPropertyValue('--dash-brand').trim() ||
            getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() ||
            '#f5510a';

        const tasksChartEl = document.getElementById("tasksChart");
        if (tasksChartEl) {
            new Chart(tasksChartEl, {
                type: 'doughnut',
                data: {
                    labels: [
                        translatedStrings.pending || 'Pending',
                        translatedStrings.on_hold || 'On Hold',
                        translatedStrings.in_progress || 'In Progress',
                        translatedStrings.completed || 'Completed',
                        translatedStrings.cancelled || 'Cancelled'
                    ],
                    datasets: [{
                        label: 'Tasks',
                        data: [
                            {{ $taskPieChartData['not_started'] ?? 0 }},
                            {{ $taskPieChartData['on_hold'] ?? 0 }},
                            {{ $taskPieChartData['in_progress'] ?? 0 }},
                            {{ $taskPieChartData['completed'] ?? 0 }},
                            {{ $taskPieChartData['cancelled'] ?? 0 }}
                        ],
                        backgroundColor: [
                            '#f59e0b',
                            '#8b5cf6',
                            themeColor,
                            '#10b981',
                            '#ef4444'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });
        }

        const projectChartCanvas = document.getElementById('projectChart');
        if (projectChartCanvas) {
            const ctx = projectChartCanvas.getContext('2d');
            const labels = [
                translatedStrings.pending || 'Pending',
                translatedStrings.on_hold || 'On Hold',
                translatedStrings.in_progress || 'In Progress',
                translatedStrings.completed || 'Completed',
                translatedStrings.cancelled || 'Cancelled'
            ];
            const barColors = [
                '#f59e0b',
                '#8b5cf6',
                themeColor,
                '#10b981',
                '#ef4444'
            ];
            const barData = [
                {{ $projectCardDetail['not_started'] ?? 0 }},
                {{ $projectCardDetail['on_hold'] ?? 0 }},
                {{ $projectCardDetail['in_progress'] ?? 0 }},
                {{ $projectCardDetail['completed'] ?? 0 }},
                {{ $projectCardDetail['cancelled'] ?? 0 }}
            ];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Projects',
                        backgroundColor: barColors,
                        data: barData,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 42
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            padding: 10,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

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
