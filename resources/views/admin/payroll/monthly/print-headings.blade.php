<colgroup>
    @foreach($printColumns as $column)<col style="width: {{ number_format($column['width'] / $totalWidth * 100, 4, '.', '') }}%">@endforeach
</colgroup>
<thead>
    <tr>@foreach($printColumns as $column)<th scope="col">{{ $column['label'] }}</th>@endforeach</tr>
</thead>
