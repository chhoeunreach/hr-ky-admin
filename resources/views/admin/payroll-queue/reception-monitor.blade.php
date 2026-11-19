<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KY TECH HR - ប្រព័ន្ធគ្រប់គ្រងការរង់ចាំ (HR QUEUE MONITOR 16:9)</title>
    
    <!-- Google Fonts: Kantumruy Pro & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Kantumruy+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for standalone 16:9 display) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            900: '#0F172A',
                            800: '#1E293B',
                            700: '#334155',
                        },
                        emerald: {
                            400: '#34D399',
                            500: '#10B981',
                            600: '#059669',
                        },
                        amber: {
                            400: '#FBBF24',
                            500: '#F59E0B',
                        },
                        blue: {
                            500: '#3B82F6',
                            600: '#2563EB',
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
        }
        .font-inter {
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: #0F172A;
            color: #F8FAFC;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
        }
        /* Emerald Calling Pulse */
        .calling-pulse {
            border-color: #10B981 !important;
            box-shadow: 0 0 35px rgba(16, 185, 129, 0.45);
            animation: pulseEmerald 1.2s infinite;
        }
        @keyframes pulseEmerald {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { transform: scale(1.015); box-shadow: 0 0 0 16px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1E293B;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        /* Marquee ticker */
        .marquee-content {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 25s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="flex flex-col h-screen select-none antialiased">

    <!-- Top Header (Height: ~85px) -->
    <header class="h-20 bg-navy-800 border-b border-navy-700/80 px-8 flex items-center justify-between shadow-lg shrink-0">
        <!-- Brand & System Title -->
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-emerald-500 shadow-md">
                <span class="font-inter font-black text-white text-xl tracking-tighter">KY</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black tracking-wide text-white font-khmer">
                        ប្រព័ន្ធគ្រប់គ្រងការរង់ចាំ <span class="text-emerald-400 font-inter font-bold text-xl ml-1">(HR QUEUE)</span>
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                        16:9 FHD
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-khmer">
                    KY TECH HR Ecosystem • សេវាកម្មបើកប្រាក់ខែ និងវត្តមានបុគ្គលិក
                </p>
            </div>
        </div>

        <!-- Clock, Connection Status & Sound Toggle -->
        <div class="flex items-center gap-6">
            <!-- WebSocket Indicator -->
            <div id="wsStatusBadge" class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="wsStatusText">Reverb / Live Online</span>
            </div>

            <!-- Audio Chime Permission Button -->
            <button id="btnToggleAudio" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-navy-700 hover:bg-navy-600 text-xs text-slate-300 border border-slate-600 transition">
                <span id="audioIcon">🔔</span>
                <span id="audioText">សំឡេង: បិទ</span>
            </button>

            <!-- Digital Clock -->
            <div class="text-right">
                <div id="liveClock" class="font-inter text-2xl font-black text-amber-400 tracking-wider">
                    00:00:00 AM
                </div>
                <div id="liveDate" class="text-xs text-slate-400 font-khmer font-medium">
                    ថ្ងៃព្រហស្បតិ៍ ទី១៩ ខែវិច្ឆិកា ឆ្នាំ២០២៦
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Split-Screen (Flex 1) -->
    <main class="flex-1 flex overflow-hidden p-6 gap-6">
        
        <!-- Left Panel: 60% Width — "កំពុងបម្រើសេវាកម្ម (NOW SERVING)" -->
        <section class="w-[60%] flex flex-col h-full bg-navy-800/60 rounded-2xl border border-navy-700/60 p-5 shadow-2xl backdrop-blur-sm">
            <!-- Section Header -->
            <div class="flex items-center justify-between pb-3 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-3.5 w-3.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                    </span>
                    <h2 class="text-xl font-black text-emerald-400 tracking-wide font-khmer">
                        កំពុងបម្រើសេវាកម្ម <span class="font-inter text-slate-300 font-bold text-lg">(NOW SERVING)</span>
                    </h2>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-md bg-navy-700 text-slate-300 border border-navy-600 font-khmer">
                    ៥ បញ្ជរសកម្ម (5 Active Slots)
                </span>
            </div>

            <!-- 5 Slots Grid (Counters 1 to 5) -->
            <div class="flex-1 grid grid-cols-5 gap-3.5 pt-4" id="servingSlotsContainer">
                <!-- Dynamically rendered via JS & WebSockets -->
            </div>
        </section>

        <!-- Right Panel: 40% Width — "បញ្ជីរង់ចាំ (WAITING LIST)" -->
        <section class="w-[40%] flex flex-col h-full bg-navy-800/60 rounded-2xl border border-navy-700/60 p-5 shadow-2xl backdrop-blur-sm">
            <!-- Section Header -->
            <div class="flex items-center justify-between pb-3 border-b border-navy-700/80 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 text-sm">
                        ⏳
                    </span>
                    <h2 class="text-xl font-black text-amber-400 tracking-wide font-khmer">
                        បញ្ជីរង់ចាំ <span class="font-inter text-slate-300 font-bold text-lg">(WAITING LIST)</span>
                    </h2>
                </div>
                <span class="text-xs font-semibold text-slate-400 font-khmer">
                    តាមលំដាប់លំដោយ (FIFO Order)
                </span>
            </div>

            <!-- Waiting Tickets List (Vertical Scrollable) -->
            <div class="flex-1 overflow-y-auto py-3 space-y-2.5 pr-1.5" id="waitingListContainer">
                <!-- Dynamically rendered via JS & WebSockets -->
            </div>

            <!-- Fixed Bottom Counter Card -->
            <div class="pt-3 border-t border-navy-700/80 shrink-0">
                <div class="bg-gradient-to-r from-navy-900 to-navy-800 rounded-xl border border-amber-500/30 p-3.5 flex items-center justify-between shadow-inner">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                            👥
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 font-khmer">ចំនួនបុគ្គលិកកំពុងរង់ចាំ</div>
                            <div class="text-sm font-bold text-white font-khmer">សរុបអ្នករង់ចាំ</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span id="waitingCountDisplay" class="font-inter font-black text-3xl text-amber-400 tracking-tight">
                            00
                        </span>
                        <span class="text-xs text-amber-300/80 font-khmer ml-1 font-semibold">នាក់</span>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Bottom Ticker Bar (Height: ~48px) -->
    <footer class="h-12 bg-navy-900 border-t border-navy-700 px-6 flex items-center overflow-hidden shrink-0 shadow-2xl">
        <div class="flex items-center gap-2 bg-emerald-500 text-navy-900 font-bold text-xs uppercase px-3 py-1 rounded-md shrink-0 shadow font-khmer">
            <span>📢 ការប្រកាស</span>
        </div>
        <div class="overflow-hidden flex-1 ml-4 relative">
            <div class="marquee-content font-khmer text-sm text-slate-200 font-semibold" id="tickerText">
                📢 សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រងជួរ KY TECH HR • សូមរៀបចំឯកសារ ឬទូរស័ព្ទដៃរបស់លោកអ្នកសម្រាប់ស្កេន និងបើកប្រាក់ខែ • ប្រព័ន្ធដំណើរការ Real-time ដោយស្វ័យប្រវត្តិ
            </div>
        </div>
    </footer>

    <!-- Audio & Real-time Integration Script -->
    <script>
        // State
        let audioEnabled = false;
        let lastCalledTicketNumber = null;
        const liveApiUrl = '/api/v1/hr-queue/live';

        // Clock Update
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour12: true });
            document.getElementById('liveClock').textContent = timeStr;
            
            const days = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
            const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
            const dateStr = `ថ្ងៃ${days[now.getDay()]} ទី${now.getDate()} ខែ${months[now.getMonth()]} ឆ្នាំ${now.getFullYear()}`;
            document.getElementById('liveDate').textContent = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Audio Chime (Synthesized 2-tone airport / banking chime)
        function playChime() {
            if (!audioEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                
                // Tone 1: High crisp bell
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(659.25, now); // E5
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.6);

                // Tone 2: Harmonic resolution
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880.00, now + 0.22); // A5
                gain2.gain.setValueAtTime(0.35, now + 0.22);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.22);
                osc2.stop(now + 0.9);
            } catch (e) {
                console.warn('Audio chime failed:', e);
            }
        }

        // Toggle Audio
        document.getElementById('btnToggleAudio').addEventListener('click', function() {
            audioEnabled = !audioEnabled;
            const textEl = document.getElementById('audioText');
            const iconEl = document.getElementById('audioIcon');
            if (audioEnabled) {
                textEl.textContent = 'សំឡេង: បើក';
                iconEl.textContent = '🔊';
                this.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 text-xs font-semibold';
                playChime();
            } else {
                textEl.textContent = 'សំឡេង: បិទ';
                iconEl.textContent = '🔔';
                this.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-navy-700 hover:bg-navy-600 text-xs text-slate-300 border border-slate-600 transition';
            }
        });

        // Render 5 Now Serving Slots
        function renderCounters(counters) {
            const container = document.getElementById('servingSlotsContainer');
            if (!container) return;

            let html = '';
            counters.forEach((c) => {
                const isCalling = c.status === 'calling';
                const isBusy = c.is_busy;

                html += `
                <div class="h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300 border ${
                    isCalling 
                        ? 'bg-gradient-to-b from-navy-800 to-navy-900 border-2 calling-pulse' 
                        : isBusy 
                            ? 'bg-navy-800/90 border-navy-600/80 shadow-md' 
                            : 'bg-navy-900/50 border-dashed border-navy-700/80'
                }">
                    <!-- Counter Badge -->
                    <div class="flex items-center justify-between pb-3 border-b border-navy-700/60">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black tracking-wide uppercase font-inter ${
                            isCalling 
                                ? 'bg-emerald-500 text-navy-900' 
                                : isBusy 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-navy-700 text-slate-400'
                        }">
                            ${c.counter_label}
                        </span>
                        <span class="text-xs font-bold font-khmer ${
                            isCalling ? 'text-emerald-400' : isBusy ? 'text-blue-400' : 'text-slate-500'
                        }">
                            ${isCalling ? 'កំពុងហៅ...' : isBusy ? 'កំពុងបម្រើ' : 'ទំនេរ'}
                        </span>
                    </div>

                    <!-- Ticket & Avatar Content -->
                    <div class="my-auto text-center py-2">
                        ${isBusy ? `
                            <div class="font-inter font-black text-3xl xl:text-4xl tracking-wider text-amber-400 drop-shadow mb-3">
                                ${c.ticket_number}
                            </div>
                            <div class="relative w-16 h-16 mx-auto mb-2.5">
                                <img src="${c.employee?.avatar_url || '/assets/images/img.png'}" 
                                     alt="Avatar" 
                                     class="w-16 h-16 rounded-full object-cover border-2 ${isCalling ? 'border-emerald-400' : 'border-slate-600'} shadow-md">
                            </div>
                            <div class="font-khmer font-bold text-sm text-white truncate px-1">
                                ${c.employee?.name || '—'}
                            </div>
                            <div class="text-[11px] font-khmer text-slate-400 truncate">
                                ${c.employee?.department || 'Staff'}
                            </div>
                        ` : `
                            <div class="w-14 h-14 mx-auto rounded-full bg-navy-800/60 border border-dashed border-navy-600 flex items-center justify-center text-slate-600 text-xl mb-3">
                                🪑
                            </div>
                            <div class="font-khmer font-bold text-sm text-slate-500">
                                បញ្ជរទំនេរ
                            </div>
                            <div class="text-[11px] text-slate-600 font-khmer mt-0.5">
                                រង់ចាំការហៅបន្ទាប់
                            </div>
                        `}
                    </div>

                    <!-- Card Footer Status -->
                    <div class="pt-2.5 border-t border-navy-700/60 text-center">
                        <span class="text-[10px] font-mono tracking-tight text-slate-400">
                            ${c.called_at ? `ម៉ោង: ${c.called_at}` : 'Ready for next'}
                        </span>
                    </div>
                </div>
                `;
            });

            container.innerHTML = html;
        }

        // Render Waiting List
        function renderWaitingList(list, totalCount) {
            const container = document.getElementById('waitingListContainer');
            const countDisplay = document.getElementById('waitingCountDisplay');
            if (countDisplay) {
                countDisplay.textContent = totalCount < 10 ? `0${totalCount}` : totalCount;
            }

            if (!container) return;

            if (!list || list.length === 0) {
                container.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-500">
                        <span class="text-4xl mb-2 opacity-40">☕</span>
                        <div class="font-khmer text-sm font-semibold">គ្មានអ្នករង់ចាំក្នុងជួរនៅឡើយទេ</div>
                        <div class="text-xs text-slate-600 mt-1">បុគ្គលិកអាចស្កេន QR Code ដើម្បីចូលជួរ</div>
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach((item, index) => {
                html += `
                <div class="flex items-center justify-between p-3 rounded-xl bg-navy-900/70 border border-navy-700/70 hover:border-amber-500/40 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-navy-800 flex items-center justify-center font-inter font-bold text-xs text-slate-400 border border-navy-700">
                            #${item.position}
                        </div>
                        <div class="font-inter font-black text-lg text-amber-400 tracking-wide">
                            ${item.ticket_number}
                        </div>
                        <div class="ml-2">
                            <div class="font-khmer font-bold text-sm text-slate-200">
                                ${item.employee_name}
                            </div>
                            <div class="text-[11px] font-khmer text-slate-400">
                                ${item.department}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-mono text-slate-400 bg-navy-800 px-2.5 py-1 rounded-md border border-navy-700">
                            ${item.joined_at}
                        </span>
                    </div>
                </div>
                `;
            });

            container.innerHTML = html;
        }

        // Fetch Live State
        function fetchLiveState() {
            fetch(liveApiUrl)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const d = res.data;
                        renderCounters(d.counters || []);
                        renderWaitingList(d.waiting_list || [], d.total_waiting || 0);

                        // If a new ticket is called, update ticker & chime
                        if (d.latest_called && d.latest_called.ticket_number !== lastCalledTicketNumber) {
                            lastCalledTicketNumber = d.latest_called.ticket_number;
                            playChime();

                            const ticker = document.getElementById('tickerText');
                            if (ticker && d.latest_called.announcement_khmer) {
                                ticker.innerHTML = `📢 <strong>${d.latest_called.announcement_khmer}</strong> • សូមអរគុណ!`;
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error('Fetch live state error:', err);
                });
        }

        // Auto-poll every 3 seconds (fail-safe for zero config WebSocket)
        fetchLiveState();
        setInterval(fetchLiveState, 3000);
    </script>
</body>
</html>
