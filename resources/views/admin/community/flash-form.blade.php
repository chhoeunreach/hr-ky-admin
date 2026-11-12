@extends('layouts.master')
@section('title', $item->exists ? 'Edit Flash View' : 'Create Flash View')
@section('action', 'Form')
@section('styles')
<style>
.fv-form{--fv-ink:#172b4d;--fv-muted:#718096;--fv-line:#e8edf3;color:var(--fv-ink)}.fv-form-hero{border:0;border-radius:14px;background:linear-gradient(115deg,#123b67,#176b78);color:#fff}.fv-form-hero .card-body{padding:22px 26px}.fv-form-hero h3{color:#fff;margin:0 0 5px;font-size:1.35rem;font-weight:700}.fv-form-hero p{color:rgba(255,255,255,.78);margin:0}.fv-section{border:1px solid var(--fv-line);border-radius:13px;box-shadow:0 4px 16px rgba(23,43,77,.035);margin-bottom:16px}.fv-section .card-body{padding:21px}.fv-section-title{display:flex;gap:12px;align-items:center;margin-bottom:17px}.fv-step{display:grid;place-items:center;width:31px;height:31px;border-radius:10px;background:#eaf4f5;color:#176b78;font-weight:750;font-size:.84rem}.fv-section-title h5{font-size:1rem;font-weight:700;margin:0;color:#213650}.fv-section-title p{font-size:.79rem;color:#718096;margin:3px 0 0}.fv-form .form-label{font-weight:650;color:#334860;font-size:.87rem}.fv-form .form-control,.fv-form .form-select{border-color:#dce3eb;border-radius:8px}.fv-form .form-control:focus,.fv-form .form-select:focus{border-color:#3b9a9d;box-shadow:0 0 0 .2rem rgba(35,138,145,.13)}.fv-hint{color:#718096;font-size:.78rem;margin-top:-7px;margin-bottom:14px}.fv-preview{position:sticky;top:18px;border:1px solid var(--fv-line);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.04);overflow:hidden}.fv-preview-head{padding:14px 17px;background:#f6f8fb;border-bottom:1px solid var(--fv-line);font-weight:700;color:#334860}.fv-preview-body{padding:19px}.fv-preview-kicker{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:#718096;font-weight:700}.fv-preview-title{font-size:1.22rem;font-weight:700;margin:7px 0 9px;color:#172b4d}.fv-preview-message{white-space:pre-wrap;color:#506176;line-height:1.55}.fv-preview-media img,.fv-preview-media video{width:100%;max-height:270px;object-fit:contain;border-radius:9px;background:#f3f5f7}.fv-preview-media iframe{border:0;border-radius:9px;max-width:100%}.fv-actions-bar{position:sticky;bottom:0;z-index:5;background:rgba(255,255,255,.96);backdrop-filter:blur(8px);border:1px solid var(--fv-line);border-radius:12px;padding:12px 15px;box-shadow:0 -5px 18px rgba(23,43,77,.06)}.fv-char-count{font-size:.75rem;color:#8794a5;text-align:right;margin-top:-10px;margin-bottom:12px}
.fv-audience-picker{border:1px solid #dce3eb;border-radius:10px;background:#fff;padding:11px;margin-bottom:15px}.fv-audience-picker:focus-within{border-color:#3b9a9d;box-shadow:0 0 0 .18rem rgba(35,138,145,.1)}.fv-audience-tools{display:flex;gap:8px;align-items:center}.fv-audience-tools .form-control{min-width:0}.fv-audience-options{max-height:210px;overflow:auto;border:1px solid #edf0f4;border-radius:8px;margin-top:9px;padding:4px}.fv-audience-option{display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:6px;cursor:pointer;color:#42566d;font-size:.83rem;margin:0}.fv-audience-option:hover{background:#f5f9fa}.fv-audience-option input{accent-color:#187b81;width:15px;height:15px;flex:0 0 auto}.fv-audience-option[hidden]{display:none}.fv-audience-summary{display:flex;justify-content:space-between;align-items:center;font-size:.76rem;color:#718096;margin-top:8px}.fv-audience-summary button{border:0;background:none;color:#167279;font-size:.76rem;font-weight:650;padding:2px 4px}.fv-audience-chips{display:flex;gap:5px;flex-wrap:wrap;margin-top:9px}.fv-audience-chip{display:inline-flex;align-items:center;gap:5px;max-width:100%;padding:4px 8px;border-radius:20px;background:#eaf4f5;color:#21666b;font-size:.73rem}.fv-audience-chip span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.fv-audience-chip button{border:0;background:none;color:inherit;font-size:1rem;line-height:1;padding:0}.fv-audience-target{padding:11px 13px;border-radius:9px;background:#f3f8f8;color:#35656b;font-size:.81rem}.fv-audience-target strong{color:#1d5158}
.fv-audience-launcher{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px;border:1px solid #dce7eb;border-radius:11px;background:linear-gradient(120deg,#f8fbfc,#f3f8f8)}.fv-audience-launcher-title{font-weight:700;color:#28455d}.fv-audience-launcher-summary{font-size:.82rem;color:#64778b;margin-top:3px}.fv-audience-launcher-count{font-size:.75rem;color:#14747a;font-weight:650;margin-top:4px}.fv-audience-dialog{width:min(1040px,calc(100vw - 24px));max-width:none;max-height:88vh;padding:0;border:0;border-radius:16px;box-shadow:0 24px 80px rgba(17,36,57,.3);color:#263c54;overflow:hidden}.fv-audience-dialog::backdrop{background:rgba(16,31,50,.55);backdrop-filter:blur(3px)}.fv-audience-dialog-head,.fv-audience-dialog-foot{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:15px 20px;background:#fff}.fv-audience-dialog-head{border-bottom:1px solid #e8edf3}.fv-audience-dialog-foot{border-top:1px solid #e8edf3}.fv-audience-dialog-head h5{margin:0;font-weight:700;color:#213650}.fv-audience-dialog-body{padding:18px 20px;overflow:auto;max-height:calc(88vh - 132px)}.fv-audience-modal-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.fv-audience-modal-grid .form-label{margin-bottom:6px}.fv-audience-modal-grid .fv-audience-picker{margin-bottom:0}.fv-audience-modal-grid .fv-audience-tools{display:block}.fv-audience-modal-grid .fv-audience-tools .btn{width:100%;margin-top:6px}.fv-audience-modal-grid .fv-audience-options{max-height:220px}.fv-audience-modal-grid .fv-audience-chips{max-height:82px;overflow:auto}.fv-audience-error{font-size:.78rem;color:#dc3545;margin-top:5px}.fv-audience-target{padding:11px 13px;border-radius:9px;background:#f3f8f8;color:#35656b;font-size:.81rem}.fv-audience-target strong{color:#1d5158}
@media(max-width:991px){.fv-preview{position:static}}
@media(max-width:767px){.fv-audience-modal-grid{grid-template-columns:1fr}.fv-audience-dialog{max-height:94vh}.fv-audience-dialog-body{max-height:calc(94vh - 132px);padding:14px}.fv-audience-launcher{align-items:flex-start}.fv-audience-launcher .btn{flex:0 0 auto}}
.fv-form .invalid-feedback{display:block;font-size:.78rem;margin-top:-9px;margin-bottom:12px}.fv-form .is-invalid{border-color:#dc3545}.fv-form .is-invalid:focus{border-color:#dc3545;box-shadow:0 0 0 .2rem rgba(220,53,69,.12)}.fv-example-box{border:1px solid #cfe8e5;border-radius:10px;background:#f3fbfa;color:#35656b;padding:10px 12px;font-size:.79rem;line-height:1.55;margin-bottom:15px}.fv-example-box strong{color:#1d5158}.fv-example-box code{color:#155e63;background:#e1f3f0;padding:1px 4px;border-radius:4px}
</style>
@endsection
@section('main-content')
<div class="fv-form">
@include('admin.section.flash_message')
@php($arrayError = fn ($field) => $errors->first($field) ?: collect($errors->get($field.'.*'))->flatten()->first())
@if($errors->any())<div class="alert alert-warning d-flex align-items-start gap-2" role="alert"><i data-feather="alert-triangle" class="mt-1" style="width:17px;height:17px"></i><div><strong>Some fields need correction.</strong><div class="small">Review the red messages below, then submit again. Your text and selections have been kept.</div></div></div>@endif
<div class="card fv-form-hero mb-3"><div class="card-body"><h3>{{ $item->exists ? 'Edit announcement' : 'Create an announcement' }}</h3><p>Build a clear in-app message, choose who sees it, and decide when it appears.</p></div></div>
<form method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.community.flash-update', $item) : route('admin.community.flash-store') }}" id="flash-form">
<div class="row"><div class="col-lg-7">
<div class="card fv-section"><div class="card-body"><div class="fv-section-title"><span class="fv-step">1</span><div><h5>Message</h5><p>Write a short, useful announcement for employees.</p></div></div>
@csrf
<div class="fv-example-box"><strong>Correct sample:</strong> Title <code>Office closure this Friday</code> and message <code>The Phnom Penh office closes at 3:00 PM on Friday. Contact HR for urgent support.</code>.</div>
<label class="form-label" for="title">Title <span class="text-danger">*</span></label><input class="form-control mb-3 @error('title') is-invalid @enderror" id="title" name="title" maxlength="180" required value="{{ old('title', $item->title) }}" placeholder="e.g. Office closure this Friday" aria-describedby="title-help title-error"><div id="title-help" class="fv-hint">Use a short title employees can understand at a glance.</div>@error('title')<div id="title-error" class="invalid-feedback">{{ $message }}</div>@enderror
<label class="form-label" for="body">Message</label><textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="4" maxlength="10000" placeholder="e.g. The Phnom Penh office closes at 3:00 PM on Friday…" aria-describedby="body-error">{{ old('body', $item->body) }}</textarea>@error('body')<div id="body-error" class="invalid-feedback">{{ $message }}</div>@enderror<div class="fv-char-count"><span id="body-count">{{ mb_strlen(old('body', $item->body ?? '')) }}</span> / 10,000</div>
</div></div>
<div class="card fv-section"><div class="card-body"><div class="fv-section-title"><span class="fv-step">2</span><div><h5>Media and action</h5><p>Add an image, video, document, or a button link.</p></div></div>
<label class="form-label" for="media_type">Content type</label><select class="form-select mb-3 @error('media_type') is-invalid @enderror" id="media_type" name="media_type" aria-describedby="media-type-error">@foreach($types as $type)<option value="{{ $type }}" @selected(old('media_type', $item->media_type ?? 'text') === $type)>{{ ucfirst($type) }}</option>@endforeach</select>@error('media_type')<div id="media-type-error" class="invalid-feedback">{{ $message }}</div>@enderror
<div id="media-url-wrap"><label class="form-label" for="media_url">Media URL</label><textarea class="form-control mb-2 @error('media_url') is-invalid @enderror" id="media_url" name="media_url" rows="2" placeholder="https://example.com/banner.jpg" aria-describedby="media-url-help media-url-error">{{ old('media_url', $item->media_url) }}</textarea><div id="media-url-help" class="fv-hint">Paste one URL only, matching the selected content type. Image: <code>https://example.com/banner.jpg</code>. YouTube: <code>https://www.youtube.com/watch?v=ScMzIvxBSi4</code>. Facebook: a full public video URL/iframe. Use HTTPS. Multiple URLs are not supported.</div>@error('media_url')<div id="media-url-error" class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div id="media-file-wrap"><label class="form-label" for="media">Upload media</label><input class="form-control mb-2 @error('media') is-invalid @enderror" type="file" name="media" id="media" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" aria-describedby="media-help media-error">@error('media')<div id="media-error" class="invalid-feedback">{{ $message }}</div>@enderror<div id="media-help" class="fv-hint">If validation fails, choose the file again because browsers do not preserve file inputs. Images up to 10 MB, videos up to 100 MB, and PDF, Word, or Excel files up to 25 MB.</div></div>
<div class="row"><div class="col-md-8"><label class="form-label" for="link_url">Button URL <span class="text-muted fw-normal">(optional, HTTPS)</span></label><input class="form-control mb-3 @error('link_url') is-invalid @enderror" id="link_url" type="url" name="link_url" value="{{ old('link_url', $item->link_url) }}" placeholder="https://hr.example.com/policy" aria-describedby="link-url-error">@error('link_url')<div id="link-url-error" class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="link_label">Button label</label><input class="form-control mb-2 @error('link_label') is-invalid @enderror" id="link_label" name="link_label" maxlength="80" value="{{ old('link_label', $item->link_label) }}" placeholder="Learn more" aria-describedby="link-label-error">@error('link_label')<div id="link-label-error" class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
</div></div>
<div class="card fv-section"><div class="card-body"><div class="fv-section-title"><span class="fv-step">3</span><div><h5>Audience</h5><p>Choose who receives the popup. Leave a filter empty to include everyone in that category.</p></div></div>
@php($selectedRoleIds = array_map('intval', (array) old('role_ids', $item->role_ids ?? [])))
@php($selectedBranchIds = array_map('intval', (array) old('branch_ids', $item->branch_ids ?? [])))
@php($selectedDepartmentIds = array_map('intval', (array) old('department_ids', $item->department_ids ?? [])))
@php($branchNameById = collect($branches ?? [])->mapWithKeys(fn ($branch) => [$branch->id => $branch->name]))
<div class="fv-audience-launcher mb-3"><div><div class="fv-audience-launcher-title">Who should see this announcement?</div><div class="fv-audience-launcher-summary" id="audience-launcher-summary">All eligible employees</div><div class="fv-audience-launcher-count" id="audience-launcher-count">No filters selected</div></div><button class="btn btn-outline-primary" type="button" id="open-audience-dialog" aria-haspopup="dialog" aria-controls="audience-dialog"><i data-feather="users" class="me-1"></i>Select audience</button></div>
<div class="fv-hint mb-0">Employees need both Flash View permissions. Within a filter, selections are alternatives; different selected filters are combined.</div>
<dialog class="fv-audience-dialog" id="audience-dialog" aria-labelledby="audience-dialog-title"><div class="fv-audience-dialog-head"><div><h5 id="audience-dialog-title">Select announcement audience</h5><div class="small text-muted">Choose one or more options. Leave a group empty to include everyone in that group.</div></div><button class="btn btn-sm btn-light" type="button" data-close-audience aria-label="Close audience selector">&times;</button></div><div class="fv-audience-dialog-body"><div class="fv-audience-modal-grid">
<div><label class="form-label" for="role-search">Roles</label><div class="fv-audience-picker {{ $errors->has('role_ids') || $errors->has('role_ids.*') ? 'border-danger' : '' }}" data-audience-picker data-group="Roles"><div class="fv-audience-tools"><input class="form-control" id="role-search" data-audience-search type="search" placeholder="Search roles…" autocomplete="off"><button class="btn btn-sm btn-outline-secondary" type="button" data-select-visible>Select visible</button></div><div class="fv-audience-summary"><span data-audience-count>0 selected · empty means all roles</span><button type="button" data-clear-audience>Clear all</button></div><div class="fv-audience-options">@foreach($roles as $role)<label class="fv-audience-option" data-audience-option data-label="{{ $role->name }}"><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" data-audience-value @checked(in_array((int) $role->id, $selectedRoleIds, true))><span>{{ $role->name }}</span></label>@endforeach</div><div class="fv-audience-chips" data-audience-chips></div></div>@if($arrayError('role_ids'))<div class="fv-audience-error">{{ $arrayError('role_ids') }}</div>@endif</div>
<div><label class="form-label" for="branch-search">Branches</label><div class="fv-audience-picker {{ $errors->has('branch_ids') || $errors->has('branch_ids.*') ? 'border-danger' : '' }}" data-audience-picker data-group="Branches"><div class="fv-audience-tools"><input class="form-control" id="branch-search" data-audience-search type="search" placeholder="Search branches…" autocomplete="off"><button class="btn btn-sm btn-outline-secondary" type="button" data-select-visible>Select visible</button></div><div class="fv-audience-summary"><span data-audience-count>0 selected · empty means all branches</span><button type="button" data-clear-audience>Clear all</button></div><div class="fv-audience-options">@foreach($branches ?? [] as $branch)<label class="fv-audience-option" data-audience-option data-label="{{ $branch->name }}"><input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" data-audience-value @checked(in_array((int) $branch->id, $selectedBranchIds, true))><span>{{ $branch->name }}</span></label>@endforeach</div><div class="fv-audience-chips" data-audience-chips></div></div>@if($arrayError('branch_ids'))<div class="fv-audience-error">{{ $arrayError('branch_ids') }}</div>@endif</div>
<div><label class="form-label" for="department-search">Departments</label><div class="fv-audience-picker {{ $errors->has('department_ids') || $errors->has('department_ids.*') ? 'border-danger' : '' }}" data-audience-picker data-group="Departments"><div class="fv-audience-tools"><input class="form-control" id="department-search" data-audience-search type="search" placeholder="Search departments or branches…" autocomplete="off"><button class="btn btn-sm btn-outline-secondary" type="button" data-select-visible>Select visible</button></div><div class="fv-audience-summary"><span data-audience-count>0 selected · empty means all departments</span><button type="button" data-clear-audience>Clear all</button></div><div class="fv-audience-options">@foreach($departments ?? [] as $department)@php($departmentLabel = $department->dept_name.($branchNameById->has($department->branch_id) ? ' · '.$branchNameById[$department->branch_id] : ''))<label class="fv-audience-option" data-audience-option data-label="{{ $departmentLabel }}"><input type="checkbox" name="department_ids[]" value="{{ $department->id }}" data-audience-value @checked(in_array((int) $department->id, $selectedDepartmentIds, true))><span>{{ $departmentLabel }}</span></label>@endforeach</div><div class="fv-audience-chips" data-audience-chips></div></div>@if($arrayError('department_ids'))<div class="fv-audience-error">{{ $arrayError('department_ids') }}</div>@endif</div>
</div><div class="fv-audience-target mt-3" aria-live="polite"><strong>Targeting summary:</strong> <span id="audience-preview">All roles, branches, and departments (employees still need Flash View access).</span></div><div class="small text-muted mt-2">If multiple filter groups are selected, an employee must match at least one selected option in every selected group.</div></div><div class="fv-audience-dialog-foot"><span class="small text-muted">Changes are included when you save the announcement.</span><button class="btn btn-primary" type="button" data-close-audience>Done</button></div></dialog>
</div></div>
<div class="card fv-section"><div class="card-body"><div class="fv-section-title"><span class="fv-step">4</span><div><h5>Timing and delivery</h5><p>Set the active period and how often employees see it.</p></div></div>
<div class="row"><div class="col-md-6"><label class="form-label" for="starts_at">Start date and time <span class="text-muted fw-normal">({{ config('app.timezone') }})</span></label><input class="form-control mb-3 @error('starts_at') is-invalid @enderror" type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}" aria-describedby="starts-error"><div class="fv-hint">Leave blank to start immediately. Example: <code>2026-11-15 08:00</code>.</div>@error('starts_at')<div id="starts-error" class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="ends_at">End date and time</label><input class="form-control mb-3 @error('ends_at') is-invalid @enderror" type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $item->ends_at?->format('Y-m-d\TH:i')) }}" aria-describedby="ends-error"><div class="fv-hint">Leave blank for no end date. End must be after the start.</div>@error('ends_at')<div id="ends-error" class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
<div class="row"><div class="col-md-7"><label class="form-label" for="display_policy">Display frequency</label><select class="form-select mb-3 @error('display_policy') is-invalid @enderror" id="display_policy" name="display_policy" aria-describedby="display-policy-error"><option value="once" @selected(old('display_policy', $item->display_policy) !== 'session')>Once per employee</option><option value="session" @selected(old('display_policy', $item->display_policy) === 'session')>Every app session</option></select>@error('display_policy')<div id="display-policy-error" class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-5"><label class="form-label" for="priority">Priority</label><input class="form-control mb-3 @error('priority') is-invalid @enderror" id="priority" type="number" name="priority" min="-1000" max="1000" value="{{ old('priority', $item->priority ?? 0) }}" required aria-describedby="priority-help priority-error"><div id="priority-help" class="fv-hint">Higher priority appears first. Use a number from -1000 to 1000.</div>@error('priority')<div id="priority-error" class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
<input type="hidden" name="is_active" value="0"><label class="d-flex align-items-start gap-2 mb-2"><input class="mt-1" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))><span><strong>Publish and enable this announcement</strong><span class="d-block text-muted small">If unchecked, it will be saved as a draft.</span></span></label>
<label class="d-flex align-items-start gap-2 mt-3 mb-0"><input class="mt-1" type="checkbox" name="alert_now" value="1" @checked(old('alert_now'))><span><strong>Send a push alert immediately after saving</strong><span class="d-block text-muted small">Sends now to matching employees with Flash View access and a registered push token. The announcement must be enabled and live.</span></span></label>
@if($item->exists)<label class="d-flex align-items-start gap-2 mt-3 mb-0"><input class="mt-1" type="checkbox" name="show_again" value="1" @checked(old('show_again'))><span><strong>Show again to employees who dismissed it</strong><span class="d-block text-muted small">Creates a new revision and resets dismissal for this announcement.</span></span></label>@endif
</div></div>
<div class="fv-actions-bar d-flex justify-content-between align-items-center gap-2"><a href="{{ route('admin.community.flashes') }}" class="btn btn-light">Cancel</a><div class="d-flex gap-2"><button class="btn btn-outline-primary" type="button" id="preview-button"><i data-feather="eye" class="me-1"></i>Update preview</button><button class="btn btn-primary" type="submit"><i data-feather="save" class="me-1"></i>{{ $item->exists ? 'Save changes' : 'Save announcement' }}</button></div></div>
</div><div class="col-lg-5"><div class="card fv-preview"><div class="fv-preview-head"><i data-feather="smartphone" class="me-1" style="width:16px;height:16px"></i> Employee app preview</div><div class="fv-preview-body"><div class="fv-preview-kicker">Flash View</div><h4 class="fv-preview-title" id="preview-title">{{ old('title', $item->title) ?: 'Announcement title' }}</h4><p class="fv-preview-message" id="preview-body">{{ old('body', $item->body) }}</p><div id="preview-media" class="fv-preview-media"></div><a id="preview-link" href="#" class="btn btn-sm btn-outline-primary mt-3 d-none" target="_blank" rel="noopener">Open link</a><p class="small text-muted mt-3 mb-0">Preview updates as you edit. Actual display also depends on the employee’s permissions and your audience settings.</p></div></div></div></div>
</form></div>
<script>
(() => {
  let objectUrl;
  const field = id => document.getElementById(id);
  const preview = () => {
    field('preview-title').textContent = field('title').value || 'Announcement title';
    field('preview-body').textContent = field('body').value;
    const holder = field('preview-media'); holder.replaceChildren();
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    const type = field('media_type').value;
    const file = field('media').files[0];
    let value = field('media_url').value.trim();
    const iframe = value.match(/<iframe\b[^>]*\bsrc=["']([^"']+)["']/i);
    if (iframe) value = iframe[1].replaceAll('&amp;', '&');
    if (file && ['image', 'video'].includes(type)) value = objectUrl = URL.createObjectURL(file);
    field('preview-link').classList.toggle('d-none', !field('link_url').value.trim());
    if (field('link_url').value.trim()) { field('preview-link').href = field('link_url').value; field('preview-link').textContent = field('link_label').value || 'Open link'; }
    if (type === 'text' || (!value && !file)) return;
    if (file && type === 'document') {
      const documentLink = document.createElement('span');
      documentLink.className = 'btn btn-sm btn-outline-secondary';
      documentLink.textContent = 'Selected file: ' + file.name;
      holder.append(documentLink);
      return;
    }
    try {
      const url = new URL(value);
      if (!file && url.protocol !== 'https:') throw Error();
      let element;
      if (type === 'image') { element = document.createElement('img'); element.src = value; }
      else if (type === 'video') { element = document.createElement('video'); element.src = value; element.controls = true; }
      else if (type === 'youtube') {
        let id;
        if (url.hostname === 'youtu.be') id = url.pathname.slice(1);
        else if (['youtube.com','www.youtube.com','m.youtube.com','www.youtube-nocookie.com'].includes(url.hostname)) id = url.searchParams.get('v') || url.pathname.match(/^\/(?:embed|shorts|live)\/([\w-]+)/)?.[1];
        if (!/^[\w-]{11}$/.test(id || '')) throw Error();
        element = document.createElement('iframe'); element.src = 'https://www.youtube.com/embed/' + id;
      } else if (type === 'facebook') {
        if (!['facebook.com','www.facebook.com','m.facebook.com','web.facebook.com'].includes(url.hostname)) throw Error();
        element = document.createElement('iframe'); element.src = url.pathname === '/plugins/video.php' ? url.href : 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url.href);
      } else { element = document.createElement('a'); element.href = url.href; element.textContent = type === 'document' ? 'Open document' : 'Open link'; element.target = '_blank'; element.rel = 'noopener'; element.className = 'btn btn-sm btn-outline-secondary'; }
      element.style.maxWidth = '100%';
      if (element.tagName === 'IFRAME') { element.style.width = '100%'; element.style.height = '280px'; element.allowFullscreen = true; }
      holder.append(element);
    } catch (_) { holder.textContent = 'Enter a supported HTTPS media URL to preview.'; }
  };
  field('preview-button').addEventListener('click', preview);
  ['title','body','media_type','media_url','media','link_url','link_label'].forEach(id => field(id).addEventListener('input', preview));
  field('body').addEventListener('input', () => field('body-count').textContent = field('body').value.length);
  const syncMediaFields = () => {
    const type = field('media_type').value;
    field('media-url-wrap').classList.toggle('d-none', type === 'text');
    field('media-file-wrap').classList.toggle('d-none', !['image','video','document'].includes(type));
    preview();
  };
  field('media_type').addEventListener('change', syncMediaFields);
  const end = field('ends_at'), start = field('starts_at');
  start.addEventListener('change', () => { end.min = start.value; });
  if (start.value) end.min = start.value;

  const audiencePickers = [...document.querySelectorAll('[data-audience-picker]')];
  const renderAudience = picker => {
    const checkboxes = [...picker.querySelectorAll('[data-audience-value]')];
    const chosen = checkboxes.filter(input => input.checked);
    const count = picker.querySelector('[data-audience-count]');
    const emptyHint = picker.dataset.group === 'Roles' ? 'empty means all roles' : `empty means all ${picker.dataset.group.toLowerCase()}`;
    count.textContent = chosen.length ? `${chosen.length} selected` : `0 selected · ${emptyHint}`;

    const chips = picker.querySelector('[data-audience-chips]');
    chips.replaceChildren();
    chosen.forEach(input => {
      const label = input.closest('[data-audience-option]').dataset.label;
      const chip = document.createElement('span'); chip.className = 'fv-audience-chip';
      const text = document.createElement('span'); text.textContent = label;
      const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = '×'; remove.setAttribute('aria-label', `Remove ${label}`);
      remove.addEventListener('click', () => { input.checked = false; renderAudience(picker); renderAudiencePreview(); });
      chip.append(text, remove); chips.append(chip);
    });
    renderAudiencePreview();
  };
  const renderAudiencePreview = () => {
    const groupCounts = audiencePickers.map(picker => {
      const names = [...picker.querySelectorAll('[data-audience-value]:checked')]
        .map(input => input.closest('[data-audience-option]').dataset.label);
      return {name: picker.dataset.group, names};
    }).filter(group => group.names.length);
    const selectedGroups = groupCounts.map(group => `${group.name}: ${group.names.slice(0, 3).join(', ')}${group.names.length > 3 ? ` +${group.names.length - 3} more` : ''}`);
    field('audience-launcher-summary').textContent = groupCounts.length
      ? groupCounts.map(group => `${group.names.length} ${group.name.toLowerCase().replace(/s$/, '')}${group.names.length === 1 ? '' : 's'}`).join(' · ')
      : 'All eligible employees';
    field('audience-launcher-count').textContent = groupCounts.length
      ? `${groupCounts.length} filter group${groupCounts.length === 1 ? '' : 's'} selected`
      : 'No filters selected';
    field('audience-preview').textContent = selectedGroups.length
      ? `${selectedGroups.join(' · ')}. Employees must match every selected filter and have Flash View access.`
      : 'All roles, branches, and departments (employees still need Flash View access).';
  };

  audiencePickers.forEach(picker => {
    const search = picker.querySelector('[data-audience-search]');
    const options = [...picker.querySelectorAll('[data-audience-option]')];
    const applySearch = () => {
      const term = search.value.trim().toLocaleLowerCase();
      options.forEach(option => { option.hidden = !option.dataset.label.toLocaleLowerCase().includes(term); });
    };
    search.addEventListener('input', applySearch);
    picker.querySelectorAll('[data-audience-value]').forEach(input => input.addEventListener('change', () => renderAudience(picker)));
    picker.querySelector('[data-select-visible]').addEventListener('click', () => {
      options.filter(option => !option.hidden).forEach(option => { option.querySelector('[data-audience-value]').checked = true; });
      renderAudience(picker);
    });
    picker.querySelector('[data-clear-audience]').addEventListener('click', () => {
      picker.querySelectorAll('[data-audience-value]').forEach(input => { input.checked = false; });
      renderAudience(picker);
    });
    renderAudience(picker);
  });
  const audienceDialog = field('audience-dialog');
  field('open-audience-dialog').addEventListener('click', () => audienceDialog.showModal());
  audienceDialog.querySelectorAll('[data-close-audience]').forEach(button => button.addEventListener('click', () => audienceDialog.close()));
  audienceDialog.addEventListener('click', event => { if (event.target === audienceDialog) audienceDialog.close(); });
  const audienceHasErrors = @json($errors->has('role_ids') || $errors->has('role_ids.*') || $errors->has('branch_ids') || $errors->has('branch_ids.*') || $errors->has('department_ids') || $errors->has('department_ids.*'));
  if (audienceHasErrors) audienceDialog.showModal();
  syncMediaFields();
})();
</script>
@endsection
