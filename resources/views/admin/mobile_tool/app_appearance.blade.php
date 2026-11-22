@extends('layouts.master')
@section('title', __('index.app_appearance'))
@section('action', __('index.app_appearance_intro'))

@section('styles')
<style>
    .aa-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(23, 43, 77, 0.04);
        background: #fff;
    }
    .aa-card .card-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
    }
    .aa-bg-preview {
        position: relative;
        width: min(100%, 320px);
        aspect-ratio: 9/16;
        max-height: 420px;
        overflow: hidden;
        border-radius: 18px;
        background: linear-gradient(160deg, #0f172a, #1e3a8a);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
    }
    .aa-bg-preview img,
    .aa-bg-preview object {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .aa-bg-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        font-size: .85rem;
        text-align: center;
        padding: 12px;
    }
    .aa-effect-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }
    .aa-effect-row + .aa-effect-row { margin-top: 10px; }
    .aa-effect-row .form-check-input { width: 2.4em; height: 1.2em; cursor: pointer; }
    .aa-effect-label { font-weight: 600; color: #172b4d; }
    .aa-effect-hint { font-size: .78rem; color: #64748b; }
</style>
@endsection

@section('main-content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-1"></i> {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i data-feather="alert-circle" class="me-1"></i> Validation Notice:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.mobile-tools.app-appearance.update') }}" method="POST" enctype="multipart/form-data" id="appearanceForm">
        @csrf

        <div class="row g-4">
            <!-- BACKGROUND -->
            <div class="col-lg-7">
                <div class="aa-card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i data-feather="image" class="me-2" style="width:18px;height:18px"></i>
                        {{ __('index.app_appearance_background') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('index.app_appearance_background_type') }}</label>
                            <select name="background_type" id="backgroundType" class="form-select">
                                <option value="none" @selected(old('background_type', $config['background_type']) === 'none')>{{ __('index.app_appearance_background_none') }}</option>
                                <option value="image" @selected(old('background_type', $config['background_type']) === 'image')>{{ __('index.app_appearance_background_image') }}</option>
                                <option value="svg" @selected(old('background_type', $config['background_type']) === 'svg')>{{ __('index.app_appearance_background_svg') }}</option>
                            </select>
                        </div>

                        <div class="mb-3" id="backgroundUploadGroup">
                            <label class="form-label fw-semibold">{{ __('index.app_appearance_background_file') }}</label>
                            <input type="file" name="background" id="backgroundFile" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,image/*,.svg+xml">
                            <div class="form-text">JPG, PNG, GIF, WebP or SVG · max 10 MB. Uploading a new file replaces the current one.</div>
                        </div>

                        @if(!empty($config['background_url']))
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remove_background" value="1" id="removeBackground">
                                <label class="form-check-label" for="removeBackground">{{ __('index.app_appearance_remove_background') }}</label>
                            </div>
                        @endif

                        <label class="form-label fw-semibold d-block">{{ __('index.app_appearance_background_current') }}</label>
                        <div class="aa-bg-preview">
                            @if(!empty($config['background_url']))
                                @if(($config['background_type'] ?? 'none') === 'svg')
                                    <img src="{{ $config['background_url'] }}" alt="App background">
                                @else
                                    <img src="{{ $config['background_url'] }}" alt="App background">
                                @endif
                            @else
                                <div class="aa-bg-placeholder">
                                    <span>{{ __('index.app_appearance_background_none') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- EFFECTS -->
            <div class="col-lg-5">
                <div class="aa-card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i data-feather="sparkles" class="me-2" style="width:18px;height:18px"></i>
                        {{ __('index.app_appearance_effects') }}
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">{{ __('index.app_appearance_effects_hint') }}</p>

                        @foreach([
                            'heat' => __('index.app_effect_heat'),
                            'snow' => __('index.app_effect_snow'),
                            'floating_hearts' => __('index.app_effect_floating_hearts'),
                            'rose_petals' => __('index.app_effect_rose_petals'),
                            'heart_glow' => __('index.app_effect_heart_glow'),
                            'sparkle_effects' => __('index.app_effect_sparkle_effects'),
                            'emoji_effect' => __('index.app_effect_emoji_effect'),
                        ] as $flag => $label)
                            <div class="aa-effect-row">
                                <div>
                                    <div class="aa-effect-label">{{ $label }}</div>
                                    <div class="aa-effect-hint"><code>{{ $flag }}</code></div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="{{ $flag }}" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="{{ $flag }}" value="1" id="flag_{{ $flag }}"
                                           @checked(old($flag, !empty($config[$flag])))>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            <label class="form-label fw-semibold" for="emojiChar">{{ __('index.app_effect_emoji_char') }}</label>
                            <input type="text" name="emoji_char" id="emojiChar" class="form-control" style="max-width:140px"
                                   maxlength="8" value="{{ old('emoji_char', $config['emoji_char'] ?? '✨') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i data-feather="save" class="me-1" style="width:16px;height:16px"></i> {{ __('index.save') }}
                    </button>
                    <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#resetModal">
                        <i data-feather="rotate-ccw" class="me-1" style="width:16px;height:16px"></i> {{ __('index.reset') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- RESET MODAL -->
    <div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i data-feather="alert-triangle" class="me-1" style="width:16px;height:16px"></i> {{ __('index.reset') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Reset the app background and all floating effects to factory defaults? The uploaded background file will be deleted.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <form action="{{ route('admin.mobile-tools.app-appearance.reset') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger fw-semibold">{{ __('index.reset') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('backgroundType');
    const uploadGroup = document.getElementById('backgroundUploadGroup');
    const removeBox = document.getElementById('removeBackground');
    const fileInput = document.getElementById('backgroundFile');

    function sync() {
        const type = typeSelect.value;
        uploadGroup.style.display = type === 'none' ? 'none' : '';
        if (removeBox) removeBox.closest('.form-check').style.display = type === 'none' ? 'none' : '';
        if (type === 'svg') {
            fileInput.accept = '.svg,.svg+xml,image/svg+xml';
        } else {
            fileInput.accept = '.jpg,.jpeg,.png,.gif,.webp,image/*';
        }
    }
    typeSelect.addEventListener('change', sync);
    sync();

    if (removeBox) {
        removeBox.addEventListener('change', function () {
            if (this.checked) fileInput.value = '';
        });
    }
})();
</script>
@endsection
