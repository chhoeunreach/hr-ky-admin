<div class="row">
    {{-- Rule Name --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="name" class="form-label"><b>{{ __('index.rule_name') ?? 'ឈ្មោះវិធានការ (Rule Name)' }}</b> <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('name') is-invalid @enderror"
               id="name"
               name="name"
               value="{{ old('name', $rule->name ?? '') }}"
               placeholder="ឧ. ជូនដំណឹងសុំច្បាប់សាខាទួលគោក ឬ ការលាលែងថ្នាក់ដឹកនាំ"
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Notification Event --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="event_key" class="form-label"><b>{{ __('index.event') ?? 'ព្រឹត្តិការណ៍ (HR Event)' }}</b> <span class="text-danger">*</span></label>
        <select class="form-select @error('event_key') is-invalid @enderror" id="event_key" name="event_key" required>
            <option value="" disabled {{ old('event_key', $rule->event_key ?? '') ? '' : 'selected' }}>-- ជ្រើសរើសព្រឹត្តិការណ៍ (Select Event) --</option>
            @foreach($categorizedEvents as $catKey => $catData)
                <optgroup label="📁 {{ $catData['label'] }}">
                    @foreach($catData['events'] as $evtKey => $evtLabel)
                        <option value="{{ $evtKey }}" {{ old('event_key', $rule->event_key ?? '') === $evtKey ? 'selected' : '' }}>
                            {{ $evtLabel }} ({{ $evtKey }})
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('event_key')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Branch Scope --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.branch_scope') ?? 'វិសាលភាពសាខា (Branch Scope)' }}</b> <span class="text-danger">*</span></label>
        @php
            $currentScope = old('branch_scope', $rule->branch_scope ?? 'all');
        @endphp
        <div class="d-flex align-items-center mt-2">
            <div class="form-check me-4">
                <input class="form-check-input" type="radio" name="branch_scope" id="scope_all" value="all" {{ $currentScope === 'all' ? 'checked' : '' }}>
                <label class="form-check-label" for="scope_all">
                    🌐 គ្រប់សាខាទាំងអស់ (All Branches)
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="branch_scope" id="scope_specific" value="specific" {{ $currentScope === 'specific' ? 'checked' : '' }}>
                <label class="form-check-label" for="scope_specific">
                    🏢 សាខាជាក់លាក់ (Specific Branches)
                </label>
            </div>
        </div>
    </div>

    {{-- Specific Branches Multi-select (Hidden if scope is 'all') --}}
    <div class="col-lg-6 col-md-6 mb-3" id="specificBranchesContainer" style="{{ $currentScope === 'specific' ? '' : 'display: none;' }}">
        <label for="branch_ids" class="form-label"><b>{{ __('index.select_branches') ?? 'ជ្រើសរើសសាខា (Select Branches)' }}</b></label>
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
        <small class="text-muted">អាចជ្រើសរើសសាខាមួយ ឬច្រើន។</small>
    </div>

    {{-- Target Telegram Channels (The Core Decoupled Feature!) --}}
    <div class="col-12 mb-3">
        <label class="form-label">
            <b>{{ __('index.target_channels') ?? 'ទិសដៅទទួលសារ (Target Telegram Channels / Groups)' }}</b> <span class="text-danger">*</span>
        </label>
        <div class="card bg-light border p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">ជ្រើសរើស Channels ឬ Supergroup Topics ដែលត្រូវទទួលដំណឹងនៅពេលមានព្រឹត្តិការណ៍នេះកើតឡើង៖</span>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-primary me-1" id="btnSelectAllChannels">ជ្រើសរើសទាំងអស់</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btnDeselectAllChannels">ដោះចេញទាំងអស់</button>
                </div>
            </div>

            @php
                $selectedChannels = (array) old('channel_ids', $rule->channel_ids ?? []);
            @endphp

            @if($channels->isEmpty())
                <div class="alert alert-warning mb-0">
                    <i class="link-icon" data-feather="alert-circle"></i>
                    មិនទាន់មាន Telegram Channel ណាមួយត្រូវបានចុះឈ្មោះឡើយ។ សូមចូលទៅកាន់ទំព័រ
                    <a href="{{ route('admin.telegram-groups.create') }}" class="alert-link fw-bold">Telegram Channels</a> ដើម្បីបង្កើតមុនសិន។
                </div>
            @else
                <div class="row g-2">
                    @foreach($channels as $ch)
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-2 bg-white channel-item h-100">
                                <div class="form-check">
                                    <input class="form-check-input channel-checkbox"
                                           type="checkbox"
                                           name="channel_ids[]"
                                           value="{{ $ch->id }}"
                                           id="channel_{{ $ch->id }}"
                                           {{ in_array($ch->id, $selectedChannels) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 cursor-pointer" for="channel_{{ $ch->id }}">
                                        <div class="fw-bold text-dark">{{ $ch->name }}</div>
                                        <div class="small text-muted">
                                            Chat ID: <code>{{ $ch->chat_id }}</code>
                                            @if($ch->topic_id)
                                                <span class="badge bg-secondary ms-1">#Topic: {{ $ch->topic_id }}</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @error('channel_ids')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Notification Payload & Delivery Options --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.delivery_options') ?? 'ជម្រើសបន្ថែមនៃការផ្ញើ (Delivery Options)' }}</b></label>
        <div class="border rounded p-3 bg-white">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input"
                       type="checkbox"
                       id="send_location"
                       name="send_location"
                       value="1"
                       {{ old('send_location', $rule->send_location ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="send_location">
                    📍 ផ្ញើផែនទី GPS (Send Location Pin)
                </label>
            </div>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input"
                       type="checkbox"
                       id="send_selfie"
                       name="send_selfie"
                       value="1"
                       {{ old('send_selfie', $rule->send_selfie ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="send_selfie">
                    📷 ផ្ញើរូបថត Selfie (Send Photo)
                </label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       id="is_silent"
                       name="is_silent"
                       value="1"
                       {{ old('is_silent', $rule->is_silent ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_silent">
                    🔕 ផ្ញើបែបស្ងាត់ៗ គ្មានសំឡេងរំខាន (Silent Alert)
                </label>
            </div>
        </div>
    </div>

    {{-- Active Status & Description --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.status') ?? 'ស្ថានភាពដំណើរការ (Status)' }}</b></label>
        <div class="border rounded p-3 bg-white mb-2">
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    ✅ បើកដំណើរការវិធានការនេះ (Rule Active)
                </label>
            </div>
        </div>

        <label for="description" class="form-label"><b>{{ __('index.description') ?? 'កំណត់ចំណាំ (Description)' }}</b></label>
        <textarea class="form-control"
                  id="description"
                  name="description"
                  rows="2"
                  placeholder="ពិពណ៌នាបន្ថែមអំពីគោលបំណងនៃវិធានការនេះ...">{{ old('description', $rule->description ?? '') }}</textarea>
    </div>

    {{-- Submit Buttons --}}
    <div class="col-12 mt-3 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="check-circle"></i>
            {{ isset($rule) ? __('index.update') : __('index.save') }}
        </button>
        <a href="{{ route('admin.telegram-rules.index') }}" class="btn btn-secondary ms-2">
            {{ __('index.cancel') }}
        </a>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            // Select2 Init
            if ($("#event_key").length) {
                $("#event_key").select2({ width: '100%' });
            }
            if ($("#branch_ids").length) {
                $("#branch_ids").select2({ width: '100%', placeholder: 'ជ្រើសរើសសាខា...' });
            }

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
                $('.channel-checkbox').prop('checked', true);
            });
            $('#btnDeselectAllChannels').on('click', function () {
                $('.channel-checkbox').prop('checked', false);
            });
        });
    </script>
@endsection
