<div class="modal fade" id="previewDocumentModal" tabindex="-1" aria-labelledby="previewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw; height: 90vh;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span id="previewCategoryBadge" class="badge bg-primary"></span>
                    <h6 class="modal-title fw-bold mb-0 text-truncate" id="previewDocumentModalLabel" style="max-width: 500px;"></h6>
                    <span id="previewStatusBadge"></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewDownloadBtn" class="btn btn-sm btn-outline-primary" download>
                        <i class="link-icon me-1" data-feather="download"></i> {{ __('index.download') }}
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printPreviewFrame()">
                        <i class="link-icon me-1" data-feather="printer"></i> {{ __('index.print') }}
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex justify-content-center align-items-center" style="height: calc(100% - 50px); background: #1e293b; overflow: hidden;">
                <!-- Loading indicator -->
                <div id="previewLoading" class="position-absolute text-white text-center">
                    <div class="spinner-border text-light mb-2" role="status"></div>
                    <div>{{ __('index.loading_document') }}</div>
                </div>

                <!-- PDF Frame -->
                <iframe id="previewPdfFrame" src="" style="width: 100%; height: 100%; border: 0; display: none;"></iframe>

                <!-- Image Viewer -->
                <div id="previewImageWrapper" style="width: 100%; height: 100%; display: none; overflow: auto; text-align: center; padding: 20px;">
                    <img id="previewImage" src="" alt="Document Preview" style="max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 4px 20px rgba(0,0,0,0.5); border-radius: 4px;">
                </div>

                <!-- Unsupported / Download Prompt -->
                <div id="previewUnsupported" class="text-white text-center p-4" style="display: none;">
                    <i class="link-icon mb-3" data-feather="file" style="width: 50px; height: 50px;"></i>
                    <h5>{{ __('index.unsupported_preview_format') }}</h5>
                    <p class="text-muted mb-3" id="previewUnsupportedName"></p>
                    <a href="#" id="previewFallbackDownloadBtn" class="btn btn-primary">
                        <i class="link-icon me-1" data-feather="download"></i> {{ __('index.click_to_download') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function printPreviewFrame() {
        const iframe = document.getElementById('previewPdfFrame');
        if (iframe && iframe.style.display !== 'none' && iframe.contentWindow) {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                window.open(iframe.src, '_blank');
            }
        } else {
            const img = document.getElementById('previewImage');
            if (img && img.src) {
                const win = window.open('', '_blank');
                win.document.write('<html><head><title>Print Document</title><style>body{margin:0;display:flex;justify-content:center;align-items:center;}img{max-width:100%;height:auto;}</style></head><body><img src="' + img.src + '" onload="window.print();window.close();"></body></html>');
                win.document.close();
            }
        }
    }
</script>
