@forelse($staffList as $staff)
    @php
        $latestConversation = $staff->chatConversations->first();
        $latestMessage = $latestConversation?->latestMessage;
        $preview = $latestMessage?->message
        ?: ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_IMAGE
            ? 'Sent a photo'
            : ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_VOICE
                ? 'Sent a voice message'
                : ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_LOCATION
                    ? 'Sent a location'
                    : ($staff->department?->dept_name ?? ($staff->phone ?: ($staff->username ?: 'Employee'))))));
        $latestTime = $latestMessage?->created_at?->diffForHumans() ?? ($staff->branch?->name ?? 'Staff');
    @endphp
    <a href="{{ route('admin.employee-chat', ['employee_id' => $staff->id]) }}"
       class="chat-staff-link {{ $selectedStaff && $selectedStaff->id === $staff->id ? 'active' : '' }}"
       data-staff-name="{{ strtolower($staff->name) }}"
       data-staff-preview="{{ strtolower($preview) }}">
        <div class="chat-avatar-wrap">
            <img src="{{ $staff->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $staff->avatar) : asset('assets/images/img.png') }}" alt="{{ $staff->name }}" class="chat-avatar">
            @if((int) $staff->online_status === \App\Models\User::ONLINE)
                <span class="chat-status-dot"></span>
            @endif
        </div>
        <div class="chat-staff-meta">
            <div class="chat-staff-name-row">
                <p class="chat-staff-name">{{ $staff->name }}</p>
                <span class="chat-staff-time">{{ $latestTime }}</span>
            </div>
            <p class="chat-staff-preview">{{ $preview }}</p>
            <span class="chat-channel-badge {{ $staff->telegram_chat_id ? '' : 'off' }}">
                <i data-feather="{{ $staff->telegram_chat_id ? 'send' : 'wifi-off' }}"></i>
                {{ $staff->telegram_chat_id ? 'System + Telegram' : 'System only' }}
            </span>
        </div>
    </a>
@empty
    <div class="chat-empty">No staff found.</div>
@endforelse
