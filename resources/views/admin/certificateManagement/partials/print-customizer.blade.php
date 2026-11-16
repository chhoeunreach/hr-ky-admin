@php($mode = $mode ?? 'single') {{-- single | batch --}}
<div id="printCustomizer" class="no-print mx-auto mb-4 bg-white rounded-xl shadow-lg border border-slate-200" style="max-width: 1100px;">
    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-slate-200 bg-slate-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <span class="text-lg">⚙️</span>
            <div>
                <div class="font-bold text-slate-800 text-sm">Customize Before Print / កំណត់មុនបោះពុម្ព</div>
                <div class="text-xs text-slate-500">Adjust layout, visibility, scale and margins. Changes apply to preview and print.</div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg shadow-sm text-sm">
                🖨️ {{ $mode === 'batch' ? 'Print All' : 'Print / Save PDF' }}
            </button>
            <button type="button" onclick="certPrintReset()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg border border-slate-300 text-sm">Reset</button>
            @if($mode === 'batch')
                <a href="{{ route('admin.certificates.index') }}" class="px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white font-semibold rounded-lg text-sm">← Directory</a>
            @endif
        </div>
    </div>

    <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <!-- Layout & Scale -->
        <div class="space-y-3">
            <div class="font-semibold text-slate-700 border-b pb-1">Layout & Scale</div>
            <label class="flex items-center justify-between gap-2">
                <span>Scale (%)</span>
                <input id="pc_scale" type="range" min="50" max="120" value="100" class="w-32" oninput="certPrintApply()">
                <span id="pc_scale_val" class="w-10 text-right font-mono text-xs">100</span>
            </label>
            <label class="flex items-center justify-between gap-2">
                <span>Page margin (mm)</span>
                <input id="pc_margin" type="number" min="0" max="30" step="1" value="0" class="w-20 border rounded px-2 py-1 text-sm" oninput="certPrintApply()">
            </label>
            <label class="flex items-center justify-between gap-2">
                <span>Certificate gap (mm, batch)</span>
                <input id="pc_gap" type="number" min="0" max="40" step="1" value="{{ $mode === 'batch' ? 6 : 0 }}" class="w-20 border rounded px-2 py-1 text-sm" oninput="certPrintApply()">
            </label>
            <label class="flex items-center gap-2">
                <input id="pc_center" type="checkbox" checked onchange="certPrintApply()">
                <span>Center on page</span>
            </label>
        </div>

        <!-- Visibility: Header & Body -->
        <div class="space-y-2">
            <div class="font-semibold text-slate-700 border-b pb-1">Show / Hide Elements</div>
            <label class="flex items-center gap-2"><input id="pc_border" type="checkbox" checked onchange="certPrintApply()"><span>Decorative border & frame</span></label>
            <label class="flex items-center gap-2"><input id="pc_ribbon" type="checkbox" checked onchange="certPrintApply()"><span>Hanging ribbon / medallion</span></label>
            <label class="flex items-center gap-2"><input id="pc_header" type="checkbox" checked onchange="certPrintApply()"><span>Header logo & issuer</span></label>
            <label class="flex items-center gap-2"><input id="pc_banner" type="checkbox" checked onchange="certPrintApply()"><span>Presentation banner</span></label>
            <label class="flex items-center gap-2"><input id="pc_citation" type="checkbox" checked onchange="certPrintApply()"><span>Citation text</span></label>
            <label class="flex items-center gap-2"><input id="pc_photo" type="checkbox" checked onchange="certPrintApply()"><span>Recipient photo / ID box</span></label>
        </div>

        <!-- Visibility: Footer -->
        <div class="space-y-2">
            <div class="font-semibold text-slate-700 border-b pb-1">Footer & Verification</div>
            <label class="flex items-center gap-2"><input id="pc_credential" type="checkbox" checked onchange="certPrintApply()"><span>Date & certificate ID</span></label>
            <label class="flex items-center gap-2"><input id="pc_signatures" type="checkbox" checked onchange="certPrintApply()"><span>Signatures</span></label>
            <label class="flex items-center gap-2"><input id="pc_seal" type="checkbox" checked onchange="certPrintApply()"><span>Gold seal</span></label>
            <label class="flex items-center gap-2"><input id="pc_qr" type="checkbox" checked onchange="certPrintApply()"><span>QR & verify badge</span></label>
            <div class="text-xs text-slate-500 pt-1">Tip: In the print dialog enable "Background graphics" for best color fidelity.</div>
        </div>
    </div>
