@can('employee.reward.manage')
    <form method="post" action="{{ route('admin.employees.profile.rewards.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Reward</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Date</label><input class="form-control" type="date" name="reward_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Type</label><select class="form-control" name="reward_type">@foreach(['praise','certificate','bonus','employee_of_month','achievement','promotion','other'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-3 mb-3"><label class="form-label">Amount</label><input class="form-control" type="number" step="0.01" name="reward_amount"></div>
            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">Add Reward</button>
    </form>
@endcan
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Amount</th><th>Description</th></tr></thead><tbody>
@forelse($rewards as $record)<tr><td>{{ optional($record->reward_date)->format('Y-m-d') }}</td><td>{{ $record->reward_type }}</td><td>{{ $record->title }}</td><td>{{ $record->reward_amount }}</td><td>{{ $record->description }}</td></tr>@empty<tr><td colspan="5" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
