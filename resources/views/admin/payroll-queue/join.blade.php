<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ចូលជួរបើកប្រាក់ខែ - {{ $session->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/vendors/core/core.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/demo1/style.css') }}" rel="stylesheet">
    <style>
        * { font-family: 'Kantumruy Pro', 'Khmer OS Battambang', sans-serif; }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .join-card {
            max-width: 440px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="join-card">
    <div class="bg-primary text-white p-4 text-center">
        <div class="badge bg-white text-primary px-3 py-1 rounded-pill mb-2 fw-bold">
            Payday Queue Check-in
        </div>
        <h4 class="fw-bold mb-1">{{ $session->title }}</h4>
        <p class="text-white-50 small mb-0">សាខា: {{ $session->branch?->name ?: 'គ្រប់សាខា' }} | ខែ: {{ $session->month }}</p>
    </div>

    <div class="p-4">
        <div id="joinAlert" class="alert d-none" role="alert"></div>

        @if(auth()->check())
            <div class="text-center mb-4">
                <img src="{{ auth()->user()->avatar_url ?: asset('assets/images/img.png') }}" 
                     alt="Avatar" class="rounded-circle border mb-2 shadow-sm" style="width: 72px; height: 72px; object-fit: cover;">
                <h5 class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</h5>
                <div class="text-muted small">
                    អត្តលេខ: <strong>{{ auth()->user()->employee_code ?: auth()->user()->username }}</strong>
                </div>
                <div class="badge bg-light text-secondary border mt-1">
                    {{ auth()->user()->department?->dept_name ?: 'General' }}
                </div>
            </div>

            <form id="joinQueueForm">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" id="btnSubmitJoin">
                    🎟️ ចូលជួរទទួលសំបុត្រ (Join Queue)
                </button>
            </form>
        @else
            <div class="text-center mb-3">
                <p class="text-muted small">សូមបញ្ចូលអត្តលេខ ឬឈ្មោះគណនីរបស់អ្នកដើម្បីចូលជួរបើកប្រាក់ខែ</p>
            </div>

            <form id="joinQueueForm">
                @csrf
                <div class="mb-3">
                    <label for="inputIdentifier" class="form-label fw-bold">អត្តលេខ ឬឈ្មោះគណនី ឬលេខទូរស័ព្ទ</label>
                    <input type="text" class="form-control form-control-lg text-center" id="inputIdentifier" name="identifier" 
                           placeholder="ឧ. EMP001 ឬ 012345678" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" id="btnSubmitJoin">
                    🔍 ស្វែងរក & ចូលជួរ (Join Queue)
                </button>
            </form>
        @endif

        <div class="text-center mt-4">
            <small class="text-muted">ប្រព័ន្ធគ្រប់គ្រងជួរបើកប្រាក់ខែ &copy; {{ date('Y') }}</small>
        </div>
    </div>
</div>

<script>
    document.getElementById('joinQueueForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitJoin');
        const alertBox = document.getElementById('joinAlert');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងចូលជួរ...';
        alertBox.className = 'alert d-none';

        const formData = new FormData(this);

        fetch("{{ route('payroll-queue.submit-join', ['token' => $session->qr_token]) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '🎟️ ចូលជួរទទួលសំបុត្រ (Join Queue)';
            
            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.textContent = data.message;
                alertBox.classList.remove('d-none');
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 800);
                }
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = data.message || 'មិនអាចចូលជួរបានទេ!';
                alertBox.classList.remove('d-none');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '🎟️ ចូលជួរទទួលសំបុត្រ (Join Queue)';
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'មានបញ្ហាបច្ចេកទេស សូមព្យាយាមម្តងទៀត!';
            alertBox.classList.remove('d-none');
        });
    });
</script>

</body>
</html>
