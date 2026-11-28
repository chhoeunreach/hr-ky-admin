<div class="row">
    {{-- Rule Name --}}
    <div class="col-lg-7 col-md-6 mb-3">
        <label for="name" class="form-label"><b>{{ __('index.rule_name') }}</b> <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('name') is-invalid @enderror"
               id="name"
               name="name"
               value="{{ old('name', $rule->name ?? '') }}"
               placeholder="{{ __('index.rule_name_placeholder') }}"
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Active Status & Silent Mode in top row --}}
    <div class="col-lg-5 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.status') }}</b></label>
        <div class="d-flex align-items-center gap-3 p-2 border rounded bg-white" style="min-height: 42px;">
            <div class="form-check form-switch m-0">
                <input class="form-check-input"
                       type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-success cursor-pointer" for="is_active">
                    {{ __('index.active') }}
                </label>
            </div>
            <div class="vr"></div>
            <div class="form-check form-switch m-0">
                <input class="form-check-input"
                       type="checkbox"
                       id="is_silent"
                       name="is_silent"
                       value="1"
                       {{ old('is_silent', $rule->is_silent ?? false) ? 'checked' : '' }}>
                <label class="form-check-label text-muted cursor-pointer small" for="is_silent">
                    🔕 {{ __('index.silent_alert') }}
                </label>
            </div>
        </div>
    </div>

    {{-- FULL Notification Events Matrix --}}
    <div class="col-12 mb-4">
        <div class="card border shadow-none" style="background-color: #fafbfe; border-radius: 10px;">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-2.5 border-bottom">
                <div class="d-flex align-items-center">
                    <i data-feather="bell" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
                    <strong class="fs-6">{{ __('index.notification_events_matrix') }} <span class="text-danger">*</span></strong>
                </div>
                <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-select-all-events">
                        <i data-feather="check-square" class="me-1" style="width: 14px; height: 14px;"></i> {{ __('index.select_all') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-deselect-all-events">
                        <i data-feather="square" class="me-1" style="width: 14px; height: 14px;"></i> {{ __('index.clear_all') }}
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-3">
                    {{ __('index.notification_events_matrix_subtitle') }}
                </p>

                @php
                    $categorizedOptions = $categorizedEvents ?? \App\Models\TelegramGroup::categorizedEventOptions();
                    $selectedEventKeys = (array) old('event_keys', isset($rule) ? $rule->event_keys_list : []);
                @endphp

                <div class="row g-3">
                    @foreach($categorizedOptions as $catKey => $catData)
                        <div class="col-xl-6 col-lg-12">
                            <div class="border rounded p-3 h-100 bg-white shadow-sm event-category-card">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input category-toggle" type="checkbox" id="cat_{{ $catKey }}" data-category="{{ $catKey }}">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer d-flex align-items-center" for="cat_{{ $catKey }}">
                                            <i data-feather="{{ $catData['icon'] ?? 'folder' }}" class="text-primary me-2" style="width: 16px; height: 16px;"></i>
                                            {{ $catData['label'] }}
                                        </label>
                                    </div>
                                    <span class="badge bg-secondary cat-badge" id="badge_{{ $catKey }}">0/{{ count($catData['events']) }}</span>
                                </div>
                                <div class="row g-2 pt-1">
                                    @foreach($catData['events'] as $eventKey => $eventLabel)
                                        <div class="col-md-6 col-12">
                                            <div class="form-check">
                                                <input class="form-check-input event-checkbox event-cat-{{ $catKey }}"
                                                       type="checkbox"
                                                       name="event_keys[]"
                                                       value="{{ $eventKey }}"
                                                       id="evt_{{ $eventKey }}"
                                                       data-category="{{ $catKey }}"
                                                       {{ in_array($eventKey, $selectedEventKeys, true) ? 'checked' : '' }}>
                                                <label class="form-check-label small cursor-pointer" for="evt_{{ $eventKey }}">
                                                    {{ $eventLabel }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('event_keys')
                    <div class="text-danger small mt-2 d-block fw-semibold">
                        <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i> {{ $message }}
                    </div>
                @enderror
                @error('event_key')
                    <div class="text-danger small mt-1 d-block fw-semibold">
                        <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i> {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Target Telegram Channels (Search & Multi-Select Dropdown) --}}
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="channel_ids" class="form-label mb-0">
                <b>{{ __('index.target_channels') }}</b> <span class="text-danger">*</span>
            </label>
            @if($channels->isNotEmpty())
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllChannels">
                        <i class="link-icon" data-feather="check-square" style="width: 12px; height: 12px;"></i> {{ __('index.select_all') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btnDeselectAllChannels">
                        <i class="link-icon" data-feather="x-square" style="width: 12px; height: 12px;"></i> {{ __('index.clear_all') }}
                    </button>
                </div>
            @endif
        </div>

        @php
            $selectedChannels = (array) old('channel_ids', $rule->channel_ids ?? []);
        @endphp

        @if($channels->isEmpty())
            <div class="alert alert-warning mb-0">
                <i class="link-icon" data-feather="alert-circle"></i>
                {{ __('index.no_channel') }}. <a href="{{ route('admin.telegram-groups.create') }}" class="alert-link fw-bold">{{ __('index.add_telegram_group') }}</a>
            </div>
        @else
            <select class="form-select @error('channel_ids') is-invalid @enderror"
                    id="channel_ids"
                    name="channel_ids[]"
                    multiple="multiple"
                    data-placeholder="{{ __('index.select_target_channels') }}"
                    required>
                @foreach($channels as $ch)
                    <option value="{{ $ch->id }}"
                            {{ in_array($ch->id, $selectedChannels) ? 'selected' : '' }}>
                        {{ $ch->name }} (Chat ID: {{ $ch->chat_id }}{{ $ch->topic_id ? ' | #Topic: ' . $ch->topic_id : '' }})
                    </option>
                @endforeach
            </select>
            <div class="form-text text-muted small mt-1">
                <i class="link-icon" data-feather="info" style="width: 12px; height: 12px;"></i>
                {{ __('index.target_channels_hint') }}
            </div>
        @endif

        @error('channel_ids')
            <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>
        @enderror
    </div>

    {{-- Branch Scope --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.branch_scope') }}</b> <span class="text-danger">*</span></label>
        @php
            $currentScope = old('branch_scope', $rule->branch_scope ?? 'all');
        @endphp
        <div class="d-flex align-items-center mt-2 p-2 border rounded bg-white">
            <div class="form-check me-4">
                <input class="form-check-input" type="radio" name="branch_scope" id="scope_all" value="all" {{ $currentScope === 'all' ? 'checked' : '' }}>
                <label class="form-check-label cursor-pointer" for="scope_all">
                    🌐 {{ __('index.all_branches') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="branch_scope" id="scope_specific" value="specific" {{ $currentScope === 'specific' ? 'checked' : '' }}>
                <label class="form-check-label cursor-pointer" for="scope_specific">
                    🏢 {{ __('index.specific_branches') }}
                </label>
            </div>
        </div>
    </div>

    {{-- Specific Branches Multi-select (Hidden if scope is 'all') --}}
    <div class="col-lg-6 col-md-6 mb-3" id="specificBranchesContainer" style="{{ $currentScope === 'specific' ? '' : 'display: none;' }}">
        <label for="branch_ids" class="form-label"><b>{{ __('index.select_branches') }}</b></label>
        @php
            $selectedBranches = (array) old('branch_ids', $rule->branch_ids ?? []);
        @endphp
        <select class="form-select" id="branch_ids" name="branch_ids[]" multiple="multiple">
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ in_array($branch->id, $selectedBranches) ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">{{ __('index.select_branches_hint') }}</small>
    </div>

    {{-- Department Field (Optional / Specific Departments) --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="department_ids" class="form-label mb-0">
                <b>{{ __('index.department') }}</b>
                <span class="text-muted small fw-normal">({{ __('index.optional') }})</span>
            </label>
            @if(isset($departments) && $departments->isNotEmpty())
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllDepts">
                        <i class="link-icon" data-feather="check-square" style="width: 11px; height: 11px;"></i> {{ __('index.select_all') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btnDeselectAllDepts">
                        <i class="link-icon" data-feather="x-square" style="width: 11px; height: 11px;"></i> {{ __('index.clear_all') }}
                    </button>
                </div>
            @endif
        </div>
        @php
            $selectedDepts = (array) old('department_ids', $rule->department_ids ?? []);
        @endphp
        <select class="form-select @error('department_ids') is-invalid @enderror"
                id="department_ids"
                name="department_ids[]"
                multiple="multiple"
                data-placeholder="{{ __('index.select_departments') }}">
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ in_array($dept->id, $selectedDepts) ? 'selected' : '' }}>
                    {{ $dept->dept_name }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted small mt-1">
            <i class="link-icon" data-feather="info" style="width: 12px; height: 12px;"></i>
            {{ __('index.department_rule_hint') }}
        </div>
        @error('department_ids')
            <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>
        @enderror
    </div>

    {{-- Delivery Options --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.delivery_options') }}</b></label>
        <div class="border rounded p-3 bg-white">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input"
                       type="checkbox"
                       id="send_location"
                       name="send_location"
                       value="1"
                       {{ old('send_location', $rule->send_location ?? true) ? 'checked' : '' }}>
                <label class="form-check-label cursor-pointer" for="send_location">
                    📍 {{ __('index.send_location_pin') }}
                </label>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input"
                       type="checkbox"
                       id="send_selfie"
                       name="send_selfie"
                       value="1"
                       {{ old('send_selfie', $rule->send_selfie ?? true) ? 'checked' : '' }}>
                <label class="form-check-label cursor-pointer" for="send_selfie">
                    📷 {{ __('index.send_photo_selfie') }}
                </label>
            </div>
        </div>
    </div>

    {{-- Description Notes --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="description" class="form-label"><b>{{ __('index.description') }}</b></label>
        <textarea class="form-control"
                  id="description"
                  name="description"
                  rows="3"
                  placeholder="{{ __('index.rule_desc_placeholder') }}">{{ old('description', $rule->description ?? '') }}</textarea>
    </div>

    {{-- Submit Buttons --}}
    <div class="col-12 mt-3 mb-3 d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="link-icon" data-feather="check-circle"></i>
            {{ isset($rule) ? __('index.update') : __('index.save') }}
        </button>
        <button type="button" class="btn btn-secondary px-3" onclick="if(window.parent !== window){try{window.parent.bootstrap.Modal.getInstance(window.parent.document.getElementById('telegramRuleActionModal')).hide();}catch(e){window.location.href='{{ route('admin.telegram-rules.index') }}';}}else{window.location.href='{{ route('admin.telegram-rules.index') }}';}">
            {{ __('index.cancel') }}
        </button>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            // Channel & Branch Select2 Init
            if ($("#channel_ids").length) {
                $("#channel_ids").select2({
                    width: '100%',
                    placeholder: $("#channel_ids").data('placeholder') || '{{ __('index.select_target_channels') }}',
                    allowClear: true,
                    closeOnSelect: false
                });
            }
            if ($("#branch_ids").length) {
                $("#branch_ids").select2({ width: '100%', placeholder: '{{ __('index.select_branches') }}...' });
            }
            if ($("#department_ids").length) {
                $("#department_ids").select2({
                    width: '100%',
                    placeholder: $("#department_ids").data('placeholder') || '{{ __('index.select_departments') }}',
                    allowClear: true,
                    closeOnSelect: false
                });
            }

            // Select All / Deselect All Departments
            $('#btnSelectAllDepts').on('click', function () {
                $('#department_ids > option').prop('selected', true);
                $('#department_ids').trigger('change');
            });
            $('#btnDeselectAllDepts').on('click', function () {
                $('#department_ids').val(null).trigger('change');
            });

            // Toggle Specific Branches
            $('input[name="branch_scope"]').on('change', function () {
                if ($(this).val() === 'specific') {
                    $('#specificBranchesContainer').slideDown(200);
                } else {
                    $('#specificBranchesContainer').slideUp(200);
                }
            });

            // Select All / Deselect All Channels
            $('#btnSelectAllChannels').on('click', function () {
                $('#channel_ids > option').prop('selected', true);
                $('#channel_ids').trigger('change');
            });
            $('#btnDeselectAllChannels').on('click', function () {
                $('#channel_ids').val(null).trigger('change');
            });

            // Notification Events Matrix State Management
            function updateCategoryState(catKey) {
                var total = $('.event-cat-' + catKey).length;
                var checked = $('.event-cat-' + catKey + ':checked').length;
                var $catToggle = $('#cat_' + catKey);
                var $badge = $('#badge_' + catKey);

                $badge.text(checked + '/' + total);
                if (checked === total && total > 0) {
                    $catToggle.prop('checked', true);
                    $catToggle.prop('indeterminate', false);
                    $badge.removeClass('bg-secondary bg-warning').addClass('bg-success');
                } else if (checked > 0) {
                    $catToggle.prop('checked', false);
                    $catToggle.prop('indeterminate', true);
                    $badge.removeClass('bg-secondary bg-success').addClass('bg-warning text-dark');
                } else {
                    $catToggle.prop('checked', false);
                    $catToggle.prop('indeterminate', false);
                    $badge.removeClass('bg-success bg-warning text-dark').addClass('bg-secondary');
                }
            }

            function updateAllCategories() {
                $('.category-toggle').each(function () {
                    var catKey = $(this).data('category');
                    updateCategoryState(catKey);
                });
            }

            $('.category-toggle').on('change', function () {
                var catKey = $(this).data('category');
                var isChecked = $(this).is(':checked');
                $('.event-cat-' + catKey).prop('checked', isChecked);
                updateCategoryState(catKey);
            });

            $('.event-checkbox').on('change', function () {
                var catKey = $(this).data('category');
                updateCategoryState(catKey);
            });

            $('#btn-select-all-events').on('click', function () {
                $('.event-checkbox').prop('checked', true);
                $('.category-toggle').prop('checked', true).prop('indeterminate', false);
                updateAllCategories();
            });

            $('#btn-deselect-all-events').on('click', function () {
                $('.event-checkbox').prop('checked', false);
                $('.category-toggle').prop('checked', false).prop('indeterminate', false);
                updateAllCategories();
            });

            // Initial load calculation
            updateAllCategories();
        });
    </script>
@endsection
