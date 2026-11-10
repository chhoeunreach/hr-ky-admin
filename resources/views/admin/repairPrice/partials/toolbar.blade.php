@php
    $repairToolbars = [
        'prices' => [__('index.repair_price_matrix'), 'dollar-sign', 'addPriceModal', ['repair_price.create', 'repair_price.manage_prices']],
        'brands' => [__('index.brands'), 'tag', 'addBrandModal', ['repair_price.manage_brands']],
        'categories' => [__('index.categories'), 'grid', 'addCategoryModal', ['repair_price.manage_categories']],
        'devices' => [__('index.repair_devices'), 'smartphone', 'addDeviceModal', ['repair_price.manage_devices']],
        'services' => [__('index.repair_services'), 'tool', 'addServiceModal', ['repair_price.manage_services']],
        'types' => [__('repair.device_types'), 'layers', 'addCatalogtypesModal', ['repair_price.manage_devices']],
        'series' => [__('repair.device_series'), 'list', 'addCatalogseriesModal', ['repair_price.manage_devices']],
    ];
    [$repairTitle, $repairIcon, $repairModal, $repairAbilities] = $repairToolbars[$repairEntity];
    $repairFormId = 'repair-'.$repairEntity.'-form';
@endphp
<form id="{{ $repairFormId }}" action="{{ route('admin.repair-price.index') }}" method="get" hidden>
    <input type="hidden" name="active_tab" value="{{ $repairEntity }}">
    @foreach(['brand_id', 'device_id', 'category_id'] as $parameter)
        <input type="hidden" name="{{ $parameter }}" value="{{ $filterParameters[$parameter] ?? '' }}">
    @endforeach
</form>
@include('admin.section.directory-toolbar', [
    'directoryTable' => 'repair-'.$repairEntity.'-table',
    'directoryForm' => $repairFormId,
    'directorySearch' => 'repair-'.$repairEntity.'-search',
    'directoryRecords' => $repairRecords,
    'directoryTitle' => $repairTitle,
    'directoryIcon' => $repairIcon,
    'directoryPermission' => $repairAbilities[0],
    'directoryCanAdd' => \Illuminate\Support\Facades\Gate::any($repairAbilities),
    'directoryAddModal' => $repairModal,
    'directoryDefaultSize' => '25',
    'directoryCurrentSize' => $filterParameters['per_page'],
    'directoryPageSizes' => [25, 50, 100, 200, 'all'],
    'directoryPageSizeId' => 'repair-'.$repairEntity.'-per-page',
    'directorySearchKey' => 'search',
    'directorySearchInForm' => true,
    'directoryLive' => false,
])
