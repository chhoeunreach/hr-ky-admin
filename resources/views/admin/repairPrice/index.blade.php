@extends('layouts.master')

@section('title', __('index.repair_price'))

@section('action', __('index.lists') ?? 'Lists')

@section('button')
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.repair-price.import') }}" class="btn btn-outline-success btn-sm">
            <i class="link-icon" data-feather="upload-cloud"></i> {{ __('index.import_excel') ?? 'Import Excel' }}
        </a>
        <a href="{{ route('admin.repair-price.sample-template') }}" class="btn btn-outline-secondary btn-sm" title="Download Sample CSV">
            <i class="link-icon" data-feather="download"></i> Sample CSV
        </a>
        @can('repair_price.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPriceModal">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_repair_price') }}
            </button>
        @endcan
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
                <i class="link-icon" data-feather="tag"></i> {{ __('index.add_brand') ?? 'Add Brand' }}
            </button>
        @endcan
        @can('repair_price.manage_categories')
            <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="link-icon" data-feather="grid"></i> {{ __('index.add_category') ?? 'Add Category' }}
            </button>
        @endcan
    </div>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <!-- Breadcrumb & Role Permissions Indicator -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.repair.dashboard') }}">{{ __('index.repair_management') ?? 'Repair Management' }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.repair_price') }}</li>
        </ol>

        <!-- Role Badges for Transparency -->
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <span class="badge bg-secondary">{{ __('index.role') ?? 'Role' }}: {{ auth()->user()->role?->name ?? 'Admin' }}</span>
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
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0"><i class="link-icon me-1" data-feather="filter"></i> {{ __('index.filters_and_search') }}</h6>
        </div>
        <form class="card-body pb-2" action="{{ route('admin.repair-price.index') }}" method="get">
            <input type="hidden" name="active_tab" id="filterActiveTab" value="{{ $filterParameters['active_tab'] ?? 'prices' }}">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-6 mb-3">
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

                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label small text-muted">{{ __('index.device_model') }}</label>
                    <select class="form-select" name="device_id">
                        <option value="">{{ __('index.all_devices') }}</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ (isset($filterParameters['device_id']) && $filterParameters['device_id'] == $device->id) ? 'selected' : '' }}>
                                {{ $device->name }} ({{ $device->model_number ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
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

                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label small text-muted">{{ __('index.keyword_search') }}</label>
                    <input type="text" class="form-control" name="search" placeholder="{{ __('index.search_placeholder') }}" value="{{ $filterParameters['search'] ?? '' }}">
                </div>

                <div class="col-12 mb-2 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success btn-sm"><i data-feather="search" class="me-1"></i> {{ __('index.filter') }}</button>
                    <a href="{{ route('admin.repair-price.index', ['active_tab' => $filterParameters['active_tab'] ?? 'prices']) }}" class="btn btn-secondary btn-sm"><i data-feather="refresh-cw" class="me-1"></i> {{ __('index.reset') }}</a>
                </div>
            </div>
        </form>
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
                    <i class="link-icon me-1" data-feather="tag"></i> {{ __('index.brands') ?? 'Brands' }}
                </a>
            </li>
        @endcan
        @can('repair_price.manage_categories')
            <li class="nav-item">
                <a class="nav-link {{ ($filterParameters['active_tab'] ?? '') == 'categories' ? 'active' : '' }}" id="categories-tab" data-bs-toggle="tab" href="#categoriesPane" role="tab" aria-controls="categoriesPane" aria-selected="false">
                    <i class="link-icon me-1" data-feather="grid"></i> {{ __('index.categories') ?? 'Categories' }}
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
        <li class="nav-item ms-auto">
            <a class="nav-link text-primary fw-bold" href="{{ route('admin.repair-price.import') }}">
                <i class="link-icon me-1 text-primary" data-feather="upload-cloud"></i> Import Excel / CSV
            </a>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="repairTabContent">

        <!-- TAB 1: PRICES & COSTS -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? 'prices') == 'prices' ? 'show active' : '' }}" id="pricesPane" role="tabpanel" aria-labelledby="prices-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('index.repair_price_matrix') }}</h6>
                    <span class="text-muted small">{{ __('index.showing_repair_records', ['count' => $prices->total()]) }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
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
                                    @canany(['repair_price.update', 'repair_price.delete'])
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prices as $key => $p)
                                    @php
                                        $profit = ($p->part_cost !== null && $p->service_fee !== null) ? ($p->selling_price - $p->part_cost - $p->service_fee) : null;
                                        $margin = ($p->selling_price > 0 && $profit !== null) ? round(($profit / $p->selling_price) * 100, 1) : null;
                                        $catName = (app()->getLocale() == 'km' && !empty($p->service?->category?->name_kh)) ? $p->service->category->name_kh : ($p->service?->category?->name ?? 'General');
                                        $srvName = (app()->getLocale() == 'km' && !empty($p->service?->name_kh)) ? $p->service->name_kh : ($p->service?->name ?? 'N/A');
                                    @endphp
                                    <tr>
                                        <td>{{ $prices->firstItem() + $key }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $p->device?->name ?? 'N/A' }}</div>
                                            <span class="badge bg-light text-secondary">{{ $p->device?->brand?->name ?? 'Brand' }}</span>
                                            <span class="text-muted small">({{ $p->device?->model_number ?? '-' }})</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $catName }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $srvName }}</div>
                                            @if($p->part_name)
                                                <div class="text-muted small">{{ $p->part_name }} ({{ $p->part_type ?? 'Standard' }})</div>
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
                                                {{ $p->warranty_days ? $p->warranty_days . ' ' . (__('index.days') ?? 'days') : '-' }}
                                            </span>
                                            @if($p->estimated_minutes)
                                                <div class="text-muted small mt-1"><i data-feather="clock" style="width:11px;height:11px;"></i> {{ $p->estimated_minutes }} {{ __('index.minute') ?? 'min' }}</div>
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
                                        @canany(['repair_price.update', 'repair_price.delete'])
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @can('repair_price.update')
                                                        <button class="btn btn-sm btn-outline-primary btn-icon edit-price-btn"
                                                                data-id="{{ $p->id }}"
                                                                data-device-id="{{ $p->repair_device_id }}"
                                                                data-service-id="{{ $p->repair_service_id }}"
                                                                data-part-name="{{ $p->part_name }}"
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
                                                    @endcan
                                                    @can('repair_price.delete')
                                                        <a href="javascript:void(0);"
                                                           class="btn btn-sm btn-outline-danger btn-icon deleteWarning"
                                                           data-title="{{ $srvName }}"
                                                           data-href="{{ route('admin.repair-price.prices.delete', $p->id) }}"
                                                           title="{{ __('index.delete') }}">
                                                            <i data-feather="trash-2"></i>
                                                        </a>
                                                    @endcan
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
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('index.brands') ?? 'Brands' }}</h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                            <i data-feather="plus" class="me-1"></i> {{ __('index.add_brand') ?? 'Add Brand' }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('index.brand') }}</th>
                                        <th>Slug</th>
                                        <th>{{ __('index.status') }}</th>
                                        <th class="text-center">{{ __('index.action') }}</th>
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
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-sm btn-outline-danger btn-icon deleteWarning"
                                                       data-title="{{ $brand->name }}"
                                                       data-href="{{ route('admin.repair-price.brands.delete', $brand->id) }}"
                                                       title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
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
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">{{ __('index.categories') ?? 'Categories' }}</h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i data-feather="plus" class="me-1"></i> {{ __('index.add_category') ?? 'Add Category' }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('index.category') }}</th>
                                        <th>{{ __('index.service_name_kh') }}</th>
                                        <th>Slug</th>
                                        <th>{{ __('index.sort_order') ?? 'Sort Order' }}</th>
                                        <th>{{ __('index.status') }}</th>
                                        <th class="text-center">{{ __('index.action') }}</th>
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
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-sm btn-outline-danger btn-icon deleteWarning"
                                                       data-title="{{ $category->name }}"
                                                       data-href="{{ route('admin.repair-price.categories.delete', $category->id) }}"
                                                       title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
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
                            {{ $categoriesList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <!-- TAB 2: DEVICE MODELS -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'devices' ? 'show active' : '' }}" id="devicesPane" role="tabpanel" aria-labelledby="devices-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('index.repair_devices') }}</h6>
                    @can('repair_price.manage_devices')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i data-feather="plus" class="me-1"></i> {{ __('index.add_device') }}
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.brand') }}</th>
                                    <th>{{ __('index.device_type') }}</th>
                                    <th>{{ __('index.series') }}</th>
                                    <th>{{ __('index.model_name') }}</th>
                                    <th>{{ __('index.model_number') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    @can('repair_price.manage_devices')
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devicesList as $key => $d)
                                    <tr>
                                        <td>{{ $devicesList->firstItem() + $key }}</td>
                                        <td><span class="badge bg-secondary">{{ $d->brand?->name ?? '-' }}</span></td>
                                        <td>{{ $d->deviceType?->name ?? 'Smartphone' }}</td>
                                        <td>{{ $d->series?->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $d->name }}</td>
                                        <td><code>{{ $d->model_number ?? '-' }}</code></td>
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
                                                            data-name="{{ $d->name }}"
                                                            data-model-number="{{ $d->model_number }}"
                                                            data-status="{{ $d->status }}"
                                                            title="{{ __('index.edit') }}">
                                                        <i data-feather="edit-2"></i>
                                                    </button>
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-sm btn-outline-danger btn-icon deleteWarning"
                                                       data-title="{{ $d->name }}"
                                                       data-href="{{ route('admin.repair-price.devices.delete', $d->id) }}"
                                                       title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        @endcan
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
                        {{ $devicesList->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: SERVICES & CATEGORIES -->
        <div class="tab-pane fade {{ ($filterParameters['active_tab'] ?? '') == 'services' ? 'show active' : '' }}" id="servicesPane" role="tabpanel" aria-labelledby="services-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ __('index.repair_services') }}</h6>
                    @can('repair_price.manage_services')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                            <i data-feather="plus" class="me-1"></i> {{ __('index.add_service') }}
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.category') }}</th>
                                    <th>{{ __('index.service_name_en') }}</th>
                                    <th>{{ __('index.service_name_kh') }}</th>
                                    <th>{{ __('index.description') }}</th>
                                    <th>{{ __('index.status') }}</th>
                                    @can('repair_price.manage_services')
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicesList as $key => $s)
                                    <tr>
                                        <td>{{ $servicesList->firstItem() + $key }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ (app()->getLocale() == 'km' && !empty($s->category?->name_kh)) ? $s->category->name_kh : ($s->category?->name ?? 'General') }}
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
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-sm btn-outline-danger btn-icon deleteWarning"
                                                       data-title="{{ $s->name }}"
                                                       data-href="{{ route('admin.repair-price.services.delete', $s->id) }}"
                                                       title="{{ __('index.delete') }}">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
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

    </div>
</section>

<!-- ================= MODALS ================= -->

@can('repair_price.manage_brands')
    <!-- ADD BRAND MODAL -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.brands.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_brand') ?? 'Add Brand' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Oppo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" placeholder="Auto generated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo URL / Path</label>
                            <input type="text" class="form-control" name="logo" placeholder="Optional">
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
                        <h5 class="modal-title">{{ __('index.edit_brand') ?? 'Edit Brand' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.brand') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_brand_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="edit_brand_slug" name="slug">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo URL / Path</label>
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
                        <h5 class="modal-title">{{ __('index.add_category') ?? 'Add Category' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.category') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Charging" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" name="name_kh" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" placeholder="Auto generated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" class="form-control" name="icon" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.sort_order') ?? 'Sort Order' }}</label>
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
                        <h5 class="modal-title">{{ __('index.edit_category') ?? 'Edit Category' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="edit_category_slug" name="slug">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" class="form-control" id="edit_category_icon" name="icon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.sort_order') ?? 'Sort Order' }}</label>
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

@can('repair_price.create')
    <!-- ADD PRICE MODAL -->
    <div class="modal fade" id="addPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.prices.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_repair_price') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('index.device_model') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="repair_device_id" required>
                                    <option value="" disabled selected>{{ __('index.select_device') ?? 'Select Device' }}</option>
                                    @foreach($devices as $d)
                                        <option value="{{ $d->id }}">{{ $d->brand?->name }} - {{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">{{ __('index.repair_service') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="repair_service_id" required>
                                    <option value="" disabled selected>{{ __('index.select_service') ?? 'Select Service' }}</option>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}">{{ (app()->getLocale() == 'km' && !empty($s->name_kh)) ? $s->name_kh : $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.part_name') }}</label>
                                <input type="text" class="form-control" name="part_name" placeholder="e.g. OLED Screen Assembly">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.part_type') }}</label>
                                <input type="text" class="form-control" name="part_type" placeholder="e.g. Original / OEM / High Quality">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-danger">{{ __('index.part_cost') }} ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="part_cost" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-warning">{{ __('index.service_fee') }} ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="service_fee" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-primary required">{{ __('index.selling_price') }} ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" name="selling_price" required placeholder="0.00">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ __('index.warranty_days') ?? __('index.warranty_period') }}</label>
                                <input type="number" min="0" class="form-control" name="warranty_days" placeholder="e.g. 90">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('index.estimated_minutes') }}</label>
                                <input type="number" min="0" class="form-control" name="estimated_minutes" placeholder="e.g. 45">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('index.availability') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="availability_status" required>
                                    <option value="available" selected>{{ __('index.available') }}</option>
                                    <option value="out_of_stock">{{ __('index.out_of_stock') }}</option>
                                    <option value="pre_order">{{ __('index.pre_order') }}</option>
                                </select>
                            </div>
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
@endcan

@can('repair_price.update')
    <!-- EDIT PRICE MODAL -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editPriceForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_repair_price') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <label class="form-label required">{{ __('index.repair_service') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_price_service_id" name="repair_service_id" required>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}">{{ (app()->getLocale() == 'km' && !empty($s->name_kh)) ? $s->name_kh : $s->name }}</option>
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
                                <label class="form-label text-danger">{{ __('index.part_cost') }} ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_part_cost" name="part_cost">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-warning">{{ __('index.service_fee') }} ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_service_fee" name="service_fee">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-primary required">{{ __('index.selling_price') }} ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_price_selling_price" name="selling_price" required>
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

@can('repair_price.manage_devices')
    <!-- ADD DEVICE MODAL -->
    <div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.repair-price.devices.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.add_device') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->brand?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('index.model_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. iPhone 15 Pro Max" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.model_number') }}</label>
                            <input type="text" class="form-control" name="model_number" placeholder="e.g. A3106">
                        </div>
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
    <div class="modal fade" id="editDeviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editDeviceForm" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.edit_device') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <input type="text" class="form-control" name="name" placeholder="e.g. Screen Replacement" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.service_name_kh') }}</label>
                            <input type="text" class="form-control" name="name_kh" placeholder="e.g. ផ្លាស់ប្តូរអេក្រង់">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Service notes..."></textarea>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        });

        // SweetAlert Delete Warning
        $('body').on('click', '.deleteWarning', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_confirmation') ?? "Are you sure you want to delete?" }}',
                text: title ? title : '',
                icon: 'warning',
                showDenyButton: true,
                confirmButtonText: '{{ __('index.yes') ?? "Yes, delete" }}',
                denyButtonText: '{{ __('index.no') ?? "Cancel" }}',
                padding: '10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        // Brand Edit Modal Populate
        $('.edit-brand-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_brand_name').val($(this).data('name'));
            $('#edit_brand_slug').val($(this).data('slug'));
            $('#edit_brand_logo').val($(this).data('logo'));
            $('#edit_brand_status').prop('checked', $(this).data('status') == 1);

            $('#editBrandForm').attr('action', "{{ url('admin/repair-price/brands') }}/" + id);
            $('#editBrandModal').modal('show');
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
            $('#editCategoryModal').modal('show');
        });

        // Price Edit Modal Populate
        $('.edit-price-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_price_device_id').val($(this).data('device-id'));
            $('#edit_price_service_id').val($(this).data('service-id'));
            $('#edit_price_part_name').val($(this).data('part-name'));
            $('#edit_price_part_type').val($(this).data('part-type'));
            $('#edit_price_part_cost').val($(this).data('part-cost'));
            $('#edit_price_service_fee').val($(this).data('service-fee'));
            $('#edit_price_selling_price').val($(this).data('selling-price'));
            $('#edit_price_warranty_days').val($(this).data('warranty-days'));
            $('#edit_price_minutes').val($(this).data('estimated-minutes'));
            $('#edit_price_availability_status').val($(this).data('availability-status'));

            $('#editPriceForm').attr('action', "{{ url('admin/repair-price/prices') }}/" + id);
            $('#editPriceModal').modal('show');
        });

        // Device Edit Modal Populate
        $('.edit-device-btn').on('click', function () {
            let id = $(this).data('id');
            $('#edit_device_brand_id').val($(this).data('brand-id'));
            $('#edit_device_type_id').val($(this).data('type-id'));
            $('#edit_device_series_id').val($(this).data('series-id'));
            $('#edit_device_name').val($(this).data('name'));
            $('#edit_device_model_number').val($(this).data('model-number'));
            $('#edit_device_status').prop('checked', $(this).data('status') == 1);

            $('#editDeviceForm').attr('action', "{{ url('admin/repair-price/devices') }}/" + id);
            $('#editDeviceModal').modal('show');
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
            $('#editServiceModal').modal('show');
        });
    });
</script>
@endsection
