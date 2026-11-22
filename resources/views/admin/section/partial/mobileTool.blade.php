@php
    $mobileControls = [
        ['route' => 'admin.showQR', 'active' => 'admin.showQR', 'label' => __('app_settings.pair_mobile_app'), 'visible' => true],
        ['route' => 'admin.app-security.index', 'active' => 'admin.app-security.*', 'label' => __('app_settings.app_security'), 'visible' => app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->allowsAny('app_security_view', 'app_security_customer_support')],
        ['route' => 'admin.app-settings.index', 'active' => 'admin.app-settings.*', 'label' => __('index.app_version_control'), 'visible' => Gate::allows('app_setting')],
        ['route' => 'admin.feature.index', 'active' => 'admin.feature.*', 'label' => __('index.feature_control'), 'visible' => Gate::allows('feature_control')],
        ['route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'label' => __('index.roles_permissions'), 'visible' => Gate::allows('role_permission')],
        ['route' => 'admin.mobile-tools.banner-slides', 'active' => 'admin.mobile-tools.banner-slides*', 'label' => __('index.banner_slides'), 'visible' => Gate::allows('banner.manage')],
        ['route' => 'admin.mobile-tools.quotes', 'active' => 'admin.mobile-tools.quotes*', 'label' => __('index.mobile_quotes'), 'visible' => Gate::allows('quote.manage')],
        ['route' => 'admin.mobile-tools.app-appearance', 'active' => 'admin.mobile-tools.app-appearance*', 'label' => __('index.app_appearance'), 'visible' => Gate::allows('appearance.manage')],
    ];
    $visibleMobileControls = collect($mobileControls)->where('visible', true);
    $mobileControlsOpen = $visibleMobileControls->contains(fn ($item) => request()->routeIs($item['active']));
@endphp
<li class="nav-item {{ $mobileControlsOpen ? 'active' : '' }}">
    <a class="nav-link" href="#mobileAppControls" data-href="#" data-bs-toggle="collapse" role="button"
       aria-controls="mobileAppControls" aria-expanded="{{ $mobileControlsOpen ? 'true' : 'false' }}">
        <i class="link-icon" data-feather="smartphone"></i>
        <span class="link-title">{{ __('app_settings.mobile_app_controls') }}</span>
        <i class="link-arrow" data-feather="chevron-down"></i>
    </a>
    <div class="collapse {{ $mobileControlsOpen ? 'show' : '' }}" id="mobileAppControls">
        <ul class="nav sub-menu">
            @foreach($visibleMobileControls as $control)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($control['active']) ? 'active' : '' }}"
                       href="{{ route($control['route']) }}"
                       data-href="{{ route($control['route']) }}"
                       @if($control['route'] === 'admin.showQR') target="_blank" rel="noopener" @endif>
                        {{ $control['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</li>
