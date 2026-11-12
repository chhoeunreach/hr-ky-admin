@extends('layouts.master')
@section('title', 'Create Social Announcement')
@section('action', 'New announcement')
@section('styles')
<style>
.sf-create{max-width:1080px;color:#172b4d}.sf-create-hero{border:0;border-radius:14px;background:linear-gradient(120deg,#283b70,#5a4c8c);color:white}.sf-create-hero h2{color:white;font-size:1.4rem;font-weight:700;margin:0 0 5px}.sf-create-hero p{color:rgba(255,255,255,.78);margin:0}.sf-create-card{border:1px solid #e8edf3;border-radius:13px;box-shadow:0 4px 16px rgba(23,43,77,.04)}.sf-step-title{font-size:.98rem;color:#283f5c;font-weight:700;margin-bottom:13px}.sf-audience-choice{display:flex;gap:10px;align-items:flex-start;border:1px solid #e8edf3;border-radius:9px;padding:11px 12px;margin-bottom:8px;cursor:pointer}.sf-audience-choice:has(input:checked){border-color:#786cb0;background:#f8f7fc}.sf-audience-choice strong{font-size:.84rem}.sf-audience-choice small{display:block;color:#718096;margin-top:2px}.sf-target-box{border:1px solid #e8edf3;border-radius:10px;padding:13px;background:#fbfcfd}.sf-target-options{max-height:190px;overflow:auto;border:1px solid #edf0f4;border-radius:8px;padding:4px;background:white}.sf-target-option{display:flex;gap:9px;align-items:center;padding:7px 8px;border-radius:5px;font-size:.82rem;color:#42566d}.sf-target-option:hover{background:#f4f7fa}.sf-target-option[hidden]{display:none}.sf-help{font-size:.79rem;color:#718096}.sf-create-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);padding:12px;border:1px solid #e8edf3;border-radius:11px;box-shadow:0 -5px 16px rgba(23,43,77,.06)}
</style>
@endsection
@section('main-content')
@php($selectedDepartmentIds = array_map('intval', (array) old('audience_department_ids', [])))
@php($selectedEmployeeIds = array_map('intval', (array) old('audience_user_ids', [])))
<div class="sf-create">
@include('admin.section.flash_message')
<div class="card sf-create-hero mb-3"><div class="card-body"><h2>Create Social Feed announcement</h2><p>Publish an official company update, choose its audience, and optionally pin it to the top of the feed.</p></div></div>
<form method="post" enctype="multipart/form-data" action="{{ route('admin.community.post-store') }}">@csrf
<div class="row"><div class="col-lg-7">
<div class="card sf-create-card mb-3"><div class="card-body p-4"><div class="sf-step-title">Announcement content</div>
<label class="form-label fw-semibold" for="body">Message</label><textarea class="form-control mb-2 @error('body') is-invalid @enderror" id="body" name="body" rows="7" maxlength="10000" placeholder="Write the company announcement…">{{ old('body') }}</textarea>@error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
<label class="form-label fw-semibold mt-3" for="media_type">Attachment type</label><select class="form-select mb-3" id="media_type" name="media_type">@foreach($types as $type)<option value="{{ $type }}" @selected(old('media_type', 'text') === $type)>{{ ucfirst($type) }}</option>@endforeach</select>
<div id="post-media-url"><label class="form-label" for="media_url">Secure media URL</label><textarea class="form-control mb-2 @error('media_url') is-invalid @enderror" name="media_url" id="media_url" rows="2" placeholder="https://…">{{ old('media_url') }}</textarea>@error('media_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
<div id="post-media-file"><label class="form-label" for="media">Upload attachment</label><input class="form-control mb-2 @error('media') is-invalid @enderror" type="file" name="media" id="media" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">@error('media')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<div class="sf-help">Images up to 10 MB, videos up to 100 MB, and PDF, Word, or Excel files up to 25 MB.</div></div>
<div class="sf-help mt-2">YouTube and Facebook video embeds accept a secure URL. Text-only posts need a message.</div></div></div>
<div class="card sf-create-card mb-3"><div class="card-body p-4"><div class="sf-step-title">Audience</div>
<label class="sf-audience-choice"><input type="radio" name="visibility" value="company" @checked(old('visibility', 'company') === 'company')><span><strong>Everyone in the company</strong><small>Available to employees in this company who can view the Social Feed.</small></span></label>
<label class="sf-audience-choice"><input type="radio" name="visibility" value="management" @checked(old('visibility') === 'management')><span><strong>Management</strong><small>Limit this announcement to management roles.</small></span></label>
<label class="sf-audience-choice"><input type="radio" name="visibility" value="hr" @checked(old('visibility') === 'hr')><span><strong>HR team</strong><small>Limit this announcement to HR roles.</small></span></label>
<label class="sf-audience-choice"><input type="radio" name="visibility" value="department" @checked(old('visibility') === 'department')><span><strong>Selected departments</strong><small>Select departments within this company.</small></span></label>
<div id="create-department-audience" class="sf-target-box mb-3"><input class="form-control mb-2" data-target-search="department" type="search" placeholder="Search departments…"><div class="sf-target-options">@foreach($departments as $department)<label class="sf-target-option" data-target-option="department" data-label="{{ $department->dept_name }}"><input type="checkbox" name="audience_department_ids[]" data-target-check="department" value="{{ $department->id }}" @checked(in_array((int) $department->id, $selectedDepartmentIds, true))> {{ $department->dept_name }}</label>@endforeach</div><div class="sf-target-count" id="create-department-count"></div>@error('audience_department_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div>
<label class="sf-audience-choice"><input type="radio" name="visibility" value="employees" @checked(old('visibility') === 'employees')><span><strong>Selected employees</strong><small>Select active employees in this company.</small></span></label>
<div id="create-employee-audience" class="sf-target-box"><input class="form-control mb-2" data-target-search="employee" type="search" placeholder="Search employees…"><div class="sf-target-options">@foreach($employees as $employee)<label class="sf-target-option" data-target-option="employee" data-label="{{ $employee->name }}"><input type="checkbox" name="audience_user_ids[]" data-target-check="employee" value="{{ $employee->id }}" @checked(in_array((int) $employee->id, $selectedEmployeeIds, true))> {{ $employee->name }}</label>@endforeach</div><div class="sf-target-count" id="create-employee-count"></div>@error('audience_user_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div>
@error('visibility')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div></div>
<div class="card sf-create-card mb-3"><div class="card-body p-4"><div class="sf-step-title">Publishing</div><label class="d-flex gap-2 align-items-start"><input class="mt-1" type="checkbox" name="pin_now" value="1" @checked(old('pin_now'))><span><strong>Pin announcement to the top of the feed</strong><span class="d-block small text-muted">You can unpin it later from Social Feed management.</span></span></label></div></div>
<div class="sf-create-actions d-flex justify-content-between"><a class="btn btn-light" href="{{ route('admin.community.feed') }}">Cancel</a><button class="btn btn-primary" type="submit"><i data-feather="send" class="me-1"></i>Publish announcement</button></div>
</div><div class="col-lg-5"><div class="card sf-create-card"><div class="card-body p-4"><div class="sf-step-title"><i data-feather="eye" style="width:16px;height:16px" class="me-1"></i>Publishing summary</div><p class="small text-muted">This will be posted as an announcement in company {{ auth()->user()->company_id }}.</p><p class="small text-muted">Employees see it according to the selected audience and their Social Feed permissions. Pinned announcements appear before other feed posts.</p><div class="alert alert-light mb-0 small">The employee app refreshes the feed when users open or refresh Social Feed.</div></div></div></div></div>
</form></div>
<script>
(() => {
  const visibility = [...document.querySelectorAll('[name="visibility"]')];
  const value = () => visibility.find(input => input.checked)?.value || 'company';
  const syncAudience = () => {
    document.getElementById('create-department-audience').hidden = value() !== 'department';
    document.getElementById('create-employee-audience').hidden = value() !== 'employees';
  };
  visibility.forEach(input => input.addEventListener('change', syncAudience)); syncAudience();
  const type = document.getElementById('media_type');
  const syncMedia = () => {
    document.getElementById('post-media-url').hidden = type.value === 'text';
    document.getElementById('post-media-file').hidden = !['image','video','document'].includes(type.value);
  };
  type.addEventListener('change', syncMedia); syncMedia();
  ['department','employee'].forEach(group => {
    const search = document.querySelector(`[data-target-search="${group}"]`);
    const options = [...document.querySelectorAll(`[data-target-option="${group}"]`)];
    const count = document.getElementById(`create-${group}-count`);
    const update = () => { count.textContent = `${document.querySelectorAll(`[data-target-check="${group}"]:checked`).length} selected`; };
    search.addEventListener('input', () => { const term = search.value.trim().toLocaleLowerCase(); options.forEach(option => { option.hidden = !option.dataset.label.toLocaleLowerCase().includes(term); }); });
    document.querySelectorAll(`[data-target-check="${group}"]`).forEach(input => input.addEventListener('change', update)); update();
  });
})();
</script>
@endsection
