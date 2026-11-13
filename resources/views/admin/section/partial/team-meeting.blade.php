@canany(['list_team_meeting','create_team_meeting'])
    @php
        $isTeamMeetingSection = request()->routeIs('admin.team-meetings.*');
        $isEmployeeJoin = request()->routeIs('admin.team-meetings.employee-join');
    @endphp
    <li class="nav-item {{ $isTeamMeetingSection ? 'active' : '' }}">
        <a class="nav-link"
           data-bs-toggle="collapse"
           href="#teamMeetingMenu"
           data-href="#"
           role="button"
           aria-expanded="{{ $isTeamMeetingSection ? 'true' : 'false' }}"
           aria-controls="teamMeetingMenu">
            <i class="link-icon" data-feather="globe"></i>
            <span class="link-title">{{ __('index.team_meeting') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ $isTeamMeetingSection ? '' : 'collapse' }}" id="teamMeetingMenu">
            <ul class="nav sub-menu">
                @can('list_team_meeting')
                    <li class="nav-item">
                        <a href="{{ route('admin.team-meetings.employee-join') }}"
                           data-href="{{ route('admin.team-meetings.employee-join') }}"
                           class="nav-link {{ $isEmployeeJoin ? 'active' : '' }}">{{ __('index.employee_join') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.team-meetings.index') }}"
                           data-href="{{ route('admin.team-meetings.index') }}"
                           class="nav-link {{ $isTeamMeetingSection && !$isEmployeeJoin ? 'active' : '' }}">{{ __('index.team_meeting') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
