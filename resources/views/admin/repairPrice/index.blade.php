@extends('layouts.master')

@section('title', __('index.repair_price'))

@section('action', __('index.lists'))

@section('button')
    <div class="d-flex gap-2 flex-wrap">
        @can('repair_price.manage_prices')
            <a href="{{ route('admin.repair-price.import') }}" class="btn btn-outline-success btn-sm">
                <i class="link-icon" data-feather="upload-cloud"></i> {{ __('index.import_excel') }}
            </a>
        @endcan
        <a href="{{ route('admin.repair-price.sample-template') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('repair.download_sample') }}">
            <i class="link-icon" data-feather="download"></i> {{ __('repair.sample_csv') }}
        </a>
        @canany(['repair_price.create', 'repair_price.manage_prices'])
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPriceModal">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_repair_price') }}
            </button>
        @endcanany
        @can('repair_price.manage_devices')
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                <i class="link-icon" data-feather="smartphone"></i> {{ __('index.add_device') }}
            </button>
        @endcan
        @can('repair_price.manage_services')
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="link-icon" data-feather="tool"></i> {{ __('index.add_service') }}
            </button>
        @endcan
        @can('repair_price.manage_brands')
            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                <i class="link-icon" data-feather="tag"></i> {{ __('index.add_brand') }}
            </button>
        @endcan
        @can('repair_price.manage_categories')
            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="link-icon" data-feather="grid"></i> {{ __('index.add_category') }}
            </button>
        @endcan
    </div>
@endsection

