@can('social_reward.view')
<li class="nav-item {{ request()->routeIs('admin.social-marketing.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.social-marketing.index') }}"><i class="link-icon" data-feather="share-2"></i><span class="link-title">Social Marketing</span></a></li>
@endcan
