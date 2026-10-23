@can('employee.profile.view')
    @can($printPermission)
        <a class="btn btn-outline-primary btn-sm ms-1" href="{{ route('admin.employees.profile.show', ['employee' => $requestRecord->requested_by, 'tab' => $printTab, $printRequestField => $requestRecord->id] + (request('request_modal') === '1' ? ['request_modal' => '1'] : [])) }}" title="{{ __('index.print_saved_request') }}"
           @if($printTab === 'leave-letter' && request('request_modal') !== '1') data-open-leave-request-form @endif>
            <i class="link-icon" data-feather="printer"></i><span class="visually-hidden">{{ __('index.print_saved_request') }}</span>
        </a>
    @endcan
@endcan
