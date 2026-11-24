
@canany(['list_office_time'])
    @php
        $isShiftSection = request()->routeIs('admin.office-times.*');
    @endphp
    <li class="nav-item {{ $isShiftSection ? 'active' : '' }}">
        <a class="nav-link {{ $isShiftSection ? '' : 'collapsed' }}" data-href="#" data-bs-toggle="collapse" href="#shiftManagement" role="button" aria-expanded="{{ $isShiftSection ? 'true' : 'false' }}" aria-controls="shiftManagement">
            <i class="link-icon" data-feather="clock"></i>
            <span class="link-title"> {{ __('index.shift_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isShiftSection ? 'show' : '' }}" id="shiftManagement">
             <ul class="nav sub-menu">

                 @can('list_office_time')
                    <li class="nav-item">
                        <a href="{{route('admin.office-times.index')}}"
                           data-href="{{route('admin.office-times.index')}}"
                           class="nav-link {{request()->routeIs('admin.office-times.*') ? 'active' : ''}}">{{ __('index.office_time') }} </a>
                    </li>
                 @endcan

            </ul>
        </div>
    </li>
@endcanany
