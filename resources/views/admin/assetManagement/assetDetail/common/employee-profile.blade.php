@if($employee)
    @php
        $canViewProfile = auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('employee.profile.view');
    @endphp
    @if($canViewProfile)
        <a class="asset-employee-profile" data-open-asset-modal href="{{ route('admin.employees.profile.show', $employee->id) }}">
    @else
        <div class="asset-employee-profile">
    @endif
        <img class="asset-employee-avatar" src="{{ $employee->avatar_url }}" alt="" width="40" height="40" loading="lazy"
             onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">
        <span>{{ $employee->name ?: __('index.not_available') }}</span>
    @if($canViewProfile)
        </a>
    @else
        </div>
    @endif
@else
    {{ __('index.not_available') }}
@endif
