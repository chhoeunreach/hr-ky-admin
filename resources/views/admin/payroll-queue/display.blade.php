<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $session->title }} - អេក្រង់បង្ហាញជួរបើកប្រាក់ខែ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/vendors/core/core.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/demo1/style.css') }}" rel="stylesheet">
    <style>
        * {
            font-family: 'Kantumruy Pro', 'Khmer OS Battambang', sans-serif;
        }
        body {
            background-color: #0b132b;
            color: #ffffff;
            margin: 0;
            padding: 15px 25px;
            overflow-x: hidden;
        }
        .tv-header {
            border-bottom: 2px solid #1c2541;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .counter-box {
            background: #1c2541;
            border-radius: 12px;
            border: 2px solid #3a506b;
            padding: 15px;
            text-align: center;
            height: 100%;
            transition: all 0.3s;
        }
        .counter-box.calling {
            border-color: #48cae4;
            box-shadow: 0 0 25px rgba(72, 202, 228, 0.5);
            animation: tvPulse 1.2s infinite;
        }
        @keyframes tvPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .counter-title {
            font-size: 18px;
            font-weight: 700;
            color: #48cae4;
            text-transform: uppercase;
            border-bottom: 1px solid #3a506b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .ticket-number-big {
            font-size: 42px;
            font-weight: 800;
            color: #ffb703;
            letter-spacing: 2px;
            margin: 8px 0;
            text-shadow: 0 2px 10px rgba(255, 183, 3, 0.4);
        }
        .employee-name-tv {
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dept-tv {
            font-size: 13px;
            color: #a0aec0;
        }
        .waiting-card {
            background: #1c2541;
            border-radius: 12px;
            border: 1px solid #3a506b;
            padding: 15px;
            height: 100%;
        }
        .qr-card-tv {
            background: #ffffff;
            border-radius: 10px;
            padding: 12px;
            display: inline-block;
        }
        .badge-tv {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="tv-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold mb-1" style="color: #48cae4;">
            🎟️ {{ $session->title }}
        </h2>
        <div class="text-white-50 fs-6">
            សាខា: {{ $session->branch?->name ?: 'គ្រប់សាខា' }} | ខែ: {{ $session->month }}
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="text-end">
            <div class="fs-4 fw-bold text-warning" id="liveTime">{{ date('h:i:s A') }}</div>
            <div class="small text-white-50">{{ date('d M Y') }}</div>
        </div>
        <button id="btnEnableAudio" class="btn btn-outline-info btn-sm">
            🔔 បើកសំឡេងរោទិ៍
        </button>
    </div>
</div>

<div class="row g-3 mb-4" id="tvCountersContainer">
    {{-- 5 Counters Grid --}}
    @foreach($counters as $cNum => $counter)
        @php
            $ticket = $counter['ticket'] ?? null;
            $isBusy = !empty($ticket);
            $isCalling = $isBusy && $ticket->status === 'calling';
        @endphp
        <div class="col">
            <div class="counter-box {{ $isCalling ? 'calling' : '' }}">
                <div class="counter-title">{{ $counter['counter_name'] }}</div>
                @if($isBusy)
                    <div class="ticket-number-big">{{ $ticket->ticket_number }}</div>
                    <img src="{{ $ticket->employee?->avatar_url ?: asset('assets/images/img.png') }}" 
                         alt="Avatar" class="rounded-circle border mb-2" style="width: 64px; height: 64px; object-fit: cover; border-color: #3a506b !important;">
                    <div class="employee-name-tv">{{ $ticket->employee?->name ?: '—' }}</div>
                    <div class="dept-tv">{{ $ticket->employee?->department?->dept_name ?: 'General' }}</div>
                    <div class="mt-2">
                        <span class="badge {{ $isCalling ? 'bg-warning text-dark' : 'bg-success' }} badge-tv">
                            {{ $isCalling ? 'កំពុងហៅ...' : 'កំពុងបម្រើ' }}
                        </span>
                    </div>
                @else
                    <div class="py-5 text-white-50">
                        <div class="fs-1 opacity-25">—</div>
                        <div class="fs-6 mt-2">តុទំនេរ</div>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Waiting List Bar --}}
    <div class="col-lg-8">
        <div class="waiting-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-warning">
                    ⏳ បញ្ជីរង់ចាំបន្ទាប់ (Waiting Next)
                </h5>
                <span class="badge bg-warning text-dark fs-6" id="tvWaitingCount">
                    រង់ចាំ: {{ $waiting_tickets->count() }} នាក់
                </span>
            </div>
            <div class="row g-2" id="tvWaitingGrid">
                @forelse($waiting_tickets->take(8) as $w)
                    <div class="col-md-3 col-sm-6">
                        <div class="p-2 rounded border border-secondary" style="background: #0b132b;">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary fs-6 me-2">{{ $w->ticket_number }}</span>
                                <div class="text-truncate">
                                    <div class="small fw-bold text-white text-truncate">{{ $w->employee?->name }}</div>
                                    <div style="font-size: 11px; color: #a0aec0;">{{ $w->employee?->department?->dept_name ?: 'Staff' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-white-50">
                        គ្មានបុគ្គលិកកំពុងរង់ចាំទេ
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Scan QR to Join Card --}}
    <div class="col-lg-4">
        <div class="waiting-card text-center d-flex align-items-center justify-content-between p-3">
            <div class="text-start">
                <h5 class="fw-bold text-info mb-1">ស្កេនចូលជួរ</h5>
                <p class="small text-white-50 mb-1">ប្រើទូរស័ព្ទស្កេន QR Code ដើម្បីទទួលបានលេខសំបុត្រ</p>
                <div class="badge bg-success badge-tv">
                    បានបើកជោគជ័យ: {{ $stats['total_paid'] }} នាក់
                </div>
            </div>
            <div class="qr-card-tv ms-3 shadow">
                {!! $qrSvg !!}
            </div>
        </div>
    </div>
</div>

<script>
    // Live clock
    setInterval(() => {
        const now = new Date();
        document.getElementById('liveTime').textContent = now.toLocaleTimeString();
    }, 1000);

    // Audio chime
    let audioAllowed = false;
    document.getElementById('btnEnableAudio').addEventListener('click', function() {
        audioAllowed = true;
        this.classList.remove('btn-outline-info');
        this.classList.add('btn-success');
        this.textContent = '🔔 សំឡេងបានបើក';
        playChime();
    });

    function playChime() {
        if (!audioAllowed) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0.3, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.6);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.25);
            gain2.gain.setValueAtTime(0.3, now + 0.25);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.25);
            osc2.stop(now + 0.9);
        } catch (e) {}
    }

    // Auto-refresh every 3.5s
    const liveStatusUrl = "{{ route('admin.payroll-queue.live-status', ['sessionId' => $session->id]) }}";
    let lastCallingTicket = null;

    function pollDisplay() {
        fetch(liveStatusUrl)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple smooth reload for kiosk or partial update
                }
            })
            .catch(() => {});
    }

    setInterval(() => {
        fetch(liveStatusUrl)
            .then(res => res.json())
            .then(data => {
                // Check if any ticket is calling to play chime
                // reload page every 5s if state changed
            });
    }, 4000);
</script>

</body>
</html>
