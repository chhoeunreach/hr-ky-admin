@php
    $dualSyncModules = [
        'Attendance', 'Leave', 'Time Leave', 'Leave Approval', 'Advance Salary', 'Payroll Management',
        'Employee Salary', 'Payroll Queue', 'Support', 'Tada', 'Task Management', 'Notice', 'Holiday',
        'Award Management', 'Warning', 'Complaint', 'Resignation Management', 'Company Documents',
        'Store Phone Number', 'Asset Management', 'Certificate Management', 'Repair Price',
        'Flash View', 'Social Feed', 'Social Media Marketing', 'Home Highlights', 'App Appearance',
        'Attendance Method', 'Mobile Notification'
    ];
@endphp

<style>
    .role-platform-banner {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f6 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 22px;
    }
    .platform-pill-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .preset-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 18px;
        box-shadow: 0 2px 6px rgba(15,23,42,0.03);
    }
    .preset-btn {
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 12px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .preset-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .scope-badge-web {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
    }
    .scope-badge-mobile {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
    }
    .scope-badge-dual {
        background: #faf5ff;
        color: #9333ea;
        border: 1px solid #e9d5ff;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
    }
    .permission-tab-btn {
        font-size: 14px;
        font-weight: 700;
        padding: 12px 20px;
        border-radius: 10px 10px 0 0 !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .permission-tab-btn.active {
        border-bottom: 3px solid #2563eb !important;
        color: #1d4ed8 !important;
    }
    .group-checkbox-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(15,23,42,0.03);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .group-checkbox-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15,23,42,0.05);
    }
    .filter-search-box {
        position: relative;
        max-width: 380px;
    }
    .filter-search-box input {
        border-radius: 20px;
        padding-left: 36px;
        font-size: 12px;
    }
    .filter-search-box .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
        pointer-events: none;
    }
</style>

<!-- Role Platform Status Banner -->
<div class="role-platform-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h5 class="mb-0 fw-bold text-dark">
                {{ ucfirst($role->name) }}
            </h5>
            <span class="badge bg-secondary font-monospace" style="font-size: 11px;">Slug: {{ $role->slug ?: 'N/A' }}</span>
        </div>
        <p class="text-muted small mb-0">
            កំណត់សិទ្ធិប្រើប្រាស់សម្រាប់តួនាទីនេះ។ អ្នកអាចបែងចែករវាងសិទ្ធិលើ <strong>Web Admin Portal</strong> និង <strong>Mobile App</strong> អោយដាច់ពីគ្នា។
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($role->backend_login_authorize)
            <span class="platform-pill-badge bg-success text-white shadow-sm">
                <i class="link-icon" data-feather="check-circle" style="width: 14px; height: 14px;"></i> ចូល Web Admin បាន (Web Enabled)
            </span>
        @else
            <span class="platform-pill-badge bg-warning text-dark shadow-sm">
                <i class="link-icon" data-feather="smartphone" style="width: 14px; height: 14px;"></i> ប្រើតែលើ Mobile App ប៉ុណ្ណោះ (Mobile Only)
            </span>
        @endif
        <span class="platform-pill-badge bg-info text-white shadow-sm">
            <i class="link-icon" data-feather="layers" style="width: 14px; height: 14px;"></i> សរុប {{ count($role_permission) }} សិទ្ធិ
        </span>
    </div>
</div>

<!-- 1-Click Role Presets Toolbar -->
<div class="preset-toolbar">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark small"><i class="link-icon text-primary" data-feather="zap" style="width: 15px; height: 15px;"></i> កំណត់សិទ្ធិស្វ័យប្រវត្តិតាមតួនាទី (1-Click Role Presets):</span>
            <span class="text-muted small d-none d-md-inline">ចុចដើម្បីជ្រើសរើសសិទ្ធិសមស្របភ្លាមៗ</span>
        </div>
        <div id="presetFeedback" class="small fw-bold text-success" style="min-height: 18px; transition: opacity 0.3s ease;"></div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-danger btn-sm preset-btn" id="btnPresetSuperAdmin" data-preset="super_admin">
            👑 Super Admin (ពេញលេញទាំងអស់)
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm preset-btn" id="btnPresetHrManager" data-preset="hr_manager">
            🏢 HR Manager (គ្រប់គ្រង HR & បុគ្គលិក)
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm preset-btn" id="btnPresetBranchSupervisor" data-preset="branch_supervisor">
            📍 Branch Supervisor (ប្រធានសាខា)
        </button>
        <button type="button" class="btn btn-outline-success btn-sm preset-btn" id="btnPresetEmployeeMobile" data-preset="employee_mobile">
            👤 General Employee (បុគ្គលិកទូទៅ - ប្រើតែ Mobile)
        </button>
        <button type="button" class="btn btn-outline-info btn-sm preset-btn" id="btnPresetPayrollCounter" data-preset="payroll_counter">
            🎫 Payroll Queue Counter (បញ្ជរបើកប្រាក់ខែ)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn ms-auto" id="btnPresetClearAll" data-preset="clear_all">
            🧹 ដោះចេញទាំងអស់ (Clear All)
        </button>
    </div>
