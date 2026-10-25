<table id="assetsTable" class="table assets-professional-table">
    <thead>
    <tr>
        @canany(['print_assets', 'show_asset', 'edit_assets', 'delete_assets', 'asset_assign_list'])
            <th data-asset-column="actions" class="text-center asset-actions-cell">{{ __('index.asset_column_actions') }}</th>
        @endcanany
        <th data-asset-column="employee" class="asset-employee-cell" aria-sort="{{ request('sort') === 'employee' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'employee', 'label' => __('index.employee')])</th>
        <th data-asset-column="name" aria-sort="{{ request('sort') === 'name' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'name', 'label' => __('index.asset_column_name')])</th>
        <th data-asset-column="phone_numbers" aria-sort="{{ request('sort') === 'phone_numbers' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'phone_numbers', 'label' => __('index.asset_column_phone')])</th>
        <th data-asset-column="icloud" title="{{ __('index.asset_icloud') }}">{{ __('index.asset_column_icloud') }}</th>
        <th data-asset-column="asset_password">{{ __('index.asset_column_password') }}</th>
        <th data-asset-column="screen_time_passcode" title="{{ __('index.asset_screen_time_passcode') }}">{{ __('index.asset_column_screen_time') }}</th>
        <th data-asset-column="set_by">{{ __('index.asset_column_set_by') }}</th>
        <th data-asset-column="asset_code" aria-sort="{{ request('sort') === 'asset_code' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'asset_code', 'label' => __('index.asset_column_code')])</th>
        <th data-asset-column="asset_serial_no" aria-sort="{{ request('sort') === 'asset_serial_no' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'asset_serial_no', 'label' => __('index.asset_column_serial')])</th>
        <th data-asset-column="description" aria-sort="{{ request('sort') === 'description' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'description', 'label' => __('index.description')])</th>
        <th data-asset-column="type" class="text-center" aria-sort="{{ request('sort') === 'type' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'type', 'label' => __('index.type')])</th>
        <th data-asset-column="is_working" class="text-center" aria-sort="{{ request('sort') === 'is_working' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'is_working', 'label' => __('index.asset_column_working')])</th>
        <th data-asset-column="is_available" class="text-center" aria-sort="{{ request('sort') === 'is_available' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'is_available', 'label' => __('index.asset_column_available')])</th>
        @can('assign_asset')
            <th data-asset-column="assigned_to" class="text-center" aria-sort="{{ request('sort') === 'assigned_to' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'assigned_to', 'label' => __('index.asset_column_assignment')])</th>
        @endcan

    </tr>
    </thead>
    <tbody>
    @forelse($assetLists as $key => $value)
        <tr data-asset-row>
            @include('admin.assetManagement.assetDetail.common.actions')
            <td class="asset-employee-cell">@include('admin.assetManagement.assetDetail.common.employee-profile', ['employee' => $value->latestAssignment && !$value->latestAssignment->returned_date ? $value->latestAssignment->user : null])</td>
            <td>{{ucfirst($value->name)}}</td>
            <td class="asset-phone-number">@include('admin.assetManagement.assetDetail.common.phone-number', ['phoneNumber' => $value->phone_numbers])</td>
            <td class="asset-account-cell"><span title="{{ $value->icloud }}">{{ $value->icloud ?: '—' }}</span></td>
            <td><span class="asset-secret-status">{{ $value->getRawOriginal('asset_password') ? '••••••••' : '—' }}</span></td>
            <td><span class="asset-secret-status">{{ $value->getRawOriginal('screen_time_passcode') ? '••••' : '—' }}</span></td>
            <td>{{ $value->setBy?->name ?: '—' }}</td>
            <td>{{ $value->asset_code ?: __('index.not_available') }}</td>
            <td class="asset-serial-cell"><span title="{{ $value->asset_serial_no }}">{{ $value->asset_serial_no ? mb_substr($value->asset_serial_no, 0, 16).(mb_strlen($value->asset_serial_no) > 16 ? '…' : '') : __('index.not_available') }}</span></td>
            <td class="asset-description-cell">@include('admin.assetManagement.assetDetail.common.description', ['note' => $value->note])</td>
            <td class="text-center">
                @if($value->type)
                    <a data-open-asset-modal href="{{ route('admin.asset-types.show', $value->type_id) }}">{{ ucfirst($value->type->name) }}</a>
                @else
                    {{ __('index.not_available') }}
                @endif
            </td>

            <td class="text-center"><span class="badge {{ $value->is_working === 'yes' ? 'asset-status-good' : 'asset-status-muted' }}">{{ __('index.'.$value->is_working) }}</span></td>

            <td class="text-center">
                <span class="badge {{ $value->is_available ? 'asset-status-good' : 'asset-status-muted' }}">{{ $value->is_available ? __('index.yes') : __('index.no') }}</span>
            </td>
            @can('assign_asset')
                <td class="text-center">

                    @if($value->latestAssignment && !$value->latestAssignment->returned_date)
                        <span class="badge bg-secondary">{{ __('index.asset_already_assigned') }}</span>
                        @if($value->is_working === 'yes')
                            <button type="button" class="assignAsset btn btn-sm btn-outline-primary mt-2"
                                data-options-url="{{ route('admin.asset-assignment.options', $value->id) }}"
                                data-assignment-mode="transfer" data-href="{{ route('admin.asset-assignment.employee', $value->id) }}">{{ __('index.asset_change_employee') }}</button>
                        @endif
                        @can('asset_assign_list')
                            <a class="d-block small mt-2" data-open-asset-modal href="{{ route('admin.asset-assignment.index', $value->id) }}">{{ __('index.assignment_history_return') }}</a>
                        @endcan
                    @elseif($value->is_working === 'yes' && $value->is_available == 1)
                        <button type="button" class="assignAsset btn btn-sm btn-info"
                           data-options-url="{{ route('admin.asset-assignment.options', $value->id) }}"
                           data-id="{{ $value->id }}"
                           data-branch-id="{{ $value->branch_id }}"
                           data-href="{{ route('admin.asset-assignment.store', $value->id) }}">
                            {{ __('index.assign_to_user') }}
                        </button>
                    @else
                        <span class="text-muted small d-block">{{ $value->is_working !== 'yes' ? __('index.asset_not_working_assignment') : __('index.asset_unavailable_assignment') }}</span>
                        @can('edit_assets')
                            <a class="d-block small mt-2" data-open-asset-modal href="{{ route('admin.assets.edit', $value->id) }}">{{ __('index.asset_edit_availability') }}</a>
                        @endcan
                    @endif

                </td>
            @endcan

        </tr>
    @empty
        <tr>
            <td colspan="100%">
                <p class="text-center"><b>{{  __('index.no_records_found') }}</b></p>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
