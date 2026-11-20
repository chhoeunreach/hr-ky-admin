@if(session('saved_request.type') === $savedRequestType)
    <link rel="stylesheet" href="{{ asset('assets/css/employee-leave-print.css') }}">
    <div class="employee-request-saved-banner" role="status">
        <h4>{{ __('index.request_saved_heading') }} <span class="fw-normal">#{{ session('saved_request.id') }}</span></h4>
        <p>{{ __('index.request_saved_next_help') }}</p>
        <div class="d-flex align-items-center gap-2 mt-2">
            @if(session('saved_request.print_url'))
                <button type="button" class="btn btn-success btn-sm quick-print-leave" data-print-url="{{ session('saved_request.print_url') }}">
                    <i class="link-icon me-1" data-feather="printer"></i> {{ __('index.print') ?? 'Print Form' }}
                </button>
            @endif
            @can('employee.profile.view')
                @can($printPermission)
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => session('saved_request.employee_id'), 'tab' => $printTab, $printRequestField => session('saved_request.id')] + (request('request_modal') === '1' ? ['request_modal' => '1'] : [])) }}"
                       @if($printTab === 'leave-letter' && request('request_modal') !== '1') data-open-leave-request-form @endif>{{ __('index.preview_saved_request') }}</a>
                @endcan
            @endcan
        </div>
    </div>
    @if($savedRequestType === 'leave' && request('request_modal') === '1')
        <script>
            if (window.parent !== window) {
                window.parent.postMessage({type: 'employee-request-form-updated', requestType: 'leave'}, window.location.origin);
            }
        </script>
    @endif
@endif
