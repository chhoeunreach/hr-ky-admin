@forelse($messages as $message)
    @php
        $isOutgoing = $message->sender_type === \App\Models\ChatMessage::SENDER_ADMIN;
        $mediaUrl = $message->resolvedMediaUrl();
        $telegramStatus = $message->meta['telegram_status'] ?? null;
        $telegramError = $message->meta['telegram_error'] ?? null;
    @endphp
    <div class="chat-bubble-row {{ $isOutgoing ? 'outgoing' : '' }}">
        <div class="chat-bubble {{ $isOutgoing ? 'outgoing' : '' }}">
            @if($message->message_type === \App\Models\ChatMessage::TYPE_IMAGE && $mediaUrl)
                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ $mediaUrl }}" alt="Chat image" class="chat-bubble-image">
                </a>
            @elseif($message->message_type === \App\Models\ChatMessage::TYPE_VOICE && $mediaUrl)
                <audio controls preload="none">
                    <source src="{{ $mediaUrl }}">
                </audio>
            @elseif($message->message_type === \App\Models\ChatMessage::TYPE_FILE && $mediaUrl)
                <div>
                    <strong>Attachment</strong>
                    <div>
                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer">
                            {{ $message->meta['file_name'] ?? 'Open file' }}
                        </a>
                    </div>
                </div>
            @elseif($message->message_type === \App\Models\ChatMessage::TYPE_LOCATION)
                <div>
                    <strong>Location</strong>
                    @if($message->latitude && $message->longitude)
                        <div>{{ $message->latitude }}, {{ $message->longitude }}</div>
                    @endif
                    @if($message->map_url)
                        <a href="{{ $message->map_url }}" target="_blank" rel="noopener noreferrer">Open map</a>
                    @endif
                </div>
            @endif

            @if($message->message)
                <div class="{{ $message->message_type !== \App\Models\ChatMessage::TYPE_TEXT ? 'mt-2' : '' }}">
                    {{ $message->message }}
                </div>
            @endif

            <div class="chat-bubble-meta">
                {{ $message->senderName() }} &bull; {{ $message->created_at->format('M d, h:i A') }}
                @if($isOutgoing && $telegramStatus)
                    &bull;
                    @if($telegramStatus === 'sent')
                        Telegram delivered
                    @elseif($telegramStatus === 'skipped')
                        Telegram skipped
                    @else
                        Telegram failed{{ $telegramError ? ': ' . $telegramError : '' }}
                    @endif
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="chat-empty">No messages yet. Start the conversation.</div>
@endforelse
