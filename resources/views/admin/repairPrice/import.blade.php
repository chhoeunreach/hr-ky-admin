@extends('layouts.master')

@section('title', __('repair.import_title'))

@section('styles')
<style>
    :root {
        --imp-bg-card: #ffffff;
        --imp-border: #e2e8f0;
        --imp-border-hover: #cbd5e1;
        --imp-text-main: #0f172a;
        --imp-text-muted: #64748b;
        --imp-dropzone-bg: #f8fafc;
        --imp-dropzone-hover: #eff6ff;
    }

    body.theme-dark {
        --imp-bg-card: #131d36;
        --imp-border: #1e293b;
        --imp-border-hover: #334155;
        --imp-text-main: #f1f5f9;
        --imp-text-muted: #94a3b8;
        --imp-dropzone-bg: #0b1329;
        --imp-dropzone-hover: #1e293b;
    }

    .import-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(14, 165, 233, 0.04) 100%);
        border: 1px solid var(--imp-border);
        border-radius: 14px;
        padding: 24px 28px;
        margin-bottom: 24px;
    }

    .import-hero-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--imp-text-main);
        margin: 0;
    }

    .import-hero-sub {
        font-size: 0.875rem;
        color: var(--imp-text-muted);
        margin-top: 4px;
        margin-bottom: 0;
    }

    .step-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .import-card {
        background: var(--imp-bg-card);
        border: 1px solid var(--imp-border);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .dropzone-box {
        border: 2px dashed #93c5fd;
        background: var(--imp-dropzone-bg);
        border-radius: 14px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .dropzone-box:hover, .dropzone-box.dragover {
        background: var(--imp-dropzone-hover);
        border-color: #2563eb;
        transform: translateY(-2px);
    }

    .dropzone-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: #eff6ff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    body.theme-dark .dropzone-icon {
        background: rgba(37, 99, 235, 0.15);
        color: #60a5fa;
    }

    .file-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(37, 99, 235, 0.08);
        border: 1px solid rgba(37, 99, 235, 0.2);
        color: #2563eb;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    body.theme-dark .file-pill {
        color: #60a5fa;
        background: rgba(37, 99, 235, 0.2);
    }

    .schema-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--imp-text-muted);
        background: transparent;
    }

    .schema-table td {
        font-size: 0.85rem;
        vertical-align: middle;
        color: var(--imp-text-main);
    }
</style>
@endsection

