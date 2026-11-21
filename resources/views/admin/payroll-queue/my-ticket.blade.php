<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>សំបុត្រជួរបើកប្រាក់ខែ - {{ $ticket->ticket_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/vendors/core/core.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/demo1/style.css') }}" rel="stylesheet">
    <style>
        * { font-family: 'Kantumruy Pro', 'Khmer OS Battambang', sans-serif; }
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .ticket-card {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            text-align: center;
        }
        .ticket-num {
            font-size: 54px;
            font-weight: 800;
            color: #0d6efd;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        .calling-box {
            background: #fff3cd;
            border: 2px solid #ffecb5;
            animation: pulseTicket 1.2s infinite;
        }
        @keyframes pulseTicket {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="ticket-card">
    <div class="bg-primary text-white p-3">
        <h5 class="fw-bold mb-0">សំបុត្ររង់ចាំបើកប្រាក់ខែ (Queue Ticket)</h5>
        <small class="text-white-50">{{ $ticket->session?->title }}</small>
    </div>

    <div class="p-4">
        {{-- Status Alert Box --}}
        <div id="statusAlertBox" class="p-3 rounded-3 mb-3 {{ $ticket->status === 'calling' ? 'calling-box' : ($ticket->status === 'paid' ? 'bg-success text-white' : 'bg-light') }}">
            @if($ticket->status === 'calling')
                <div class="fs-4 fw-bold text-danger mb-1">🎉 ដល់វេនអ្នកហើយ!</div>
                <div class="fs-5 fw-bold text-dark">សូមអញ្ជើញទៅកាន់ <span class="badge bg-danger fs-5">តុទី {{ $ticket->counter_number }}</span></div>
            @elseif($ticket->status === 'serving')
                <div class="fs-5 fw-bold text-primary mb-1">កំពុងផ្ទៀងផ្ទាត់ និងបើកប្រាក់ខែ</div>
                <div class="text-muted">នៅតុទី {{ $ticket->counter_number }}</div>
            @elseif($ticket->status === 'paid')
                <div class="fs-4 fw-bold mb-1">✅ បានបើកប្រាក់ខែជោគជ័យ!</div>
                <div class="small">ទឹកប្រាក់បានបើក: <strong>${{ number_format($ticket->net_amount_paid, 2) }}</strong></div>
            @elseif($ticket->status === 'skipped')
                <div class="fw-bold">{{ __('index.skip') }}</div>
                <a href="{{ route('payroll-queue.join', ['token' => $ticket->session->qr_token]) }}">Scan or open the session link to rejoin.</a>
            @elseif($ticket->status === 'cancelled')
                <div class="fw-bold">This ticket has been cancelled.</div>
            @else
                <div class="fs-6 fw-bold text-warning text-dark mb-1">⏳ កំពុងរង់ចាំក្នុងជួរ (Waiting)</div>
                <div class="text-muted small">មនុស្សនៅពីមុខអ្នក: <strong id="aheadCountDisplay" class="text-primary fs-6">{{ $aheadCount }}</strong> នាក់</div>
            @endif
        </div>

        {{-- Ticket Number --}}
        <div class="small text-muted text-uppercase fw-bold">លេខសំបុត្ររបស់អ្នក (Your Ticket)</div>
        <div class="ticket-num" id="ticketNumberDisplay">{{ $ticket->ticket_number }}</div>

        <hr class="my-3">

        {{-- Employee Profile --}}
        <div class="d-flex align-items-center justify-content-center mb-3">
            <img src="{{ $ticket->employee?->avatar_url ?: asset('assets/images/img.png') }}" 
                 alt="Avatar" class="rounded-circle border me-3" style="width: 52px; height: 52px; object-fit: cover;">
            <div class="text-start">
                <div class="fw-bold text-dark fs-6">{{ $ticket->employee?->name }}</div>
                <div class="text-muted small">អត្តលេខ: {{ $ticket->employee?->employee_code ?: $ticket->employee?->username }}</div>
                <div class="badge bg-light text-secondary border">{{ $ticket->employee?->department?->dept_name ?: 'General' }}</div>
            </div>
        </div>

        <div class="text-muted small mb-2">
            ម៉ោងចូលជួរ: <strong>{{ $ticket->joined_at ? $ticket->joined_at->format('h:i A') : '—' }}</strong>
        </div>

        <div class="alert alert-info py-2 small mb-0">
            ℹ️ ប្រព័ន្ធនឹងធ្វើបច្ចុប្បន្នភាពដោយស្វ័យប្រវត្តិនៅពេលដល់វេនអ្នក។
        </div>
    </div>
</div>

<script>
    const initialStatus = @js($ticket->status);
    const statusUrl = "{{ route('payroll-queue.ticket-status', ['ticketId' => $ticket->id]) }}";

    function pollTicket() {
        fetch(statusUrl)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                if (data.status !== initialStatus) {
                    window.location.reload();
                    return;
                }
                const ahead = document.getElementById('aheadCountDisplay');
                if (ahead) ahead.textContent = data.ahead_count;
            })
            .catch(() => {});
    }

    // Auto-poll every 3.5 seconds
    setInterval(pollTicket, 3500);
</script>

</body>
</html>
