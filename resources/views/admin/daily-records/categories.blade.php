@extends('layouts.master')

@section('title', __('daily_records.category_settings_title'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.daily-records.index') }}" class="text-decoration-none">
                        <i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.task_and_report_center') }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.category_settings_breadcrumb') }}</li>
            </ol>
        </nav>
        <a class="dr-btn-outline py-1 px-3" href="{{ route('admin.daily-records.index') }}">
            <i class="fa-solid fa-arrow-left"></i> {{ __('daily_records.back_to_center') }}
        </a>
    </div>

    @include('admin.daily-records.workflow-messages')

    <div class="row g-3">
        {{-- Categories Table --}}
        <div class="col-lg-7">
            <div class="dr-card">
                <div class="dr-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="dr-card-title">
                            <i class="fa-solid fa-folder-tree text-primary"></i> {{ __('daily_records.categories_and_subcategories') }}
                        </h1>
                        <span class="dr-meta-badge">{{ __('daily_records.defined_count', ['count' => $categories->total()]) }}</span>
                    </div>
                    <a class="dr-btn-primary btn-sm py-1 px-3 text-decoration-none" href="{{ route('admin.daily-records.categories') }}">
                        <i class="fa-solid fa-plus"></i> {{ __('daily_records.new_category') }}
                    </a>
                </div>
                <div class="dr-card-body p-0">
                    <div class="dr-table-responsive">
                        <table class="dr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('daily_records.type') }}</th>
                                    <th>{{ __('daily_records.classification_name') }}</th>
                                    <th>{{ __('daily_records.status_and_privacy') }}</th>
                                    <th class="text-center">{{ __('daily_records.order') }}</th>
                                    <th class="text-end">{{ __('daily_records.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>
                                            <span class="dr-badge {{ $category->kind === 'incident' ? 'dr-badge-danger' : 'dr-badge-primary' }}">
                                                {{ __('daily_records.type_' . $category->kind) !== 'daily_records.type_' . $category->kind ? __('daily_records.type_' . $category->kind) : ucfirst($category->kind) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $category->name_en }}</div>
                                            <div class="small text-muted">{{ $category->name_km }}</div>
                                        </td>
                                        <td>
                                            @if ($category->active)
                                                <span class="dr-badge dr-badge-success">
                                                    <span class="dr-badge-dot"></span> {{ __('daily_records.active') }}
                                                </span>
                                            @else
                                                <span class="dr-badge dr-badge-secondary">{{ __('daily_records.inactive') }}</span>
                                            @endif
                                            @if ($category->sensitive)
                                                <span class="dr-badge dr-badge-purple ms-1">
                                                    <i class="fa-solid fa-lock me-1"></i> {{ __('daily_records.confidential') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $category->position }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a class="dr-btn-outline py-1 px-2 text-decoration-none small" href="{{ route('admin.daily-records.categories', ['edit' => $category->id, 'page' => $categories->currentPage()]) }}">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i> {{ __('daily_records.edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="dr-empty-state">
                                                <div class="dr-empty-icon"><i class="fa-solid fa-folder-open"></i></div>
                                                <div class="dr-empty-title">No Categories Found</div>
                                                <p class="dr-empty-desc">Create your first category or classification node on the right.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($categories->hasPages())
                        <div class="p-3 border-top">
                            {{ $categories->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Column --}}
        <div class="col-lg-5">
            <div class="dr-card">
                <div class="dr-card-header">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-pen-ruler text-primary"></i> {{ $editing ? __('daily_records.edit_classification') : __('daily_records.new_classification') }}
                    </h2>
                    @if ($editing)
                        <a href="{{ route('admin.daily-records.categories') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('daily_records.cancel_edit') }}</a>
                    @endif
                </div>
                <div class="dr-card-body">
                    <form method="POST" action="{{ route('admin.daily-records.category-save') }}" class="d-grid gap-3">
                        @csrf
                        @if ($editing)
                            <input type="hidden" name="id" value="{{ $editing->id }}">
                        @endif

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.record_type') }}</label>
                            <select class="dr-form-select" name="kind">
                                @foreach ([
                                    'activity' => __('daily_records.type_activity'),
                                    'incident' => __('daily_records.type_incident')
                                ] as $kindKey => $kindTitle)
                                    <option value="{{ $kindKey }}" @selected(old('kind',$editing?->kind) === $kindKey)>{{ $kindTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.parent_category') }}</label>
                            <select class="dr-form-select" name="parent_id">
                                <option value="">{{ __('daily_records.primary_top_level_category') }}</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected(old('parent_id',$editing?->parent_id) == $parent->id)>
                                        {{ ucfirst($parent->kind) }} · {{ $parent->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.english_name') }} <span class="text-danger">*</span></label>
                            <input required class="dr-form-control" name="name_en" value="{{ old('name_en',$editing?->name_en) }}" maxlength="120" placeholder="e.g. Workplace Safety">
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.khmer_name') }} <span class="text-danger">*</span></label>
                            <input required class="dr-form-control" name="name_km" value="{{ old('name_km',$editing?->name_km) }}" maxlength="120" placeholder="e.g. សុវត្ថិភាពកន្លែងធ្វើការ">
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.restricted_department') }}</label>
                            <select class="dr-form-select" name="department_id">
                                <option value="">{{ __('daily_records.all_departments') }}</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id',$editing?->department_id) == $department->id)>{{ $department->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-6 dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.icon_key') }}</label>
                                <input class="dr-form-control" name="icon" maxlength="24" placeholder="e.g. shield" value="{{ old('icon',$editing?->icon) }}">
                            </div>
                            <div class="col-6 dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.display_order') }}</label>
                                <input class="dr-form-control" required type="number" min="0" name="position" value="{{ old('position',$editing?->position ?? 0) }}">
                            </div>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.default_follow_up_target_days') }}</label>
                            <input class="dr-form-control" type="number" min="0" max="365" name="follow_up_days" value="{{ old('follow_up_days',$editing?->follow_up_days) }}" placeholder="e.g. 7">
                        </div>

                        <div class="d-flex gap-4 p-2 bg-light rounded border">
                            <input type="hidden" name="active" value="0">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkActive" name="active" value="1" @checked(old('active',$editing?->active ?? true))>
                                <label class="form-check-label fw-semibold small" for="checkActive">{{ __('daily_records.active_category') }}</label>
                            </div>
                            <input type="hidden" name="sensitive" value="0">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkSensitive" name="sensitive" value="1" @checked(old('sensitive',$editing?->sensitive ?? false))>
                                <label class="form-check-label fw-semibold small text-danger" for="checkSensitive">{{ __('daily_records.confidential_sensitive') }}</label>
                            </div>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.suggested_tags') }}</label>
                            <textarea class="dr-form-control font-monospace" name="tags" rows="2" placeholder='["equipment", "urgent"]'>{{ is_string(old('tags')) ? old('tags') : ($editing?->tags ?? '[]') }}</textarea>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.allowed_incident_destinations') }}</label>
                            <textarea class="dr-form-control font-monospace" name="workflow" rows="2" placeholder='["investigation", "remediation"]'>{{ is_string(old('workflow')) ? old('workflow') : ($editing?->workflow ?? '[]') }}</textarea>
                        </div>

                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.field_definitions') }}</label>
                            <textarea class="dr-form-control font-monospace" name="fields" rows="4">{{ is_string(old('fields')) ? old('fields') : json_encode(json_decode($editing?->fields ?? '[]', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                            <div class="form-text text-muted small">Example: <code>[{"key":"ref","label_en":"Ref","label_km":"លេខ","type":"text"}]</code></div>
                        </div>

                        <button class="dr-btn-primary justify-content-center py-2" type="submit">
                            <i class="fa-solid fa-check"></i>
                            <span>{{ __('daily_records.save_classification') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
