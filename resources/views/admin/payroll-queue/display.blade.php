<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $session->title }} - TV Display (Desktop &amp; iPad)</title>

    <!-- Google Fonts: Kantumruy Pro & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Kantumruy+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            950: '#070D1D',
                            900: '#0B1528',
                            850: '#101E38',
                            800: '#172847',
                            700: '#233862',
                        }
                    },
                    fontFamily: {
                        khmer: ['"Kantumruy Pro"', 'sans-serif'],
                        inter: ['"Inter"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Kantumruy Pro', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-inter {
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: #070D1D;
            color: #F8FAFC;
        }
        /* Custom Slim Scrollbar for Panels */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Pulsing Glow for Calling Counter */
        .calling-glow {
            border-color: #10B981 !important;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.55);
            animation: pulseCalling 1.2s infinite;
        }
        @keyframes pulseCalling {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.015); box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Bottom Marquee */
        .marquee-track {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 30s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden flex flex-col bg-navy-950 text-slate-100 select-none">

    <!-- TOP HEADER (Height: ~68px) -->
    <header class="h-16 md:h-18 bg-navy-900 border-b border-navy-700/80 px-4 md:px-6 flex items-center justify-between shadow-xl shrink-0 z-20">
        <!-- Brand & Title -->
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 md:w-11 md:h-11 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-emerald-500 shadow-md">
                <span class="font-inter font-black text-white text-lg tracking-tighter">PQ</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base md:text-xl font-black text-white tracking-wide truncate max-w-xs md:max-w-md lg:max-w-xl font-khmer">
                        {{ $session->title }}
                    </h1>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ __('index.live_system') }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="text-slate-300 font-medium">{{ $session->branch?->name ?: __('index.all_branches') }}</span>
                    <span>•</span>
                    <span class="font-mono text-amber-400 font-semibold">{{ $session->month }}</span>
                    <span>•</span>
                    <span class="font-mono text-slate-400 text-[11px]">{{ $session->session_code }}</span>
                </div>
            </div>
        </div>

        <!-- Header Actions: QR Modal Button, Audio Toggle, Fullscreen & Live Clock -->
        <div class="flex items-center gap-2 md:gap-4">
            <!-- QR Modal Button -->
            <button id="btnOpenQrModal" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-700 text-xs font-semibold text-cyan-300 border border-cyan-500/30 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M4 8h4V4H4v4zm12 0h4V4h-4v4zM4 20h4v-4H4v4z"></path></svg>
                <span class="hidden sm:inline">{{ __('index.enlarge_qr') }}</span>
            </button>

            <!-- Audio Chime Toggle -->
            <button id="btnToggleAudio" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-navy-800 hover:bg-navy-700 text-xs font-semibold text-slate-300 border border-slate-600 transition shadow-sm">
                <span id="audioIcon">🔔</span>
                <span id="audioText" class="hidden sm:inline">{{ __('index.sound_off') }}</span>
            </button>

            <!-- Fullscreen Toggle -->
            <button id="btnFullscreen" class="flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg bg-navy-800 hover:bg-navy-700 text-slate-300 border border-slate-700 transition" title="{{ __('index.fullscreen') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </button>

            <!-- Live Digital Clock -->
            <div class="text-right pl-2 border-l border-navy-700">
                <div id="liveClock" class="font-inter font-black text-lg md:text-xl text-amber-400 tracking-wider">
                    {{ date('h:i:s A') }}
                </div>
                <div id="liveDate" class="text-[11px] text-slate-400 font-khmer">
                    {{ date('d M Y') }}
                </div>
            </div>
        </div>
    </header>

    <!-- SECTION: តុ DISPLAY ONLY ONE LINE (Strictly 1 Single Horizontal Row) -->
    <section class="bg-navy-900/90 border-b border-navy-700/80 px-4 py-2.5 shrink-0 z-10 shadow-lg">
        <div class="flex items-center justify-between mb-1.5">
            <div class="flex items-center gap-2">
                <span class="inline-flex w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider font-inter">
                    {{ __('index.active_serving_counters') }}
                </span>
                <span class="hidden md:inline text-[11px] text-slate-400">
                    — {{ __('index.single_row_line') }}
                </span>
            </div>
            <div class="text-xs font-bold text-slate-400 font-inter">
                <span id="activeCounterCount" class="text-emerald-400 font-mono">{{ count($counters) }}</span>
            </div>
        </div>

        <!-- Single Horizontal Row: Grid with auto-columns so all counters sit in ONE line -->
        <div class="flex justify-center w-full" id="tvCountersWrapper">
            <div class="grid grid-flow-col auto-cols-fr gap-2 md:gap-3 w-full {{ count($counters) === 1 ? 'max-w-xl' : (count($counters) === 2 ? 'max-w-3xl' : '') }}" id="tvCountersBar">
                @foreach($counters as $cNum => $counter)
                    @php
                        $ticket = $counter['ticket'] ?? null;
                        $isBusy = !empty($ticket);
                        $isCalling = $isBusy && $ticket->status === 'calling';
                    @endphp
                    <div class="counter-slot-card flex flex-col justify-between bg-navy-850/90 rounded-xl border {{ $isCalling ? 'calling-glow border-emerald-400' : ($isBusy ? 'border-navy-700 shadow-md' : 'border-slate-800/80') }} p-2 md:p-2.5 transition-all duration-300 min-w-0" data-counter-number="{{ $cNum }}">
                        <!-- Counter Card Header -->
                        <div class="flex items-center justify-between pb-1 border-b border-navy-700/60 mb-1 shrink-0">
                            <span class="text-xs font-extrabold text-cyan-400 uppercase tracking-wide truncate">
                                {{ $counter['counter_name'] }}
                            </span>
                            @if($isCalling)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-400 text-slate-950 animate-pulse uppercase tracking-wider">
                                    {{ __('index.counter_calling') }}
                                </span>
                            @elseif($isBusy)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ __('index.counter_serving') }}
                                </span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                    {{ __('index.counter_available') }}
                                </span>
                            @endif
                        </div>

                        <!-- Counter Card Body -->
                        @if($isBusy)
                            <div class="flex items-center justify-between gap-1.5 py-1">
                                <div class="min-w-0 flex-1">
                                    <div class="font-inter font-black text-2xl md:text-3xl text-amber-400 tracking-tight leading-none drop-shadow">
                                        {{ $ticket->ticket_number }}
                                    </div>
                                    <div class="text-xs md:text-sm font-bold text-white truncate mt-1">
                                        {{ $ticket->employee?->english_name ?: ($ticket->employee?->name ?: 'Staff') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate">
                                        {{ $ticket->employee?->department?->dept_name ?: ($ticket->employee?->branch?->name ?: 'Staff') }}
                                    </div>
                                </div>
                                <img src="{{ $ticket->employee?->avatar_url ?: asset('assets/images/img.png') }}"
                                     alt="Avatar"
                                     class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover border-2 border-cyan-500/50 shrink-0 shadow">
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-2 text-slate-500 text-center">
                                <span class="font-inter text-xl font-bold opacity-30">—</span>
                                <span class="text-[11px] font-medium text-slate-400 mt-0.5">{{ __('index.counter_available') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECTION: 5 DEDICATED PANELS (QR Code, Pending, Waiting, In Room, Done) -->
    <main class="flex-1 min-h-0 p-2.5 md:p-3.5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5 md:gap-3.5 overflow-hidden">

        <!-- PANEL 1: SCAN QR CODE / ស្កេនចូលជួរ (Cyan / Emerald Theme) -->
        <section class="flex flex-col h-full bg-navy-900/85 rounded-2xl border border-cyan-500/40 p-3 md:p-3.5 shadow-xl backdrop-blur-sm overflow-hidden ring-1 ring-cyan-500/20">
            <!-- Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-sm shrink-0">
                        📱
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-cyan-400 uppercase tracking-wide truncate font-inter">
                            {{ __('index.scan_qr') }}
                        </h2>
                        <p class="text-[10px] text-slate-400 truncate font-khmer">{{ __('index.scan_qr_to_get_ticket') }}</p>
                    </div>
                </div>
                <span class="font-inter font-black text-xs px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 shrink-0">
                    JOIN
                </span>
            </div>

            <!-- QR Panel Body -->
            <div class="flex-1 overflow-y-auto flex flex-col items-center justify-between text-center p-2 custom-scrollbar">
                <div class="my-auto flex flex-col items-center">
                    <!-- QR Box -->
                    <div class="bg-white p-2.5 rounded-2xl shadow-2xl border-2 border-cyan-400/40 mb-2.5 transform hover:scale-105 transition duration-300 inline-block">
                        <div class="w-28 h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>svg]:max-w-full [&>svg]:max-h-full">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <!-- Pulse Indicator -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-bold font-khmer mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        {{ __('index.scan_to_join') }}
                    </div>

                    <!-- Instruction Note -->
                    <p class="text-[11px] text-slate-300 font-khmer font-medium leading-relaxed max-w-[210px] mx-auto">
                        {{ __('index.scan_qr_tv_instruction') }}
                    </p>
                </div>

                <!-- Footer Summary inside QR panel -->
                <div class="w-full pt-2 border-t border-navy-700/60 mt-2 shrink-0">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-khmer">
                        <span>{{ __('index.total_joined') }}:</span>
                        <span class="font-inter font-black text-sm text-amber-400" id="totalJoinedDisplay">
                            {{ $stats['total_joined'] ?? 0 }} {{ __('index.persons') }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- PANEL 2: PENDING / រង់ចាំមិនទាន់ចូល (Amber Theme) -->
        <section class="flex flex-col h-full bg-navy-900/80 rounded-2xl border border-amber-500/30 p-3 md:p-3.5 shadow-xl backdrop-blur-sm overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 text-sm shrink-0">
                        📋
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-amber-400 uppercase tracking-wide truncate font-inter">
                            {{ __('index.pending') }}
                        </h2>
                        <p class="text-[10px] text-slate-400 truncate font-khmer">{{ __('index.awaiting_checkin') }}</p>
                    </div>
                </div>
                <span id="badgePendingCount" class="font-inter font-black text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 shrink-0">
                    {{ count($pending_employees) }}
                </span>
            </div>

            <!-- List Container -->
            <div class="flex-1 overflow-y-auto space-y-2 pt-2.5 pr-1 custom-scrollbar" id="containerPendingList">
                @forelse($pending_employees as $emp)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-amber-500/40 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="{{ $emp->avatar_url ?: asset('assets/images/img.png') }}"
                                 alt="" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    {{ $emp->english_name ?: $emp->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    {{ $emp->department?->dept_name ?: ($emp->branch?->name ?: 'Staff') }} • <span class="font-mono text-slate-400">{{ $emp->employee_code ?: $emp->username }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 shrink-0 font-khmer">
                            {{ __('index.pending') }}
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                        <span class="text-2xl mb-1 opacity-40">✓</span>
                        <p class="text-xs">{{ __('index.no_employees_pending') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- PANEL 3: WAITING / កំពុងរង់ចាំ (Orange Theme) -->
        <section class="flex flex-col h-full bg-navy-900/80 rounded-2xl border border-orange-500/30 p-3 md:p-3.5 shadow-xl backdrop-blur-sm overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-orange-500/15 border border-orange-500/30 flex items-center justify-center text-orange-400 text-sm shrink-0">
                        ⏳
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-orange-400 uppercase tracking-wide truncate font-inter">
                            {{ __('index.waiting') }}
                        </h2>
                        <p class="text-[10px] text-slate-400 truncate font-khmer">{{ __('index.fifo_queue') }}</p>
                    </div>
                </div>
                <span id="badgeWaitingCount" class="font-inter font-black text-xs px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/40 shrink-0">
                    {{ count($waiting_tickets) }}
                </span>
            </div>

            <!-- List Container -->
            <div class="flex-1 overflow-y-auto space-y-2 pt-2.5 pr-1 custom-scrollbar" id="containerWaitingList">
                @forelse($waiting_tickets as $w)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-orange-500/40 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="font-inter font-black text-sm px-2 py-1 rounded-lg bg-orange-500/20 text-orange-400 border border-orange-500/40 shrink-0">
                                {{ $w->ticket_number }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    {{ $w->employee?->english_name ?: ($w->employee?->name ?: 'Staff') }}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    {{ $w->employee?->department?->dept_name ?: ($w->employee?->branch?->name ?: 'Staff') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-mono text-slate-400">
                                {{ $w->joined_at ? $w->joined_at->format('h:i A') : '' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                        <span class="text-2xl mb-1 opacity-40">⌛</span>
                        <p class="text-xs">{{ __('index.no_tickets_waiting') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- PANEL 4: IN ROOM / កំពុងក្នុងបន្ទប់ (Emerald Theme) -->
        <section class="flex flex-col h-full bg-navy-900/80 rounded-2xl border border-emerald-500/40 p-3 md:p-3.5 shadow-xl backdrop-blur-sm overflow-hidden ring-1 ring-emerald-500/20">
            <!-- Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-sm shrink-0">
                        🚪
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-emerald-400 uppercase tracking-wide truncate font-inter">
                            {{ __('index.in_room') }}
                        </h2>
                        <p class="text-[10px] text-slate-400 truncate font-khmer">{{ __('index.at_counter') }}</p>
                    </div>
                </div>
                <span id="badgeInRoomCount" class="font-inter font-black text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">
                    {{ count($in_room_tickets) }}
                </span>
            </div>

            <!-- List Container -->
            <div class="flex-1 overflow-y-auto space-y-2 pt-2.5 pr-1 custom-scrollbar" id="containerInRoomList">
                @forelse($in_room_tickets as $ir)
                    @php $isCallingItem = $ir->status === 'calling'; @endphp
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border {{ $isCallingItem ? 'border-amber-400 shadow-md ring-1 ring-amber-400/50' : 'border-emerald-500/30' }} transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-inter font-black text-sm px-2 py-1 rounded-lg {{ $isCallingItem ? 'bg-amber-400 text-slate-950 animate-pulse' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' }} shrink-0">
                                {{ $ir->ticket_number }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    {{ $ir->employee?->english_name ?: ($ir->employee?->name ?: 'Staff') }}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    {{ $ir->employee?->department?->dept_name ?: ($ir->employee?->branch?->name ?: 'Staff') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 font-khmer">
                                {{ __('index.counter_number_title', ['number' => $ir->counter_number]) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                        <span class="text-2xl mb-1 opacity-40">🚪</span>
                        <p class="text-xs">{{ __('index.no_tickets_in_room') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- PANEL 5: DONE / បានបើករួចរាល់ (Cyan Theme) -->
        <section class="flex flex-col h-full bg-navy-900/80 rounded-2xl border border-cyan-500/30 p-3 md:p-3.5 shadow-xl backdrop-blur-sm overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-sm shrink-0">
                        ✅
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-cyan-400 uppercase tracking-wide truncate font-inter">
                            {{ __('index.paid') }}
                        </h2>
                        <p class="text-[10px] text-slate-400 truncate font-khmer">{{ __('index.completed_paid') }}</p>
                    </div>
                </div>
                <span id="badgeDoneCount" class="font-inter font-black text-xs px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 shrink-0">
                    {{ count($done_tickets) }}
                </span>
            </div>

            <!-- List Container -->
            <div class="flex-1 overflow-y-auto space-y-2 pt-2.5 pr-1 custom-scrollbar" id="containerDoneList">
                @forelse($done_tickets as $d)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-cyan-500/40 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-inter font-bold text-xs px-1.5 py-0.5 rounded bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 shrink-0">
                                {{ $d->ticket_number }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    {{ $d->employee?->english_name ?: ($d->employee?->name ?: 'Staff') }}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    {{ $d->employee?->department?->dept_name ?: ($d->employee?->branch?->name ?: 'Staff') }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-right shrink-0">
                            <span class="text-[10px] font-mono text-slate-400">
                                {{ $d->paid_at ? $d->paid_at->format('h:i A') : __('index.paid') }}
                            </span>
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">
                                ✓
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                        <span class="text-2xl mb-1 opacity-40">✨</span>
                        <p class="text-xs">{{ __('index.no_tickets_done') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

    </main>

    <!-- BOTTOM MARQUEE TICKER (Height: ~38px) -->
    <footer class="h-9 bg-navy-950 border-t border-navy-700/80 px-4 flex items-center overflow-hidden shrink-0 z-20">
        <div class="flex items-center gap-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 font-black text-[11px] uppercase px-2.5 py-0.5 rounded shrink-0 shadow-sm font-khmer">
            <span>📢 {{ __('index.announcement') }}</span>
        </div>
        <div class="overflow-hidden flex-1 ml-3 relative">
            <div class="marquee-track font-khmer text-xs text-slate-300 font-medium">
                {{ __('index.announcement_banner_tv') }}
            </div>
        </div>
    </footer>

    <!-- QR CODE INSPECTION MODAL FOR EMPLOYEES & VISITORS -->
    <div id="qrModalBackdrop" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-navy-900 border border-cyan-500/40 rounded-2xl p-6 max-w-sm w-full text-center shadow-2xl relative">
            <button id="btnCloseQrModal" class="absolute top-3 right-3 text-slate-400 hover:text-white text-xl font-bold w-8 h-8 rounded-full bg-navy-800 flex items-center justify-center">
                &times;
            </button>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                📱
            </div>
            <h3 class="font-khmer font-bold text-lg text-white mb-1">{{ __('index.scan_to_join') }}</h3>
            <p class="text-xs text-slate-400 mb-4 font-khmer">{{ __('index.scan_qr_to_get_ticket') }}</p>

            <div class="bg-white p-3 rounded-xl inline-block shadow-inner mb-4">
                {!! $qrSvg !!}
            </div>

            <div class="text-[11px] font-mono text-cyan-400 bg-navy-950 p-2 rounded-lg border border-navy-700 select-all break-all mb-4">
                {{ $joinUrl }}
            </div>

            <button id="btnDismissQr" class="w-full py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition">
                {{ __('index.close_modal') }}
            </button>
        </div>
    </div>

    <!-- SOUND & REALTIME JAVASCRIPT ENGINE -->
    <script>
        (function () {
            // Localization dictionary from server
            const i18n = {
                soundOn: @json(__('index.sound_on')),
                soundOff: @json(__('index.sound_off')),
                counterCalling: @json(__('index.counter_calling')),
                counterServing: @json(__('index.counter_serving')),
                counterAvailable: @json(__('index.counter_available')),
                counterPrefix: @json(__('index.counter_number_prefix')),
                noEmployeesPending: @json(__('index.no_employees_pending')),
                noTicketsWaiting: @json(__('index.no_tickets_waiting')),
                noTicketsInRoom: @json(__('index.no_tickets_in_room')),
                noTicketsDone: @json(__('index.no_tickets_done')),
                pendingBadge: @json(__('index.pending')),
                personsUnit: @json(__('index.persons')),
                paid: @json(__('index.paid'))
            };

            // Live clock & Date
            const liveClockEl = document.getElementById('liveClock');
            const liveDateEl = document.getElementById('liveDate');
            function updateClock() {
                const now = new Date();
                if (liveClockEl) liveClockEl.textContent = now.toLocaleTimeString('en-US', { hour12: true });
                if (liveDateEl) {
                    const days = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
                    const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
                    liveDateEl.textContent = `ថ្ងៃ${days[now.getDay()]} ទី${now.getDate()} ខែ${months[now.getMonth()]} ឆ្នាំ${now.getFullYear()}`;
                }
            }
            setInterval(updateClock, 1000);

            // Web Audio Alert Chime (Harmonic Ding-Dong)
            let audioEnabled = false;
            let lastCalls = null;
            const btnToggleAudio = document.getElementById('btnToggleAudio');
            const audioIcon = document.getElementById('audioIcon');
            const audioText = document.getElementById('audioText');

            if (btnToggleAudio) {
                btnToggleAudio.addEventListener('click', function () {
                    audioEnabled = !audioEnabled;
                    if (audioEnabled) {
                        btnToggleAudio.classList.remove('text-slate-300', 'border-slate-600');
                        btnToggleAudio.classList.add('bg-emerald-600', 'text-white', 'border-emerald-500');
                        if (audioIcon) audioIcon.textContent = '🔊';
                        if (audioText) audioText.textContent = i18n.soundOn;
                        playChime();
                    } else {
                        btnToggleAudio.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-500');
                        btnToggleAudio.classList.add('bg-navy-800', 'text-slate-300', 'border-slate-600');
                        if (audioIcon) audioIcon.textContent = '🔔';
                        if (audioText) audioText.textContent = i18n.soundOff;
                    }
                });
            }

            function playChime() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const ctx = new AudioContext();
                    const now = ctx.currentTime;

                    // Tone 1: High bell chime
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(659.25, now); // E5
                    gain1.gain.setValueAtTime(0.35, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.55);

                    // Tone 2: Harmonious resonance
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.22); // A5
                    gain2.gain.setValueAtTime(0.35, now + 0.22);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.22);
                    osc2.stop(now + 0.9);
                } catch (e) {}
            }

            // Fullscreen API Toggle
            const btnFullscreen = document.getElementById('btnFullscreen');
            if (btnFullscreen) {
                btnFullscreen.addEventListener('click', function () {
                    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                        const root = document.documentElement;
                        if (root.requestFullscreen) root.requestFullscreen();
                        else if (root.webkitRequestFullscreen) root.webkitRequestFullscreen();
                    } else {
                        if (document.exitFullscreen) document.exitFullscreen();
                        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    }
                });
            }

            // QR Code Modal Handlers
            const qrModal = document.getElementById('qrModalBackdrop');
            const btnOpenQr = document.getElementById('btnOpenQrModal');
            const btnCloseQr = document.getElementById('btnCloseQrModal');
            const btnDismissQr = document.getElementById('btnDismissQr');

            if (btnOpenQr && qrModal) {
                btnOpenQr.addEventListener('click', () => qrModal.classList.remove('hidden'));
            }
            if (btnCloseQr && qrModal) {
                btnCloseQr.addEventListener('click', () => qrModal.classList.add('hidden'));
            }
            if (btnDismissQr && qrModal) {
                btnDismissQr.addEventListener('click', () => qrModal.classList.add('hidden'));
            }
            if (qrModal) {
                qrModal.addEventListener('click', (e) => {
                    if (e.target === qrModal) qrModal.classList.add('hidden');
                });
            }

            // Real-time Asynchronous Polling
            const liveStatusUrl = "{{ route('admin.payroll-queue.live-status', ['sessionId' => $session->id]) }}";

            function renderCounters(counters) {
                const container = document.getElementById('tvCountersBar');
                if (!container || !counters) return;

                const countEl = document.getElementById('activeCounterCount');
                if (countEl) countEl.textContent = counters.length;

                if (counters.length === 1) {
                    container.classList.add('max-w-xl');
                    container.classList.remove('max-w-3xl');
                } else if (counters.length === 2) {
                    container.classList.add('max-w-3xl');
                    container.classList.remove('max-w-xl');
                } else {
                    container.classList.remove('max-w-xl', 'max-w-3xl');
                }

                let html = '';
                counters.forEach((c, idx) => {
                    const cNum = c.counter_number || (idx + 1);
                    const ticket = c.ticket;
                    const isBusy = !!ticket;
                    const isCalling = isBusy && (ticket.status === 'calling');

                    html += `
                        <div class="counter-slot-card flex flex-col justify-between bg-navy-850/90 rounded-xl border ${isCalling ? 'calling-glow border-emerald-400' : (isBusy ? 'border-navy-700 shadow-md' : 'border-slate-800/80')} p-2 md:p-2.5 transition-all duration-300 min-w-0" data-counter-number="${cNum}">
                            <div class="flex items-center justify-between pb-1 border-b border-navy-700/60 mb-1 shrink-0">
                                <span class="text-xs font-extrabold text-cyan-400 uppercase tracking-wide truncate">
                                    ${c.counter_name || (i18n.counterPrefix + cNum)}
                                </span>
                                ${isCalling ? `
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-400 text-slate-950 animate-pulse uppercase tracking-wider">
                                        ${i18n.counterCalling}
                                    </span>
                                ` : (isBusy ? `
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        ${i18n.counterServing}
                                    </span>
                                ` : `
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        ${i18n.counterAvailable}
                                    </span>
                                `)}
                            </div>
                            ${isBusy ? `
                                <div class="flex items-center justify-between gap-1.5 py-1">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-inter font-black text-2xl md:text-3xl text-amber-400 tracking-tight leading-none drop-shadow">
                                            ${ticket.ticket_number}
                                        </div>
                                        <div class="text-xs md:text-sm font-bold text-white truncate mt-1">
                                            ${ticket.employee_name || ticket.employee?.name || 'Staff'}
                                        </div>
                                        <div class="text-[11px] text-slate-400 truncate">
                                            ${ticket.department_name || ticket.employee?.department?.dept_name || 'Staff'}
                                        </div>
                                    </div>
                                    <img src="${ticket.employee_avatar || ticket.employee?.avatar_url || '/assets/images/img.png'}"
                                         alt="Avatar"
                                         class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover border-2 border-cyan-500/50 shrink-0 shadow">
                                </div>
                            ` : `
                                <div class="flex flex-col items-center justify-center py-2 text-slate-500 text-center">
                                    <span class="font-inter text-xl font-bold opacity-30">—</span>
                                    <span class="text-[11px] font-medium text-slate-400 mt-0.5">${i18n.counterAvailable}</span>
                                </div>
                            `}
                        </div>
                    `;
                });
                container.innerHTML = html;
            }

            function renderPending(list) {
                const badge = document.getElementById('badgePendingCount');
                const container = document.getElementById('containerPendingList');
                if (badge) badge.textContent = list ? list.length : 0;
                if (!container || !list) return;

                if (list.length === 0) {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                            <span class="text-2xl mb-1 opacity-40">✓</span>
                            <p class="text-xs">${i18n.noEmployeesPending}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = list.map(emp => `
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-amber-500/40 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="${emp.avatar || '/assets/images/img.png'}"
                                 alt="" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    ${emp.name}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    ${emp.department || 'Staff'} • <span class="font-mono text-slate-400">${emp.code || ''}</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 shrink-0 font-khmer">
                            ${i18n.pendingBadge}
                        </span>
                    </div>
                `).join('');
            }

            function renderWaiting(list) {
                const badge = document.getElementById('badgeWaitingCount');
                const container = document.getElementById('containerWaitingList');
                if (badge) badge.textContent = list ? list.length : 0;
                if (!container || !list) return;

                if (list.length === 0) {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                            <span class="text-2xl mb-1 opacity-40">⌛</span>
                            <p class="text-xs">${i18n.noTicketsWaiting}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = list.map(w => `
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-orange-500/40 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="font-inter font-black text-sm px-2 py-1 rounded-lg bg-orange-500/20 text-orange-400 border border-orange-500/40 shrink-0">
                                ${w.ticket_number}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    ${w.employee_name || 'Staff'}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    ${w.department_name || 'Staff'}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-mono text-slate-400">
                                ${w.joined_at || ''}
                            </span>
                        </div>
                    </div>
                `).join('');
            }

            function renderInRoom(list) {
                const badge = document.getElementById('badgeInRoomCount');
                const container = document.getElementById('containerInRoomList');
                if (badge) badge.textContent = list ? list.length : 0;
                if (!container || !list) return;

                if (list.length === 0) {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                            <span class="text-2xl mb-1 opacity-40">🚪</span>
                            <p class="text-xs">${i18n.noTicketsInRoom}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = list.map(ir => {
                    const isCalling = !!ir.is_calling;
                    return `
                        <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border ${isCalling ? 'border-amber-400 shadow-md ring-1 ring-amber-400/50' : 'border-emerald-500/30'} transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-inter font-black text-sm px-2 py-1 rounded-lg ${isCalling ? 'bg-amber-400 text-slate-950 animate-pulse' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'} shrink-0">
                                    ${ir.ticket_number}
                                </span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-white truncate">
                                        ${ir.employee_name || 'Staff'}
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">
                                        ${ir.department_name || 'Staff'}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 font-khmer">
                                    ${i18n.counterPrefix + (ir.counter_number || 1)}
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function renderDone(list) {
                const badge = document.getElementById('badgeDoneCount');
                const container = document.getElementById('containerDoneList');
                if (badge) badge.textContent = list ? list.length : 0;
                if (!container || !list) return;

                if (list.length === 0) {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center py-8 text-slate-500">
                            <span class="text-2xl mb-1 opacity-40">✨</span>
                            <p class="text-xs">${i18n.noTicketsDone}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = list.map(d => `
                    <div class="flex items-center justify-between p-2 rounded-xl bg-navy-850/80 border border-slate-800 hover:border-cyan-500/40 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-inter font-bold text-xs px-1.5 py-0.5 rounded bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 shrink-0">
                                ${d.ticket_number}
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-white truncate">
                                    ${d.employee_name || 'Staff'}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    ${d.department_name || 'Staff'}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-right shrink-0">
                            <span class="text-[10px] font-mono text-slate-400">
                                ${d.paid_at || i18n.paid}
                            </span>
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-bold">
                                ✓
                            </span>
                        </div>
                    </div>
                `).join('');
            }

            // Polling routine every 2.8 seconds
            function pollLiveQueue() {
                fetch(liveStatusUrl, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.success) return;

                        const calls = new Map(data.in_room_tickets.map(ticket => [ticket.id, ticket.call_timestamp]));
                        if (audioEnabled && lastCalls && [...calls].some(([id, timestamp]) =>
                            timestamp && timestamp !== lastCalls.get(id))) {
                            playChime();
                        }
                        lastCalls = calls;

                        // Render updated lists
                        if (data.stats && document.getElementById('totalJoinedDisplay')) {
                            document.getElementById('totalJoinedDisplay').textContent = (data.stats.total_joined || 0) + ' ' + i18n.personsUnit;
                        }
                        if (data.counters) renderCounters(data.counters);
                        if (data.pending_employees) renderPending(data.pending_employees);
                        if (data.waiting_tickets) renderWaiting(data.waiting_tickets);
                        if (data.in_room_tickets) renderInRoom(data.in_room_tickets);
                        if (data.done_tickets) renderDone(data.done_tickets);
                    })
                    .catch(() => {});
            }

            setInterval(pollLiveQueue, 2800);
        })();
    </script>
</body>
</html>
