@extends('layouts.master')
@section('title',__('index.app_setting'))
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">@lang('index.dashboard') </a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('index.app_settings')</li>
            </ol>
            <button
                class="btn btn-success btn-md"
                data-bs-toggle="modal"
                data-bs-target="#addslider">
                @lang('index.export_database_data')
            </button>
        </nav>

        <!-- Mobile App Version & Update Control Card -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i data-feather="smartphone" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-semibold" style="font-size: 16px;">@lang('index.app_version_control')</h5>
                        <p class="text-muted mb-0 small" style="font-size: 12px;">@lang('index.app_version_subtitle')</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning btn-sm py-1 px-3 rounded-pill d-inline-flex align-items-center gap-1.5 shadow-sm text-dark fw-medium" data-bs-toggle="modal" data-bs-target="#sendUpdateAlertModal">
                        <i data-feather="send" style="width: 13px; height: 13px;"></i>
                        @lang('index.send_update_alert')
                    </button>
                    @if(!empty($appVersionSetting['enabled']))
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                            <span class="status-dot bg-success" style="width: 6px; height: 6px; border-radius: 50%; display: inline-block;"></span> Active
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            Disabled
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.app-settings.app-version.update') }}" method="POST" id="appVersionForm">
                    @csrf
                    <!-- Master Toggle Row -->
                    <div class="p-3 mb-4 rounded-3 border d-flex align-items-center justify-content-between" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <label class="switch mb-0">
                                <input type="checkbox" name="enabled" value="1" id="versionCheckEnabledSwitch" {{ !empty($appVersionSetting['enabled']) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <div>
                                <label for="versionCheckEnabledSwitch" class="fw-semibold text-dark mb-0 cursor-pointer" style="font-size: 13.5px;">@lang('index.version_check_enabled')</label>
                                <small class="text-muted d-block" style="font-size: 11.5px;">Enable or pause version comparison alerts across all mobile app endpoints.</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-muted border" id="enabledStatusLabel">{{ !empty($appVersionSetting['enabled']) ? 'Enabled' : 'Disabled' }}</span>
                    </div>

                    <div class="row g-4">
                        <!-- Left Column: Versions & URLs -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                                <i data-feather="sliders" style="width: 14px; height: 14px;" class="text-primary"></i>
                                Version Targets & Constraints
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="targetVersionInput">
                                    @lang('index.target_app_version') <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i data-feather="tag" style="width: 14px; height: 14px;"></i></span>
                                    <input type="text" name="target_version" id="targetVersionInput" class="form-control" placeholder="13.00" value="{{ $appVersionSetting['target_version'] ?? '13.00' }}" required>
                                </div>
                                <div class="form-text text-muted" style="font-size: 11.5px;">
                                    @lang('index.target_app_version_hint')
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="minVersionInput">
                                    @lang('index.min_required_version') <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i data-feather="shield" style="width: 14px; height: 14px;"></i></span>
                                    <input type="text" name="min_version" id="minVersionInput" class="form-control" placeholder="13.00" value="{{ $appVersionSetting['min_version'] ?? '13.00' }}" required>
                                </div>
                                <div class="form-text text-muted" style="font-size: 11.5px;">
                                    @lang('index.min_required_version_hint')
                                </div>
                            </div>

                            <!-- Force Update Switch -->
                            <div class="p-3 mb-3 rounded-3 border" style="background-color: #fff; border-color: #e2e8f0 !important;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="forceUpdateSwitch" class="fw-medium text-dark mb-0 cursor-pointer small">@lang('index.force_update')</label>
                                        <small class="text-muted d-block" style="font-size: 11.5px;">@lang('index.force_update_hint')</small>
                                    </div>
                                    <label class="switch mb-0">
                                        <input type="checkbox" name="force_update" value="1" id="forceUpdateSwitch" {{ !empty($appVersionSetting['force_update']) ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- URLs -->
                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="androidUrlInput">
                                    @lang('index.android_download_url')
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i data-feather="download" style="width: 14px; height: 14px;"></i></span>
                                    <input type="url" name="android_url" id="androidUrlInput" class="form-control" placeholder="https://hr.kneayerng.com or Play Store link" value="{{ $appVersionSetting['android_url'] ?? '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="iosUrlInput">
                                    @lang('index.ios_download_url')
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i data-feather="external-link" style="width: 14px; height: 14px;"></i></span>
                                    <input type="url" name="ios_url" id="iosUrlInput" class="form-control" placeholder="https://apps.apple.com/..." value="{{ $appVersionSetting['ios_url'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Alert Content & Live Mobile Preview -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                                <i data-feather="message-circle" style="width: 14px; height: 14px;" class="text-primary"></i>
                                Alert Content & Live Preview
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="alertTitleInput">
                                    @lang('index.alert_title') <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="alert_title" id="alertTitleInput" class="form-control form-control-sm" placeholder="New Version Available" value="{{ $appVersionSetting['alert_title'] ?? 'New Version Available' }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="alertMessageInput">
                                    @lang('index.alert_message') <span class="text-danger">*</span>
                                </label>
                                <textarea name="alert_message" id="alertMessageInput" class="form-control form-control-sm" rows="3" placeholder="A new version of the app (:target_version) is available..." required>{{ $appVersionSetting['alert_message'] ?? 'A new version of the app (:target_version) is available. Please update to enjoy the latest features and improvements.' }}</textarea>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    @lang('index.alert_message_hint')
                                </div>
                            </div>

                            <!-- Live Mobile Dialog Preview -->
                            <div class="p-3 mb-3 rounded-3 border" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f6 100%); border-color: #cbd5e1 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.5px;">
                                        <i data-feather="eye" style="width: 11px; height: 11px;" class="me-1"></i> @lang('index.preview_alert')
                                    </span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 9.5px;" id="previewModeBadge">Soft Alert</span>
                                </div>

                                <div class="bg-white rounded-3 p-3 shadow-sm border text-center" style="border-color: #e2e8f0 !important;">
                                    <div class="mx-auto mb-2 p-2 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i data-feather="alert-circle" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" id="previewTitle" style="font-size: 14px;">New Version Available</h6>
                                    <p class="text-muted mb-3" id="previewMessage" style="font-size: 12px; line-height: 1.4;"></p>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-3 rounded-pill" id="previewLaterBtn" style="font-size: 11px;">
                                            @lang('index.later')
                                        </button>
                                        <button type="button" class="btn btn-primary btn-xs py-1 px-3 rounded-pill" id="previewUpdateBtn" style="font-size: 11px;">
                                            @lang('index.update_now')
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Interactive Version Tester -->
                            <div class="p-3 rounded-3 border" style="background-color: #ffffff; border-color: #e2e8f0 !important;">
                                <label class="form-label fw-medium text-dark small mb-1.5 d-flex align-items-center gap-1" for="testVersionInput">
                                    <i data-feather="check-circle" style="width: 12px; height: 12px;" class="text-success"></i>
                                    @lang('index.test_version')
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="testVersionInput" class="form-control" placeholder="@lang('index.test_version_placeholder')" value="12.00">
                                    <button class="btn btn-outline-primary" type="button" id="runTestVersionBtn">Simulate</button>
                                </div>
                                <div id="testVersionVerdict" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-end gap-2" style="border-color: #f1f5f9 !important;">
                        <button type="button" class="btn btn-outline-warning btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#sendUpdateAlertModal">
                            <i data-feather="send" style="width: 14px; height: 14px;"></i> @lang('index.send_update_alert')
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5">
                            <i data-feather="save" style="width: 14px; height: 14px;"></i> @lang('index.update')
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>@lang('index.name')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($appSettings as $key => $value)

                                <tr>
                                    <td><strong> {{( $value->name =='override bssid') ? __('index.check_router_bssid'):__('seeder.'.$value->slug)}} </strong> </td>
                                    <td class="text-center">
                                        @if($value->slug === 'android-apk')
                                            <form action="{{ route('admin.app-settings.android-apk.update') }}" method="post" enctype="multipart/form-data" class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                                @csrf
                                                <label class="switch mb-0">
                                                    <input class="toggleStatus" href="{{route('admin.app-settings.toggle-status',$value->id)}}"
                                                           type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <input type="file" name="android_apk" class="form-control form-control-sm" accept=".apk" required style="max-width: 260px;">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="link-icon" data-feather="upload"></i> @lang('index.upload_android_apk')
                                                </button>
                                                @if($value->value)
                                                    <a href="{{ asset($value->value) }}" class="btn btn-outline-secondary btn-sm" download>
                                                        <i class="link-icon" data-feather="download"></i> @lang('index.current_file')
                                                    </a>
                                                @endif
                                            </form>
                                        @else
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.app-settings.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        @endif
                                    </td>
                                </tr>


                                @empty
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel">@lang('index.export_table_data')</h5>
                    </div>
                    <div class="modal-body">
                        <a href="{{route('admin.leave-type-export')}}">
                            <button class="btn btn-secondary btn-sm">@lang('index.leave_types') </button>
                        </a>
                        <a href="{{route('admin.leave-request-export')}}">
                            <button class="btn btn-success btn-sm">@lang('index.leave_requests') </button>
                        </a>
                        <a href="{{route('admin.employee-lists-export')}}">
                            <button class="btn btn-warning btn-sm">@lang('index.employee_lists') </button>
                        </a>
                        <a href="{{route('admin.attendance-lists-export')}}">
                            <button class="btn btn-danger btn-sm">@lang('index.attendances')  </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Update Alert Modal -->
        <div class="modal fade" id="sendUpdateAlertModal" tabindex="-1" aria-labelledby="sendUpdateAlertModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-warning bg-opacity-10 border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 bg-warning bg-opacity-20 text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i data-feather="send" style="width: 16px; height: 16px;"></i>
                            </div>
                            <h5 class="modal-title fw-semibold text-dark" id="sendUpdateAlertModalLabel" style="font-size: 15.5px;">@lang('index.send_update_alert_to_users')</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.app-settings.send-update-alert') }}" method="POST" id="sendUpdateAlertForm">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-start gap-2" style="font-size: 12px;">
                                <i data-feather="info" style="width: 15px; height: 15px; flex-shrink: 0;" class="mt-0.5"></i>
                                <span>@lang('index.send_alert_notice')</span>
                            </div>

                            <!-- Target Version Badge -->
                            <div class="p-2.5 mb-3 rounded-2 bg-light border d-flex align-items-center justify-content-between" style="font-size: 12.5px;">
                                <span class="text-muted">@lang('index.target_app_version'):</span>
                                <span class="badge bg-primary px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                    v{{ $appVersionSetting['target_version'] ?? '13.00' }}
                                </span>
                            </div>

                            <!-- Target Audience -->
                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small">@lang('index.target_audience') <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column gap-2">
                                    <label class="p-2.5 rounded-2 border d-flex align-items-center gap-2.5 cursor-pointer bg-white" style="border-color: #e2e8f0 !important;">
                                        <input type="radio" name="target_audience" value="all" checked class="form-check-input mt-0">
                                        <div>
                                            <span class="fw-medium text-dark d-block" style="font-size: 12.5px;">@lang('index.all_mobile_users')</span>
                                            <small class="text-muted" style="font-size: 11px;">Send push notification and in-app notice to all registered mobile app users.</small>
                                        </div>
                                    </label>
                                    <label class="p-2.5 rounded-2 border d-flex align-items-center gap-2.5 cursor-pointer bg-white" style="border-color: #e2e8f0 !important;">
                                        <input type="radio" name="target_audience" value="outdated" class="form-check-input mt-0">
                                        <div>
                                            <span class="fw-medium text-dark d-block" style="font-size: 12.5px;">{{ __('index.outdated_devices_only', ['version' => $appVersionSetting['target_version'] ?? '13.00']) }}</span>
                                            <small class="text-muted" style="font-size: 11px;">Target only devices that have not yet updated to target version.</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Alert Title -->
                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small" for="modalAlertTitle">@lang('index.alert_title') <span class="text-danger">*</span></label>
                                <input type="text" name="alert_title" id="modalAlertTitle" class="form-control form-control-sm" value="{{ $appVersionSetting['alert_title'] ?? 'New Version Available' }}" required>
                            </div>

                            <!-- Alert Message -->
                            <div class="mb-2">
                                <label class="form-label fw-medium text-dark small" for="modalAlertMessage">@lang('index.alert_message') <span class="text-danger">*</span></label>
                                <textarea name="alert_message" id="modalAlertMessage" class="form-control form-control-sm" rows="3" required>{{ $appVersionSetting['alert_message'] ?? 'A new version of the app (:target_version) is available. Please update to enjoy the latest features and improvements.' }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2 px-4 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">@lang('index.cancel')</button>
                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 text-dark fw-medium d-inline-flex align-items-center gap-1.5" id="btnSubmitSendAlert">
                                <i data-feather="send" style="width: 13px; height: 13px;"></i> @lang('index.send_now')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '@lang('index.change_status_confirm')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,
                    padding:'10px 50px 10px 50px',
                    // width:'500px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            });

            // App Version Real-time Preview and Simulator
            function compareVersions(v1, v2) {
                var clean1 = (v1 || '').replace(/^[vV]/, '').split('+')[0].split('-')[0].trim();
                var clean2 = (v2 || '').replace(/^[vV]/, '').split('+')[0].split('-')[0].trim();
                if (!clean1 || !clean2) return 0;

                var parts1 = clean1.split('.').map(function(n) { return parseInt(n, 10) || 0; });
                var parts2 = clean2.split('.').map(function(n) { return parseInt(n, 10) || 0; });
                var len = Math.max(parts1.length, parts2.length);

                for (var i = 0; i < len; i++) {
                    var num1 = parts1[i] || 0;
                    var num2 = parts2[i] || 0;
                    if (num1 < num2) return -1;
                    if (num1 > num2) return 1;
                }
                return 0;
            }

            function updatePreview() {
                var title = $('#alertTitleInput').val() || 'New Version Available';
                var targetVer = $('#targetVersionInput').val() || '13.00';
                var minVer = $('#minVersionInput').val() || '13.00';
                var rawMsg = $('#alertMessageInput').val() || '';
                var isForce = $('#forceUpdateSwitch').is(':checked');
                var isEnabled = $('#versionCheckEnabledSwitch').is(':checked');

                var formattedMsg = rawMsg
                    .replace(/:target_version/g, targetVer)
                    .replace(/:min_version/g, minVer)
                    .replace(/:current_version/g, $('#testVersionInput').val() || targetVer);

                $('#previewTitle').text(title);
                $('#previewMessage').text(formattedMsg);

                if (isForce) {
                    $('#previewModeBadge').attr('class', 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5').text('Force Update');
                    $('#previewLaterBtn').hide();
                } else {
                    $('#previewModeBadge').attr('class', 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5').text('Soft Alert');
                    $('#previewLaterBtn').show();
                }

                $('#enabledStatusLabel').text(isEnabled ? 'Enabled' : 'Disabled')
                    .attr('class', isEnabled ? 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'badge bg-light text-muted border');

                runVersionSimulator();
            }

            function runVersionSimulator() {
                var testVer = ($('#testVersionInput').val() || '').trim();
                var targetVer = ($('#targetVersionInput').val() || '').trim();
                var minVer = ($('#minVersionInput').val() || '').trim();
                var isForce = $('#forceUpdateSwitch').is(':checked');
                var isEnabled = $('#versionCheckEnabledSwitch').is(':checked');

                if (!isEnabled) {
                    $('#testVersionVerdict').html(
                        '<div class="alert alert-secondary py-1.5 px-2 mb-0 d-flex align-items-center gap-1.5" style="font-size: 11.5px;">' +
                        '<i data-feather="info" style="width: 13px; height: 13px;"></i> ' +
                        'Version checking is currently <strong>Disabled</strong>. No alerts will be sent to users.' +
                        '</div>'
                    );
                    if (window.feather) feather.replace();
                    return;
                }

                if (!testVer) {
                    $('#testVersionVerdict').empty();
                    return;
                }

                var isUnderTarget = compareVersions(testVer, targetVer) < 0;
                var isUnderMin = compareVersions(testVer, minVer) < 0;

                if (isUnderMin || (isForce && isUnderTarget)) {
                    $('#testVersionVerdict').html(
                        '<div class="alert alert-danger py-1.5 px-2 mb-0 d-flex align-items-center gap-1.5" style="font-size: 11.5px;">' +
                        '<i data-feather="alert-triangle" style="width: 13px; height: 13px;"></i> ' +
                        '<strong>Update Required:</strong> Version <code>' + testVer + '</code> is under required target <code>' + targetVer + '</code>. User will be blocked until updated.' +
                        '</div>'
                    );
                } else if (isUnderTarget) {
                    $('#testVersionVerdict').html(
                        '<div class="alert alert-warning py-1.5 px-2 mb-0 d-flex align-items-center gap-1.5" style="font-size: 11.5px;">' +
                        '<i data-feather="alert-circle" style="width: 13px; height: 13px;"></i> ' +
                        '<strong>Alert Shown:</strong> Version <code>' + testVer + '</code> is under target <code>' + targetVer + '</code>. User will see update alert modal.' +
                        '</div>'
                    );
                } else {
                    $('#testVersionVerdict').html(
                        '<div class="alert alert-success py-1.5 px-2 mb-0 d-flex align-items-center gap-1.5" style="font-size: 11.5px;">' +
                        '<i data-feather="check-circle" style="width: 13px; height: 13px;"></i> ' +
                        '<strong>Up to Date:</strong> Version <code>' + testVer + '</code> &ge; target <code>' + targetVer + '</code>. No alert shown.' +
                        '</div>'
                    );
                }

                if (window.feather) feather.replace();
            }

            $('#alertTitleInput, #targetVersionInput, #minVersionInput, #alertMessageInput').on('input', updatePreview);
            $('#forceUpdateSwitch, #versionCheckEnabledSwitch').on('change', updatePreview);
            $('#testVersionInput').on('input', runVersionSimulator);
            $('#runTestVersionBtn').on('click', runVersionSimulator);

            // Initial render
            updatePreview();
        });
    </script>
@endsection
