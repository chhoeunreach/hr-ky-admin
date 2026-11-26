<div class="row mb-3">
    <div class="col-md-12">
        <ul class="nav nav-tabs nav-tabs-line" id="telegramHubTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.telegram-groups.*') ? 'active' : '' }}"
                   href="{{ route('admin.telegram-groups.index') }}"
                   style="font-size: 15px; font-weight: 500;">
                    <i class="link-icon" data-feather="message-circle" style="width: 16px; height: 16px; margin-right: 6px;"></i>
                    {{ __('index.telegram_channels') ?? 'Telegram Channels / Groups' }}
                    @if(isset($totalChannels))
                        <span class="badge bg-primary ms-1">{{ $totalChannels }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.telegram-rules.*') ? 'active' : '' }}"
                   href="{{ route('admin.telegram-rules.index') }}"
                   style="font-size: 15px; font-weight: 500;">
                    <i class="link-icon" data-feather="bell" style="width: 16px; height: 16px; margin-right: 6px;"></i>
                    {{ __('index.telegram_notification_rules') ?? 'Event Notification Rules' }}
                    @if(isset($totalRules))
                        <span class="badge bg-success ms-1">{{ $totalRules }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
</div>
