@php
    $embed = request()->boolean('embed') || request()->boolean('content_only');
@endphp
@if($embed)
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CertiCraft Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Battambang:wght@400;700&family=Cinzel:wght@400;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Great+Vibes&family=JetBrains+Mono:wght@400;500;600&family=Kantumruy+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Montserrat:wght@400;500;600;700;800&family=Moul&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" crossorigin href="{{ asset('certicraft/assets/index--J-rHrUf.css') }}">
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #fff; }
        #root { width: 100%; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
    </style>
</head>
<body>
    <div id="root" class="w-100 h-100"></div>
    <script>
      window.__LARAVEL_CERT_BOOTSTRAP__ = {
          embedded: true,
          locale: @json(app()->getLocale() === 'km' ? 'km' : 'en'),
          csrfToken: @json(csrf_token()),
          saveUrl: @json(route('certificates.api.store')),
          saveTemplateUrl: @json(route('certificates.api.template.save')),
          resetTemplateUrl: @json(url('/certificates/api/template')),
          verifyBaseUrl: @json(url('/certificates/verify')),
          indexUrl: @json(route('admin.certificates.templates')),
          templatesUrl: @json(route('admin.certificates.templates')),
          initialCertificate: @json($initialCert ?? null),
          selectedEmployee: @json($selectedEmployee ?? null),
          employees: @json($employees ?? []),
          templates: @json($templates ?? []),
      };
    </script>
    <script type="module" crossorigin src="{{ asset('certicraft/assets/index-C0hBGPWS.js') }}"></script>
</body>
</html>
@else
@extends('layouts.master')

@section('title', 'CertiCraft Studio — វិញ្ញាបនបត្រ គំរូ & Visual Designer')

@section('action', 'CertiCraft Studio')

@section('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Battambang:wght@400;700&family=Cinzel:wght@400;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Great+Vibes&family=JetBrains+Mono:wght@400;500;600&family=Kantumruy+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Montserrat:wght@400;500;600;700;800&family=Moul&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" crossorigin href="{{ asset('certicraft/assets/index--J-rHrUf.css') }}">

    <style>
        /* Scoped style integration inside HR Admin */
        .certicraft-wrapper-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            height: calc(100vh - 175px);
            min-height: 780px;
            display: flex;
            flex-direction: column;
        }

        #root {
            width: 100%;
            height: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Ensure NobleUI sidebar & navbar stay above canvas */
        .sidebar {
            z-index: 999 !important;
        }
        .navbar {
            z-index: 998 !important;
        }
    </style>
@endsection

@section('main-content')
<section class="content p-0">
    @include('admin.section.flash_message')

    <!-- Top Breadcrumb & Action Toolbar inside HR layout -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-2 px-1">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">{{ __('index.certificate_management') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.templates') }}">{{ __('index.certificate_templates') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.certificate_studio') }}</li>
        </ol>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.certificates.templates') }}" class="btn btn-outline-primary btn-sm">
                <i class="link-icon" data-feather="layout"></i> {{ __('index.certificate_templates') }}
            </a>
            <a href="{{ route('admin.certificates.create') }}" class="btn btn-outline-success btn-sm">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.issue_certificate') }}
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
                <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.certificate_directory') }}
            </a>
        </div>
    </nav>

    <!-- CertiCraft Studio Shell mounted inside HR Layout -->
    <div class="certicraft-wrapper-card">
        <div id="root" class="w-100 h-100"></div>
    </div>
</section>
@endsection

@section('scripts')
    <script>
      window.__LARAVEL_CERT_BOOTSTRAP__ = {
          embedded: true,
          locale: @json(app()->getLocale() === 'km' ? 'km' : 'en'),
          csrfToken: @json(csrf_token()),
          saveUrl: @json(route('certificates.api.store')),
          saveTemplateUrl: @json(route('certificates.api.template.save')),
          resetTemplateUrl: @json(url('/certificates/api/template')),
          verifyBaseUrl: @json(url('/certificates/verify')),
          indexUrl: @json(route('admin.certificates.templates')),
          templatesUrl: @json(route('admin.certificates.templates')),
          initialCertificate: @json($initialCert ?? null),
          selectedEmployee: @json($selectedEmployee ?? null),
          employees: @json($employees ?? []),
          templates: @json($templates ?? []),
      };
    </script>
    <script type="module" crossorigin src="{{ asset('certicraft/assets/index-C0hBGPWS.js') }}"></script>
@endsection
@endif
