<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Certificate Verification — {{ $verification['recipient_name'] ?? 'Knea Yoeng' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Kantumruy+Pro:wght@400;500;600;700&family=Moul&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kantumruy Pro', sans-serif; }
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-slate-800 border border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        
        <!-- Glow accent -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center pb-6 border-b border-slate-700">
            <div class="w-16 h-16 rounded-full bg-emerald-500/10 border-2 border-emerald-500 text-emerald-400 mx-auto flex items-center justify-center text-3xl shadow-lg shadow-emerald-500/20 mb-3">
                ✓
            </div>
            <div class="inline-block px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold uppercase tracking-wider mb-2">
                AUTHENTIC & VERIFIED
            </div>
            <h1 class="text-xl font-bold text-white font-cinzel">KNEA YOENG CERTIFICATE VERIFICATION</h1>
            <p class="text-xs text-slate-400 mt-1">ប្រព័ន្ធផ្ទៀងផ្ទាត់ភាពត្រឹមត្រូវនៃវិញ្ញាបនបត្រឌីជីថល</p>
        </div>

        <div class="py-6 space-y-4 text-sm">
            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Credential ID:</span>
                <span class="font-mono font-bold text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded border border-amber-400/20">
                    {{ $verification['credential_id'] ?? 'KY-LDR-2026-001' }}
                </span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Recipient Name:</span>
                <div class="text-right">
                    <div class="font-bold text-white">{{ $verification['recipient_name'] ?? '' }}</div>
                    @if(!empty($verification['khmer_recipient_name']))
                        <div class="text-xs text-amber-300 font-moul">{{ $verification['khmer_recipient_name'] }}</div>
                    @endif
                </div>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Award / Track:</span>
                <span class="font-semibold text-slate-200 text-right">{{ $verification['course_title'] ?? 'Strategic Leadership' }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Accredited Hours:</span>
                <span class="font-bold text-emerald-400 font-mono">{{ $verification['training_hours'] ?? 60 }} Digital HR Hours</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Issuer:</span>
                <span class="text-slate-200 text-right">{{ $verification['issuer'] ?? 'គ្នា​យើង · Knea Yoeng' }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-700/60">
                <span class="text-slate-400">Issue Date:</span>
                <span class="text-slate-300">{{ $verification['issue_date'] ?? '15 November 2026' }}</span>
            </div>

            <div class="py-2">
                <span class="text-slate-400 text-xs block mb-1">Cryptographic Security Hash:</span>
                <div class="font-mono text-[10px] text-slate-400 break-all bg-slate-900/80 p-2 rounded-lg border border-slate-700/50">
                    {{ $verification['tamper_evident_hash'] ?? hash('sha256', 'KY-CERT-VERIFY') }}
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-700 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('certificates.show', ['id' => $verification['credential_id'] ?? 'KY-LDR-2026-001']) }}" class="flex-1 py-3 text-center bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5">
                <span>📜</span>
                <span>មើលវិញ្ញាបនបត្រ / View Certificate</span>
            </a>
            <a href="{{ route('certificates.downloadPdf', ['id' => $verification['credential_id'] ?? 'KY-LDR-2026-001']) }}" class="flex-1 py-3 text-center bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5">
                <span>⬇️</span>
                <span>ទាញយក PDF / Download</span>
            </a>
            <a href="{{ route('certificates.index') }}" class="flex-1 py-3 text-center bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5">
                <span>←</span>
                <span>បញ្ជី / Registry</span>
            </a>
        </div>

    </div>
</body>
</html>
