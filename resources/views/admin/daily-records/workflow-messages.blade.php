@if (session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3 shadow-sm border-0" role="status">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-3 shadow-sm border-0" role="alert">
        <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
        <ul class="mb-0 ps-2">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