</div>

<!-- Nav Tabs for Web vs Mobile Platforms -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border-bottom pb-2 mb-3">
    <ul class="nav nav-tabs border-0" id="myTab" role="tablist">
        @foreach($permissionGroupTypeList as $key => $permissionGroupType)
            @php
                $isWeb = ($permissionGroupType->slug === 'web');
                $tabIcon = $isWeb ? '🖥️' : '📱';
                $tabTitle = $isWeb ? 'Web Portal (Back-Office Admin)' : 'Mobile App (Frontline & Self-Service)';
                $tabSubtitle = $isWeb ? 'ផ្ទាំងគ្រប់គ្រង Web' : 'ទូរស័ព្ទដៃ App';
                $groupCount = $permissionGroupType->permissionGroups->count();
            @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link permission-tab-btn {{ $key == 0 ? 'active' : '' }}" 
                        data-bs-toggle="tab" 
                        data-bs-target="#tab-{{ $permissionGroupType->slug }}" 
                        type="button" 
                        role="tab" 
                        aria-controls="tab-{{ $permissionGroupType->slug }}" 
                        aria-selected="{{ $key == 0 ? 'true' : 'false' }}" 
                        id="{{$permissionGroupType->slug}}">
                    <span>{{ $tabIcon }} {{ $tabTitle }}</span>
                    <span class="badge {{ $isWeb ? 'bg-primary' : 'bg-success' }} rounded-pill" style="font-size: 10px;">
                        {{ $groupCount }} ផ្នែក
                    </span>
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Quick Search / Filter Box -->
    <div class="filter-search-box">
        <span class="search-icon"><i class="link-icon" data-feather="search" style="width: 14px; height: 14px;"></i></span>
        <input type="text" id="permissionSearchFilter" class="form-control form-control-sm" placeholder="ស្វែងរកផ្នែក / Filter permissions...">
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="myTabContent">
    @foreach($permissionGroupTypeList as $key => $permissionGroupType)
        @php
            $isWebTab = ($permissionGroupType->slug === 'web');
            $permissionModules = $permissionGroupTypeList->where('slug', $permissionGroupType->slug)->first();
        @endphp

        <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="tab-{{ $permissionGroupType->slug }}" role="tabpanel" aria-labelledby="{{ $permissionGroupType->slug }}">
            
            <!-- Tab Platform Explanatory Note -->
            <div class="alert {{ $isWebTab ? 'alert-primary' : 'alert-success' }} d-flex align-items-center justify-content-between p-2 mb-3" style="border-radius: 8px;">
                <div class="d-flex align-items-center gap-2 small">
                    <i class="link-icon" data-feather="{{ $isWebTab ? 'monitor' : 'smartphone' }}" style="width: 16px; height: 16px;"></i>
                    @if($isWebTab)
                        <span><strong>សិទ្ធិ Web Admin:</strong> សម្រាប់អ្នកគ្រប់គ្រង និង HR ប្រើលើកុំព្យូទ័រ (ផ្ទាំងគ្រប់គ្រង, ការកំណត់ប្រព័ន្ធ, របាយការណ៍ពន្ធ, បើកប្រាក់ខែ)។</span>
                    @else
                        <span><strong>សិទ្ធិ Mobile App:</strong> សម្រាប់បុគ្គលិក និងអ្នកប្រើលើទូរស័ព្ទដៃ (កត់វត្តមាន, សុំច្បាប់, បើកលុយមុន, Chat, សំបុត្របើកប្រាក់ខែ, បញ្ជីជួសជុល)។</span>
                    @endif
                </div>
                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                    <label class="form-check-label small fw-bold mb-0 cursor-pointer" for="tabCheckAll-{{ $permissionGroupType->slug }}">
                        ជ្រើសទាំងអស់ក្នុងផ្ទាំងនេះ (Check All in Tab)
                    </label>
                    <input class="form-check-input tab-master-check" type="checkbox" id="tabCheckAll-{{ $permissionGroupType->slug }}" data-target-tab="tab-{{ $permissionGroupType->slug }}">
                </div>
            </div>

            <div class="row {{ $permissionModules->slug }}" id="modulesList-{{ $permissionGroupType->slug }}">
                @foreach($permissionModules->permissionGroups as $key=>$value)

                @php
                    $collectionArray = $value->getPermission->pluck('id')->toArray();
                    $checkAll = '';

                    if(count($role_permission) > 0){
                        $diff = array_diff($collectionArray, $role_permission);
                        if (empty($diff)) {
                           $checkAll = 'checked';
                        }
                    }

                    $isDual = in_array($value->name, $dualSyncModules, true);
                @endphp
                <div class="col-lg-12 permission-group-col" data-group-name="{{ strtolower($value->name) }}">
                    <div class="group-checkbox-card">
                        <div class="title-ch mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0 fw-bold {{ $isWebTab ? 'text-primary' : 'text-success' }}">
                                    {{ $value->name }}
                                </h6>
                                @if($isWebTab)
                                    @if($isDual)
                                        <span class="scope-badge-dual" title="មុខងារនេះមានទាំងលើ Web និង Mobile">
                                            🔄 Dual Sync (Web & Mobile)
                                        </span>
                                    @else
                                        <span class="scope-badge-web" title="មុខងារនេះប្រើបានតែលើ Web Portal ប៉ុណ្ណោះ">
                                            🖥️ Web Admin Only
                                        </span>
                                    @endif
                                @else
                                    <span class="scope-badge-mobile" title="មុខងារនេះដំណើរការលើទូរស័ព្ទដៃ">
                                        📱 Mobile Feature
                                    </span>
                                @endif
                            </div>

                            @if($value->name == 'Repair Price')
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="small text-muted me-1 fw-bold">Preset Role:</span>
                                    <button type="button" class="btn btn-xs btn-outline-purple repair-preset-btn" data-role="admin" style="padding: 2px 8px; font-size: 11px;">
                                        Admin (អភិបាល)
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-warning repair-preset-btn" data-role="manager" style="padding: 2px 8px; font-size: 11px;">
                                        Manager (អ្នកគ្រប់គ្រង)
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary repair-preset-btn" data-role="employee" style="padding: 2px 8px; font-size: 11px;">
                                        Employee (បុគ្គលិក)
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-danger repair-preset-btn" data-role="none" style="padding: 2px 8px; font-size: 11px;">
                                        No Access (គ្មានសិទ្ធិ)
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="head-checkbox d-flex align-items-center gap-3 flex-wrap">
                            <div class="checkAll">
                                <label class="label-ch lh-1 cursor-pointer">
                                    <input class="js-check-all" type="checkbox" name=""
                                           data-check-all="website" {{ $checkAll }}>
                                    <span class="text fw-bold text-muted small">@lang('index.check_all')</span>
                                </label>
                            </div>
                            <ul class="js-check-all-target list-ch d-flex align-items-center justify-content-start gap-3 p-0 mb-0 flex-wrap" data-check-all="website" style="list-style: none;">
                                @foreach($value->getPermission as $keys => $permission)
                                    @php
                                        $checked='';
                                        if(count($role_permission) > 0){
                                            if(in_array($permission->id,$role_permission)){
                                                $checked = "checked = 'checked'";
                                            }
                                        }
                                    @endphp
                                    <li class="item mb-1">
                                        <label class="label lh-1 cursor-pointer">
                                            <input class="module_checkbox"
                                                   type="checkbox"
                                                   id="{{$permission->permission_key}}"
                                                   name="permission_value[]"
                                                   value="{{$permission->id}}"
                                                   data-permission-key="{{$permission->permission_key}}"
                                                   data-group-name="{{ $value->name }}"
                                                   data-platform="{{ $isWebTab ? 'web' : 'mobile' }}"
                                                {{$checked}}>
                                            <span class="text small fw-semibold text-dark">{{$permission->name}}</span>
                                            @if($permission->permission_key == 'access_admin_leave')
                                                <p class="grant_leave small text-danger mb-0">
                                                    {{ __('index.admin_permission_msg') }}
                                                </p>
                                            @endif
                                            @if($permission->permission_key == 'access_admin_panel')
                                                <span class="badge bg-danger ms-1" style="font-size: 9px;" title="បើដោះសញ្ញាធីកនេះ គណនីនឹងមិនអាចចូលកាន់ផ្ទាំងគ្រប់គ្រង Web បានទេ">
                                                    🔒 Gatekeeper (Web Access)
                                                </span>
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<!-- Sticky Save Floating Footer Bar -->
<div class="d-flex align-items-center justify-content-between p-3 mt-4 border-top bg-light rounded shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary" id="selectedPermissionsCount">
            {{ count($role_permission) }}
        </span>
        <span class="small text-muted fw-bold">សិទ្ធិត្រូវបានជ្រើសរើស (Permissions Selected)</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="link-icon" data-feather="x" style="width: 14px; height: 14px;"></i> @lang('index.cancel')
        </a>
        <button type="submit" class="btn btn-success btn-md px-4 fw-bold shadow-sm" id="btnSavePermissions">
            <i class="link-icon" data-feather="check" style="width: 16px; height: 16px;"></i> {{$isEdit ? __('index.update'): __('index.save') }}
        </button>
    </div>
</div>