@section('main-content')
<link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}?v={{ filemtime(public_path('assets/css/directory-table-tools.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/repair-price-modals.css') }}?v={{ filemtime(public_path('assets/css/repair-price-modals.css')) }}">
<style>.repair-price-page .card{border:1px solid rgba(125,139,160,.18);border-radius:14px;overflow:hidden}.repair-price-page .directory-add{background:linear-gradient(110deg,#ff0800,#ffad00);box-shadow:0 4px 14px #ff3c0026}.repair-price-page .directory-filter-toggle,.repair-price-page .directory-title>svg{color:#ff0800}</style>
<section class="content repair-price-page">
    @include('admin.section.flash_message')

    <!-- Breadcrumb & Role Permissions Indicator -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.repair.dashboard') }}">{{ __('index.repair_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.repair_price') }}</li>
        </ol>

        <!-- Role Badges for Transparency -->
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <span class="badge bg-secondary">{{ __('index.role') }}: {{ auth()->user()->role?->name ?? __('repair.admin') }}</span>
            @can('repair_price.view_cost')
                <span class="badge bg-success" title="{{ __('index.cost_visible') }}"><i data-feather="eye" style="width:12px;height:12px;"></i> {{ __('index.cost_visible') }}</span>
            @else
                <span class="badge bg-warning text-dark" title="{{ __('index.cost_hidden') }}"><i data-feather="eye-off" style="width:12px;height:12px;"></i> {{ __('index.cost_hidden') }}</span>
            @endcan
            @can('repair_price.manage_prices')
                <span class="badge bg-info text-dark">{{ __('index.price_manage_on') }}</span>
            @endcan
            @can('repair_price.manage_devices')
                <span class="badge bg-primary">{{ __('index.device_manage_on') }}</span>
            @endcan
        </div>
    </nav>

    <!-- Filters Card -->
    @php
        $repairFiltersExpanded = request()->filled('brand_id') || request()->filled('device_id') || request()->filled('category_id');
        $partSharePercent = (int) round((float) config('repair-pricing.part_share', 0.70) * 100);
    @endphp
    <div class="directory-filter-panel mb-4">
        @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'repairFilterCollapse', 'directoryExpanded' => $repairFiltersExpanded])
        <div id="repairFilterCollapse" class="collapse {{ $repairFiltersExpanded ? 'show' : '' }}">
        <form id="repairFilterForm" class="directory-filter-form" action="{{ route('admin.repair-price.index') }}" method="get">
            <input type="hidden" name="search" value="{{ $filterParameters['search'] ?? '' }}">
            <input type="hidden" name="per_page" value="{{ $filterParameters['per_page'] }}">
            <input type="hidden" name="active_tab" id="filterActiveTab" value="{{ $filterParameters['active_tab'] ?? 'prices' }}">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="form-label small text-muted">{{ __('index.brand') }}</label>
                    <select class="form-select" name="brand_id">
                        <option value="">{{ __('index.all_brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ (isset($filterParameters['brand_id']) && $filterParameters['brand_id'] == $brand->id) ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="form-label small text-muted">{{ __('index.device_model') }}</label>
                    <select class="form-select" name="device_id">
                        <option value="">{{ __('index.all_devices') }}</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ (isset($filterParameters['device_id']) && $filterParameters['device_id'] == $device->id) ? 'selected' : '' }}>
                                {{ $device->name }} ({{ $device->model_number ?? __('repair.not_available') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="form-label small text-muted">{{ __('index.category') }}</label>
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('index.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (isset($filterParameters['category_id']) && $filterParameters['category_id'] == $category->id) ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'km' && !empty($category->name_kh) ? $category->name_kh : $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
            @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.repair-price.index', ['active_tab' => $filterParameters['active_tab'], 'per_page' => $filterParameters['per_page']])])
        </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-tabs-line mb-3" id="repairTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ ($filterParameters['active_tab'] ?? 'prices') == 'prices' ? 'active' : '' }}" id="prices-tab" data-bs-toggle="tab" href="#pricesPane" role="tab" aria-controls="pricesPane" aria-selected="true">
                <i class="link-icon me-1" data-feather="dollar-sign"></i> {{ __('index.repair_price_matrix') }}
            </a>
        </li>
        @can('repair_price.manage_brands')
            <li class="nav-item">
                <a class="nav-link {{ ($filterParameters['active_tab'] ?? '') == 'brands' ? 'active' : '' }}" id="brands-tab" data-bs-toggle="tab" href="#brandsPane" role="tab" aria-controls="brandsPane" aria-selected="false">
                    <i class="link-icon me-1" data-feather="tag"></i> {{ __('index.brands') }}
                </a>
            </li>
        @endcan
        @can('repair_price.manage_categories')
            <li class="nav-item">
                <a class="nav-link {{ ($filterParameters['active_tab'] ?? '') == 'categories' ? 'active' : '' }}" id="categories-tab" data-bs-toggle="tab" href="#categoriesPane" role="tab" aria-controls="categoriesPane" aria-selected="false">
                    <i class="link-icon me-1" data-feather="grid"></i> {{ __('index.categories') }}
                </a>
            </li>
        @endcan
        <li class="nav-item">
            <a class="nav-link {{ ($filterParameters['active_tab'] ?? '') == 'devices' ? 'active' : '' }}" id="devices-tab" data-bs-toggle="tab" href="#devicesPane" role="tab" aria-controls="devicesPane" aria-selected="false">
                <i class="link-icon me-1" data-feather="smartphone"></i> {{ __('index.repair_devices') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($filterParameters['active_tab'] ?? '') == 'services' ? 'active' : '' }}" id="services-tab" data-bs-toggle="tab" href="#servicesPane" role="tab" aria-controls="servicesPane" aria-selected="false">
                <i class="link-icon me-1" data-feather="tool"></i> {{ __('index.repair_services') }}
            </a>
        </li>
        @can('repair_price.manage_devices')
            @foreach(['types' => __('repair.device_types'), 'series' => __('repair.device_series')] as $tab => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $filterParameters['active_tab'] === $tab ? 'active' : '' }}" id="{{ $tab }}-tab" data-bs-toggle="tab" href="#{{ $tab }}Pane" role="tab">{{ $label }}</a>
                </li>
            @endforeach
        @endcan
        @can('repair_price.manage_prices')
        <li class="nav-item ms-auto">
            <a class="nav-link text-primary fw-bold" href="{{ route('admin.repair-price.import') }}">
                <i class="link-icon me-1 text-primary" data-feather="upload-cloud"></i> {{ __('repair.import_excel_csv') }}
            </a>
        </li>
        @endcan
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="repairTabContent">

        <!-- TAB 1: PRICES & COSTS -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? 'prices') == 'prices' ? 'show active' : '' }}" id="pricesPane" role="tabpanel" aria-labelledby="prices-tab">
            <div class="card shadow-sm">
                @include('admin.repairPrice.partials.toolbar', ['repairEntity' => 'prices', 'repairRecords' => $prices])
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="repair-prices-table" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.device_model') }}</th>
                                    <th>{{ __('index.category') }}</th>
                                    <th>{{ __('index.service_or_part') }}</th>
                                    <th>{{ __('index.selling_price') }}</th>
                                    @can('repair_price.view_cost')
                                        <th class="text-end text-danger">{{ __('index.part_cost') }}</th>
                                        <th class="text-end text-warning">{{ __('index.service_fee') }}</th>
                                        <th class="text-end text-success">{{ __('index.net_profit') }}</th>
                                        <th class="text-end text-info">{{ __('index.margin') }}</th>
                                    @endcan
                                    <th>{{ __('index.warranty') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    @canany(['repair_price.create', 'repair_price.update', 'repair_price.delete', 'repair_price.manage_prices'])
                                        <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prices as $key => $p)
                                    @php
                                        $profit = ($p->part_cost !== null && $p->service_fee !== null) ? ($p->selling_price - $p->part_cost - $p->service_fee) : null;
                                        $margin = ($p->selling_price > 0 && $profit !== null) ? round(($profit / $p->selling_price) * 100, 1) : null;
                                        $catName = (app()->getLocale() == 'km' && !empty($p->service?->category?->name_kh)) ? $p->service->category->name_kh : ($p->service?->category?->name ?? __('repair.general'));
                                        $srvName = (app()->getLocale() == 'km' && !empty($p->service?->name_kh)) ? $p->service->name_kh : ($p->service?->name ?? __('repair.not_available'));
                                    @endphp
                                    <tr>
                                        <td>{{ $prices->firstItem() + $key }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $p->device?->name ?? __('repair.not_available') }}</div>
                                            <span class="badge bg-light text-secondary">{{ $p->device?->brand?->name ?? __('index.brand') }}</span>
                                            <span class="text-muted small">({{ $p->device?->model_number ?? '-' }})</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $catName }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $srvName }}</div>
                                            @if($p->part_name)
                                                <div class="text-muted small">{{ $p->part_name }} ({{ $p->part_type ?? __('repair.standard') }})</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fs-6 fw-bold text-primary">${{ number_format($p->selling_price, 2) }}</span>
                                        </td>
                                        @can('repair_price.view_cost')
                                            <td class="text-end text-danger fw-semibold">
                                                {{ $p->part_cost !== null ? '$' . number_format($p->part_cost, 2) : '-' }}
                                            </td>
                                            <td class="text-end text-warning fw-semibold">
                                                {{ $p->service_fee !== null ? '$' . number_format($p->service_fee, 2) : '-' }}
                                            </td>
                                            <td class="text-end fw-bold {{ ($profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $profit !== null ? '$' . number_format($profit, 2) : '-' }}
                                            </td>
                                            <td class="text-end text-info fw-semibold">
                                                {{ $margin !== null ? $margin . '%' : '-' }}
                                            </td>
                                        @endcan
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $p->warranty_days ? $p->warranty_days . ' ' . (__('index.days')) : '-' }}
                                            </span>
                                            @if($p->estimated_minutes)
                                                <div class="text-muted small mt-1"><i data-feather="clock" style="width:11px;height:11px;"></i> {{ $p->estimated_minutes }} {{ __('index.minute') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p->availability_status == 'available')
                                                <span class="badge bg-success">{{ __('index.available') }}</span>
                                            @elseif($p->availability_status == 'out_of_stock')
                                                <span class="badge bg-danger">{{ __('index.out_of_stock') }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ __('index.pre_order') }}</span>
                                            @endif
                                        </td>
                                        @canany(['repair_price.create', 'repair_price.update', 'repair_price.delete', 'repair_price.manage_prices'])
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @canany(['repair_price.create', 'repair_price.manage_prices'])
                                                        @php
                                                            $priceTemplate = $p->only(['repair_service_id', 'part_name', 'part_type', 'selling_price', 'warranty_days', 'estimated_minutes', 'availability_status', 'note']);
                                                            if ($canViewCost) $priceTemplate += $p->only(['part_cost', 'service_fee']);
                                                            $priceTemplate += ['source_price_id' => $p->id, 'repair_category_id' => $p->service?->repair_category_id, 'source_device_id' => $p->repair_device_id, 'costs_hidden' => !$canViewCost];
                                                        @endphp
                                                        <button type="button" class="btn btn-sm btn-outline-secondary copy-price-btn"
                                                                data-bs-toggle="modal" data-bs-target="#addPriceModal"
                                                                data-price-template="{{ json_encode($priceTemplate) }}"
                                                                title="{{ __('repair.copy_price_to_models') }}">{{ __('repair.copy_price_to_models') }}</button>
                                                    @endcanany
                                                    @canany(['repair_price.update', 'repair_price.manage_prices'])
                                                        <button class="btn btn-sm btn-outline-primary btn-icon edit-price-btn"
                                                                data-id="{{ $p->id }}"
                                                                data-device-id="{{ $p->repair_device_id }}"
                                                                data-service-id="{{ $p->repair_service_id }}"
                                                                data-part-name="{{ $p->part_name }}"
                                                                data-note="{{ $p->note }}"
                                                                data-part-type="{{ $p->part_type }}"
                                                                data-part-cost="{{ $p->part_cost }}"
                                                                data-service-fee="{{ $p->service_fee }}"
                                                                data-selling-price="{{ $p->selling_price }}"
                                                                data-warranty-days="{{ $p->warranty_days }}"
                                                                data-estimated-minutes="{{ $p->estimated_minutes }}"
                                                                data-availability-status="{{ $p->availability_status }}"
                                                                title="{{ __('index.edit') }}">
                                                            <i data-feather="edit-2"></i>
                                                        </button>
                                                    @endcanany
                                                    @canany(['repair_price.delete', 'repair_price.manage_prices'])
                                                        <form action="{{ route('admin.repair-price.prices.delete', $p->id) }}" method="post" class="repair-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon repair-delete-btn"
                                                                    data-title="{{ $srvName }}" title="{{ __('index.delete') }}">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                        </form>
                                                    @endcanany
                                                </div>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <p class="text-muted mb-0">{{ __('index.no_records_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $prices->links() }}
                    </div>
                </div>
            </div>
        </div>

        @can('repair_price.manage_brands')
            <!-- TAB 2: BRANDS -->
            <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'brands' ? 'show active' : '' }}" id="brandsPane" role="tabpanel" aria-labelledby="brands-tab">
                <div class="card shadow-sm">
                    @include('admin.repairPrice.partials.toolbar', ['repairEntity' => 'brands', 'repairRecords' => $brandsList])
                <div class="card-body">
                        <div class="table-responsive">
                            <table id="repair-brands-table" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('index.brand') }}</th>
                                        <th>{{ __('repair.slug') }}</th>
                                        <th>{{ __('index.status') }}</th>
                                        <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($brandsList as $key => $brand)
                                        <tr>
                                            <td>{{ $brandsList->firstItem() + $key }}</td>
                                            <td class="fw-bold">{{ $brand->name }}</td>
                                            <td><code>{{ $brand->slug }}</code></td>
                                            <td>
                                                <span class="badge {{ $brand->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $brand->status ? __('index.active') : __('index.inactive') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-outline-primary btn-icon edit-brand-btn"
                                                            data-id="{{ $brand->id }}"
                                                            data-name="{{ $brand->name }}"
                                                            data-slug="{{ $brand->slug }}"
                                                            data-logo="{{ $brand->logo }}"
                                                            data-status="{{ $brand->status }}"
                                                            title="{{ __('index.edit') }}">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <form action="{{ route('admin.repair-price.brands.delete', $brand->id) }}" method="post" class="repair-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon repair-delete-btn"
                                                                    data-title="{{ $brand->name }}" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                        </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">{{ __('index.no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $brandsList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('repair_price.manage_categories')
            <!-- TAB 3: CATEGORIES -->
            <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'categories' ? 'show active' : '' }}" id="categoriesPane" role="tabpanel" aria-labelledby="categories-tab">
                <div class="card shadow-sm">
                    @include('admin.repairPrice.partials.toolbar', ['repairEntity' => 'categories', 'repairRecords' => $categoriesList])
                <div class="card-body">
                        <div class="table-responsive">
                            <table id="repair-categories-table" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('index.category') }}</th>
                                        <th>{{ __('index.service_name_kh') }}</th>
                                        <th>{{ __('repair.slug') }}</th>
                                        <th>{{ __('index.sort_order') }}</th>
                                        <th>{{ __('repair.linked_models') }}</th>
                                        <th>{{ __('index.status') }}</th>
                                        <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoriesList as $key => $category)
                                        <tr>
                                            <td>{{ $categoriesList->firstItem() + $key }}</td>
                                            <td class="fw-bold">{{ $category->name }}</td>
                                            <td>{{ $category->name_kh ?? '-' }}</td>
                                            <td><code>{{ $category->slug }}</code></td>
                                            <td>{{ $category->sort_order }}</td>
                                            <td><a href="{{ route('admin.repair-price.index', ['active_tab' => 'devices', 'category_id' => $category->id]) }}#devicesPane">{{ $category->devices_count }}</a></td>
                                            <td>
                                                <span class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $category->status ? __('index.active') : __('index.inactive') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-outline-primary btn-icon edit-category-btn"
                                                            data-id="{{ $category->id }}"
                                                            data-name="{{ $category->name }}"
                                                            data-name-kh="{{ $category->name_kh }}"
                                                            data-slug="{{ $category->slug }}"
                                                            data-icon="{{ $category->icon }}"
                                                            data-sort-order="{{ $category->sort_order }}"
                                                            data-status="{{ $category->status }}"
                                                            title="{{ __('index.edit') }}">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <form action="{{ route('admin.repair-price.categories.delete', $category->id) }}" method="post" class="repair-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon repair-delete-btn"
                                                                    data-title="{{ $category->name }}" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                        </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">{{ __('index.no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $categoriesList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <!-- TAB 2: DEVICE MODELS -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'devices' ? 'show active' : '' }}" id="devicesPane" role="tabpanel" aria-labelledby="devices-tab">
            <div class="card shadow-sm">
                @include('admin.repairPrice.partials.toolbar', ['repairEntity' => 'devices', 'repairRecords' => $devicesList])
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="repair-devices-table" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.brand') }}</th>
                                    <th>{{ __('index.device_type') }}</th>
                                    <th>{{ __('index.series') }}</th>
                                    <th>{{ __('index.model_name') }}</th>
                                    <th>{{ __('index.model_number') }}</th>
                                    <th>{{ __('repair.linked_categories') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    @can('repair_price.manage_devices')
                                        <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devicesList as $key => $d)
                                    <tr>
                                        <td>{{ $devicesList->firstItem() + $key }}</td>
                                        <td><span class="badge bg-secondary">{{ $d->brand?->name ?? '-' }}</span></td>
                                        <td>{{ $d->deviceType?->name ?? __('repair.smartphone') }}</td>
                                        <td>{{ $d->series?->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $d->name }}
                                            <a class="d-block small fw-normal" href="{{ route('admin.repair-price.index', ['active_tab' => 'prices', 'device_id' => $d->id]) }}#pricesPane">{{ __('repair.model_prices') }}</a>
                                        </td>
                                        <td><code>{{ $d->model_number ?? '-' }}</code></td>
                                        <td>
                                            @forelse($d->categories as $category)
                                                <a class="badge bg-light text-primary" href="{{ route('admin.repair-price.index', ['active_tab' => 'prices', 'device_id' => $d->id, 'category_id' => $category->id]) }}#pricesPane">{{ app()->getLocale() === 'km' && $category->name_kh ? $category->name_kh : $category->name }}</a>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="badge {{ $d->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $d->status ? __('index.active') : __('index.inactive') }}
                                            </span>
                                        </td>
                                        @can('repair_price.manage_devices')
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-outline-primary btn-icon edit-device-btn"
                                                            data-id="{{ $d->id }}"
                                                            data-brand-id="{{ $d->repair_brand_id }}"
                                                            data-type-id="{{ $d->repair_device_type_id }}"
                                                            data-series-id="{{ $d->repair_device_series_id }}"
                                                            data-category-ids='@json($d->categories->modelKeys())'
                                                            data-prices='{{ $d->prices->map->only(['id', 'repair_service_id', 'part_type', 'selling_price'])->toJson() }}'
                                                            data-name="{{ $d->name }}"
                                                            data-model-number="{{ $d->model_number }}"
                                                            data-status="{{ $d->status }}"
                                                            title="{{ __('index.edit') }}">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <form action="{{ route('admin.repair-price.devices.delete', $d->id) }}" method="post" class="repair-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon repair-delete-btn"
                                                                    data-title="{{ $d->name }}" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                        </form>
                                                </div>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canManageDevices ? 9 : 8 }}" class="text-center py-4">{{ __('index.no_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $devicesList->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: SERVICES & CATEGORIES -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'services' ? 'show active' : '' }}" id="servicesPane" role="tabpanel" aria-labelledby="services-tab">
            <div class="card shadow-sm">
                @include('admin.repairPrice.partials.toolbar', ['repairEntity' => 'services', 'repairRecords' => $servicesList])
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="repair-services-table" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.category') }}</th>
                                    <th>{{ __('index.service_name_en') }}</th>
                                    <th>{{ __('index.service_name_kh') }}</th>
                                    <th>{{ __('index.description') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    @can('repair_price.manage_services')
                                        <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicesList as $key => $s)
                                    <tr>
                                        <td>{{ $servicesList->firstItem() + $key }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ (app()->getLocale() == 'km' && !empty($s->category?->name_kh)) ? $s->category->name_kh : ($s->category?->name ?? __('repair.general')) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ $s->name }}</td>
                                        <td>{{ $s->name_kh ?? '-' }}</td>
                                        <td class="text-muted small">{{ Str::limit($s->description, 50) ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $s->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $s->status ? __('index.active') : __('index.inactive') }}
                                            </span>
                                        </td>
                                        @can('repair_price.manage_services')
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-outline-primary btn-icon edit-service-btn"
                                                            data-id="{{ $s->id }}"
                                                            data-category-id="{{ $s->repair_category_id }}"
                                                            data-name="{{ $s->name }}"
                                                            data-name-kh="{{ $s->name_kh }}"
                                                            data-description="{{ $s->description }}"
                                                            data-status="{{ $s->status }}"
                                                            title="{{ __('index.edit') }}">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <form action="{{ route('admin.repair-price.services.delete', $s->id) }}" method="post" class="repair-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon repair-delete-btn"
                                                                    data-title="{{ $s->name }}" title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                        </form>
                                                </div>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">{{ __('index.no_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $servicesList->links() }}
                    </div>
                </div>
            </div>
        </div>

        @include('admin.repairPrice.partials.catalog-tabs')
    </div>
