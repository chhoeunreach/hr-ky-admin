<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">Group Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ old('name', $telegramGroup->name ?? '') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
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
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="action_keys" class="form-label">Action <span style="color: red">*</span></label>
        @php
            $selectedActions = old('action_keys', $telegramGroup->action_keys ?? [$telegramGroup->action_key ?? \App\Models\TelegramGroup::ACTION_GENERAL]);
            $selectedActions = is_array($selectedActions) ? $selectedActions : [];
        @endphp
        <select class="form-select" id="action_keys" name="action_keys[]" multiple required>
            @foreach($actionOptions as $key => $label)
                <option value="{{ $key }}" {{ in_array($key, $selectedActions, true) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <label class="form-label">Events</label>
        @php
            $selectedEvents = old('event_keys', $telegramGroup->event_keys ?? [$telegramGroup->action_key ?? \App\Models\TelegramGroup::ACTION_GENERAL]);
            $selectedEvents = is_array($selectedEvents) ? $selectedEvents : [];
        @endphp
        <select class="form-select" id="event_keys" name="event_keys[]" multiple>
            @foreach($eventOptions as $key => $label)
                <option value="{{ $key }}" {{ in_array($key, $selectedEvents, true) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_ids" class="form-label">Branch</label>
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
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_ids" class="form-label">Department</label>
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
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_name" class="form-label">Branch Name Match</label>
        <input type="text" class="form-control" id="branch_name" name="branch_name"
               value="{{ old('branch_name', $telegramGroup->branch_name ?? '') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_name" class="form-label">Department Name Match</label>
        <input type="text" class="form-control" id="department_name" name="department_name"
               value="{{ old('department_name', $telegramGroup->department_name ?? '') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="send_for_all" class="form-label">Send For All</label>
        <select class="form-select" id="send_for_all" name="send_for_all">
            <option value="0" {{ (string) old('send_for_all', isset($telegramGroup) ? (int) $telegramGroup->send_for_all : 0) === '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ (string) old('send_for_all', isset($telegramGroup) ? (int) $telegramGroup->send_for_all : 0) === '1' ? 'selected' : '' }}>Yes</option>
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
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $telegramGroup->description ?? '') }}</textarea>
    </div>

    <div class="col-lg-6 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i> {{ isset($telegramGroup) ? __('index.update') : __('index.create') }}
        </button>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#chat_ids').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
            $('#action_keys').select2();
            $('#event_keys').select2();
            $('#branch_ids').select2({
                placeholder: 'All Branches'
            });
            $('#department_ids').select2({
                placeholder: 'All Departments'
            });

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
