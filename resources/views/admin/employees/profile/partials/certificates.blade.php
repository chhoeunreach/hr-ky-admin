<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold">
        <i data-feather="award" class="me-2 text-primary"></i>Certificates & Accreditations (វិញ្ញាបនបត្រ)
    </h6>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.certificates.studio', ['employee_id' => $employee->id]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i data-feather="layout" class="me-1"></i> CertiCraft Studio 🎨
        </a>
        <a href="{{ route('admin.certificates.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary btn-sm">
            <i data-feather="plus" class="me-1"></i> Issue Certificate (ចេញវិញ្ញាបនបត្រ)
        </a>
    </div>
</div>

@php
    $empCertificates = \App\Models\Certificate::where('user_id', $employee->id)
        ->orWhere('employee_id', $employee->employee_code)
        ->latest('id')
        ->get();
@endphp

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
            <tr>
                <th>Credential ID</th>
                <th>Certificate Title</th>
                <th>Category</th>
                <th>Digital HR Hours</th>
                <th>Issue Date</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($empCertificates as $cert)
            <tr>
                <td class="font-monospace fw-bold text-primary">
                    <span class="badge bg-light text-dark border">
                        {{ $cert->credential_id }}
                    </span>
                </td>
                <td>
                    @if($cert->khmer_title)
                        <div class="fw-bold text-dark">{{ $cert->khmer_title }}</div>
                    @endif
                    <div class="small text-muted">{{ $cert->certificate_title }}</div>
                </td>
                <td>
                    @if($cert->category === 'leader')
                        <span class="badge bg-warning text-dark">Leadership</span>
                    @elseif($cert->category === 'service')
                        <span class="badge bg-info text-dark">Service</span>
                    @elseif($cert->category === 'course')
                        <span class="badge bg-success">CPD Training</span>
                    @else
                        <span class="badge bg-secondary">Honor</span>
                    @endif
                </td>
                <td>
                    <span class="fw-bold text-success">{{ $cert->training_hours ?? 0 }}.0 hrs</span>
                    <div class="small text-muted">{{ $cert->compliance_status ?? 'Accredited' }}</div>
                </td>
                <td>{{ $cert->issue_date }}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.certificates.show', $cert->credential_id) }}" target="_blank" class="btn btn-outline-primary" title="View Certificate">
                            <i data-feather="eye" style="width: 13px; height: 13px;"></i>
                        </a>
                        <a href="{{ route('admin.certificates.downloadPdf', $cert->credential_id) }}" class="btn btn-outline-secondary" title="Download PDF">
                            <i data-feather="download" style="width: 13px; height: 13px;"></i>
                        </a>
                        <a href="{{ route('certificates.verify', $cert->credential_id) }}" target="_blank" class="btn btn-outline-success" title="Verify QR">
                            <i data-feather="check-circle" style="width: 13px; height: 13px;"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i data-feather="award" class="mb-2" style="width: 32px; height: 32px;"></i>
                    <p class="mb-0">No certificates issued to this employee yet.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
