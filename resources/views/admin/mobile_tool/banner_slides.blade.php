@extends('layouts.master')
@section('title', 'Banner Slides')
@section('action', 'Manage the mobile home banner slideshow')
@section('styles')
<style>
.mt-page{--ink:#172b4d;--muted:#718096;--line:#e8edf3;--soft:#f6f8fb;color:var(--ink)}
.mt-hero{border:0;border-radius:16px;background:linear-gradient(120deg,#1f4e79,#2f7bb8);color:#fff;box-shadow:0 10px 26px rgba(23,60,96,.16)}
.mt-hero .card-body{padding:26px 32px}
.mt-eyebrow{font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:#cfe4f5;font-weight:700}
.mt-hero h2{color:#fff;font-size:1.55rem;font-weight:700;margin:.45rem 0}
.mt-hero p{color:rgba(255,255,255,.85);margin:0;max-width:760px}
.mt-card{border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 18px rgba(23,43,77,.04)}
.mt-slide{border:1px solid var(--line);border-radius:12px;background:var(--soft);padding:16px;margin-bottom:14px}
.mt-slide-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.mt-slide-title{font-weight:700;font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#5a6b80}
.mt-preview{width:100%;max-height:150px;object-fit:cover;border-radius:8px;background:#e9eef4;margin-top:8px;border:1px solid var(--line)}
.mt-settings{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px}
@media(max-width:992px){.mt-settings{grid-template-columns:repeat(2,minmax(0,1fr))}}
.mt-actions{display:flex;gap:10px;flex-wrap:wrap}
</style>
@endsection
@section('main-content')
<div class="mt-page">
@include('admin.section.flash_message')
<div class="card mt-hero mb-4"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
<div><div class="mt-eyebrow">Mobile Tool</div><h2>Banner Slides</h2><p>Control the image carousel employees see at the top of the mobile home screen. Slides rotate using the timing below.</p></div>
<button type="button" class="btn btn-light text-primary fw-semibold" onclick="addSlide()"><i data-feather="plus" class="me-1"></i>Add slide</button>
</div></div>

@if(isset($errors) && $errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('admin.mobile-tools.banner-slides.update') }}" enctype="multipart/form-data" id="banner-form">
@csrf
<div class="card mt-card mb-4"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0">Slides <span class="badge bg-light text-dark" id="slide-count">{{ count($config['slides']) }}</span></h5>
<small class="text-muted">Up to 20 slides. Each slide needs an uploaded image or an image URL.</small>
</div>
<div id="slides">
@forelse($config['slides'] as $slide)
@include('admin.mobile_tool.slide_row', ['slide' => $slide, 'index' => $loop->index])
@empty
<p class="text-muted mb-0" id="empty-note">No slides yet — add your first slide to start the slideshow.</p>
@endforelse
</div>
<template id="slide-template">
@include('admin.mobile_tool.slide_row', ['slide' => ['id' => '', 'imageUrl' => null, 'linkUrl' => null, 'title' => null, 'enabled' => true], 'index' => '__INDEX__'])
</template>
</div></div>

<div class="card mt-card mb-4"><div class="card-body">
<h5 class="mb-1">Playback settings</h5>
<p class="text-muted mb-3">How the slideshow behaves on employee phones.</p>
<div class="mt-settings">
<div><label class="form-label" for="intervalSeconds">Interval (seconds)</label>
<input class="form-control" type="number" id="intervalSeconds" name="intervalSeconds" min="2" max="30" required value="{{ old('intervalSeconds', $config['intervalSeconds']) }}"></div>
<div><label class="form-label">Autoplay</label>
<select class="form-select" name="autoplay"><option value="1" @selected((bool) old('autoplay', $config['autoplay']))>On</option><option value="0" @selected(old('autoplay', $config['autoplay']) !== null && !(bool) old('autoplay', $config['autoplay']))>Off</option></select></div>
<div><label class="form-label">Indicators</label>
<select class="form-select" name="showIndicators"><option value="1" @selected((bool) old('showIndicators', $config['showIndicators']))>Shown</option><option value="0" @selected(old('showIndicators', $config['showIndicators']) !== null && !(bool) old('showIndicators', $config['showIndicators']))>Hidden</option></select></div>
<div><label class="form-label">Pause on touch</label>
<select class="form-select" name="pauseOnTouch"><option value="1" @selected((bool) old('pauseOnTouch', $config['pauseOnTouch']))>Yes</option><option value="0" @selected(old('pauseOnTouch', $config['pauseOnTouch']) !== null && !(bool) old('pauseOnTouch', $config['pauseOnTouch']))>No</option></select></div>
<div><label class="form-label">Loop</label>
<select class="form-select" name="loop"><option value="1" @selected((bool) old('loop', $config['loop']))>Yes</option><option value="0" @selected(old('loop', $config['loop']) !== null && !(bool) old('loop', $config['loop']))>No</option></select></div>
<div><label class="form-label" for="transition">Transition</label>
<select class="form-select" id="transition" name="transition">
<option value="fade" @selected(old('transition', $config['transition']) === 'fade')>Fade</option>
<option value="slide" @selected(old('transition', $config['transition']) === 'slide')>Slide</option>
</select></div>
</div>
</div></div>

<div class="mt-actions mb-4">
<button type="submit" class="btn btn-primary"><i data-feather="save" class="me-1"></i>Save slideshow</button>
</div>
</form>

<form method="post" action="{{ route('admin.mobile-tools.banner-slides.reset') }}" onsubmit="return confirm('Reset the slideshow to an empty default? Uploaded slide images will be deleted.')">
@csrf
<button type="submit" class="btn btn-outline-danger"><i data-feather="rotate-ccw" class="me-1"></i>Reset to default</button>
</form>
</div>

<script>
let slideIndex = parseInt(document.getElementById('slide-count').textContent, 10) || 0;
function addSlide() {
    const template = document.getElementById('slide-template').innerHTML;
    const note = document.getElementById('empty-note');
    if (note) note.remove();
    document.getElementById('slides').insertAdjacentHTML('beforeend', template.replace(/__INDEX__/g, slideIndex++));
    const rows = document.querySelectorAll('#slides .mt-slide');
    const idInput = rows[rows.length - 1].querySelector('input[data-slide-id]');
    if (idInput && !idInput.value) idInput.value = 'slide_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
    updateCount();
    if (window.feather) feather.replace();
}
function removeSlide(row) {
    row.remove();
    updateCount();
}
function updateCount() {
    document.getElementById('slide-count').textContent = document.querySelectorAll('#slides .mt-slide').length;
}
document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="file"][data-slide-image]')) {
        const preview = event.target.closest('.mt-slide').querySelector('img.mt-preview');
        const urlInput = event.target.closest('.mt-slide').querySelector('input[data-slide-image-url]');
        if (event.target.files && event.target.files[0]) {
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = '';
        }
        if (urlInput) urlInput.value = '';
    }
});
</script>
@endsection
