@php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $themeColor = \App\Helpers\AppHelper::getThemeColor();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}" class="locale-{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Digital HR Complete HR Attendance System">
    <meta name="author" content="Digital HR">
    <meta name="keywords" content="Digital HR">

    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $themeColor->primary_color ?? '#0F766E' }};
            --hover-color: {{ $themeColor->hover_color ?? '#115E59' }};
            --dark-primary-color: {{ $themeColor->dark_primary_color ?? '#14B8A6' }};
            --dark-hover-color: {{ $themeColor->dark_hover_color ?? '#0F766E' }};
        }

        html.locale-km body,
        html.locale-km button,
        html.locale-km input,
        html.locale-km select,
        html.locale-km textarea {
            font-family: 'Kantumruy Pro', 'Noto Sans Khmer', 'Roboto', Arial, sans-serif;
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
    </style>
    @include('admin.section.head_links')
    @yield('styles')
</head>

<body>
<div id="preloader" >
    @include('admin.section.preloader')
</div>

<div class="main-wrapper">
    @include('admin.section.sidebar')
    <div class="page-wrapper">
        @include('admin.section.nav')

        <div class="page-content">
            @include('admin.section.page_header')
            @yield('main-content')
        </div>

        <!-- partial -->
        @include('admin.section.footer')
    </div>
</div>

@include('admin.section.body_links')

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
