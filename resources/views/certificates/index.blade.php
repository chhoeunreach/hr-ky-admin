<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Certificates Registry — hr-ky-admin (Knea Yoeng)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&family=Moul&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Kantumruy Pro', sans-serif; }
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-white flex items-center justify-center text-2xl font-bold shadow-md shadow-amber-500/20">
                    📜
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <span>ប្រព័ន្ធគ្រប់គ្រងវិញ្ញាបនបត្រ</span>
                        <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">Certificates Registry</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">គ្នា​យើង · Knea Yoeng HRMS · A4 Landscape WYSIWYG & PDF Generator</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('certificates.studio') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold text-sm shadow-md shadow-amber-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <span>✨</span>
                    <span>បើក Certificate Studio ពេញលេញ</span>
                </a>
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase font-semibold text-slate-400">Total Issued</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1">{{ count($certificates) }}</div>
                    <div class="text-xs text-amber-600 font-medium mt-0.5">Accredited Records</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    🏆
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase font-semibold text-slate-400">KNEAYERNG HRMS Training</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1">165+ <span class="text-sm font-normal text-slate-500">Hours</span></div>
                    <div class="text-xs text-emerald-600 font-medium mt-0.5">CPD Verified</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    ⏱️
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase font-semibold text-slate-400">Mode & Status</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1">Standalone</div>
                    <div class="text-xs text-blue-600 font-medium mt-0.5">Zero DB Dependency Ready</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    ⚡
                </div>
            </div>
        </div>

        @if(session('status') || session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('status') ?? session('success') }}</span>
            </div>
        @endif

        <!-- Search Form -->
        <form method="GET" action="{{ route('certificates.index') }}" class="flex gap-2">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ $search ?? '' }}"
                       placeholder="ស្វែងរកតាមឈ្មោះ (EN / ខ្មែរ) លេខកូដ Credential ID ឬ Employee ID..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-sm" />
                <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">🔍</span>
            </div>
            <button type="submit" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-sm transition-colors">
                ស្វែងរក / Search
            </button>
            @if(!empty($search))
                <a href="{{ route('certificates.index') }}" class="px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-colors flex items-center">
                    សម្អាត
                </a>
            @endif
        </form>

        <!-- Certificates Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Credential ID</th>
                            <th class="py-3.5 px-4">Recipient (ខ្មែរ / English)</th>
                            <th class="py-3.5 px-4">Certificate Title / Track</th>
                            <th class="py-3.5 px-4">HR ID & Hours</th>
                            <th class="py-3.5 px-4">Issue Date</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($certificates as $cert)
                            @php
                                $credId = is_object($cert) ? ($cert->credential_id ?? $cert->id ?? '') : ($cert['credentialId'] ?? $cert['credential_id'] ?? $cert['id'] ?? '');
                                $recName = is_object($cert) ? ($cert->recipient_name ?? '') : ($cert['recipientName'] ?? $cert['recipient_name'] ?? '');
                                $khRecName = is_object($cert) ? ($cert->khmer_recipient_name ?? '') : ($cert['khmerRecipientName'] ?? $cert['khmer_recipient_name'] ?? '');
                                $title = is_object($cert) ? ($cert->certificate_title ?? '') : ($cert['certificateTitle'] ?? $cert['certificate_title'] ?? '');
                                $khTitle = is_object($cert) ? ($cert->khmer_title ?? '') : ($cert['khmerTitle'] ?? $cert['khmer_title'] ?? '');
                                $empId = is_object($cert) ? ($cert->employee_id ?? '') : ($cert['digitalHr']['employeeId'] ?? $cert['employee_id'] ?? '');
                                $hours = is_object($cert) ? ($cert->training_hours ?? 0) : ($cert['digitalHr']['trainingHours'] ?? $cert['training_hours'] ?? 0);
                                $date = is_object($cert) ? ($cert->issue_date ?? '') : ($cert['issueDate'] ?? $cert['issue_date'] ?? '');
                            @endphp
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-mono text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-0.5 inline-block">
                                        {{ $credId }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900">{{ $recName }}</div>
                                    @if($khRecName)
                                        <div class="text-xs text-slate-500 font-moul mt-0.5">{{ $khRecName }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-800">{{ $title }}</div>
                                    @if($khTitle)
                                        <div class="text-xs text-amber-900 mt-0.5 line-clamp-1">{{ $khTitle }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-mono text-xs">
                                    <div class="font-semibold text-slate-700">{{ $empId ?: '—' }}</div>
                                    <div class="text-amber-700 font-bold mt-0.5">{{ $hours }} hrs</div>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600 whitespace-nowrap">
                                    {{ $date }}
                                </td>
                                <td class="py-4 px-4 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('certificates.studio', ['id' => $credId]) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold text-xs transition-colors">
                                        ✏️ Edit Studio
                                    </a>
                                    <a href="{{ route('certificates.show', ['id' => $credId]) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                                        🖨️ View / Print
                                    </a>
                                    <a href="{{ route('certificates.downloadPdf', ['id' => $credId]) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-xs transition-colors">
                                        ⬇ PDF
                                    </a>
                                    <a href="{{ route('certificates.verify', ['id' => $credId]) }}"
                                       class="inline-block px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition-colors" title="Verify authenticity">
                                        🛡️
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <div class="text-4xl mb-2">📜</div>
                                    <div class="font-bold text-slate-700">មិនទាន់មានវិញ្ញាបនបត្រត្រូវបានរក្សាទុកទេ</div>
                                    <p class="text-xs text-slate-400 mt-1">សូមចុច «+ បើក Certificate Studio ពេញលេញ» ដើម្បីបង្កើតវិញ្ញាបនបត្រថ្មីដំបូង!</p>
                                    <div class="mt-4">
                                        <a href="{{ route('certificates.studio') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-xs font-bold shadow">
                                            + បង្កើតវិញ្ញាបនបត្រឥឡូវនេះ
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($certificates, 'links'))
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $certificates->links() }}
                </div>
            @endif
        </div>

        <!-- Footer Info -->
        <div class="text-center text-xs text-slate-400 space-y-1">
            <div>CertiCraft Studio · ស្ថាប័ន គ្នា​យើង (KNEA YOENG)</div>
            <div>Supports V1-V6 templates, 17 ornamental frames, instant A4 landscape rendering, QR verification, and PDF generation.</div>
        </div>

    </div>
</body>
</html>
