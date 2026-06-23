<table>
    <thead>
    <tr>
        <th>id</th>
        <th>name</th>
        <th>branch_id</th>
        <th>branch</th>
        <th>gender</th>
        <th>is_paid</th>
        <th>leave_allocated</th>
        <th>is_active</th>
    </tr>
    </thead>
    <tbody>
    @foreach($types as $key => $data)
        <tr>
            <td>{{$data->id}}</td>
            <td>{{ $data->name }}</td>
            <td>{{ $data->branch_id }}</td>
            <td>{{ $data->branch?->name }}</td>
            <td>{{ $data->gender }}</td>
            <td>{{ $data->leave_allocated ? 1 : 0 }}</td>
            <td>{{ $data->leave_allocated }}</td>
            <td>{{ $data->is_active ? 1 : 0 }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
