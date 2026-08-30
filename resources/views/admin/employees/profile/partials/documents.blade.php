@can('employee.document.manage')
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.documents.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Document</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Type</label><select class="form-control" name="document_type" id="addDocumentType">@foreach(['national_id','employment_contract','cv','certificate','salary_letter','promotion_letter','warning_letter','performance_review','training_certificate','other'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-3"><label class="form-label">Title</label><input class="form-control" name="title" id="addDocumentTitle" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">Document Date</label><input class="form-control" type="date" name="document_date"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Expiry Date</label><input class="form-control" type="date" name="expiry_date"></div>
            <div class="col-md-2 mb-3"><label class="form-label">File</label><input class="form-control" type="file" name="file"></div>
            <div class="col-md-12 mb-3"><label class="form-label">Note</label><textarea class="form-control" name="note" rows="2" id="addDocumentNote"></textarea></div>
        </div>
        <button class="btn btn-primary">Add Document</button>
    </form>
@endcan
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>Type</th><th>Title</th><th>Date</th><th>Expiry</th><th>File</th><th>Note</th></tr></thead><tbody>
@forelse($documents as $record)<tr><td>{{ $record->document_type }}</td><td>{{ $record->title }}</td><td>{{ optional($record->document_date)->format('Y-m-d') }}</td><td>{{ optional($record->expiry_date)->format('Y-m-d') }}</td><td>@if($record->file_path)<a href="{{ route('admin.employees.profile.documents.download', [$employee->id, $record->id]) }}">Download</a>@else N/A @endif</td><td>{{ $record->note }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var typeSel = document.getElementById('addDocumentType');
        var titleInput = document.getElementById('addDocumentTitle');
        var noteInput = document.getElementById('addDocumentNote');
        if (!typeSel || !titleInput) return;

        var employeeName = @json($employee->name);
        var nationalId = @json($profile->national_id ?? '');

        function autoFill() {
            if (typeSel.value !== 'national_id') return;
            if (!titleInput.value.trim()) {
                titleInput.value = 'National ID - ' + employeeName;
            }
            if (nationalId && !noteInput.value.trim()) {
                noteInput.value = 'National ID Number: ' + nationalId;
            }
        }

        typeSel.addEventListener('change', autoFill);
    });
</script>
