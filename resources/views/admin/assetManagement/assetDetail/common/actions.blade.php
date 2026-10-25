@canany(['print_assets', 'show_asset', 'edit_assets', 'delete_assets', 'asset_assign_list'])
<td class="text-center asset-actions-cell">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                id="assetActions{{ $value->id }}" data-bs-toggle="dropdown" data-asset-actions
                aria-expanded="false" aria-label="{{ __('index.action') }}: {{ $value->name }}">
            {{ __('index.action') }}
        </button>
        <ul class="dropdown-menu asset-actions-menu" aria-labelledby="assetActions{{ $value->id }}">
            @can('print_assets')
                <li>
                    @if($value->latestAssignment && !$value->latestAssignment->returned_date)
                        <a class="dropdown-item" data-open-asset-modal href="{{ route('admin.assets.receipt', $value->id) }}">
                            <i data-feather="printer"></i> {{ __('index.print') }}
                        </a>
                    @else
                        <span class="dropdown-item disabled" aria-disabled="true" title="{{ __('index.asset_no_employee') }}">
                            <i data-feather="printer"></i> {{ __('index.print') }}
                        </span>
                    @endif
                </li>
            @endcan
            @can('show_asset')
                <li><button class="dropdown-item" type="button" onclick="showAssetDetails('{{ route('admin.assets.show', $value->id) }}')">
                    <i data-feather="eye"></i> {{ __('index.view') }}
                </button></li>
            @endcan
            @can('edit_assets')
                <li><a class="dropdown-item" data-open-asset-modal href="{{ route('admin.assets.edit', $value->id) }}">
                    <i data-feather="edit"></i> {{ __('index.edit') }}
                </a></li>
            @endcan
            @can('asset_assign_list')
                <li><a class="dropdown-item" data-open-asset-modal href="{{ route('admin.asset-assignment.index', $value->id) }}">
                    <i data-feather="clock"></i> {{ __('index.history') }}
                </a></li>
            @endcan
            @can('delete_assets')
                <li><button class="dropdown-item text-danger delete" type="button"
                            data-title="{{ $value->name }}" data-href="{{ route('admin.assets.delete', $value->id) }}">
                    <i data-feather="trash-2"></i> {{ __('index.delete') }}
                </button></li>
            @endcan
        </ul>
    </div>
</td>
@endcanany
