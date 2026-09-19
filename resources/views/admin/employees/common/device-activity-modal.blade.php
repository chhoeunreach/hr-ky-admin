<!-- Employee Device & Activity Logs Modal -->
<div class="modal fade text-start" id="employeeDeviceActivityModal" tabindex="-1" aria-labelledby="employeeDeviceActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-3 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                <div class="d-flex align-items-center gap-3 w-100">
                    <img id="edaEmployeeAvatar" src="{{ asset('assets/images/img.png') }}"
                         alt="Avatar"
                         class="rounded-circle shadow-sm"
                         style="width: 46px; height: 46px; object-fit: cover; border: 2px solid #e2e8f0;">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-dark mb-0 text-truncate" id="edaEmployeeName">
                                {{ __('index.loading') }}
                            </h5>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5 fw-medium" id="edaEmployeeCode" style="font-size: 11px;">
                                ---
                            </span>
                            <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5"
                                  id="edaOnlineBadge"
                                  style="font-size: 11px; background-color: #f1f5f9; color: #64748b;">
                                <span class="status-dot"></span>
                                <span id="edaOnlineText">{{ __('index.loading') }}</span>
                            </span>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill"
                                    id="edaViewLiveLocationBtn"
                                    title="{{ __('index.view_latest_location') }}"
                                    disabled
                                    style="font-size: 11px; font-weight: 600;">
                                <i data-feather="map-pin" style="width: 12px; height: 12px;"></i>
                                <span id="edaViewLiveLocationText">{{ __('index.view_live_location') }}</span>
                            </button>
                        </div>
                        <div class="text-muted small mt-0.5 text-truncate" style="font-size: 0.78rem;">
                            <span id="edaEmployeeRole">---</span> &bull; 
                            <span id="edaEmployeeDept">---</span> &bull; 
                            <span id="edaEmployeeBranch">---</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Subnav / Tabs -->
            <div class="bg-light border-bottom px-4 pt-2">
                <ul class="nav nav-tabs border-0 gap-2" id="edaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold d-inline-flex align-items-center gap-1.5 py-2 px-3 border-0 rounded-top"
                                id="eda-sessions-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#edaSessionsPane"
                                type="button"
                                role="tab"
                                aria-controls="edaSessionsPane"
                                aria-selected="true"
                                style="font-size: 0.85rem;">
                            <i data-feather="smartphone" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('index.device_sessions') }}</span>
                            <span class="badge bg-primary rounded-pill ms-1" id="edaSessionCountBadge" style="font-size: 10px;">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold d-inline-flex align-items-center gap-1.5 py-2 px-3 border-0 rounded-top text-muted"
                                id="eda-activities-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#edaActivitiesPane"
                                type="button"
                                role="tab"
                                aria-controls="edaActivitiesPane"
                                aria-selected="false"
                                style="font-size: 0.85rem;">
                            <i data-feather="activity" style="width: 14px; height: 14px;"></i>
                            <span>{{ __('index.activity_logs') }}</span>
                            <span class="badge bg-secondary rounded-pill ms-1" id="edaActivityCountBadge" style="font-size: 10px;">0</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 position-relative" style="min-height: 380px; max-height: calc(85vh - 170px); overflow-y: auto;">
                <!-- Loading State -->
                <div id="edaLoadingState" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">{{ __('index.loading') }}</span>
                    </div>
                    <p class="text-muted small fw-medium mb-0">{{ __('index.loading') }}...</p>
                </div>

                <!-- Error State -->
                <div id="edaErrorState" class="text-center py-5 d-none">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger bg-opacity-10 text-danger" style="width: 54px; height: 54px;">
                        <i data-feather="alert-triangle" style="width: 26px; height: 26px;"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" id="edaErrorMessage">Failed to load device info</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 mt-2" id="edaRetryBtn">
                        <i data-feather="refresh-cw" style="width: 12px; height: 12px;"></i>
                        <span>Retry</span>
                    </button>
                </div>

                <!-- Content State -->
                <div id="edaContentState" class="tab-content d-none">
                    <!-- Tab 1: Device & Active Sessions -->
                    <div class="tab-pane fade show active" id="edaSessionsPane" role="tabpanel" aria-labelledby="eda-sessions-tab">
                        <!-- Primary Device Card -->
                        <div class="card border border-slate-200 shadow-none mb-4" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <a href="#" target="_blank" rel="noopener noreferrer"
                                   class="d-flex align-items-center gap-2 text-decoration-none"
                                   id="edaDevicePlatformLink"
                                   title="{{ __('index.view_on_map') }}">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 28px; height: 28px;">
                                        <i data-feather="shield" style="width: 14px; height: 14px;"></i>
                                    </div>
                                    <span class="fw-bold text-dark small">{{ __('index.device_name') }} &amp; {{ __('index.platform') }}</span>
                                    <i data-feather="map-pin" class="text-primary" style="width: 13px; height: 13px;"></i>
                                </a>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div id="edaDeviceStatusBadgeContainer"></div>
                                    <div id="edaPlatformBadgeContainer"></div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <!-- Col 1: Device & Hardware -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <span class="text-muted d-block small" style="font-size: 11px;">{{ __('index.device_name') }}</span>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                                    <span class="fw-bold text-dark text-truncate" id="edaDeviceName" style="font-size: 0.9rem;">---</span>
                                                    <span id="edaBatteryBadge"></span>
                                                </div>
                                                <small class="text-muted d-block mt-1 text-truncate" id="edaDeviceModel" style="font-size: 10.5px;">---</small>
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-1 border-opacity-50 d-flex align-items-center justify-content-between">
                                                <small class="text-muted text-truncate" id="edaDeviceUuid" style="font-size: 10px; font-family: monospace; max-width: 150px;">---</small>
                                                <button type="button" class="btn btn-link btn-xs p-0 text-muted" id="edaCopyUuidBtn" title="Copy UUID">
                                                    <i data-feather="copy" style="width: 11px; height: 11px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Col 2: Connection & Activity -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100">
                                            <span class="text-muted d-block small" style="font-size: 11px;">{{ __('index.online_status') }}</span>
                                            <div class="mt-1 mb-2" id="edaDeviceConnectionBadge">
                                                <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-2 py-0.5" style="font-size: 10.5px;">---</span>
                                            </div>
                                            <div class="mb-1.5">
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ __('index.login_time') }}</span>
                                                <span class="fw-bold text-dark d-block" id="edaLoginTime" style="font-size: 0.82rem;">---</span>
                                                <small class="text-muted d-block" id="edaLoginTimeHuman" style="font-size: 10px;">---</small>
                                            </div>
                                            <div>
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ __('index.last_seen') }}</span>
                                                <span class="fw-semibold text-dark d-block" id="edaLastSeenTime" style="font-size: 0.82rem;">---</span>
                                                <small class="text-muted d-block" id="edaLastSeenHuman" style="font-size: 10px;">---</small>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Col 3: App Information -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100">
                                            <span class="text-muted d-block small" style="font-size: 11px;">App &amp; Notifications</span>
                                            <span class="fw-bold text-dark d-block text-truncate mt-0.5" id="edaAppName" style="font-size: 0.88rem;">---</span>
                                            <small class="text-muted d-block mt-0.5 text-truncate" id="edaAppVersion" style="font-size: 10.5px;">---</small>
                                            <div class="mt-2 pt-2 border-top border-1 border-opacity-50">
                                                <span class="badge rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="edaFcmBadge" style="font-size: 10px;">
                                                    <i data-feather="bell" style="width: 10px; height: 10px;"></i>
                                                    <span id="edaFcmText">---</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Col 4: Device Location -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="text-muted small" style="font-size: 11px;">{{ __('index.last_location') }}</span>
                                                    <span class="badge rounded-pill px-1.5 py-0.5" id="edaLocationStatusBadge" style="font-size: 9.5px;">---</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="fw-semibold text-dark text-truncate" id="edaLocationCoords" style="font-size: 0.85rem;">---</span>
                                                    <button type="button" class="btn btn-link btn-xs p-0 text-muted d-none" id="edaCopyCoordsBtn" title="{{ __('index.copy_coordinates') }}">
                                                        <i data-feather="copy" style="width: 11px; height: 11px;"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-0.5" id="edaLocationTime" style="font-size: 10.5px;">---</small>
                                                <small class="text-muted d-block" id="edaLocationAccuracy" style="font-size: 10px;">---</small>
                                            </div>
                                            <div class="mt-2 pt-1" id="edaMapLinkWrapper">
                                                <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-primary py-1 px-2.5 rounded-2 d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center" id="edaMapBtn" style="font-size: 11px; font-weight: 600;">
                                                    <i data-feather="map-pin" style="width: 12px; height: 12px;"></i>
                                                    <span>{{ __('index.view_on_map') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Sessions List -->
                        <div class="card border border-slate-200 shadow-none" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 28px; height: 28px;">
                                        <i data-feather="key" style="width: 14px; height: 14px;"></i>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-dark small">{{ __('index.active_sessions') }}</span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2 py-0.5" id="edaSessionsTotalBadge" style="font-size: 10.5px;">0</span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="edaSessionsOnlineBadge" style="font-size: 10.5px;">
                                            <span class="status-dot"></span> <span id="edaSessionsOnlineCount">0</span> {{ __('index.online_now') }}
                                        </span>
                                        <span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-20 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="edaSessionsOfflineBadge" style="font-size: 10.5px;">
                                            <span class="status-dot"></span> <span id="edaSessionsOfflineCount">0</span> {{ __('index.offline') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2.5 rounded-2 d-inline-flex align-items-center gap-1 shadow-none" id="edaForceLogoutAllBtn" style="font-size: 0.78rem;">
                                        <i data-feather="log-out" style="width: 12px; height: 12px;"></i>
                                        <span>{{ __('index.revoke_all_sessions') }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.03em;">
                                        <tr>
                                            <th class="ps-3" style="width: 40px;">#</th>
                                            <th>{{ __('index.device_name') }} / {{ __('index.platform') }}</th>
                                            <th class="text-center">{{ __('index.online_status') }}</th>
                                            <th>{{ __('index.session_id') }}</th>
                                            <th>{{ __('index.last_location') }}</th>
                                            <th>{{ __('index.login_time') }} &amp; {{ __('index.last_seen') }}</th>
                                            <th class="text-center">{{ __('index.status') }}</th>
                                            <th class="text-end pe-3">{{ __('index.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edaSessionsTableBody">
                                        <!-- Rendered via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Activity Logs -->
                    <div class="tab-pane fade" id="edaActivitiesPane" role="tabpanel" aria-labelledby="eda-activities-tab">
                        <div class="card border border-slate-200 shadow-none" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 28px; height: 28px;">
                                        <i data-feather="clock" style="width: 14px; height: 14px;"></i>
                                    </div>
                                    <span class="fw-bold text-dark small">{{ __('index.activity_logs') }} (Check-In/Out &amp; GPS)</span>
                                </div>
                                <div class="btn-group btn-group-sm" role="group" id="edaActivityFilterGroup">
                                    <button type="button" class="btn btn-outline-secondary active py-1 px-2.5" data-filter="all" style="font-size: 11px;">All</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2.5" data-filter="attendance" style="font-size: 11px;">Attendance</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2.5" data-filter="location" style="font-size: 11px;">GPS Location</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                    <thead class="bg-light text-muted text-uppercase sticky-top" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.03em;">
                                        <tr>
                                            <th class="ps-3" style="width: 40px;">#</th>
                                            <th>{{ __('index.activity_type') }}</th>
                                            <th>Source / Method</th>
                                            <th>Location / Coordinates</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edaActivitiesTableBody">
                                        <!-- Rendered via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top py-2 px-4 d-flex align-items-center justify-content-between">
                <small class="text-muted" style="font-size: 11px;">
                    <i data-feather="info" style="width: 12px; height: 12px;" class="me-1"></i>
                    Sessions and device activity synchronize directly with authentication and attendance services.
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 px-3" data-bs-dismiss="modal">
                    {{ __('index.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal styles */
    #employeeDeviceActivityModal .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background-color: currentColor;
    }
    #employeeDeviceActivityModal .status-online {
        background-color: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #bbf7d0;
    }
    #employeeDeviceActivityModal .status-online .status-dot {
        background-color: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25);
        animation: edaPulse 1.8s infinite;
    }
    #employeeDeviceActivityModal .status-offline {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
        border: 1px solid #e2e8f0;
    }
    #employeeDeviceActivityModal .status-offline .status-dot {
        background-color: #94a3b8;
    }
    @keyframes edaPulse {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    #edaTabs .nav-link {
        color: #64748b;
        background: transparent;
        transition: all 0.15s ease;
    }
    #edaTabs .nav-link.active {
        color: #2563eb !important;
        background: #ffffff !important;
        border-bottom: 2px solid #2563eb !important;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.02);
    }
    .session-token-code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.72rem;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        color: #334155;
    }
</style>
