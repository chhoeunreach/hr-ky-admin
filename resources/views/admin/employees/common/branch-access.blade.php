@if(\App\Helpers\AppHelper::canManageEmployeeBranchAccess())
    <div class="col-12 mb-3">
        <input type="hidden" name="branch_access_present" value="1">
        <label for="additionalBranchAccess" class="form-label">{{ __('index.employee_additional_branches') }}</label>
        @php($additionalBranchIds = array_map('strval', (array) old('additional_branch_ids', old('branch_access_present') ? [] : ($userDetail->additional_branch_ids ?? []))))
        <select class="form-select" id="additionalBranchAccess" name="additional_branch_ids[]" multiple aria-describedby="additionalBranchAccessHelp" data-placeholder="{{ __('index.employee_additional_branches_placeholder') }}">
            @foreach($additionalAccessBranches ?? [] as $accessBranch)
                <option value="{{ $accessBranch->id }}" @selected(in_array((string) $accessBranch->id, $additionalBranchIds, true))>{{ $accessBranch->name }}</option>
            @endforeach
        </select>
        <p class="text-muted small mt-2 mb-0" id="additionalBranchAccessHelp">{{ __('index.employee_additional_branches_help') }}</p>
        @error('additional_branch_ids')<div class="text-danger small mt-1" role="alert">{{ $message }}</div>@enderror
        @error('additional_branch_ids.*')<div class="text-danger small mt-1" role="alert">{{ $message }}</div>@enderror
    </div>
@endif
