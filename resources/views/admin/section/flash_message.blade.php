@if (isset($errors) && $errors->any())
    <div class="alert alert-danger app-flash-message" role="alert">
        <i class="icon icon-alert-circle" aria-hidden="true"></i>
        <div class="app-flash-message-content">
            <strong class="app-flash-message-title">{{ __('index.dialog_validation_title') }}</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@foreach ([
    'success' => ['class' => 'success', 'icon' => 'check-circle', 'title' => 'dialog_success_title'],
    'danger' => ['class' => 'danger', 'icon' => 'alert-circle', 'title' => 'dialog_error_title'],
    'info' => ['class' => 'info', 'icon' => 'info', 'title' => 'dialog_info_title'],
    'warning' => ['class' => 'warning', 'icon' => 'alert-triangle', 'title' => 'dialog_warning_title'],
] as $messageType => $presentation)
    @if (session()->has($messageType))
        <div class="alert alert-{{ $presentation['class'] }} app-flash-message {{ session()->has($messageType . '_important') ? 'alert-important' : '' }}"
             role="{{ in_array($messageType, ['danger', 'warning']) ? 'alert' : 'status' }}">
            <i class="icon icon-{{ $presentation['icon'] }}" aria-hidden="true"></i>
            <div class="app-flash-message-content">
                <strong class="app-flash-message-title">{{ __('index.' . $presentation['title']) }}</strong>
                <span>{{ session($messageType) }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('index.close') }}"></button>
        </div>
    @endif
@endforeach
