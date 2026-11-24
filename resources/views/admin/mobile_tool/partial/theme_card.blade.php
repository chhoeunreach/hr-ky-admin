@php
    $isActive = (bool) ($theme['is_active'] ?? true);
    $isSystem = (bool) ($theme['is_system'] ?? false);
    $isDark = (bool) ($theme['is_dark'] ?? false);
    $rolesList = (array) ($theme['allowed_roles'] ?? []);
    $bg = $theme['background_color'] ?? '#FFFFFF';
    $card = $theme['card_color'] ?? '#F8FAFC';
    $text = $theme['text_color'] ?? '#0F172A';
    $primary = $theme['primary_color'] ?? '#2563EB';
    $secondary = $theme['secondary_color'] ?? '#475569';
    $accent = $theme['accent_color'] ?? '#E11D48';
@endphp
<div class="col-xl-4 col-md-6">
    <div class="card tm-card h-100 {{ !$isActive ? 'opacity-75' : '' }}">
        <div class="tm-card-header d-flex justify-content-between align-items-start">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill {{ $isDark ? 'bg-dark' : 'bg-light text-dark border' }}">
                        {{ $isDark ? __('index.dark_mode') : __('index.light_mode') }}
                    </span>
                    @if($isSystem)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="{{ __('index.core_systems') }}">
                            <i data-feather="shield" style="width:10px;height:10px" class="me-1"></i>{{ __('index.core_systems') }}
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="{{ __('index.custom_themes') }}">
                            <i data-feather="star" style="width:10px;height:10px" class="me-1"></i>{{ __('index.custom_themes') }}
                        </span>
                    @endif
                    @if($isActive)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('index.shown_on_mobile') }}</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ __('index.hidden_on_mobile') }}</span>
                    @endif
                </div>
                <h5 class="mt-2 mb-0 fw-bold text-dark">{{ $theme['name'] }}</h5>
                <code class="text-muted small">{{ $key }}</code>
            </div>
            <form action="{{ route('admin.mobile-tools.theme-modes.toggle', $key) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $isActive ? __('index.hidden_on_mobile') : __('index.shown_on_mobile') }}">
                    <i data-feather="{{ $isActive ? 'eye-off' : 'eye' }}" style="width:14px;height:14px"></i>
                    {{ $isActive ? __('index.hide') : __('index.show') }}
                </button>
            </form>
        </div>

        <div class="card-body">
            @if(!empty($theme['description']))
                <p class="text-muted small mb-3">{{ $theme['description'] }}</p>
            @endif

            <!-- Live Mini Preview Box -->
            <div class="tm-preview-box" style="background-color: {{ $bg }}; color: {{ $text }};">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold small" style="color: {{ $text }};">{{ $theme['name'] }}</span>
                    <span class="badge" style="background-color: {{ $accent }}; color: #fff;">{{ __('index.theme_modes_preview_accent') }}</span>
                </div>
                <div class="p-2 rounded" style="background-color: {{ $card }}; border: 1px solid rgba(0,0,0,0.06);">
                    <span class="d-block small fw-semibold" style="color: {{ $text }};">{{ __('index.theme_modes_preview_card') }}</span>
                    <div class="mt-2 d-flex gap-1">
                        <button type="button" class="btn btn-xs text-white" style="background-color: {{ $primary }}; font-size: 0.7rem; padding: 2px 6px;">{{ __('index.theme_modes_preview_primary') }}</button>
                        <button type="button" class="btn btn-xs text-white" style="background-color: {{ $secondary }}; font-size: 0.7rem; padding: 2px 6px;">{{ __('index.theme_modes_preview_secondary') }}</button>
                    </div>
                </div>
            </div>

            <!-- 6 Palettes -->
            <div class="tm-color-grid mb-3">
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $primary }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_primary') }}</div>
                        <div class="tm-color-val">{{ $primary }}</div>
                    </div>
                </div>
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $secondary }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_secondary') }}</div>
                        <div class="tm-color-val">{{ $secondary }}</div>
                    </div>
                </div>
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $accent }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_accent') }}</div>
                        <div class="tm-color-val">{{ $accent }}</div>
                    </div>
                </div>
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $bg }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_background') }}</div>
                        <div class="tm-color-val">{{ $bg }}</div>
                    </div>
                </div>
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $card }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_card') }}</div>
                        <div class="tm-color-val">{{ $card }}</div>
                    </div>
                </div>
                <div class="tm-color-item">
                    <div class="tm-swatch" style="background-color: {{ $text }};"></div>
                    <div>
                        <div class="tm-color-label">{{ __('index.color_text') }}</div>
                        <div class="tm-color-val">{{ $text }}</div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            <div class="border-top pt-2">
                <div class="small fw-semibold text-muted mb-1">{{ __('index.allowed_roles') }}:</div>
                <div class="d-flex flex-wrap gap-1">
                    @if(empty($rolesList) || in_array('*', $rolesList))
                        <span class="badge bg-light text-dark border">{{ __('index.all_roles') }}</span>
                    @else
                        @foreach($rolesList as $roleItem)
                            @php
                                $matchedRole = $roles->first(fn($r) => (string)$r->id === (string)$roleItem || strtolower($r->slug) === strtolower($roleItem));
                                $roleLabel = $matchedRole ? $matchedRole->name : $roleItem;
                            @endphp
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $roleLabel }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-primary btn-sm btn-edit-theme"
                    data-theme='@json($theme)'
                    data-update-url="{{ route('admin.mobile-tools.theme-modes.update', $key) }}">
                <i data-feather="edit-2" style="width:14px;height:14px" class="me-1"></i> {{ __('index.edit_colors_roles') }}
            </button>
            <div class="d-flex gap-1">
                @if($isSystem)
                    <form action="{{ route('admin.mobile-tools.theme-modes.reset-theme', $key) }}" method="POST" onsubmit='return confirm(@json(__('index.theme_modes_reset_core_confirm', ['name' => $theme['name']])));'>
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="{{ __('index.theme_modes_reset_core_title') }}">
                            <i data-feather="rotate-ccw" style="width:14px;height:14px"></i>
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.mobile-tools.theme-modes.destroy', $key) }}" method="POST" onsubmit='return confirm(@json(__('index.theme_modes_delete_custom_confirm', ['name' => $theme['name']])));'>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('index.theme_modes_delete_custom_title') }}">
                            <i data-feather="trash-2" style="width:14px;height:14px"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
