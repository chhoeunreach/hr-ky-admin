<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $certificate['khmerTitle'] ?? $certificate['certificateTitle'] }} - {{ $certificate['recipientName'] }}</title>
    
    <!-- Khmer & Latin Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400&family=Kantumruy+Pro:wght@400;600;700&family=Moul&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN or Vite asset) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }
        
        @page {
            size: A4 landscape;
            margin: 0;
        }
        @media print {
            body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Print / Export Bar -->
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg shadow-md cursor-pointer">
            🖨️ Print / Save as PDF
        </button>
        <a href="{{ url('/certificates') }}" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white font-semibold rounded-lg shadow-md">
            ← Back to Directory
        </a>
    </div>

    <!-- Certificate Canvas (A4 Aspect Ratio: 1060px x 750px) -->
    <div class="relative w-[1060px] h-[750px] bg-[#FFFDF8] border-8 border-[#C89B3C] shadow-2xl overflow-hidden flex flex-col justify-between p-12 text-center text-[#2D1328]"
         style="background-image: radial-gradient(ellipse at 50% 35%, #FFFFFF 0%, #FFFDF8 65%, #FBF6EB 100%);">
        
        <!-- Ornate Gold Inner Hairline Frame -->
        <div class="absolute inset-3 border-2 border-[#2D1328] pointer-events-none opacity-40"></div>
        <div class="absolute inset-5 border border-[#C89B3C] pointer-events-none"></div>

        <!-- Top Zone: Issuer & Insignia -->
        <div class="flex flex-col items-center pt-2">
            <div class="w-16 h-16 rounded-full border-2 border-[#C89B3C] overflow-hidden shadow-md mb-2 bg-white flex items-center justify-center">
                <img src="{{ $certificate['issuer']['logoUrl'] ?? '/assets/aistudio/unnamed_521b71dc.jpg' }}" alt="Logo" class="w-full h-full object-cover">
            </div>
            <h4 class="font-moul text-sm text-[#2D1328]">{{ $certificate['issuer']['khmerName'] ?? $certificate['issuer']['name'] }}</h4>
            <p class="font-kantumruy text-xs text-[#C89B3C] font-semibold">{{ $certificate['issuer']['khmerDepartment'] ?? $certificate['issuer']['department'] }}</p>
        </div>

        <!-- Preamble Decree -->
        <div class="my-1">
            <p class="font-moul text-sm text-amber-900">{{ $certificate['khmerPreamble'] }}</p>
            <p class="font-cinzel text-[10px] tracking-widest text-[#C89B3C] font-bold">{{ $certificate['authorityPreamble'] }}</p>
        </div>

        <!-- Title & Subtitle -->
        <div class="my-1 space-y-1">
            <h1 class="font-moul text-2xl text-amber-900">{{ $certificate['khmerTitle'] }}</h1>
            <h2 class="font-cinzel text-xl font-bold tracking-wider text-[#2D1328]">{{ $certificate['certificateTitle'] }}</h2>
            <p class="font-kantumruy text-xs text-slate-700">{{ $certificate['khmerSubtitle'] }}</p>
        </div>

        <!-- Recipient -->
        <div class="my-2">
            <p class="font-kantumruy text-xs font-bold text-[#C89B3C]">{{ $certificate['khmerRecipientTitle'] ?? $certificate['recipientTitle'] }}</p>
            <div class="py-1">
                <h2 class="font-moul text-3xl text-[#2D1328]">{{ $certificate['khmerRecipientName'] }}</h2>
                <h3 class="font-cinzel text-2xl font-bold tracking-wide text-[#2D1328]">{{ $certificate['recipientName'] }}</h3>
            </div>
            <div class="w-72 h-[1.5px] bg-gradient-to-r from-transparent via-[#C89B3C] to-transparent mx-auto mt-1"></div>
        </div>

        <!-- KNEAYERNG HRMS Plaque -->
        <div class="mx-auto px-6 py-2 border-2 border-[#C89B3C]/50 bg-amber-50/50 rounded flex items-center justify-between gap-6 max-w-lg">
            <div class="text-left">
                <div class="font-cinzel text-[9px] uppercase tracking-wider text-[#C89B3C] font-bold">CPD & KNEAYERNG HRMS Accredited</div>
                <div class="font-cinzel font-bold text-xs text-amber-950">{{ $certificate['digitalHr']['trainingHours'] }}.0 ម៉ោងបណ្តុះបណ្តាលឌីជីថល / KNEAYERNG HRMS HOURS</div>
            </div>
            <div class="text-right text-[10px] font-mono font-semibold text-slate-700">
                REG: {{ $certificate['digitalHr']['employeeId'] }}<br>
                <span class="text-amber-800 font-kantumruy font-bold">{{ $certificate['digitalHr']['complianceStatus'] }}</span>
            </div>
        </div>

        <!-- Course / Citation -->
        <div class="max-w-2xl mx-auto my-1">
            <h3 class="font-moul text-base text-[#2D1328]">{{ $certificate['khmerCourseOrAchievementTitle'] }}</h3>
            <p class="font-kantumruy text-xs text-slate-700 italic px-6 mt-1">« {{ $certificate['khmerCitation'] }} »</p>
        </div>

        <!-- Signatures & Footer -->
        <div class="flex items-end justify-between pt-4 border-t border-[#C89B3C]/30 mt-2">
            <!-- Left: Verification -->
            <div class="text-left text-[10px] font-kantumruy text-slate-600">
                <div><strong>កាលបរិច្ឆេទ / Date:</strong> {{ $certificate['khmerIssueDate'] ?? $certificate['issueDate'] }}</div>
                <div class="font-mono">ID: {{ $certificate['credentialId'] }}</div>
            </div>

            <!-- Signatories -->
            <div class="flex gap-16">
                @foreach($certificate['signatories'] as $sig)
                <div class="text-center">
                    <div class="w-40 border-b border-[#2D1328] pb-1 mb-1">
                        <span class="font-serif italic text-lg text-[#2D1328]">{{ $sig['name'] }}</span>
                    </div>
                    <div class="font-kantumruy font-bold text-xs text-slate-900">{{ $sig['khmerName'] ?? $sig['name'] }}</div>
                    <div class="font-kantumruy text-[10px] text-slate-600">{{ $sig['khmerRole'] ?? $sig['role'] }}</div>
                </div>
                @endforeach
            </div>

            <!-- Right: Seal & Security -->
            <div class="text-right text-[10px] font-cinzel text-[#C89B3C] font-bold">
                <div>គ្នា​យើង OFFICIAL SEAL</div>
                <div class="text-[8px] text-slate-500 font-mono">SECURE HASH VERIFIED</div>
            </div>
        </div>
    </div>
</body>
</html>
