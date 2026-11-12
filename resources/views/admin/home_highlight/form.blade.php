@extends('layouts.master')
@section('title', $item->exists ? 'Edit Home Highlight' : 'New Home Highlight')
@section('action', 'Highlight editor')
@section('styles')
<style>
.hh-form{--ink:#172b4d;--muted:#718096;--line:#e8edf3;color:var(--ink)}
.hh-form-hero{border:0;border-radius:14px;background:linear-gradient(115deg,#4a2f6b,#7a4b8c);color:#fff}.hh-form-hero .card-body{padding:22px 26px}.hh-form-hero h3{color:#fff;margin:0 0 5px;font-size:1.35rem;font-weight:700}.hh-form-hero p{color:rgba(255,255,255,.8);margin:0}
.hh-section{border:1px solid var(--line);border-radius:13px;box-shadow:0 4px 16px rgba(23,43,77,.035);margin-bottom:16px}.hh-section .card-body{padding:21px}.hh-section-title{display:flex;gap:12px;align-items:center;margin-bottom:17px}.hh-step{display:grid;place-items:center;width:31px;height:31px;border-radius:10px;background:#f1eaf6;color:#7a4b8c;font-weight:750;font-size:.84rem}.hh-section-title h5{font-size:1rem;font-weight:700;margin:0;color:#2f2340}.hh-section-title p{font-size:.79rem;color:#718096;margin:3px 0 0}
.hh-form .form-label{font-weight:650;color:#3b2a4d;font-size:.87rem}.hh-form .form-control,.hh-form .form-select{border-color:#dce3eb;border-radius:8px}.hh-form .form-control:focus,.hh-form .form-select:focus{border-color:#9a6cb0;box-shadow:0 0 0 .2rem rgba(122,75,140,.13)}.hh-hint{color:#718096;font-size:.78rem;margin-top:-6px;margin-bottom:14px}.hh-char-count{font-size:.75rem;color:#8794a5;text-align:right;margin-top:-10px;margin-bottom:12px}
.hh-audience-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.hh-audience-picker{border:1px solid #dce3eb;border-radius:10px;background:#fff;padding:14px}.hh-audience-count{color:#7a4b8c;font-size:.78rem;font-weight:650}.hh-audience-selection{color:#718096;font-size:.8rem;line-height:1.5;min-height:38px;margin:8px 0 12px;overflow-wrap:anywhere}.hh-audience-modal .modal-content{border:0;border-radius:14px}.hh-audience-modal .modal-header{background:#f8f6fa;border-bottom:1px solid #e8edf3}.hh-audience-modal .modal-title{color:#3b2a4d;font-size:1.1rem;font-weight:700}.hh-audience-options{max-height:45vh;overflow:auto;border:1px solid #edf0f4;border-radius:8px;padding:4px}.hh-audience-option{display:flex;align-items:center;gap:9px;padding:10px;border-radius:6px;cursor:pointer;color:#42566d;font-size:.87rem;margin:0}.hh-audience-option:hover{background:#f8f5fa}.hh-audience-option input{accent-color:#7a4b8c;width:16px;height:16px;flex:0 0 auto}.hh-audience-option[hidden]{display:none}.hh-audience-toolbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin:12px 0}.hh-audience-modal .modal-footer{justify-content:space-between}
.hh-preview{position:sticky;top:18px;border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.04);overflow:hidden}.hh-preview-head{padding:14px 17px;background:#f8f6fa;border-bottom:1px solid var(--line);font-weight:700;color:#3b2a4d}.hh-preview-body{padding:19px}.hh-preview-kicker{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:#718096;font-weight:700}.hh-preview-title{font-size:1.22rem;font-weight:700;margin:7px 0 9px;color:#2f2340}.hh-preview-image{width:100%;max-height:210px;object-fit:cover;border-radius:9px;background:#f3f5f7;margin-bottom:11px}.hh-preview-message{color:#506176;line-height:1.55;max-height:180px;overflow:auto}.hh-preview-link{display:inline-block;margin-top:12px}
.hh-actions-bar{position:sticky;bottom:0;z-index:5;background:rgba(255,255,255,.96);backdrop-filter:blur(8px);border:1px solid var(--line);border-radius:12px;padding:12px 15px;box-shadow:0 -5px 18px rgba(23,43,77,.06)}
.hh-form .invalid-feedback{display:block;font-size:.78rem;margin-top:-9px;margin-bottom:12px}.hh-form .is-invalid{border-color:#dc3545}.hh-form .is-invalid:focus{border-color:#dc3545;box-shadow:0 0 0 .2rem rgba(220,53,69,.12)}
@media(max-width:991px){.hh-preview{position:static}}@media(max-width:767px){.hh-audience-grid{grid-template-columns:1fr}}
</style>
@endsection
@section('main-content')
@php
    $departmentNameById = collect($departments ?? [])->pluck('dept_name', 'id');
@endphp
<div class="hh-form">
@include('admin.section.flash_message')
@if($errors->any())<div class="alert alert-warning d-flex align-items-start gap-2" role="alert"><i data-feather="alert-triangle" class="mt-1" style="width:17px;height:17px"></i><div><strong>Some fields need correction.</strong><div class="small">Review the red messages below, then submit again. Your text and selections have been kept.</div><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
<div class="card hh-form-hero mb-3"><div class="card-body"><h3>{{ $item->exists ? 'Edit Home Highlight' : 'Create a Home Highlight' }}</h3><p>Share a story, win, or update in the Highlights tab of the employee home card.</p></div></div>
<form method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.community.home-highlights.update', $item) : route('admin.community.home-highlights.store') }}" id="home-highlight-form">
<div class="row"><div class="col-lg-7">
<div class="card hh-section"><div class="card-body"><div class="hh-section-title"><span class="hh-step">1</span><div><h5>Content</h5><p>Write a short highlight employees can scan quickly.</p></div></div>
@csrf
<label class="form-label" for="title">Title <span class="text-danger">*</span></label><input class="form-control mb-3 @error('title') is-invalid @enderror" id="title" name="title" maxlength="180" required value="{{ old('title', $item->title) }}" placeholder="e.g. Welcome to our newest team members">@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
<label class="form-label" for="body">Description</label><textarea class="form-control @error('body') is-invalid @enderror" id="highlight-body" name="body" rows="7" maxlength="20000" placeholder="Tell employees what happened and why it matters…">{{ old('body', $item->body) }}</textarea>@error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="hh-char-count"><span id="body-count">{{ mb_strlen(strip_tags(old('body', $item->body ?? ''))) }}</span> / 20,000</div>
<div class="hh-hint">Basic formatting is supported. Keep the first sentence meaningful because it is shown in the home carousel.</div>
</div></div>
<div class="card hh-section"><div class="card-body"><div class="hh-section-title"><span class="hh-step">2</span><div><h5>Image and action</h5><p>Add an optional cover image and a link button.</p></div></div>
<label class="form-label" for="image">Cover image</label><input class="form-control mb-2 @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">@error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="hh-hint">JPG, PNG, GIF, or WebP up to 10 MB. A wide landscape image works best.</div>
<label class="form-label" for="image_url">Or image URL <span class="text-muted fw-normal">(HTTPS)</span></label><input class="form-control mb-2 @error('image_url') is-invalid @enderror" id="image_url" type="url" name="image_url" value="{{ old('image_url', $item->image_url) }}" placeholder="https://example.com/highlight.jpg">@error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
@if($item->image_url)<div class="d-flex align-items-center gap-3 mb-3"><img src="{{ $item->image_url }}" alt="Current cover" style="width:110px;height:72px;object-fit:cover;border-radius:8px"><label class="d-flex align-items-center gap-2 small text-muted mb-0"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>Remove current image</label></div>@endif
<div class="row"><div class="col-md-8"><label class="form-label" for="link_url">Button URL <span class="text-muted fw-normal">(optional)</span></label><input class="form-control mb-3 @error('link_url') is-invalid @enderror" id="link_url" type="url" name="link_url" value="{{ old('link_url', $item->link_url) }}" placeholder="https://hr.example.com/news">@error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="link_label">Button label</label><input class="form-control mb-2 @error('link_label') is-invalid @enderror" id="link_label" name="link_label" maxlength="80" value="{{ old('link_label', $item->link_label) }}" placeholder="Read more">@error('link_label')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
</div></div>
<div class="card hh-section"><div class="card-body"><div class="hh-section-title"><span class="hh-step">3</span><div><h5>Audience</h5><p>Leave a group empty to include everyone in that group.</p></div></div>
@php
    $audienceGroups = [
        ['key' => 'role_ids', 'label' => 'Roles', 'options' => $roles->pluck('name', 'id')],
        ['key' => 'branch_ids', 'label' => 'Branches', 'options' => $branches->pluck('name', 'id')],
        ['key' => 'department_ids', 'label' => 'Departments', 'options' => $departments->mapWithKeys(fn ($department) => [$department->id => $department->dept_name.($branchNameById->has($department->branch_id) ? ' · '.$branchNameById[$department->branch_id] : '')])],
        ['key' => 'user_ids', 'label' => 'Employees', 'options' => $employees->mapWithKeys(fn ($employee) => [$employee->id => $employee->name.($departmentNameById->has($employee->department_id) ? ' · '.$departmentNameById[$employee->department_id] : '')])],
    ];
    foreach ($audienceGroups as &$group) {
        $group['selected'] = array_map('intval', (array) old($group['key'], session()->hasOldInput() ? [] : ($item->{$group['key']} ?? [])));
        $group['selectedLabels'] = $group['options']->filter(fn ($label, $id) => in_array((int) $id, $group['selected'], true))->values();
    }
    unset($group);
@endphp
<div class="hh-audience-grid">
@foreach($audienceGroups as $group)
<div class="hh-audience-picker">
    <div class="d-flex justify-content-between align-items-center gap-2"><strong class="form-label mb-0">{{ $group['label'] }}</strong><span class="hh-audience-count" id="audience-count-{{ $group['key'] }}" aria-live="polite">{{ count($group['selected']) }} selected</span></div>
    <p class="hh-audience-selection" id="audience-selection-{{ $group['key'] }}">{{ $group['selectedLabels']->isEmpty() ? 'All '.strtolower($group['label']).' included' : $group['selectedLabels']->take(3)->implode(', ').($group['selectedLabels']->count() > 3 ? ' +'.($group['selectedLabels']->count() - 3).' more' : '') }}</p>
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#audience-modal-{{ $group['key'] }}" aria-controls="audience-modal-{{ $group['key'] }}">Select {{ strtolower($group['label']) }}</button>
    @error($group['key'])<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
@endforeach
</div>
<div class="hh-hint mt-3 mb-0">An employee must match at least one selection in every group that has selections. Empty groups include everyone.</div>
</div></div>
<div class="card hh-section"><div class="card-body"><div class="hh-section-title"><span class="hh-step">4</span><div><h5>Schedule and visibility</h5><p>Control when the highlight appears and how high it ranks.</p></div></div>
<div class="row"><div class="col-md-6"><label class="form-label" for="starts_at">Start date and time <span class="text-muted fw-normal">({{ config('app.timezone') }})</span></label><input class="form-control mb-3 @error('starts_at') is-invalid @enderror" type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}">@error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="hh-hint">Leave blank to start immediately.</div></div><div class="col-md-6"><label class="form-label" for="ends_at">End date and time</label><input class="form-control mb-3 @error('ends_at') is-invalid @enderror" type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $item->ends_at?->format('Y-m-d\TH:i')) }}">@error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="hh-hint">Leave blank for no end date. End must be after the start.</div></div></div>
<div class="row"><div class="col-md-7"><label class="form-label" for="priority">Priority</label><input class="form-control mb-2 @error('priority') is-invalid @enderror" id="priority" type="number" name="priority" min="-1000" max="1000" value="{{ old('priority', $item->priority ?? 0) }}" required>@error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="hh-hint">Higher priority appears first in the carousel.</div></div><div class="col-md-5 d-flex align-items-center"><input type="hidden" name="is_active" value="0"><label class="d-flex align-items-start gap-2 mb-0"><input class="mt-1" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))><span><strong>Publish this highlight</strong><span class="d-block text-muted small">Unchecked saves it as a draft.</span></span></label></div></div>
</div></div>
<div class="hh-actions-bar d-flex justify-content-between align-items-center gap-2"><a href="{{ route('admin.community.home-highlights.index') }}" class="btn btn-light">Cancel</a><button class="btn btn-primary" type="submit"><i data-feather="save" class="me-1"></i>{{ $item->exists ? 'Save changes' : 'Publish highlight' }}</button></div>
</div><div class="col-lg-5"><div class="card hh-preview"><div class="hh-preview-head"><i data-feather="smartphone" class="me-1" style="width:16px;height:16px"></i> Employee home preview</div><div class="hh-preview-body"><div class="hh-preview-kicker">Highlights</div><h4 class="hh-preview-title" id="preview-title">{{ old('title', $item->title) ?: 'Highlight title' }}</h4><img id="preview-image" class="hh-preview-image {{ $item->image_url ? '' : 'd-none' }}" src="{{ $item->image_url }}" alt=""><div class="hh-preview-message" id="preview-body">{!! \App\Services\Community\HighlightContent::clean(old('body', $item->body)) !!}</div><a id="preview-link" href="#" class="btn btn-sm btn-outline-primary hh-preview-link {{ old('link_url', $item->link_url) ? '' : 'd-none' }}" target="_blank" rel="noopener">{{ old('link_label', $item->link_label) ?: 'Read more' }}</a><p class="small text-muted mt-3 mb-0">The preview updates as you edit. Actual visibility also depends on the employee's role, branch, department, and permissions.</p></div></div></div></div>
</form>
@foreach($audienceGroups as $group)
    @include('admin.home_highlight.audience_modal', ['group' => $group])
@endforeach
</div>
<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script>
(() => {
  const field = id => document.getElementById(id);
  const previewTitle = field('preview-title');
  const previewBody = field('preview-body');
  const previewImage = field('preview-image');
  const previewLink = field('preview-link');
  let objectUrl;
  const safeBody = html => {
    const parsed = new DOMParser().parseFromString(html, 'text/html');
    const allowed = new Set(['P','BR','STRONG','B','EM','I','U','UL','OL','LI','BLOCKQUOTE','H2','H3','H4','A','SPAN']);
    const copy = node => {
      if (node.nodeType === Node.TEXT_NODE) return document.createTextNode(node.textContent);
      const result = document.createDocumentFragment();
      if (node.nodeType !== Node.ELEMENT_NODE || ['SCRIPT','STYLE','IFRAME','OBJECT','SVG','MATH'].includes(node.tagName)) return result;
      const target = allowed.has(node.tagName) ? document.createElement(node.tagName.toLowerCase()) : result;
      if (node.tagName === 'A' && /^(https?:|mailto:)/i.test(node.getAttribute('href') || '')) {
        target.setAttribute('href', node.getAttribute('href'));
        target.setAttribute('rel', 'noopener noreferrer');
      }
      if (node.tagName === 'SPAN' && node.style.textDecoration === 'underline') target.style.textDecoration = 'underline';
      node.childNodes.forEach(child => target.appendChild(copy(child)));
      return target;
    };
    const result = document.createDocumentFragment();
    parsed.body.childNodes.forEach(node => result.appendChild(copy(node)));
    return result;
  };
  const syncPreview = () => {
    previewTitle.textContent = field('title').value || 'Highlight title';
    const body = field('highlight-body');
    const html = typeof tinymce !== 'undefined' && tinymce.get('highlight-body')
      ? tinymce.get('highlight-body').getContent() : body.value;
    previewBody.replaceChildren(safeBody(html));
    const file = field('image').files[0];
    const url = field('image_url').value.trim();
    if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
    if (file) {
      objectUrl = URL.createObjectURL(file);
      previewImage.src = objectUrl;
      previewImage.classList.remove('d-none');
    } else if (url && !document.querySelector('[name=remove_image]:checked')) {
      previewImage.src = url;
      previewImage.classList.remove('d-none');
    } else {
      previewImage.removeAttribute('src');
      previewImage.classList.add('d-none');
    }
    const link = field('link_url').value.trim();
    const validLink = /^https:\/\//i.test(link);
    previewLink.classList.toggle('d-none', !validLink);
    if (validLink) {
      previewLink.href = link;
      previewLink.textContent = field('link_label').value || 'Read more';
    }
    field('body-count').textContent = previewBody.textContent.length;
  };
  ['title','image_url','link_url','link_label','image'].forEach(id => field(id).addEventListener('input', syncPreview));
  field('image').addEventListener('change', syncPreview);
  document.querySelector('[name=remove_image]')?.addEventListener('change', syncPreview);
  field('highlight-body').addEventListener('input', syncPreview);

  const initEditor = () => {
    if (typeof tinymce === 'undefined') return;
    tinymce.init({
      selector: '#highlight-body',
      height: 320,
      menubar: false,
      plugins: 'link lists code',
      toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat | code',
      branding: false,
      setup: editor => editor.on('change keyup input', syncPreview),
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initEditor);
  else initEditor();

  document.querySelectorAll('[data-audience-modal]').forEach(modal => {
    const search = modal.querySelector('[data-audience-search]');
    const options = [...modal.querySelectorAll('[data-audience-option]')];
    const inputs = [...modal.querySelectorAll('[data-audience-value]')];
    const count = modal.querySelector('[data-audience-count]');
    const summaryCount = field(`audience-count-${modal.dataset.group}`);
    const summary = field(`audience-selection-${modal.dataset.group}`);
    const empty = modal.querySelector('[data-audience-empty]');
    const selectAll = modal.querySelector('[data-select-all-audience]');
    const clearAll = modal.querySelector('[data-clear-audience]');
    const update = () => {
      const selected = inputs.filter(input => input.checked);
      count.textContent = `${selected.length} of ${inputs.length} selected`;
      summaryCount.textContent = `${selected.length} selected`;
      const labels = selected.slice(0, 3).map(input => input.closest('[data-audience-option]').dataset.label);
      summary.textContent = selected.length
        ? labels.join(', ') + (selected.length > 3 ? ` +${selected.length - 3} more` : '')
        : `All ${modal.dataset.label} included`;
      selectAll.disabled = inputs.length === 0 || selected.length === inputs.length;
      clearAll.disabled = selected.length === 0;
    };
    const filter = () => {
      const term = search.value.trim().toLocaleLowerCase();
      options.forEach(option => { option.hidden = !option.dataset.label.toLocaleLowerCase().includes(term); });
      empty.hidden = options.some(option => !option.hidden);
    };
    search.addEventListener('input', filter);
    search.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
    inputs.forEach(input => input.addEventListener('change', update));
    selectAll.addEventListener('click', () => {
      inputs.forEach(input => { input.checked = true; });
      update();
    });
    clearAll.addEventListener('click', () => {
      inputs.forEach(input => { input.checked = false; });
      update();
    });
    modal.addEventListener('shown.bs.modal', () => { search.focus(); });
    modal.addEventListener('hidden.bs.modal', () => { search.value = ''; filter(); });
    update();
    filter();
  });

  const start = field('starts_at');
  const end = field('ends_at');
  start.addEventListener('change', () => { end.min = start.value; });
  if (start.value) end.min = start.value;
  field('home-highlight-form').addEventListener('submit', () => {
    if (typeof tinymce !== 'undefined') tinymce.triggerSave();
  });
  syncPreview();
})();
</script>
@endsection
