@if(session('saved_request.type') === $savedRequestType)
    <link rel="stylesheet" href="{{ asset('assets/css/employee-leave-print.css') }}">
    <div class="employee-request-saved-banner" role="status">
        <h4>{{ __('index.request_saved_heading') }} <span class="fw-normal">#{{ session('saved_request.id') }}</span></h4>
        <p>{{ __('index.request_saved_next_help') }}</p>
        @can('employee.profile.view')
            @can($printPermission)
                <a class="btn btn-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => session('saved_request.employee_id'), 'tab' => $printTab, $printRequestField => session('saved_request.id')] + (request('request_modal') === '1' ? ['request_modal' => '1'] : [])) }}"
                   @if($printTab === 'leave-letter' && request('request_modal') !== '1') data-open-leave-request-form @endif>{{ __('index.preview_saved_request') }}</a>
            @endcan
        @endcan
    </div>
@endif
