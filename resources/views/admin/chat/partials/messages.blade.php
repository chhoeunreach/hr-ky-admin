@forelse($messages as $message)
    @php
        $isOutgoing = $message->sender_type === \App\Models\ChatMessage::SENDER_ADMIN;
    @endphp
    <div class="chat-bubble-row {{ $isOutgoing ? 'outgoing' : '' }}">
        <div class="chat-bubble {{ $isOutgoing ? 'outgoing' : '' }}">
            @if($message->message_type === \App\Models\ChatMessage::TYPE_IMAGE && $message->media_url)
                <img src="{{ $message->media_url }}" alt="Chat image" class="chat-bubble-image">
            @elseif($message->message_type === \App\Models\ChatMessage::TYPE_VOICE && $message->media_url)
                <audio controls preload="none">
                    <source src="{{ $message->media_url }}">
                </audio>
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
                {{ ucfirst($message->sender_type) }} • {{ $message->created_at->format('M d, h:i A') }}
            </div>
        </div>
    </div>
@empty
    <div class="chat-empty">No messages yet. Start the conversation.</div>
@endforelse
