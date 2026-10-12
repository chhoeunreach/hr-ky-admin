@php
    $dialogMessages = [
        'ok' => __('index.ok'),
        'yes' => __('index.yes'),
        'no' => __('index.no'),
        'cancel' => __('index.cancel'),
        'confirm' => __('index.dialog_confirm'),
        'delete' => __('index.dialog_delete'),
        'deleteTitle' => __('index.dialog_delete_title'),
        'confirmTitle' => __('index.dialog_confirm_title'),
        'successTitle' => __('index.dialog_success_title'),
        'errorTitle' => __('index.dialog_error_title'),
        'warningTitle' => __('index.dialog_warning_title'),
        'infoTitle' => __('index.dialog_info_title'),
        'requestFailed' => __('index.dialog_request_failed'),
        'legacySuccess' => __('index.success'),
        'legacyError' => __('index.error'),
    ];
@endphp
<script>
    window.AppDialogMessages = @json($dialogMessages);
</script>
<script src="{{ asset('assets/js/app-dialogs.js') }}?v={{ filemtime(public_path('assets/js/app-dialogs.js')) }}"></script>