</section>

<!-- ================= MODALS ================= -->
@include('admin.repairPrice.partials.catalog-modals')

@can('repair_price.manage_brands')
    <!-- ADD BRAND MODAL -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.brands.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_brand') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="{{ __('repair.example_brand') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.slug') }}</label>
                            <input type="text" class="form-control" name="slug" placeholder="{{ __('repair.auto_slug') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.logo_path') }}</label>
                            <input type="text" class="form-control" name="logo" placeholder="{{ __('repair.optional') }}">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="brandStatusSwitch" checked>
                            <label class="form-check-label" for="brandStatusSwitch">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT BRAND MODAL -->
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editBrandForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_brand') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_brand_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.slug') }}</label>
                            <input type="text" class="form-control" id="edit_brand_slug" name="slug">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.logo_path') }}</label>
                            <input type="text" class="form-control" id="edit_brand_logo" name="logo">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="edit_brand_status">
                            <label class="form-check-label" for="edit_brand_status">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('repair_price.manage_categories')
    <!-- ADD CATEGORY MODAL -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.categories.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="{{ __('repair.example_category') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" name="name_kh" placeholder="{{ __('repair.optional') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.slug') }}</label>
                            <input type="text" class="form-control" name="slug" placeholder="{{ __('repair.auto_slug') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.icon') }}</label>
                            <input type="text" class="form-control" name="icon" placeholder="{{ __('repair.optional') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.sort_order') }}</label>
                            <input type="number" min="0" class="form-control" name="sort_order" value="0">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="categoryStatusSwitch" checked>
                            <label class="form-check-label" for="categoryStatusSwitch">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT CATEGORY MODAL -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCategoryForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" id="edit_category_name_kh" name="name_kh">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.slug') }}</label>
                            <input type="text" class="form-control" id="edit_category_slug" name="slug">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('repair.icon') }}</label>
                            <input type="text" class="form-control" id="edit_category_icon" name="icon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.sort_order') }}</label>
                            <input type="number" min="0" class="form-control" id="edit_category_sort_order" name="sort_order">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="edit_category_status">
                            <label class="form-check-label" for="edit_category_status">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@canany(['repair_price.create', 'repair_price.manage_prices'])
    <!-- ADD PRICE MODAL -->
    <div class="modal fade repair-scroll-modal" id="addPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.prices.store') }}" method="post" novalidate data-guided-price-form data-copied-label="{{ __('repair.copied_cost') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_repair_price') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.repairPrice.partials.price-workflow')
                        <input type="hidden" name="source_price_id" value="">
                        <input type="hidden" name="copy_source_device_id" value="">
                        <p class="form-text repair-copy-costs-message" hidden>{{ __('repair.costs_copied_privately') }}</p>
                        <section data-price-step="1" aria-label="{{ __('repair.service_parts') }}">
                            <h6 tabindex="-1" class="repair-step-heading">{{ __('repair.choose_service_heading') }}</h6>
                            @include('admin.repairPrice.partials.price-services')
                            <div class="mt-3">@include('admin.repairPrice.partials.category-part-fields')</div>
                        </section>
                        <section data-price-step="2" aria-label="{{ __('repair.device_models_step') }}" hidden>
                            <h6 tabindex="-1" class="repair-step-heading">{{ __('repair.choose_models_heading') }}</h6>
                            @include('admin.repairPrice.partials.price-models')
                        </section>
                        <section data-price-step="3" aria-label="{{ __('repair.prices_step') }}" hidden>
                            <h6 tabindex="-1" class="repair-step-heading">{{ __('repair.enter_prices_heading') }}</h6>
                            @include('admin.repairPrice.partials.model-price-fields')
                            <details class="repair-price-options mt-3">
                                <summary>{{ __('repair.optional_price_details') }}</summary>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('index.warranty_days') ?? __('index.warranty_period') }}</label>
                                        <input type="number" min="0" class="form-control" name="warranty_days" placeholder="{{ __('repair.example_days') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('index.estimated_minutes') }}</label>
                                        <input type="number" min="0" class="form-control" name="estimated_minutes" placeholder="{{ __('repair.example_minutes') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">{{ __('index.availability') }} <span class="text-danger">*</span></label>
                                        <select class="form-select" name="availability_status" required>
                                            <option value="available" selected>{{ __('index.available') }}</option>
                                            <option value="out_of_stock">{{ __('index.out_of_stock') }}</option>
                                            <option value="pre_order">{{ __('index.pre_order') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('repair.price_notes') }}</label>
                                        <textarea class="form-control" name="note" rows="2" maxlength="10000"></textarea>
                                    </div>
                                </div>
                            </details>
                        </section>
                        <section data-price-step="4" aria-label="{{ __('repair.review_step') }}" hidden>
                            @include('admin.repairPrice.partials.price-review')
                        </section>
                    </div>
                    <div class="modal-footer repair-workflow-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="button" class="btn btn-outline-secondary" data-price-back hidden>{{ __('repair.back') }}</button>
                        <button type="button" class="btn btn-primary" data-price-next>{{ __('repair.continue') }}</button>
                        <button type="submit" class="btn btn-primary" data-price-save hidden>{{ __('repair.save_prices') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcanany

@canany(['repair_price.update', 'repair_price.manage_prices'])
    <!-- EDIT PRICE MODAL -->
    <div class="modal fade repair-scroll-modal" id="editPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="editPriceForm" method="post" data-price-group>
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_repair_price') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('index.device_model') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_price_device_id" name="repair_device_id" required>
                                    @foreach($devices as $d)
                                        <option value="{{ $d->id }}">{{ $d->brand?->name }} - {{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                                <select class="form-select repair-price-category" name="repair_category_id" required aria-label="{{ __('index.category') }}">
                                    <option value="">{{ __('repair.choose_category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ app()->getLocale() === 'km' && $category->name_kh ? $category->name_kh : $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('repair.model_category_price_help') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('index.repair_service') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_price_service_id" name="repair_service_id" required>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}" data-category-id="{{ $s->repair_category_id }}">{{ (app()->getLocale() == 'km' && !empty($s->name_kh)) ? $s->name_kh : $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.part_name') }}</label>
                                <input type="text" class="form-control" id="edit_price_part_name" name="part_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.part_type') }}</label>
                                <input type="text" class="form-control" id="edit_price_part_type" name="part_type">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-danger">{{ __('index.part_cost') }} ($) <span class="text-muted fw-normal">{{ $partSharePercent }}%</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_part_cost" name="part_cost" data-price-field="part_cost">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-warning">{{ __('index.service_fee') }} ($) <span class="text-muted fw-normal">{{ __('repair.auto_calculated') }}</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_service_fee" name="service_fee" data-price-field="service_fee" readonly tabindex="-1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-primary required">{{ __('index.selling_price') }} ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_selling_price" name="selling_price" data-price-field="selling_price" required>
                            </div>
                            <div class="col-12">
                                <p class="form-text mt-0 mb-0 text-primary"><i data-feather="zap" style="width:14px;height:14px;"></i> {{ __('repair.price_split_help', ['part' => $partSharePercent]) }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ __('index.warranty_days') ?? __('index.warranty_period') }}</label>
                                <input type="number" min="0" class="form-control" id="edit_price_warranty_days" name="warranty_days">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('index.estimated_minutes') }}</label>
                                <input type="number" min="0" class="form-control" id="edit_price_minutes" name="estimated_minutes">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('index.availability') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_price_availability_status" name="availability_status" required>
                                    <option value="available">{{ __('index.available') }}</option>
                                    <option value="out_of_stock">{{ __('index.out_of_stock') }}</option>
                                    <option value="pre_order">{{ __('index.pre_order') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('repair.price_notes') }}</label>
                                <textarea class="form-control" name="note" rows="2" maxlength="10000"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcanany

@can('repair_price.manage_devices')
    <!-- ADD DEVICE MODAL -->
    <div class="modal fade repair-scroll-modal" id="addDeviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.devices.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_device') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="repair_brand_id" required>
                                <option value="" disabled selected>{{ __('index.all_brands') }}</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.device_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="repair_device_type_id" required>
                                @foreach($deviceTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.series') }}</label>
                            <select class="form-select" name="repair_device_series_id">
                                <option value="">{{ __('index.series') }}</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}" data-brand-id="{{ $s->repair_brand_id }}" data-type-id="{{ $s->repair_device_type_id }}">{{ $s->name }} ({{ $s->brand?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.model_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="{{ __('repair.example_model') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.model_number') }}</label>
                            <input type="text" class="form-control" name="model_number" placeholder="{{ __('repair.example_model_number') }}">
                        </div>
                        @include('admin.repairPrice.partials.device-categories')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="deviceStatusSwitch" checked>
                            <label class="form-check-label" for="deviceStatusSwitch">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT DEVICE MODAL -->
    <div class="modal fade repair-scroll-modal" id="editDeviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="editDeviceForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_device') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_device_brand_id" name="repair_brand_id" required>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.device_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_device_type_id" name="repair_device_type_id" required>
                                @foreach($deviceTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.series') }}</label>
                            <select class="form-select" id="edit_device_series_id" name="repair_device_series_id">
                                <option value="">{{ __('index.series') }}</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}" data-brand-id="{{ $s->repair_brand_id }}" data-type-id="{{ $s->repair_device_type_id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.model_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_device_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.model_number') }}</label>
                            <input type="text" class="form-control" id="edit_device_model_number" name="model_number">
                        </div>
                        @include('admin.repairPrice.partials.device-categories')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="edit_device_status">
                            <label class="form-check-label" for="edit_device_status">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('repair_price.manage_services')
    <!-- ADD SERVICE MODAL -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.services.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_service') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="repair_category_id" required>
                                <option value="" disabled selected>{{ __('index.all_categories') }}</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ (app()->getLocale() == 'km' && !empty($c->name_kh)) ? $c->name_kh : $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.service_name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="{{ __('repair.example_service') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" name="name_kh" placeholder="{{ __('repair.example_service_kh') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="{{ __('repair.service_notes') }}"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="serviceStatusSwitch" checked>
                            <label class="form-check-label" for="serviceStatusSwitch">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT SERVICE MODAL -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editServiceForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_service') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_service_category_id" name="repair_category_id" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ (app()->getLocale() == 'km' && !empty($c->name_kh)) ? $c->name_kh : $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.service_name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_service_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" id="edit_service_name_kh" name="name_kh">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.description') }}</label>
                            <textarea class="form-control" id="edit_service_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="edit_service_status">
                            <label class="form-check-label" for="edit_service_status">{{ __('index.active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Tab switching & retaining
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $(e.target).attr("href");
            if (target === '#pricesPane') $('#filterActiveTab').val('prices');
            else if (target === '#brandsPane') $('#filterActiveTab').val('brands');
            else if (target === '#categoriesPane') $('#filterActiveTab').val('categories');
            else if (target === '#devicesPane') $('#filterActiveTab').val('devices');
            else if (target === '#servicesPane') $('#filterActiveTab').val('services');
            else if (target === '#typesPane') $('#filterActiveTab').val('types');
            else if (target === '#seriesPane') $('#filterActiveTab').val('series');
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('active_tab', $('#filterActiveTab').val());
            window.history.replaceState(null, '', nextUrl);

        });

        // SweetAlert Delete Warning
        $('.repair-delete-form').on('submit', function (event) {
            event.preventDefault();
            const form = this;
            let title = $(form).find('.repair-delete-btn').data('title');
            Swal.fire({
                title: @json(__('index.delete_confirmation')),
                text: title ? title : '',
                icon: 'warning',
                showDenyButton: true,
                confirmButtonText: @json(__('index.yes')),
                denyButtonText: @json(__('index.no')),
                padding: '10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        $('.edit-catalog-btn').on('click', function () {
            const record = $(this).data('record');
            const entity = $(this).data('entity');
            const form = document.getElementById('editCatalog' + entity + 'Form');
            form.action = $(this).data('url');
            ['name', 'slug', 'icon', 'repair_brand_id', 'repair_device_type_id'].forEach(function (key) {
                const field = form.elements.namedItem(key);
                if (field) field.value = record[key] == null ? '' : record[key];
            });
            form.elements.namedItem('status').checked = !!record.status;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editCatalog' + entity + 'Modal')).show();
        });

        // Brand Edit Modal Populate
        $('.edit-brand-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_brand_name').val($(this).data('name'));
            $('#edit_brand_slug').val($(this).data('slug'));
            $('#edit_brand_logo').val($(this).data('logo'));
            $('#edit_brand_status').prop('checked', $(this).data('status') == 1);

            $('#editBrandForm').attr('action', "{{ url('admin/repair-price/brands') }}/" + id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editBrandModal')).show();
        });

        // Category Edit Modal Populate
        $('.edit-category-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_category_name').val($(this).data('name'));
            $('#edit_category_name_kh').val($(this).data('name-kh'));
            $('#edit_category_slug').val($(this).data('slug'));
            $('#edit_category_icon').val($(this).data('icon'));
            $('#edit_category_sort_order').val($(this).data('sort-order'));
            $('#edit_category_status').prop('checked', $(this).data('status') == 1);

            $('#editCategoryForm').attr('action', "{{ url('admin/repair-price/categories') }}/" + id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editCategoryModal')).show();
        });

        // Price Edit Modal Populate
        $('.edit-price-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_price_device_id').val($(this).data('device-id'));
            $('#edit_price_service_id').val($(this).data('service-id'));
            $('#edit_price_part_name').val($(this).data('part-name'));
            $('#editPriceForm [name="note"]').val($(this).data('note'));
            $('#edit_price_part_type').val($(this).data('part-type'));
            $('#edit_price_part_cost').val($(this).data('part-cost'));
            $('#edit_price_service_fee').val($(this).data('service-fee'));
            $('#edit_price_selling_price').val($(this).data('selling-price'));
            $('#edit_price_warranty_days').val($(this).data('warranty-days'));
            $('#edit_price_minutes').val($(this).data('estimated-minutes'));
            $('#edit_price_availability_status').val($(this).data('availability-status'));

            $('#editPriceForm').attr('action', "{{ url('admin/repair-price/prices') }}/" + id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editPriceModal')).show();
        });

        // Device Edit Modal Populate
        $('.repair-category-search').on('input', function () {
            const search = this.value.toLocaleLowerCase();
            $(this).closest('.repair-category-picker').find('.repair-category-option').each(function () {
                $(this).toggle($(this).find('.form-check-label').text().toLocaleLowerCase().includes(search));
            });
        });
        $('.repair-categories-all').on('click', function () {
            $(this).closest('.repair-category-picker').find('.repair-category-option:visible input[type="checkbox"]').prop('checked', true).each(function () {
                this.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });
        $('.repair-categories-clear').on('click', function () {
            $(this).closest('.repair-category-picker').find('input[type="checkbox"]').prop('checked', false).each(function () {
                this.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });

        $('.edit-device-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_device_brand_id').val($(this).data('brand-id'));
            $('#edit_device_type_id').val($(this).data('type-id'));
            $('#edit_device_series_id').val($(this).data('series-id'));
            $('#edit_device_name').val($(this).data('name'));
            $('#edit_device_model_number').val($(this).data('model-number'));
            const categoryIds = ($(this).data('category-ids') || []).map(String);
            $('#editDeviceForm [name="category_ids[]"]').each(function () {
                this.checked = categoryIds.includes(this.value);
            });
            $('#editDeviceForm .repair-category-search').val('').trigger('input');
            $('#edit_device_status').prop('checked', $(this).data('status') == 1);

            document.getElementById('editDeviceForm').dataset.prices = JSON.stringify($(this).data('prices') || []);
            $('#editDeviceForm').attr('action', "{{ url('admin/repair-price/devices') }}/" + id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editDeviceModal')).show();
        });

        // Service Edit Modal Populate
        $('.edit-service-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_service_category_id').val($(this).data('category-id'));
            $('#edit_service_name').val($(this).data('name'));
            $('#edit_service_name_kh').val($(this).data('name-kh'));
            $('#edit_service_description').val($(this).data('description'));
            $('#edit_service_status').prop('checked', $(this).data('status') == 1);

            $('#editServiceForm').attr('action', "{{ url('admin/repair-price/services') }}/" + id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editServiceModal')).show();
        });
    });
</script>
<script>window.REPAIR_PRICING = {{ Illuminate\Support\Js::from(['partShare' => (float) config('repair-pricing.part_share', 0.70), 'priceStep' => (float) config('repair-pricing.price_step', 0.50)]) }};</script>
<script src="{{ asset('assets/js/repair-price-auto-split.js') }}?v={{ filemtime(public_path('assets/js/repair-price-auto-split.js')) }}"></script>
<script src="{{ asset('assets/js/repair-price-models.js') }}?v={{ filemtime(public_path('assets/js/repair-price-models.js')) }}"></script>
<script src="{{ asset('assets/js/repair-price-workflow.js') }}?v={{ filemtime(public_path('assets/js/repair-price-workflow.js')) }}"></script>
@include('admin.repairPrice.partials.form-behavior')
@endsection
