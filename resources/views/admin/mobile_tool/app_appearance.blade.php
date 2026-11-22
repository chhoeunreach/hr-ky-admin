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
    .aa-custom-effect { display:grid;grid-template-columns:minmax(100px,1fr) minmax(90px,1fr) 100px auto auto;align-items:center;gap:8px;padding:10px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc; }
    .aa-custom-effect + .aa-custom-effect { margin-top:8px; }
    .aa-custom-effect-emoji { font-size:1.15rem; }
    @media(max-width:575px){.aa-custom-effect{grid-template-columns:1fr 1fr}.aa-custom-effect .aa-effect-enabled{grid-column:1}.aa-custom-effect .aa-effect-remove{grid-column:2;justify-self:end}}
    .aa-floating-preview{position:relative;height:150px;margin-top:14px;overflow:hidden;border:1px solid #dbe3ee;border-radius:12px;background:linear-gradient(160deg,#f8fafc,#eef2ff)}
    .aa-floating-preview-label{position:absolute;z-index:1;top:8px;left:10px;color:#64748b;font-size:.72rem;font-weight:600}
    .aa-floating-preview-empty{position:absolute;inset:0;display:grid;place-items:center;padding:12px;color:#64748b;font-size:.85rem;text-align:center}
    .aa-floating-preview-particle{position:absolute;bottom:-34px;left:var(--left);font-size:var(--size);animation:aa-float-up var(--duration) ease-in var(--delay) both}
    @keyframes aa-float-up{0%{transform:translateY(0) rotate(-12deg);opacity:0}12%{opacity:.9}85%{opacity:.75}100%{transform:translateY(-190px) rotate(18deg);opacity:0}}
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
                                <img src="{{ $config['background_url'] }}" alt="App background">
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

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <h6 class="fw-bold mb-1">Custom floating effects</h6>
                                <p class="text-muted small mb-0">Edit each effect here, then save the page. Delete removes it from the next saved configuration.</p>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshEffectsPreview"><i data-feather="refresh-cw" class="me-1" style="width:14px;height:14px"></i>Refresh preview</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addCustomEffect"><i data-feather="plus" class="me-1" style="width:14px;height:14px"></i>Add effect</button>
                            </div>
                        </div>
                        <div id="customEffectsList">
                            @foreach(old('custom_effects', $config['custom_effects'] ?? []) as $index => $effect)
                                <div class="aa-custom-effect" data-custom-effect>
                                    <input type="hidden" name="custom_effects[{{ $index }}][id]" value="{{ $effect['id'] ?? 'effect-'.$index }}">
                                    <input type="text" class="form-control form-control-sm" name="custom_effects[{{ $index }}][name]" value="{{ $effect['name'] ?? '' }}" maxlength="60" placeholder="Effect name" aria-label="Effect name" required>
                                    <input type="text" class="form-control form-control-sm aa-custom-effect-emoji" name="custom_effects[{{ $index }}][emoji]" value="{{ $effect['emoji'] ?? '' }}" maxlength="16" placeholder="✨" aria-label="Floating emoji" required>
                                    <label class="form-check form-switch aa-effect-enabled m-0"><input class="form-check-input" type="checkbox" name="custom_effects[{{ $index }}][enabled]" value="1" @checked(old("custom_effects.$index.enabled", $effect['enabled'] ?? true))><span class="small ms-1">On</span></label>
                                    <button type="button" class="btn btn-sm btn-outline-danger aa-effect-remove" data-remove-effect aria-label="Delete effect" title="Delete effect"><i data-feather="trash-2" style="width:14px;height:14px"></i><span class="ms-1">Delete</span></button>
                                </div>
                            @endforeach
                        </div>
                        <div class="aa-floating-preview" id="customEffectsPreview" aria-label="Preview of enabled custom floating effects" aria-live="polite">
                            <span class="aa-floating-preview-label">Floating effects preview</span>
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
    const effectsList = document.getElementById('customEffectsList');
    const addEffectButton = document.getElementById('addCustomEffect');
    const refreshPreviewButton = document.getElementById('refreshEffectsPreview');
    const effectsPreview = document.getElementById('customEffectsPreview');
    let nextEffectIndex = {{ count(old('custom_effects', $config['custom_effects'] ?? [])) }};

    function refreshEffectsPreview() {
        effectsPreview.querySelectorAll('.aa-floating-preview-particle, .aa-floating-preview-empty').forEach(node => node.remove());
        const enabledEmojis = Array.from(effectsList.querySelectorAll('[data-custom-effect]'))
            .filter(row => row.querySelector('.aa-effect-enabled input')?.checked)
            .map(row => row.querySelector('.aa-custom-effect-emoji')?.value.trim())
            .filter(Boolean);

        if (enabledEmojis.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'aa-floating-preview-empty';
            empty.textContent = 'Enable a custom effect to preview it here.';
            effectsPreview.appendChild(empty);
            return;
        }

        for (let index = 0; index < 18; index++) {
            const particle = document.createElement('span');
            particle.className = 'aa-floating-preview-particle';
            particle.textContent = enabledEmojis[index % enabledEmojis.length];
            particle.style.setProperty('--left', `${Math.random() * 92 + 2}%`);
            particle.style.setProperty('--size', `${18 + Math.random() * 18}px`);
            particle.style.setProperty('--duration', `${3.5 + Math.random() * 2.5}s`);
            particle.style.setProperty('--delay', `${Math.random() * 2.5}s`);
            effectsPreview.appendChild(particle);
        }
    }

    function addEffect(effect = {}) {
        const index = nextEffectIndex++;
        const id = effect.id || `custom-${Date.now()}-${index}`;
        const row = document.createElement('div');
        row.className = 'aa-custom-effect';
        row.dataset.customEffect = '';
        const fields = [
            `<input type="hidden" name="custom_effects[${index}][id]" value="${escapeHtml(id)}">`,
            `<input type="text" class="form-control form-control-sm" name="custom_effects[${index}][name]" value="${escapeHtml(effect.name || '')}" maxlength="60" placeholder="Effect name" aria-label="Effect name" required>`,
            `<input type="text" class="form-control form-control-sm aa-custom-effect-emoji" name="custom_effects[${index}][emoji]" value="${escapeHtml(effect.emoji || '✨')}" maxlength="16" placeholder="✨" aria-label="Floating emoji" required>`,
            `<label class="form-check form-switch aa-effect-enabled m-0"><input class="form-check-input" type="checkbox" name="custom_effects[${index}][enabled]" value="1" ${effect.enabled === false ? '' : 'checked'}><span class="small ms-1">On</span></label>`,
            '<button type="button" class="btn btn-sm btn-outline-danger aa-effect-remove" data-remove-effect aria-label="Delete effect" title="Delete effect"><i data-feather="trash-2" style="width:14px;height:14px"></i><span class="ms-1">Delete</span></button>'
        ];
        row.innerHTML = fields.join('');
        effectsList.appendChild(row);
        if (window.feather) window.feather.replace();
        row.querySelector('input[name$="[name]"]').focus();
        refreshEffectsPreview();
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    }

    addEffectButton.addEventListener('click', () => addEffect());
    refreshPreviewButton.addEventListener('click', refreshEffectsPreview);
    effectsList.addEventListener('input', event => {
        if (event.target.matches('.aa-custom-effect-emoji')) refreshEffectsPreview();
    });
    effectsList.addEventListener('change', event => {
        if (event.target.matches('.aa-effect-enabled input')) refreshEffectsPreview();
    });
    effectsList.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-effect]');
        if (button) {
            button.closest('[data-custom-effect]').remove();
            refreshEffectsPreview();
        }
    });

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
    refreshEffectsPreview();

    if (removeBox) {
        removeBox.addEventListener('change', function () {
            if (this.checked) fileInput.value = '';
        });
    }
})();
</script>
@endsection
