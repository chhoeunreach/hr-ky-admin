@can('view_task_list')
    <li class="nav-item {{ request()->routeIs('admin.tasks.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.tasks.index') }}"
            data-href="{{ route('admin.tasks.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="check-square"></i>
            <span class="link-title">{{__('index.task_management')}}</span>
        </a>
    </li>
@endcan