@section('main-content')
<div class="import-page">
    @include('admin.section.flash_message')

    <!-- Breadcrumb -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.repair.dashboard') }}">{{ __('index.repair_management') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.repair-price.index') }}">{{ __('index.repair_price') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('repair.import') }}</li>
        </ol>
        <a href="{{ route('admin.repair-price.index') }}" class="btn btn-outline-secondary btn-sm">
            <i data-feather="arrow-left" class="me-1"></i> {{ __('repair.back_to_prices') }}
        </a>
    </nav>

    <!-- Hero Header -->
    <div class="import-hero d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="import-hero-title">{{ __('repair.bulk_import') }}</h1>
            <p class="import-hero-sub">{{ __('repair.import_intro') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.repair-price.sample-template') }}" class="btn btn-primary">
                <i data-feather="download" class="me-1"></i> {{ __('repair.download_sample') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left: Upload & Process -->
        <div class="col-lg-7">
            <div class="import-card">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="step-badge">1</span>
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: var(--imp-text-main);">{{ __('repair.upload') }}</h5>
                        <small class="text-muted">{{ __('repair.choose_file') }}</small>
                    </div>
                </div>

                <form action="{{ route('admin.repair-price.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf

                    <!-- Dropzone -->
                    <div class="dropzone-box" id="dropzoneBox" onclick="document.getElementById('fileInput').click();">
                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                        <div class="dropzone-icon">
                            <i data-feather="upload-cloud" style="width: 28px; height: 28px;"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--imp-text-main);">{{ __('repair.drop_file') }}</h6>
                        <p class="text-muted small mb-3">{{ __('repair.file_support') }}</p>

                        <div class="d-flex justify-content-center gap-2">
                            <span class="file-pill"><i data-feather="file-text" style="width: 14px; height: 14px;"></i> .XLSX</span>
                            <span class="file-pill"><i data-feather="file-text" style="width: 14px; height: 14px;"></i> .XLS</span>
                            <span class="file-pill"><i data-feather="file-text" style="width: 14px; height: 14px;"></i> .CSV</span>
                        </div>

                        <!-- Selected File Preview -->
                        <div id="filePreview" class="mt-4 p-3 rounded bg-white shadow-sm border d-none" style="max-width: 400px; margin: 0 auto; text-align: left;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i data-feather="file" class="text-primary flex-shrink-0"></i>
                                    <span id="fileName" class="fw-semibold text-truncate small">filename.xlsx</span>
                                </div>
                                <span id="fileSize" class="text-muted small ms-2 flex-shrink-0">0 KB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Import Options -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="step-badge" style="background: #64748b;">2</span>
                            <div>
                                <h6 class="mb-0 fw-bold" style="color: var(--imp-text-main);">{{ __('repair.import_options') }}</h6>
                                <small class="text-muted">{{ __('repair.duplicate_options') }}</small>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="updateExisting" name="update_existing" value="1" checked>
                            <label class="form-check-label fw-semibold small" for="updateExisting">
                                {{ __('repair.auto_update') }}
                            </label>
                            <div class="text-muted small">{{ __('repair.auto_update_help') }}</div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.repair-price.index') }}" class="btn btn-secondary">{{ __('index.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <i data-feather="check" class="me-1"></i> {{ __('repair.start_import') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Instructions & Schema Guide -->
        <div class="col-lg-5">
            <div class="import-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i data-feather="help-circle" class="text-primary"></i>
                    <h5 class="mb-0 fw-bold" style="color: var(--imp-text-main);">{{ __('repair.column_guide') }}</h5>
                </div>
                <p class="text-muted small mb-3">{{ __('repair.column_guide_help') }}</p>

                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table schema-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('repair.column_header') }}</th>
                                <th>{{ __('repair.required_question') }}</th>
                                <th>{{ __('repair.example') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>brand</code></td>
                                <td><span class="badge bg-danger">{{ __('repair.required') }}</span></td>
                                <td>Apple, Samsung</td>
                            </tr>
                            <tr>
                                <td><code>model</code></td>
                                <td><span class="badge bg-danger">{{ __('repair.required') }}</span></td>
                                <td>iPhone 15 Pro Max</td>
                            </tr>
                            <tr>
                                <td><code>category</code></td>
                                <td><span class="badge bg-danger">{{ __('repair.required') }}</span></td>
                                <td>{{ __('repair.sample_categories') }}</td>
                            </tr>
                            <tr>
                                <td><code>service</code></td>
                                <td><span class="badge bg-danger">{{ __('repair.required') }}</span></td>
                                <td>{{ __('repair.sample_service') }}</td>
                            </tr>
                            <tr>
                                <td><code>selling_price</code></td>
                                <td><span class="badge bg-danger">{{ __('repair.required') }}</span></td>
                                <td>220.00</td>
                            </tr>
                            <tr>
                                <td><code>device_type</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>{{ __('repair.sample_types') }}</td>
                            </tr>
                            <tr>
                                <td><code>model_number</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>A3106, SM-S928B</td>
                            </tr>
                            <tr>
                                <td><code>category_kh</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>អេក្រង់, ថ្ម</td>
                            </tr>
                            <tr>
                                <td><code>service_kh</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>ដូរអេក្រង់, ដូរថ្ម</td>
                            </tr>
                            <tr>
                                <td><code>part_name</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>{{ __('repair.sample_part') }}</td>
                            </tr>
                            <tr>
                                <td><code>part_type</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>{{ __('repair.sample_quality') }}</td>
                            </tr>
                            <tr>
                                <td><code>part_cost</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>180.00</td>
                            </tr>
                            <tr>
                                <td><code>service_fee</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>20.00</td>
                            </tr>
                            <tr>
                                <td><code>warranty</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>{{ __('repair.sample_warranty') }}</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td><span class="badge bg-secondary">{{ __('repair.optional') }}</span></td>
                                <td>available, out_of_stock</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 p-3 rounded bg-light border">
                    <div class="d-flex align-items-start gap-2">
                        <i data-feather="info" class="text-primary mt-1 flex-shrink-0" style="width: 16px; height: 16px;"></i>
                        <p class="small text-muted mb-0"><strong>{{ __('repair.tip') }}</strong> {{ __('repair.import_tip') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const dropzoneBox = document.getElementById('dropzoneBox');

        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                filePreview.classList.remove('d-none');
                if (typeof feather !== 'undefined') feather.replace();
            }
        });

        // Drag & Drop handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneBox.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneBox.classList.remove('dragover');
            }, false);
        });

        dropzoneBox.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
@endsection
