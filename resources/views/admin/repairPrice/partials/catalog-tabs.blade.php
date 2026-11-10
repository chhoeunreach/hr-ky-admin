@can('repair_price.manage_devices')
    @foreach(['types' => $typesList, 'series' => $seriesList] as $entity => $records)
        <div class="tab-pane fade {{ $filterParameters['active_tab'] === $entity ? 'show active' : '' }}" id="{{ $entity }}Pane" role="tabpanel" aria-labelledby="{{ $entity }}-tab">
            <div class="card shadow-sm">
                @include('admin.repairPrice.partials.toolbar', ['repairEntity' => $entity, 'repairRecords' => $records])
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="repair-{{ $entity }}-table" class="table table-hover align-middle">
                            <thead><tr>
                                <th>{{ __('index.name') }}</th>
                                @if($entity === 'series')<th>{{ __('index.brand') }}</th><th>{{ __('index.device_type') }}</th>@endif
                                <th>{{ __('repair.slug') }}</th><th>{{ __('index.status') }}</th><th data-directory-actions>{{ __('index.action') }}</th>
                            </tr></thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td>{{ $record->name }}</td>
                                        @if($entity === 'series')<td>{{ $record->brand?->name }}</td><td>{{ $record->deviceType?->name }}</td>@endif
                                        <td>{{ $record->slug }}</td>
                                        <td>{{ $record->status ? __('index.active') : __('index.inactive') }}</td>
                                        <td><div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-catalog-btn" data-entity="{{ $entity }}" data-record='@json($record)' data-url="{{ route('admin.repair-price.'.$entity.'.update', $record->id) }}">{{ __('index.edit') }}</button>
                                            <form method="post" action="{{ route('admin.repair-price.'.$entity.'.delete', $record->id) }}" class="repair-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger repair-delete-btn" data-title="{{ $record->name }}">{{ __('index.delete') }}</button>
                                            </form>
                                        </div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">{{ __('index.no_records_found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    @endforeach
@endcan
