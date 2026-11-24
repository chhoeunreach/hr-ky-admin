@extends('layouts.master')
@section('title', __('index.theme_modes'))
@section('action', __('index.theme_modes_action'))

@section('styles')
<style>
    .tm-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(23, 43, 77, 0.04);
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .tm-card:hover {
        box-shadow: 0 8px 24px rgba(23, 43, 77, 0.08);
    }
    .tm-card-header {
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 20px;
    }
    .tm-preview-box {
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 14px;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .tm-color-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .tm-color-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tm-swatch {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .tm-color-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        line-height: 1.1;
    }
    .tm-color-val {
        font-size: 0.75rem;
        font-family: monospace;
        color: #1e293b;
        font-weight: 600;
    }
    .color-input-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .color-picker-thumb {
        width: 42px;
        height: 38px;
        padding: 2px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        cursor: pointer;
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 18px;
    }
    .nav-tabs .nav-link.active {
        color: #2563eb;
        border-bottom: 2px solid #2563eb;
        background: transparent;
    }
    .stat-badge {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
    }
</style>
@endsection

@section('main-content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-1"></i> {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i data-feather="alert-circle" class="me-1"></i> {{ __('index.theme_modes_notice') }}:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $themesCol = collect($themes);
        $coreThemes = $themesCol->filter(fn($t) => !empty($t['is_system']));
        $customThemes = $themesCol->filter(fn($t) => empty($t['is_system']));
        $activeCount = $themesCol->filter(fn($t) => !empty($t['is_active']))->count();
        $hiddenCount = $themesCol->filter(fn($t) => empty($t['is_active']))->count();
    @endphp

    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-1 text-dark fw-bold">{{ __('index.theme_modes') }}</h4>
            <p class="text-muted small mb-0">{{ __('index.theme_modes_subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.mobile-tools.theme-modes.reset') }}" method="POST" onsubmit='return confirm(@json(__('index.confirm_reset_all_themes')));'>
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i data-feather="rotate-ccw" class="me-1" style="width:14px;height:14px"></i> {{ __('index.reset_all_defaults') }}
                </button>
            </form>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addThemeModal">
                <i data-feather="plus" class="me-1" style="width:14px;height:14px"></i> {{ __('index.add_custom_theme') }}
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="stat-badge bg-primary-subtle text-primary border border-primary-subtle">
            <i data-feather="shield" style="width:13px;height:13px" class="me-1"></i> {{ __('index.core_systems') }}: <strong>{{ $coreThemes->count() }}</strong>
        </span>
        <span class="stat-badge bg-warning-subtle text-warning border border-warning-subtle">
            <i data-feather="plus-circle" style="width:13px;height:13px" class="me-1"></i> {{ __('index.custom_themes') }}: <strong>{{ $customThemes->count() }}</strong>
        </span>
        <span class="stat-badge bg-success-subtle text-success border border-success-subtle">
            <i data-feather="eye" style="width:13px;height:13px" class="me-1"></i> {{ __('index.shown_on_mobile') }}: <strong>{{ $activeCount }}</strong>
        </span>
        @if($hiddenCount > 0)
            <span class="stat-badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                <i data-feather="eye-off" style="width:13px;height:13px" class="me-1"></i> {{ __('index.hidden_on_mobile') }}: <strong>{{ $hiddenCount }}</strong>
            </span>
        @endif
    </div>

    <!-- Tabs: Core Systems vs Custom Themes vs All -->
    <ul class="nav nav-tabs mb-4" id="themeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#tab-all" type="button" role="tab">
                {{ __('index.all_themes') }} ({{ $themesCol->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="core-tab" data-bs-toggle="tab" data-bs-target="#tab-core" type="button" role="tab">
                <i data-feather="shield" style="width:14px;height:14px" class="me-1"></i> {{ __('index.core_systems') }} ({{ $coreThemes->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="custom-tab" data-bs-toggle="tab" data-bs-target="#tab-custom" type="button" role="tab">
                <i data-feather="plus-circle" style="width:14px;height:14px" class="me-1"></i> {{ __('index.custom_themes') }} ({{ $customThemes->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="themeTabsContent">
        <!-- ALL TAB -->
        <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
            <div class="row g-4">
                @foreach($themes as $key => $theme)
                    @include('admin.mobile_tool.partial.theme_card', ['theme' => $theme, 'key' => $key, 'roles' => $roles])
                @endforeach
            </div>
        </div>

        <!-- CORE SYSTEMS TAB -->
        <div class="tab-pane fade" id="tab-core" role="tabpanel">
            <div class="alert alert-info py-2 px-3 small mb-3 border-0 bg-info-subtle text-info d-flex align-items-center">
                <i data-feather="info" class="me-2" style="width:16px;height:16px"></i>
                <span>{{ __('index.theme_modes_core_info') }}</span>
            </div>
            <div class="row g-4">
                @foreach($coreThemes as $key => $theme)
                    @include('admin.mobile_tool.partial.theme_card', ['theme' => $theme, 'key' => $key, 'roles' => $roles])
                @endforeach
            </div>
        </div>

        <!-- CUSTOM THEMES TAB -->
        <div class="tab-pane fade" id="tab-custom" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted small mb-0">{{ __('index.theme_modes_custom_intro') }}</p>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addThemeModal">
                    <i data-feather="plus" class="me-1" style="width:14px;height:14px"></i> Add Custom Theme
                </button>
            </div>
            @if($customThemes->isEmpty())
                <div class="card p-5 text-center bg-light border-dashed">
                    <div class="mb-3">
                        <i data-feather="palette" style="width:48px;height:48px;color:#94a3b8"></i>
                    </div>
                    <h5 class="fw-bold text-dark">{{ __('index.theme_modes_empty_title') }}</h5>
                    <p class="text-muted small">{{ __('index.theme_modes_empty_text') }}</p>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addThemeModal">
                            <i data-feather="plus" class="me-1" style="width:14px;height:14px"></i> {{ __('index.theme_modes_create_first') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach($customThemes as $key => $theme)
                        @include('admin.mobile_tool.partial.theme_card', ['theme' => $theme, 'key' => $key, 'roles' => $roles])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Custom Theme Modal -->
<div class="modal fade" id="addThemeModal" tabindex="-1" aria-labelledby="addThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.mobile-tools.theme-modes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addThemeModalLabel">{{ __('index.theme_modes_add_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_key') }} <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control" placeholder="{{ __('index.theme_modes_key_example') }}" required pattern="^[a-z0-9_]+$">
                            <div class="form-text">{{ __('index.theme_modes_key_hint') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('index.theme_modes_name_example') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_description') }}</label>
                            <input type="text" name="description" class="form-control" placeholder="{{ __('index.theme_modes_description_hint') }}">
                        </div>

                        <!-- 6 Colors -->
                        <div class="col-12"><h6 class="fw-bold mt-2 mb-1">{{ __('index.theme_modes_color_customization') }}</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_primary') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#2563EB" id="add_primary_picker">
                                <input type="text" name="primary_color" id="add_primary_text" class="form-control font-monospace" value="#2563EB" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_secondary') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#475569" id="add_secondary_picker">
                                <input type="text" name="secondary_color" id="add_secondary_text" class="form-control font-monospace" value="#475569" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_accent') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#F59E0B" id="add_accent_picker">
                                <input type="text" name="accent_color" id="add_accent_text" class="form-control font-monospace" value="#F59E0B" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_background') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#FFFFFF" id="add_background_picker">
                                <input type="text" name="background_color" id="add_background_text" class="form-control font-monospace" value="#FFFFFF" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_card') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#F8FAFC" id="add_card_picker">
                                <input type="text" name="card_color" id="add_card_text" class="form-control font-monospace" value="#F8FAFC" required pattern="^#[0-9a-fA-F]{6,8}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_text') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" value="#0F172A" id="add_text_picker">
                                <input type="text" name="text_color" id="add_text_text" class="form-control font-monospace" value="#0F172A" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>

                        <!-- Settings & Roles -->
                        <div class="col-md-6 mt-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_dark" value="1" id="add_is_dark">
                                <label class="form-check-label fw-semibold" for="add_is_dark">{{ __('index.theme_modes_dark_theme') }}</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add_is_active" checked>
                                <label class="form-check-label fw-semibold" for="add_is_active">{{ __('index.theme_modes_show_mobile') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold">{{ __('index.allowed_roles') }}</label>
                            <div class="p-2 border rounded" style="max-height: 140px; overflow-y: auto;">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allowed_roles[]" value="{{ $role->slug }}" id="add_role_{{ $role->id }}">
                                        <label class="form-check-label" for="add_role_{{ $role->id }}">{{ $role->name }} ({{ $role->slug }})</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">{{ __('index.theme_modes_roles_hint') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.theme_modes_cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('index.theme_modes_create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Theme Modal -->
<div class="modal fade" id="editThemeModal" tabindex="-1" aria-labelledby="editThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editThemeForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editThemeModalLabel">{{ __('index.theme_modes_edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_key') }}</label>
                            <input type="text" id="edit_key" class="form-control bg-light font-monospace" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('index.theme_modes_description') }}</label>
                            <input type="text" name="description" id="edit_description" class="form-control">
                        </div>

                        <!-- 6 Colors -->
                        <div class="col-12"><h6 class="fw-bold mt-2 mb-1">{{ __('index.theme_modes_color_customization') }}</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_primary') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_primary_picker">
                                <input type="text" name="primary_color" id="edit_primary_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_secondary') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_secondary_picker">
                                <input type="text" name="secondary_color" id="edit_secondary_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_accent') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_accent_picker">
                                <input type="text" name="accent_color" id="edit_accent_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_background') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_background_picker">
                                <input type="text" name="background_color" id="edit_background_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_card') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_card_picker">
                                <input type="text" name="card_color" id="edit_card_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6,8}$">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('index.color_text') }}</label>
                            <div class="color-input-group">
                                <input type="color" class="color-picker-thumb" id="edit_text_picker">
                                <input type="text" name="text_color" id="edit_text_text" class="form-control font-monospace" required pattern="^#[0-9a-fA-F]{6}$">
                            </div>
                        </div>

                        <!-- Settings & Roles -->
                        <div class="col-md-6 mt-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_dark" value="1" id="edit_is_dark">
                                <label class="form-check-label fw-semibold" for="edit_is_dark">{{ __('index.theme_modes_dark_theme') }}</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                <label class="form-check-label fw-semibold" for="edit_is_active">{{ __('index.theme_modes_show_mobile') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold">{{ __('index.allowed_roles') }}</label>
                            <div class="p-2 border rounded" style="max-height: 140px; overflow-y: auto;">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input edit-role-chk" type="checkbox" name="allowed_roles[]" value="{{ $role->slug }}" id="edit_role_{{ $role->id }}">
                                        <label class="form-check-label" for="edit_role_{{ $role->id }}">{{ $role->name }} ({{ $role->slug }})</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">{{ __('index.theme_modes_roles_hint') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.theme_modes_cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('index.theme_modes_save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function bindColorSync(pickerId, textId) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (!picker || !text) return;

        picker.addEventListener('input', function() {
            text.value = picker.value.toUpperCase();
        });
        text.addEventListener('input', function() {
            if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
                picker.value = text.value;
            }
        });
    }

    ['primary', 'secondary', 'accent', 'background', 'card', 'text'].forEach(function(field) {
        bindColorSync('add_' + field + '_picker', 'add_' + field + '_text');
        bindColorSync('edit_' + field + '_picker', 'edit_' + field + '_text');
    });

    document.querySelectorAll('.btn-edit-theme').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.theme);
            const form = document.getElementById('editThemeForm');
            form.action = this.dataset.updateUrl;

            document.getElementById('edit_key').value = data.key || '';
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_is_dark').checked = !!data.is_dark;
            document.getElementById('edit_is_active').checked = !!data.is_active;

            function setField(field, hex) {
                const text = document.getElementById('edit_' + field + '_text');
                const picker = document.getElementById('edit_' + field + '_picker');
                if (text && picker) {
                    text.value = (hex || '#000000').toUpperCase();
                    if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
                        picker.value = hex;
                    }
                }
            }

            setField('primary', data.primary_color);
            setField('secondary', data.secondary_color);
            setField('accent', data.accent_color);
            setField('background', data.background_color);
            setField('card', data.card_color);
            setField('text', data.text_color);

            const allowed = Array.isArray(data.allowed_roles) ? data.allowed_roles.map(String) : [];
            document.querySelectorAll('.edit-role-chk').forEach(function(chk) {
                chk.checked = allowed.includes(chk.value);
            });

            const modal = new bootstrap.Modal(document.getElementById('editThemeModal'));
            modal.show();
        });
    });
</script>
@endsection
