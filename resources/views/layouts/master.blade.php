@php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $themeColor = \App\Helpers\AppHelper::getThemeColor();
@endphp
@if(request('asset_modal') === '1' || request('employee_modal') === '1')
    @section('embedded', '1')
@endif
<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}" class="locale-{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="KNEAYERNG HRMS Complete HR Attendance System">
    <meta name="author" content="KNEAYERNG HRMS">
    <meta name="keywords" content="KNEAYERNG HRMS">

    <title>@yield('title')</title>
    <style>
        :root {
            --primary-color: {{ $themeColor->primary_color ?? '#0B5FFF' }};
            --hover-color: {{ $themeColor->hover_color ?? '#0A4FD6' }};
            --dark-primary-color: {{ $themeColor->dark_primary_color ?? '#5EB1FF' }};
            --dark-hover-color: {{ $themeColor->dark_hover_color ?? '#0B5FFF' }};
            --navy-deep: #0A1F44;
            --navy-canvas: #071533;
        }

        .sidebar .sidebar-body .nav .nav-item.nav-category {
            height: auto;
            min-height: 15px;
            line-height: 1.35;
            padding-top: 8px;
            padding-bottom: 4px;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .sidebar .sidebar-body .nav .nav-item .nav-link {
            min-height: 42px;
            height: auto;
            align-items: flex-start;
            white-space: normal;
        }

        .sidebar .sidebar-body .nav .nav-item .nav-link .link-title,
        .sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link,
        .navbar .dropdown-menu .dropdown-item,
        .navbar .nav-link {
            line-height: 1.45;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .sidebar .sidebar-body .nav .nav-item .nav-link .link-title {
            min-width: 0;
            max-width: 170px;
        }

        .sidebar .sidebar-body .nav.sub-menu .nav-item .nav-link {
            height: auto;
            min-height: 32px;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .sidebar .sidebar-body .nav .nav-item .nav-link .link-icon {
            width: 17px;
            height: 17px;
            stroke-width: 2.1px;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
        }

        .sidebar .sidebar-body .nav .nav-item:hover .nav-link .link-icon {
            transform: scale(1.12);
        }

        .sidebar .sidebar-body .nav .nav-item.active .nav-link .link-icon {
            stroke-width: 2.3px;
        }
    </style>
    @include('admin.section.head_links')
    @include('admin.section.typography')
    @yield('styles')
    @if(request('asset_modal') === '1' || request('employee_modal') === '1')
        <style>
            .page-wrapper { margin-left: 0 !important; width: 100% !important; }
            .page-content { padding: 1rem !important; margin-top: 0 !important; }
            .content { margin: 0 !important; }
        </style>
    @endif
</head>

<body>
<div id="preloader" >
    @include('admin.section.preloader')
</div>

<div class="main-wrapper">
    @hasSection('embedded')
    @else
        @include('admin.section.sidebar')
    @endif
    <div class="page-wrapper">
        @hasSection('embedded')
        @else
            @include('admin.section.nav')
        @endif

        <div class="page-content">
            @hasSection('embedded')
            @else
                @include('admin.section.page_header')
            @endif
            @yield('main-content')
        </div>

        <!-- partial -->
        @hasSection('embedded')
        @else
            @include('admin.section.footer')
        @endif
    </div>
</div>

@hasSection('embedded')
    @if(request('employee_modal') === '1')
        @include('admin.employees.profile.partials.request-form-modal')
    @endif
@else
    @if(request()->routeIs('admin.employees.profile.*'))
        @include('admin.employees.profile.partials.form-modal')
    @endif
    @include('admin.employees.profile.partials.request-form-modal')
    @if(request()->routeIs('admin.assets.*', 'admin.asset-assignment.*'))
        @include('admin.assetManagement.assetDetail.common.action-modal')
    @endif
@endif

@include('admin.section.body_links')

@hasSection('embedded')
    @if(request('employee_modal') === '1')
        <script src="{{ asset('assets/js/employee-request-form-modal.js') }}?v={{ filemtime(public_path('assets/js/employee-request-form-modal.js')) }}"></script>
    @endif
@else
    @if(request()->routeIs('admin.employees.profile.*'))
        <script src="{{ asset('assets/js/employee-profile-form-modal.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-form-modal.js')) }}"></script>
    @endif
    <script src="{{ asset('assets/js/employee-request-form-modal.js') }}?v={{ filemtime(public_path('assets/js/employee-request-form-modal.js')) }}"></script>
    @if(request()->routeIs('admin.assets.*', 'admin.asset-assignment.*'))
        <script src="{{ asset('assets/js/asset-action-modal.js') }}?v={{ filemtime(public_path('assets/js/asset-action-modal.js')) }}"></script>
    @endif
@endif

@include('layouts.nav_notification_scripts')
@include('layouts.nav_search_scripts')
@include('layouts.theme_scripts')

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

@yield('scripts')
<script type="text/javascript">
    let url = "{{ route('admin.language.change') }}";

    $(".changeLang").click(function() {
        let lang = $(this).data('lang');
        window.location.href = url + "?lang=" + lang;
    });
</script>
<script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>

</body>

</html>
