<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">Group Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ old('name', $telegramGroup->name ?? '') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="chat_id" class="form-label">Telegram Chat ID <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="chat_id" name="chat_id" required
               value="{{ old('chat_id', $telegramGroup->chat_id ?? '') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="action_key" class="form-label">Action <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="action_key" name="action_key" required
               list="telegram_action_options"
               value="{{ old('action_key', $telegramGroup->action_key ?? \App\Models\TelegramGroup::ACTION_GENERAL) }}">
        <datalist id="telegram_action_options">
            @foreach($actionOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </datalist>
    </div>

    <div class="col-lg-12 mb-4">
        <label class="form-label">Events</label>
        @php
            $selectedEvents = old('event_keys', $telegramGroup->event_keys ?? [$telegramGroup->action_key ?? \App\Models\TelegramGroup::ACTION_GENERAL]);
            $selectedEvents = is_array($selectedEvents) ? $selectedEvents : [];
        @endphp
        <div class="row">
            @foreach($eventOptions as $key => $label)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="event_keys[]" id="event_{{ $key }}" value="{{ $key }}"
                               {{ in_array($key, $selectedEvents, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="event_{{ $key }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">Branch</label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ (string) old('branch_id', $telegramGroup->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">Department</label>
        <select class="form-select" id="department_id" name="department_id">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" data-branch-id="{{ $department->branch_id }}"
                    {{ (string) old('department_id', $telegramGroup->department_id ?? '') === (string) $department->id ? 'selected' : '' }}>
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
            function filterDepartments() {
                var branchId = $('#branch_id').val();
                $('#department_id option').each(function () {
                    var optionBranchId = $(this).data('branch-id');
                    if (!optionBranchId || !branchId || String(optionBranchId) === String(branchId)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            $('#branch_id').on('change', function () {
                filterDepartments();
                var selectedBranchName = $('#branch_id option:selected').text().trim();
                if ($(this).val()) {
                    $('#branch_name').val(selectedBranchName);
                }
            });

            $('#department_id').on('change', function () {
                var selectedDepartmentName = $('#department_id option:selected').text().trim();
                if ($(this).val()) {
                    $('#department_name').val(selectedDepartmentName);
                }
            });

            filterDepartments();
        });
    </script>
@endsection
