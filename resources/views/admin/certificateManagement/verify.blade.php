<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ការផ្ទៀងផ្ទាត់វិញ្ញាបនបត្រ / Certificate Verification - {{ $verification['credential_id'] }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@600;700;800;900&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400;1,600&family=Great+Vibes&family=Kantumruy+Pro:wght@400;500;600;700&family=Moul&family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }
        .font-script { font-family: 'Great Vibes', cursive; }
        .font-signature { font-family: 'Alex Brush', cursive; }
        .font-sans-clean { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif; }
        .verify-cert-wrap { position: relative; width: 100%; max-width: 1080px; aspect-ratio: 1080 / 760; margin: 0 auto; overflow: hidden; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,.35); background: #fff; }
        .verify-cert-scale { position: absolute; top: 0; left: 0; width: 1080px; height: 760px; transform-origin: top left; }
        @media (max-width: 1100px) {
            .verify-cert-wrap { aspect-ratio: auto; height: auto; overflow: visible; max-width: 100%; }
            .verify-cert-scale { position: relative; transform: none !important; width: 1080px; }
            .verify-cert-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-6xl w-full bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-amber-500/30 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-[#2D1328] via-[#4A1D42] to-[#2D1328] text-white p-6 text-center relative border-b-4 border-[#C89B3C]">
            <div class="w-16 h-16 rounded-full border-2 border-[#C89B3C] mx-auto mb-3 overflow-hidden bg-white flex items-center justify-center shadow-lg">
                <img src="{{ asset('assets/aistudio/unnamed_521b71dc.jpg') }}" alt="Knea Yoeng" class="w-full h-full object-cover">
            </div>
            <h1 class="font-moul text-lg text-amber-300">ស្ថាប័ន គ្នា​យើង (KNEA YOENG)</h1>
            <p class="font-cinzel text-xs tracking-widest text-amber-200/80 font-bold mt-1">OFFICIAL CREDENTIAL VERIFICATION SYSTEM</p>
        </div>

        <!-- Verification Status Plaque -->
        <div class="p-6 sm:p-8 space-y-6">

            @if(!empty($certificate) && !empty($certificate['recipientName']))
            <div class="verify-cert-scroll">
                <div class="verify-cert-wrap" id="verifyCertWrap">
                    <div class="verify-cert-scale" id="verifyCertScale">
                        @include('admin.certificateManagement.partials.certificate-canvas', ['certificate' => $certificate])
                    </div>
                </div>
            </div>
            @endif
            
            <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl flex-shrink-0 shadow-md">
                    ✓
                </div>
                <div>
                    <h2 class="font-bold text-emerald-900 text-base flex items-center gap-2">
                        <span>វិញ្ញាបនបត្រពិតប្រាកដ / Authenticated Credential</span>
                    </h2>
                    <p class="text-xs text-emerald-700 mt-0.5">This certificate has been cryptographically verified and recorded in the Knea Yoeng HRMS official registry.</p>
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 divide-y divide-slate-200 text-sm">
                
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">លេខសម្គាល់ / Credential ID:</span>
                    <span class="font-mono font-bold text-slate-900 bg-amber-100 px-2.5 py-0.5 rounded text-xs border border-amber-300">
                        {{ $verification['credential_id'] }}
                    </span>
                </div>

                <div class="py-2.5 flex justify-between items-start">
                    <span class="text-slate-500 font-medium">ម្ចាស់ជ័យលាភី / Recipient:</span>
                    <div class="text-right">
                        @if(!empty($verification['khmer_recipient_name']))
                            <div class="font-moul text-slate-900 text-sm">{{ $verification['khmer_recipient_name'] }}</div>
                        @endif
                        <div class="font-bold text-slate-800">{{ $verification['recipient_name'] }}</div>
                    </div>
                </div>

                <div class="py-2.5 flex justify-between items-start">
                    <span class="text-slate-500 font-medium">កម្មវិធី / Title:</span>
                    <div class="text-right max-w-xs">
                        @if(!empty($verification['khmer_course_title']))
                            <div class="font-bold text-slate-900 text-xs">{{ $verification['khmer_course_title'] }}</div>
                        @endif
                        <div class="text-slate-700 text-xs">{{ $verification['course_title'] }}</div>
                    </div>
                </div>

                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">ម៉ោងបណ្តុះបណ្តាល / CPD Hours:</span>
                    <span class="font-bold text-amber-900 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        {{ $verification['training_hours'] }}.0 Digital HR Hours
                    </span>
                </div>

                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">ស្ថានភាព / Accreditation:</span>
                    <span class="font-semibold text-emerald-800">
                        {{ $verification['compliance_status'] }}
                    </span>
                </div>

                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">កាលបរិច្ឆេទ / Issue Date:</span>
                    <span class="font-medium text-slate-800">
                        {{ $verification['khmer_issue_date'] ?? $verification['issue_date'] }}
                    </span>
                </div>

                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">ស្ថាប័នចេញ / Issuer:</span>
                    <span class="font-semibold text-slate-800">
                        {{ $verification['issuer'] }}
                    </span>
                </div>
            </div>

            <!-- Cryptographic Verification Footprint -->
            <div class="p-3 bg-slate-900 text-slate-300 rounded-lg text-[10px] font-mono break-all space-y-1">
                <div class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">SECURITY HASH / SHA-256:</div>
                <div class="text-amber-400">{{ $verification['tamper_evident_hash'] }}</div>
                <div class="text-slate-500 text-[9px] pt-1">Verified at: {{ $verification['verified_at'] }}</div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="{{ route('certificates.show', $verification['credential_id']) }}" class="flex-1 text-center py-3 px-4 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold rounded-xl shadow-md transition">
                    📜 មើលវិញ្ញាបនបត្រពេញ / View Full Certificate
                </a>
                <a href="{{ route('certificates.downloadPdf', ['id' => $verification['credential_id']]) }}" class="flex-1 text-center py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl shadow-md transition">
                    ⬇️ Download PDF
                </a>
            </div>

        </div>

        <div class="bg-slate-100 text-center py-3 text-[11px] text-slate-500 border-t border-slate-200">
            © 2026 គ្នា​យើង (Knea Yoeng) HRMS · Digital Leadership & HR Accreditation
        </div>

    </div>

    <script>
        (function () {
            function fitVerifyCert() {
                var wrap = document.getElementById('verifyCertWrap');
                var inner = document.getElementById('verifyCertScale');
                if (!wrap || !inner) return;
                if (window.innerWidth <= 1100) {
                    inner.style.transform = 'none';
                    wrap.style.height = 'auto';
                    return;
                }
                var scale = Math.min(wrap.clientWidth / 1080, 1);
                inner.style.transform = 'scale(' + scale + ')';
                wrap.style.height = (760 * scale) + 'px';
            }
            window.addEventListener('load', fitVerifyCert);
            window.addEventListener('resize', fitVerifyCert);
            fitVerifyCert();
        })();
    </script>
</body>
</html>
