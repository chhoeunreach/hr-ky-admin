@extends('layouts.master')
@section('title', 'Daily Records settings')
@section('main-content')
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.daily-records.index') }}">Task &amp; Report Center</a>
@include('admin.daily-records.workflow-messages')
<div class="row g-3">
    <div class="col-lg-7"><div class="card"><div class="card-body">
        <div class="d-flex gap-2 justify-content-between">
            <h1 class="h4">Categories and subcategories</h1>
            <a href="{{ route('admin.daily-records.categories') }}">New category</a>
        </div>
        <div class="table-responsive"><table class="table mb-3">
            <thead><tr><th>Type</th><th>Name</th><th>Status</th><th>Order</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr><td>{{ ucfirst($category->kind) }}</td>
                        <td>{{ $category->name_en }}<div class="small text-muted">{{ $category->name_km }}</div></td>
                        <td>{{ $category->active ? 'Active' : 'Inactive' }} @if ($category->sensitive) · Confidential @endif</td>
                        <td>{{ $category->position }}</td>
                        <td><a href="{{ route('admin.daily-records.categories', ['edit' => $category->id, 'page' => $categories->currentPage()]) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        {{ $categories->withQueryString()->links() }}
    </div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-body">
        <h2 class="h6">{{ $editing ? 'Edit classification' : 'New classification' }}</h2>
        <form method="POST" action="{{ route('admin.daily-records.category-save') }}" class="d-grid gap-2">
            @csrf
            @if ($editing)<input type="hidden" name="id" value="{{ $editing->id }}">@endif
            <label>Record type<select class="form-select" name="kind">
                @foreach (['activity','incident'] as $kind)
                    <option value="{{ $kind }}" @selected(old('kind',$editing?->kind) === $kind)>{{ ucfirst($kind) }}</option>
                @endforeach
            </select></label>
            <label>Parent category<select class="form-select" name="parent_id">
                <option value="">Primary category</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id',$editing?->parent_id) == $parent->id)>{{ ucfirst($parent->kind) }} · {{ $parent->name_en }}</option>
                @endforeach
            </select></label>
            <label>English name<input required class="form-control" name="name_en" value="{{ old('name_en',$editing?->name_en) }}" maxlength="120"></label>
            <label>Khmer name<input required class="form-control" name="name_km" value="{{ old('name_km',$editing?->name_km) }}" maxlength="120"></label>
            <label>Department<select class="form-select" name="department_id"><option value="">All departments</option>
                @foreach ($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id',$editing?->department_id) == $department->id)>{{ $department->dept_name }}</option>@endforeach
            </select></label>
            <label>Icon key<input class="form-control" name="icon" maxlength="24" value="{{ old('icon',$editing?->icon) }}"></label>
            <label>Display order<input class="form-control" required type="number" min="0" name="position" value="{{ old('position',$editing?->position ?? 0) }}"></label>
            <label>Default follow-up in days<input class="form-control" type="number" min="0" max="365" name="follow_up_days" value="{{ old('follow_up_days',$editing?->follow_up_days) }}"></label>
            <input type="hidden" name="active" value="0">
            <label><input type="checkbox" class="form-check-input" name="active" value="1" @checked(old('active',$editing?->active ?? true))> Active</label>
            <input type="hidden" name="sensitive" value="0">
            <label><input type="checkbox" class="form-check-input" name="sensitive" value="1" @checked(old('sensitive',$editing?->sensitive ?? false))> Confidential</label>
            <label>Suggested tags (JSON list)<textarea class="form-control font-monospace" name="tags" rows="2">{{ is_string(old('tags')) ? old('tags') : ($editing?->tags ?? '[]') }}</textarea></label>
            <label>Allowed incident destinations (JSON list)<textarea class="form-control font-monospace" name="workflow" rows="2">{{ is_string(old('workflow')) ? old('workflow') : ($editing?->workflow ?? '[]') }}</textarea></label>
            <label>Field definitions (JSON list)<textarea class="form-control font-monospace" name="fields" rows="6">{{ is_string(old('fields')) ? old('fields') : json_encode(json_decode($editing?->fields ?? '[]', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea></label>
            <small class="text-muted">Example: <code>[{"key":"reference","label_en":"Reference","label_km":"លេខយោង","type":"text","required":false}]</code></small>
            <button class="btn btn-primary">Save classification</button>
        </form>
    </div></div></div>
</div>
@endsection
