@extends('layouts.master')

@section('title', 'Edit Certificate - កែប្រែវិញ្ញាបនបត្រ')

@section('action', 'Edit Certificate')

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">Certificates</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit {{ $certificate->credential_id }}</li>
        </ol>
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
            <i class="link-icon" data-feather="arrow-left"></i> Back to Directory
        </a>
    </nav>

    <form action="{{ route('admin.certificates.update', $certificate->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="file-text" class="me-1"></i> Edit Certificate: {{ $certificate->credential_id }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Title</label>
                                <input type="text" name="khmer_title" class="form-control" value="{{ old('khmer_title', $certificate->khmer_title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Title <span class="text-danger">*</span></label>
                                <input type="text" name="certificate_title" class="form-control" value="{{ old('certificate_title', $certificate->certificate_title) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Recipient Name</label>
                                <input type="text" name="khmer_recipient_name" class="form-control" value="{{ old('khmer_recipient_name', $certificate->khmer_recipient_name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name', $certificate->recipient_name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Recipient Role/Title</label>
                                <input type="text" name="khmer_recipient_title" class="form-control" value="{{ old('khmer_recipient_title', $certificate->khmer_recipient_title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Recipient Role/Title</label>
                                <input type="text" name="recipient_title" class="form-control" value="{{ old('recipient_title', $certificate->recipient_title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Course/Achievement Title</label>
                                <input type="text" name="khmer_course_or_achievement_title" class="form-control" value="{{ old('khmer_course_or_achievement_title', $certificate->khmer_course_or_achievement_title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Course/Achievement Title <span class="text-danger">*</span></label>
                                <input type="text" name="course_or_achievement_title" class="form-control" value="{{ old('course_or_achievement_title', $certificate->course_or_achievement_title) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Citation</label>
                                <textarea name="khmer_citation" rows="3" class="form-control">{{ old('khmer_citation', $certificate->khmer_citation) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Citation</label>
                                <textarea name="citation" rows="3" class="form-control">{{ old('citation', $certificate->citation) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">Metadata & Accreditation</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Training Hours</label>
                            <input type="number" step="0.5" name="training_hours" class="form-control" value="{{ old('training_hours', $certificate->training_hours) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Accreditation Status</label>
                            <input type="text" name="compliance_status" class="form-control" value="{{ old('compliance_status', $certificate->compliance_status) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Issue Date (English)</label>
                            <input type="text" name="issue_date" class="form-control" value="{{ old('issue_date', $certificate->issue_date) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Issue Date (Khmer)</label>
                            <input type="text" name="khmer_issue_date" class="form-control" value="{{ old('khmer_issue_date', $certificate->khmer_issue_date) }}">
                        </div>

                        <div class="d-grid gap-2 pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="check"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.certificates.show', $certificate->credential_id) }}" target="_blank" class="btn btn-outline-secondary">
                                <i data-feather="eye"></i> Preview Certificate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection
