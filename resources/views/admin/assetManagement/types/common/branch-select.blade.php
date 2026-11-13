@php
    $typeBranches = isset($companyDetail) ? $companyDetail->branches : ($branch ?? collect());
    $ownBranchId = \App\Helpers\AppHelper::canAccessAllBranches() ? null : auth()->user()?->branch_id;
    $selectedBranch = old('branch_id', $assetTypeDetail->branch_id ?? null);
@endphp
<label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
<select class="form-select" id="branch_id" name="branch_id" aria-describedby="assetTypeBranchHelp">
    <option value="" @selected(!$selectedBranch)>{{ __('index.asset_all_branches') }}</option>
    @foreach($typeBranches as $typeBranch)
        @if(!$ownBranchId || (string) $typeBranch->id === (string) $ownBranchId)
            <option value="{{ $typeBranch->id }}" @selected((string) $selectedBranch === (string) $typeBranch->id)>{{ $typeBranch->name }}</option>
        @endif
    @endforeach
</select>
<div class="form-text" id="assetTypeBranchHelp">{{ __('index.asset_type_branch_help') }}</div>
