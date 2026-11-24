<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="name" class="form-label">Group Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ old('name', $telegramGroup->name ?? '') }}">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="chat_ids" class="form-label">Telegram Chat ID <span style="color: red">*</span></label>
        @php
            $selectedChatIds = old('chat_ids', $telegramGroup->chat_ids ?? (isset($telegramGroup->chat_id) ? [$telegramGroup->chat_id] : []));
            $selectedChatIds = is_array($selectedChatIds) ? $selectedChatIds : [];
            $chatOptions = array_values(array_unique(array_merge($chatOptions ?? [], $selectedChatIds)));
        @endphp
        <select class="form-select" id="chat_ids" name="chat_ids[]" multiple required>
            @foreach($chatOptions as $chatId)
                <option value="{{ $chatId }}" {{ in_array($chatId, $selectedChatIds, true) ? 'selected' : '' }}>{{ $chatId }}</option>
            @endforeach
        </select>
        <div class="form-text small">អាចបញ្ចូល Chat ID ដោយផ្ទាល់ រួចចុច Enter ឬ Comma</div>
    </div>

    <div class="col-lg-12 mb-4">
        <div class="card border shadow-none" style="background-color: #fafbfe;">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-2 border-bottom">
                <div class="d-flex align-items-center">
                    <i data-feather="bell" class="me-2 text-primary" style="width: 18px; height: 18px;"></i>
                    <strong class="fs-6">ជ្រើសរើស Actions & Events សម្រាប់ផ្ញើសារ (Notification Events Matrix) <span style="color: red">*</span></strong>
                </div>
                <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-select-all-events">
                        <i data-feather="check-square" class="me-1" style="width: 14px; height: 14px;"></i> Select All (ជ្រើសទាំងអស់)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-deselect-all-events">
                        <i data-feather="square" class="me-1" style="width: 14px; height: 14px;"></i> Clear All (លុបទាំងអស់)
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-3">
                    សូមជ្រើសរើសប្រភេទ Events ឬ Actions ជាក់លាក់ដែលអ្នកចង់ឱ្យ Telegram Group នេះទទួលបានការជូនដំណឹង។ អ្នកអាចធីកតាមក្រុមទាំងមូល ឬជ្រើសរើស Event ដាច់ដោយឡែក។
                </p>

                <div class="row g-3">
                    @php
                        $categorizedOptions = $categorizedEvents ?? \App\Models\TelegramGroup::categorizedEventOptions();
                        $selectedEvents = old('event_keys', $telegramGroup->event_keys ?? []);
                        $selectedActions = old('action_keys', $telegramGroup->action_keys ?? (isset($telegramGroup->action_key) ? [$telegramGroup->action_key] : []));
                        $selectedEvents = is_array($selectedEvents) ? $selectedEvents : [];
                        $selectedActions = is_array($selectedActions) ? $selectedActions : [];
                    @endphp

                    @foreach($categorizedOptions as $catKey => $catData)
                        <div class="col-xl-6 col-lg-12">
                            <div class="border rounded p-3 h-100 bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input category-toggle" type="checkbox" id="cat_{{ $catKey }}" data-category="{{ $catKey }}">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer d-flex align-items-center" for="cat_{{ $catKey }}">
                                            <i data-feather="{{ $catData['icon'] ?? 'folder' }}" class="text-primary me-2" style="width: 16px; height: 16px;"></i>
                                            {{ $catData['label'] }}
                                        </label>
                                    </div>
                                    <input type="checkbox" name="action_keys[]" value="{{ $catData['action_key'] }}" id="action_{{ $catData['action_key'] }}" class="d-none action-input" {{ in_array($catData['action_key'], $selectedActions, true) ? 'checked' : '' }}>
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
                                                       data-action="{{ $catData['action_key'] }}"
                                                       {{ in_array($eventKey, $selectedEvents, true) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="evt_{{ $eventKey }}">
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
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_ids" class="form-label">Branch (សាខា)</label>
        @php
            $selectedBranches = old('branch_ids', $telegramGroup->branch_ids ?? (isset($telegramGroup->branch_id) ? [$telegramGroup->branch_id] : []));
            $selectedBranches = is_array($selectedBranches) ? array_map('strval', $selectedBranches) : [];
        @endphp
        <select class="form-select" id="branch_ids" name="branch_ids[]" multiple>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ in_array((string) $branch->id, $selectedBranches, true) ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text small">ទុកទទេ ប្រសិនបើចង់ទទួលគ្រប់សាខា</div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_ids" class="form-label">Department (ដេប៉ាតឺម៉ង់)</label>
        @php
            $selectedDepartments = old('department_ids', $telegramGroup->department_ids ?? (isset($telegramGroup->department_id) ? [$telegramGroup->department_id] : []));
            $selectedDepartments = is_array($selectedDepartments) ? array_map('strval', $selectedDepartments) : [];
        @endphp
        <select class="form-select" id="department_ids" name="department_ids[]" multiple>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" data-branch-id="{{ $department->branch_id }}"
                    {{ in_array((string) $department->id, $selectedDepartments, true) ? 'selected' : '' }}>
                    {{ $department->dept_name }}
                </option>
            @endforeach
        </select>
        <div class="form-text small">ទុកទទេ ប្រសិនបើចង់ទទួលគ្រប់ដេប៉ាតឺម៉ង់</div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="send_for_all" class="form-label">Send For All (ផ្ញើគ្រប់សាខាដោយមិនកំណត់)</label>
        <select class="form-select" id="send_for_all" name="send_for_all">
            <option value="0" {{ (string) old('send_for_all', isset($telegramGroup) ? (int) $telegramGroup->send_for_all : 0) === '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ (string) old('send_for_all', isset($telegramGroup) ? (int) $telegramGroup->send_for_all : 0) === '1' ? 'selected' : '' }}>Yes</option>
        </select>
    </div>

    <input type="hidden" id="branch_name" name="branch_name" value="{{ old('branch_name', $telegramGroup->branch_name ?? '') }}">
    <input type="hidden" id="department_name" name="department_name" value="{{ old('department_name', $telegramGroup->department_name ?? '') }}">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="send_location" class="form-label">Send Map (ផ្ញើទីតាំង GPS)</label>
        <select class="form-select" id="send_location" name="send_location">
            <option value="1" {{ (string) old('send_location', isset($telegramGroup) ? (int) ($telegramGroup->send_location ?? 1) : 1) === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (string) old('send_location', isset($telegramGroup) ? (int) ($telegramGroup->send_location ?? 1) : 1) === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="send_selfie" class="form-label">Send Selfie Photo (ផ្ញើរូបភាព Selfie)</label>
        <select class="form-select" id="send_selfie" name="send_selfie">
            <option value="1" {{ (string) old('send_selfie', isset($telegramGroup) ? (int) ($telegramGroup->send_selfie ?? 1) : 1) === '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (string) old('send_selfie', isset($telegramGroup) ? (int) ($telegramGroup->send_selfie ?? 1) : 1) === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="is_active" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="is_active" name="is_active">
            <option value="1" {{ (string) old('is_active', isset($telegramGroup) ? (int) $telegramGroup->is_active : 1) === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
            <option value="0" {{ (string) old('is_active', isset($telegramGroup) ? (int) $telegramGroup->is_active : 1) === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <label for="description" class="form-label">Description (ការពិពណ៌នាបន្ថែម)</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="បញ្ជាក់អំពីគោលបំណងនៃ Group នេះ...">{{ old('description', $telegramGroup->description ?? '') }}</textarea>
    </div>

    <div class="col-lg-12 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <button type="submit" class="btn btn-primary">
                <i class="link-icon" data-feather="save"></i> {{ isset($telegramGroup) ? __('index.update') : __('index.create') }}
            </button>
            <a href="{{ route('admin.telegram-groups.index') }}" class="btn btn-secondary ms-2">
                <i class="link-icon" data-feather="x"></i> {{ __('index.cancel') }}
            </a>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info" id="btnSendTestMessage">
                <i class="link-icon" data-feather="send"></i> ផ្ញើសារសាកល្បង (Send Test Message)
            </button>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            $('#btnSendTestMessage').on('click', function () {
                var btn = $(this);
                var chatIds = $('#chat_ids').val() || [];
                var name = $('#name').val() || '';
                var id = '{{ $telegramGroup->id ?? "" }}';

                if (!chatIds || chatIds.length === 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'សូមបញ្ចូល Chat ID',
                            text: 'សូមបញ្ចូលយ៉ាងហោចណាស់មួយ Telegram Chat ID ជាមុនសិន!',
                            confirmButtonText: 'យល់ព្រម'
                        });
                    } else {
                        alert('សូមបញ្ចូលយ៉ាងហោចណាស់មួយ Telegram Chat ID ជាមុនសិន!');
                    }
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> កំពុងផ្ញើសារ...');

                $.ajax({
                    url: '{{ route("admin.telegram-groups.send-test") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id,
                        name: name,
                        chat_ids: chatIds
                    },
                    success: function (res) {
                        btn.prop('disabled', false).html('<i class="link-icon" data-feather="send"></i> ផ្ញើសារសាកល្បង (Send Test Message)');
                        if (typeof feather !== 'undefined') feather.replace();
                        var msg = res.message || 'សារសាកល្បងត្រូវបានផ្ញើទៅកាន់ Telegram Group ដោយជោគជ័យ!';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ជោគជ័យ!',
                                text: msg,
                                confirmButtonText: 'យល់ព្រម'
                            });
                        } else {
                            alert(msg);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="link-icon" data-feather="send"></i> ផ្ញើសារសាកល្បង (Send Test Message)');
                        if (typeof feather !== 'undefined') feather.replace();
                        var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'ការផ្ញើសារសាកល្បងមិនជោគជ័យ។ សូមពិនិត្យមើល Bot Token និង Chat ID!';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'បរាជ័យ!',
                                text: err,
                                confirmButtonText: 'យល់ព្រម'
                            });
                        } else {
                            alert(err);
                        }
                    }
                });
            });

            $('#chat_ids').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
            $('#branch_ids').select2({
                placeholder: 'All Branches'
            });
            $('#department_ids').select2({
                placeholder: 'All Departments'
            });

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

                var $actionInput = $('.event-cat-' + catKey).first().data('action');
                if ($actionInput) {
                    $('#action_' + $actionInput).prop('checked', checked > 0);
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
                $('.action-input').prop('checked', true);
                updateAllCategories();
            });

            $('#btn-deselect-all-events').on('click', function () {
                $('.event-checkbox').prop('checked', false);
                $('.category-toggle').prop('checked', false).prop('indeterminate', false);
                $('.action-input').prop('checked', false);
                updateAllCategories();
            });

            updateAllCategories();

            function filterDepartments() {
                var branchIds = $('#branch_ids').val() || [];
                $('#department_ids option').each(function () {
                    var optionBranchId = $(this).data('branch-id');
                    if (!optionBranchId || branchIds.length === 0 || branchIds.includes(String(optionBranchId))) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            $('#branch_ids').on('change', function () {
                filterDepartments();
                var selectedBranchNames = $('#branch_ids option:selected').map(function () {
                    return $(this).text().trim();
                }).get();
                $('#branch_name').val(selectedBranchNames.join(','));
            });

            $('#department_ids').on('change', function () {
                var selectedDepartmentNames = $('#department_ids option:selected').map(function () {
                    return $(this).text().trim();
                }).get();
                $('#department_name').val(selectedDepartmentNames.join(','));
            });

            filterDepartments();
        });
    </script>
@endsection
