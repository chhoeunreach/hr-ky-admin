@php
    $assetAllowsAllBranches = \App\Helpers\AppHelper::canAccessAllBranches();
    $assetOwnBranchId = auth('admin')->check() || $assetAllowsAllBranches ? null : auth()->user()?->branch_id;
    $assetBranchOptions = $assetOwnBranchId
        ? \App\Models\Branch::withoutGlobalScopes()->whereKey($assetOwnBranchId)->get(['id', 'name'])
        : (isset($companyDetail) ? $companyDetail->branches()->get() : collect());
    $assetSelectedBranchId = $assetOwnBranchId ?? (($assetBranchFilter ?? false)
        ? ($filterParameters['branch_id'] ?? null)
        : old('branch_id', $assetDetail->branch_id ?? null));
@endphp
<label for="branch_id" class="form-label">{{ __('index.branch') }} @unless($assetBranchFilter ?? false)<span class="text-danger">*</span>@endunless</label>
<select class="form-select" id="branch_id" name="branch_id" @unless($assetBranchFilter ?? false) required @endunless>
    @unless($assetOwnBranchId)
        <option value="" @selected(!$assetSelectedBranchId) @unless($assetBranchFilter ?? false) disabled @endunless>{{ ($assetBranchFilter ?? false) ? __('index.all') : __('index.select_branch') }}</option>
    @endunless
    @foreach($assetBranchOptions as $assetBranchOption)
        <option value="{{ $assetBranchOption->id }}" @selected((string) $assetSelectedBranchId === (string) $assetBranchOption->id)>{{ ucfirst($assetBranchOption->name) }}</option>
    @endforeach
</select>
@error('branch_id')<div class="text-danger small" role="alert">{{ $message }}</div>@enderror