</div>

<style id="certPrintDynamicStyle"></style>
<style>
    @media print {
        #printCustomizer { display: none !important; }
    }
</style>

<script>
(function () {
    function setDisplay(selector, show) {
        document.querySelectorAll(selector).forEach(function (el) {
            el.style.display = show ? '' : 'none';
        });
    }

    window.certPrintApply = function () {
        var scale = parseInt(document.getElementById('pc_scale').value || '100', 10);
        var margin = parseFloat(document.getElementById('pc_margin').value || '0');
        var gap = parseFloat(document.getElementById('pc_gap').value || '0');
        var center = document.getElementById('pc_center').checked;
        document.getElementById('pc_scale_val').textContent = scale;

        setDisplay('.cert-border-svg', document.getElementById('pc_border').checked);
        setDisplay('.cert-ribbon-zone', document.getElementById('pc_ribbon').checked);
        setDisplay('.cert-header-zone', document.getElementById('pc_header').checked);
        setDisplay('.cert-banner-zone', document.getElementById('pc_banner').checked);
        setDisplay('.cert-citation-zone', document.getElementById('pc_citation').checked);
        setDisplay('.cert-photo-zone', document.getElementById('pc_photo').checked);
        setDisplay('.cert-credential-zone', document.getElementById('pc_credential').checked);
        setDisplay('.cert-signature-zone', document.getElementById('pc_signatures').checked);
        setDisplay('.cert-seal-zone', document.getElementById('pc_seal').checked);
        setDisplay('.cert-qr-zone', document.getElementById('pc_qr').checked);

        var containers = document.querySelectorAll('.certificate-container');
        var s = scale / 100;
        containers.forEach(function (el) {
            el.style.transformOrigin = center ? 'top center' : 'top left';
            el.style.transform = s === 1 ? '' : 'scale(' + s + ')';
        });

        var pages = document.querySelectorAll('.batch-page');
        pages.forEach(function (p) {
            p.style.marginBottom = gap + 'mm';
            if (s !== 1) {
                // reserve scaled height so pages don't overlap in preview
                p.style.height = (760 * s) + 'px';
                p.style.width = (1080 * s) + 'px';
                p.style.overflow = 'visible';
            } else {
                p.style.height = '';
                p.style.width = '';
            }
        });

        var style = document.getElementById('certPrintDynamicStyle');
        var css = '@page { size: A4 landscape; margin: ' + margin + 'mm; }\n';
        css += '@media print { .certificate-container { transform-origin: top center !important; transform: ' + (s === 1 ? 'none' : 'scale(' + s + ')') + ' !important; }';
        css += ' .batch-page { margin-bottom: 0 !important; page-break-after: always; break-after: page; height: auto !important; width: auto !important; }';
        css += ' .batch-page:last-of-type { page-break-after: auto; break-after: auto; } }';
        style.textContent = css;
    };

    window.certPrintReset = function () {
        document.getElementById('pc_scale').value = 100;
        document.getElementById('pc_margin').value = 0;
        document.getElementById('pc_gap').value = {{ $mode === 'batch' ? 6 : 0 }};
        document.getElementById('pc_center').checked = true;
        ['pc_border','pc_ribbon','pc_header','pc_banner','pc_citation','pc_photo','pc_credential','pc_signatures','pc_seal','pc_qr'].forEach(function (id) {
            var el = document.getElementById(id); if (el) el.checked = true;
        });
        certPrintApply();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', certPrintApply);
    } else {
        certPrintApply();
    }
})();
</script>