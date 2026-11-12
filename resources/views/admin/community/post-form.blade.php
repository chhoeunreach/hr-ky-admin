@extends('layouts.master')
@section('title', 'Edit Social Post')
@section('action', 'Moderator edit')
@section('styles')
<style>
.sf-edit{max-width:1040px;color:#172b4d}.sf-edit-hero{border:0;border-radius:14px;background:linear-gradient(120deg,#283b70,#5a4c8c);color:#fff}.sf-edit-hero h2{color:#fff;font-size:1.4rem;font-weight:700;margin:0 0 5px}.sf-edit-hero p{color:rgba(255,255,255,.78);margin:0}.sf-edit-card{border:1px solid #e8edf3;border-radius:13px;box-shadow:0 4px 16px rgba(23,43,77,.04)}.sf-context{background:#f6f8fb;border-radius:9px;padding:12px 14px;color:#617287;font-size:.84rem}.sf-editor-label{font-size:.87rem;color:#334860;font-weight:700}.sf-audience-choice{display:flex;gap:10px;align-items:flex-start;border:1px solid #e8edf3;border-radius:9px;padding:11px 12px;margin-bottom:8px;cursor:pointer}.sf-audience-choice:has(input:checked){border-color:#786cb0;background:#f8f7fc}.sf-audience-choice strong{font-size:.84rem}.sf-audience-choice small{display:block;color:#718096;margin-top:2px}.sf-target-box{border:1px solid #e8edf3;border-radius:10px;padding:13px;background:#fbfcfd}.sf-target-search{margin-bottom:8px}.sf-target-options{max-height:230px;overflow:auto;border:1px solid #edf0f4;border-radius:8px;padding:4px;background:white}.sf-target-option{display:flex;gap:9px;align-items:center;padding:7px 8px;border-radius:5px;font-size:.82rem;color:#42566d}.sf-target-option:hover{background:#f4f7fa}.sf-target-option[hidden]{display:none}.sf-target-count{font-size:.76rem;color:#718096;margin-top:7px}.sf-attachment{display:inline-flex;padding:7px 10px;margin:3px;border-radius:7px;background:#f2f5f8;font-size:.82rem}
</style>
@endsection
@section('main-content')
@php($visibility = old('visibility', $post->visibility ?: 'company'))
@php($selectedDepartmentIds = array_map('intval', (array) old('audience_department_ids', $post->audience_department_ids ?? [])))
@php($selectedEmployeeIds = array_map('intval', (array) old('audience_user_ids', $post->audience_user_ids ?? [])))
<div class="sf-edit">
@include('admin.section.flash_message')
<div class="card sf-edit-hero mb-3"><div class="card-body"><h2>Edit social post</h2><p>Update post text and visibility. Changes are recorded in the application log.</p></div></div>
<div class="card sf-edit-card"><div class="card-body p-4">
<div class="sf-context mb-3"><strong>{{ $post->author?->name ?? 'Former employee' }}</strong> · {{ $post->created_at?->format('M j, Y H:i') }} · Company {{ $post->company_id ?? '—' }} · {{ $post->comments_count ?? $post->comments()->count() }} comments · {{ $post->reactions_count ?? $post->reactions()->count() }} reactions</div>
@if($post->media_url)<a class="sf-attachment" href="{{ $post->media_url }}" target="_blank" rel="noopener">Open attached {{ $post->media_type }}</a>@endif
@foreach($post->media as $media)<a class="sf-attachment" href="{{ $media->media_url }}" target="_blank" rel="noopener">{{ $media->file_name ?: 'Open '.$media->media_type }}</a>@endforeach
@if($post->poll)<div class="sf-context mt-3">Poll: <strong>{{ $post->poll->question }}</strong> · {{ $post->poll->votes()->count() }} votes. Poll options and votes are preserved.</div>@endif
<form method="post" action="{{ route('admin.community.post-update', $post) }}" class="mt-4">@csrf
<label class="sf-editor-label" for="body">Post text</label><textarea class="form-control mb-2 @error('body') is-invalid @enderror" id="body" name="body" rows="8" maxlength="10000" placeholder="Post text…">{{ old('body', $post->body) }}</textarea>@error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
<p class="small text-muted">Media, poll votes, reactions, and comments stay attached to this post.</p>
<hr class="my-4">
<label class="sf-editor-label mb-2" for="visibility">Who can see this post?</label><select class="form-select mb-3 @error('visibility') is-invalid @enderror" id="visibility" name="visibility"><option value="company" @selected($visibility === 'company')>Everyone in the company</option><option value="department" @selected($visibility === 'department')>Selected departments</option><option value="employees" @selected($visibility === 'employees')>Selected employees</option><option value="management" @selected($visibility === 'management')>Management</option><option value="hr" @selected($visibility === 'hr')>HR team</option></select>@error('visibility')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
<div id="department-audience" class="sf-target-box mb-3"><div class="small text-muted mb-2">Select one or more departments. A selected post stays within this post’s company.</div><input class="form-control sf-target-search" type="search" data-target-search="department" placeholder="Search departments…"><div class="sf-target-options">@foreach($departments as $department)<label class="sf-target-option" data-target-option="department" data-label="{{ $department->dept_name }}"><input type="checkbox" name="audience_department_ids[]" value="{{ $department->id }}" data-target-check="department" @checked(in_array((int) $department->id, $selectedDepartmentIds, true))><span>{{ $department->dept_name }}</span></label>@endforeach</div><div class="sf-target-count" id="department-count"></div>@error('audience_department_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div>
<div id="employee-audience" class="sf-target-box mb-3"><div class="small text-muted mb-2">Select one or more active employees from this post’s company.</div><input class="form-control sf-target-search" type="search" data-target-search="employee" placeholder="Search employees…"><div class="sf-target-options">@foreach($employees as $employee)<label class="sf-target-option" data-target-option="employee" data-label="{{ $employee->name }}"><input type="checkbox" name="audience_user_ids[]" value="{{ $employee->id }}" data-target-check="employee" @checked(in_array((int) $employee->id, $selectedEmployeeIds, true))><span>{{ $employee->name }}@if($employee->department_id) <small>· Department {{ $employee->department_id }}</small>@endif</span></label>@endforeach</div><div class="sf-target-count" id="employee-count"></div>@error('audience_user_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div>
<div class="d-flex justify-content-between mt-4"><a class="btn btn-light" href="{{ route('admin.community.feed') }}">Cancel</a><button class="btn btn-primary" type="submit"><i data-feather="save" class="me-1"></i>Save post changes</button></div>
</form></div></div></div>
<script>
(() => {
  const visibility = document.getElementById('visibility');
  const departments = document.getElementById('department-audience');
  const employees = document.getElementById('employee-audience');
  const syncAudience = () => {
    departments.hidden = visibility.value !== 'department';
    employees.hidden = visibility.value !== 'employees';
  };
  visibility.addEventListener('change', syncAudience);
  syncAudience();

  ['department','employee'].forEach(group => {
    const search = document.querySelector(`[data-target-search="${group}"]`);
    const options = [...document.querySelectorAll(`[data-target-option="${group}"]`)];
    const count = document.getElementById(`${group}-count`);
    const update = () => {
      const selected = document.querySelectorAll(`[data-target-check="${group}"]:checked`).length;
      count.textContent = `${selected} selected`;
    };
    search.addEventListener('input', () => {
      const term = search.value.trim().toLocaleLowerCase();
      options.forEach(option => { option.hidden = !option.dataset.label.toLocaleLowerCase().includes(term); });
    });
    document.querySelectorAll(`[data-target-check="${group}"]`).forEach(input => input.addEventListener('change', update));
    update();
  });
})();
</script>
@endsection
