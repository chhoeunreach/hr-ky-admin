@if(request('request_modal') === '1')
    @section('embedded', '1')
    @section('styles')
        <style>
            .page-wrapper { margin-left: 0 !important; width: 100% !important; }
            .page-content { padding: 1rem !important; margin-top: 0 !important; }
            .content { margin: 0 !important; }
        </style>
    @endsection
@endif
