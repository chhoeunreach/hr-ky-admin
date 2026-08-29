<div class="table-responsive">
    <table class="table table-sm employee-complete-table">
        <thead>
        <tr>
            @foreach($columns as $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                @foreach($columns as $field => $label)
                    @php $value = data_get($record, $field); @endphp
                    <td>
                        @if($value instanceof \Carbon\CarbonInterface)
                            {{ $value->format('Y-m-d') }}
                        @else
                            {{ filled($value) ? $value : '________________' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) }}" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
