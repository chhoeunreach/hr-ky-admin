@if (isset($errors) && count($errors) > 0)
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif


@if (Session::has('success'))
    <div style="max-width: 100%" id="message"   class="alert alert-success {{Session::has('success_important') ? 'alert-important': ''}} ">
{{--        <button type="button" style="color:white !important;opacity: 1 !important;" class="close" data-dismiss="alert" aria-hidden="true">×</button>--}}
        @if(Session::has('success_important'))
        @endif
        {{session('success')}}
    </div>
@endif

@if (Session::has('danger'))
    <div style="max-width: 100%" id="message" class="alert alert-danger {{Session::has('danger_important') ? 'alert-important': ''}}">
{{--        <button type="button" style="color:white !important;opacity: 1 !important;" class="close" data-dismiss="alert" aria-hidden="true">×</button>--}}
        @if(Session::has('danger_important'))
        @endif
        {{session('danger')}}
    </div>
@endif

@if (Session::has('info'))
    <div style="max-width: 100%" id="message" class="alert alert-info {{Session::has('info_important') ? 'alert-important': ''}}">
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @if(Session::has('info_important'))
        @endif
        {{session('info')}}
    </div>
@endif

@if (Session::has('warning'))
    <div style="max-width: 100%" id="message" class="alert alert-warning {{Session::has('warning_important') ? 'alert-important': ''}}">
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @if(Session::has('warning_important'))
        @endif
        {{session('warning')}}
    </div>
@endif
