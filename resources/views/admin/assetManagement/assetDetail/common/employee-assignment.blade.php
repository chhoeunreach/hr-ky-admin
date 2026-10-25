@php
    $currentAssignment = $assetDetail->latestAssignment;
    $hasCurrentAssignment = $currentAssignment && !$currentAssignment->returned_date;
@endphp
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h6 class="mb-2">{{ __('index.employee') }}</h6>
            <p class="mb-0">{{ $hasCurrentAssignment ? ($currentAssignment->user?->name ?: __('index.not_available')) : __('index.asset_no_employee') }}</p>
            <p class="text-muted small mb-0 mt-1">{{ __('index.asset_edit_assignment_help') }}</p>
        </div>
        @if(auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('assign_asset'))
            @if(!$hasCurrentAssignment && $assetDetail->is_working === 'yes' && $assetDetail->is_available)
                <button type="button" class="assignAsset btn btn-primary"
                        data-options-url="{{ route('admin.asset-assignment.options', $assetDetail->id) }}"
                        data-href="{{ route('admin.asset-assignment.store', $assetDetail->id) }}">
                    <i data-feather="user-plus"></i> {{ __('index.assign_asset') }}
                </button>
            @elseif($hasCurrentAssignment)
                @if($assetDetail->is_working === 'yes')
                    <button type="button" class="assignAsset btn btn-primary"
                        data-options-url="{{ route('admin.asset-assignment.options', $assetDetail->id) }}"
                        data-assignment-mode="transfer" data-href="{{ route('admin.asset-assignment.employee', $assetDetail->id) }}">{{ __('index.asset_change_employee') }}</button>
                @endif
                @can('print_assets')
                <a class="btn btn-outline-primary" data-open-asset-modal href="{{ route('admin.assets.receipt', $assetDetail->id) }}"><i data-feather="printer"></i> {{ __('index.asset_print_receipt') }}</a>
                @endcan
                @can('asset_assign_list')
                <a class="btn btn-outline-secondary" data-open-asset-modal href="{{ route('admin.asset-assignment.index', $assetDetail->id) }}">{{ __('index.assignment_history_return') }}</a>
                @endcan
            @else
                <span class="text-muted small">{{ __('index.asset_assign_condition_help') }}</span>
            @endif
        @endif
    </div>
</div>
