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
.mt-preview{position:relative;width:min(100%,360px);aspect-ratio:3/1;overflow:hidden;border-radius:22px;background:#0f172a;margin-top:8px;color:#fff}
.mt-preview img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.mt-preview::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(10,25,47,.65),rgba(10,25,47,.245),rgba(10,25,47,.55));pointer-events:none}
.mt-preview-copy{position:absolute;z-index:1;inset:12px auto 12px 16px;width:50%;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;font:600 11.5px system-ui,sans-serif}
.mt-preview-title{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;background:#ffffff3d;border:1px solid #ffffff59;border-radius:10px;padding:2px 7px;font-size:9.5px;font-weight:800;margin-bottom:2px}
.mt-preview-title[hidden]{display:none}
.mt-preview-clock{font-size:32px;font-weight:900;letter-spacing:-.5px;line-height:1;margin:3px 0 5px}
.mt-preview-clock small{font-size:17px}
.mt-preview-copy>span:last-child{max-width:100%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.mt-playback-preview{margin-bottom:18px}
.mt-preview-dots{position:absolute;z-index:2;bottom:5px;left:50%;transform:translateX(-50%);display:flex;gap:4px;padding:3px 6px;border-radius:10px;background:#0006}
.mt-preview-dots[hidden]{display:none}
.mt-preview-dots span{width:5px;height:5px;border-radius:8px;background:#ffffff73}
.mt-preview-dots .active{width:12px;background:#fff}
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
<small class="text-muted">Up to 20 slides. Slides without an image use the Home wallpaper.</small>
</div>
<section class="mt-playback-preview" aria-label="Slideshow playback preview">
<h6>Mobile slideshow preview</h6>
@include('admin.mobile_tool.banner_preview')
<div class="mt-actions mt-2">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="preview-prev">Previous</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="preview-play">Pause</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="preview-next">Next</button>
</div>
</section>
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
@foreach(\App\Support\BannerSlideshowTransitions::OPTIONS as $value => $label)
<option value="{{ $value }}" @selected(old('transition', $config['transition']) === $value)>{{ $label }}</option>
@endforeach
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
    const row = rows[rows.length - 1];
    updateSlidePreview(row);
    const idInput = row.querySelector('input[data-slide-id]');
    if (idInput && !idInput.value) idInput.value = 'slide_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
    updateCount();
    renderPlayback();
    if (window.feather) feather.replace();
}
function removeSlide(row) {
    if (row.previewUrl) URL.revokeObjectURL(row.previewUrl);
    row.remove();
    updateCount();
    renderPlayback();
}
function updateCount() {
    const rows = [...document.querySelectorAll('#slides .mt-slide')];
    document.getElementById('slide-count').textContent = rows.length;
    rows.forEach((row, index) => {
        row.querySelector('.mt-slide-title').textContent = 'Slide ' + (index + 1);
        row.querySelector('[data-move-up]').disabled = index === 0;
        row.querySelector('[data-move-down]').disabled = index === rows.length - 1;
    });
}
function updateSlidePreview(row) {
    const preview = row.querySelector('.mt-preview');
    const file = row.querySelector('[data-slide-image]').files[0];
    const urlInput = row.querySelector('[data-slide-image-url]');
    const title = row.querySelector('[data-slide-title]').value.trim();
    const caption = preview.querySelector('.mt-preview-title');
    caption.textContent = title;
    caption.hidden = !title;
    const image = preview.querySelector('img');
    if (row.previewFile !== file) {
        if (row.previewUrl) URL.revokeObjectURL(row.previewUrl);
        row.previewFile = file;
        row.previewUrl = file ? URL.createObjectURL(file) : null;
    }
    const source = row.previewUrl || urlInput.value.trim() || image.dataset.fallback;
    if (image.dataset.source !== source) {
        image.dataset.source = source;
        image.src = source;
    }
}
document.addEventListener('input', function (event) {
    if (event.target.matches('[data-slide-title], [data-slide-image-url]')) {
        const row = event.target.closest('.mt-slide');
        if (event.target.matches('[data-slide-image-url]')) row.querySelector('[data-slide-image]').value = '';
        updateSlidePreview(row);
    }
});
document.addEventListener('change', function (event) {
    if (event.target.matches('[data-slide-image]')) updateSlidePreview(event.target.closest('.mt-slide'));
});
const form = document.getElementById('banner-form');
const live = document.querySelector('.mt-playback-preview .mt-preview');
const image = live.querySelector('img');
const dots = live.querySelector('.mt-preview-dots');
const play = document.getElementById('preview-play');
let playbackIndex = 0;
let playbackTimer;
let paused = false;
let pointerHeld = false;
let lastSlide;
let lastSource;
let animation;
let outgoing;
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const transitionStarts = {
    fade: 'none',
    slide: 'translateX(8%)',
    'slide-up': 'translateY(8%)',
    zoom: 'scale(.92)',
};
const option = name => form.elements.namedItem(name).value === '1';
const enabledRows = () => [...document.querySelectorAll('#slides .mt-slide')]
    .filter(row => row.querySelector('[data-slide-enabled]').checked);
function moveSlide(row, direction) {
    const target = direction < 0 ? row.previousElementSibling : row.nextElementSibling;
    if (!target?.classList.contains('mt-slide')) return;
    if (direction < 0) target.before(row); else target.after(row);
    playbackIndex = 0;
    updateCount();
    renderPlayback();
}
function renderPlayback() {
    clearTimeout(playbackTimer);
    const rows = enabledRows();
    playbackIndex = Math.min(playbackIndex, Math.max(0, rows.length - 1));
    const selected = rows[playbackIndex]?.querySelector('.mt-preview');
    const source = selected?.querySelector('img')?.dataset.source || image.dataset.fallback;
    const caption = live.querySelector('.mt-preview-title');
    caption.textContent = selected?.querySelector('.mt-preview-title')?.textContent || '';
    caption.hidden = !caption.textContent;
    if (source !== lastSource || selected !== lastSlide) {
        animation?.cancel();
        outgoing?.remove();
        const transition = form.elements.transition.value;
        if (lastSource && transition !== 'instant' && !reducedMotion.matches) {
            const previous = image.cloneNode();
            image.before(previous);
            outgoing = previous;
            const start = transitionStarts[transition] || transitionStarts.fade;
            const hidden = {opacity: 0, transform: start};
            const shown = {opacity: 1, transform: 'none'};
            animation = image.animate([hidden, shown],
                {duration: 480, easing: 'cubic-bezier(.215,.61,.355,1)'});
            previous.animate([shown, hidden],
                {duration: 480, easing: 'cubic-bezier(.55,.055,.675,.19)'})
                .onfinish = () => previous.remove();
        }
        image.src = source;
        lastSource = source;
        lastSlide = selected;
    }
    dots.hidden = !option('showIndicators') || rows.length < 2;
    dots.replaceChildren(...rows.map((_, index) => {
        const dot = document.createElement('span');
        dot.classList.toggle('active', index === playbackIndex);
        return dot;
    }));
    const ended = !option('loop') && playbackIndex === rows.length - 1;
    play.disabled = rows.length < 2 || !option('autoplay');
    play.textContent = paused ? 'Play' : ended ? 'Replay' : 'Pause';
    document.getElementById('preview-prev').disabled = rows.length < 2;
    document.getElementById('preview-next').disabled = rows.length < 2;
    if (!play.disabled && !paused && !ended && !pointerHeld && !reducedMotion.matches) {
        const seconds = Math.min(30, Math.max(2, Number(form.elements.intervalSeconds.value) || 5));
        playbackTimer = setTimeout(() => stepPlayback(1), seconds * 1000);
    }
}
function stepPlayback(direction) {
    const count = enabledRows().length;
    playbackIndex = count ? (playbackIndex + direction + count) % count : 0;
    renderPlayback();
}
document.getElementById('preview-prev').onclick = () => stepPlayback(-1);
document.getElementById('preview-next').onclick = () => stepPlayback(1);
play.onclick = () => {
    const ended = !option('loop') && playbackIndex === enabledRows().length - 1;
    if (ended && !paused) playbackIndex = 0;
    else paused = !paused;
    renderPlayback();
};
live.addEventListener('pointerdown', event => {
    if (!option('pauseOnTouch')) return;
    live.setPointerCapture(event.pointerId);
    pointerHeld = true;
    renderPlayback();
});
for (const event of ['pointerup', 'pointercancel', 'lostpointercapture']) {
    live.addEventListener(event, () => {
        pointerHeld = false;
        renderPlayback();
    });
}
reducedMotion.addEventListener('change', renderPlayback);
form.addEventListener('input', () => queueMicrotask(renderPlayback));
form.addEventListener('change', () => queueMicrotask(renderPlayback));
document.querySelectorAll('#slides .mt-slide').forEach(updateSlidePreview);
updateCount();
renderPlayback();
</script>
@endsection
