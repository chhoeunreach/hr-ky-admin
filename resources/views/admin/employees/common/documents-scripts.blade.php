@can('employee.document.manage')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/vendors/tesseract/tesseract.min.js') }}"></script>
    <script src="{{ asset('assets/js/cambodian-id-ocr.js') }}?v={{ filemtime(public_path('assets/js/cambodian-id-ocr.js')) }}"></script>
    <script src="{{ asset('assets/js/employee-form-documents.js') }}?v={{ filemtime(public_path('assets/js/employee-form-documents.js')) }}"></script>
@endcan
