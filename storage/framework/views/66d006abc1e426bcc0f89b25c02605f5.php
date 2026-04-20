
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_team_meeting')): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.team-meetings.*')  ? 'active' : ''); ?>">
        <a
            href="<?php echo e(route('admin.team-meetings.index')); ?>"
            data-href="<?php echo e(route('admin.team-meetings.index')); ?>"
            class="nav-link">
            <i class="link-icon" data-feather="globe"></i>
            <span class="link-title"><?php echo e(__('index.team_meeting')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/team-meeting.blade.php ENDPATH**/ ?>