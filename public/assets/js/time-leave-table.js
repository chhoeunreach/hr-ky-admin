(function (root) {
    'use strict';

    function exportText(data, row, column, node) {
        return node && node.hasAttribute('data-export')
            ? node.getAttribute('data-export')
            : (node ? node.textContent.trim() : String(data));
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[character]);
    }

    root.initTimeLeaveTable = function (labels) {
        const element = document.getElementById('timeLeaveTable');
        if (!element || !root.DataTable || root.DataTable.isDataTable(element)) return;

        // Use a Khmer-capable font for employee names and reasons in downloaded PDFs.
        if (root.pdfMake) {
            const font = 'https://cdn.jsdelivr.net/gh/notofonts/noto-fonts@main/hinted/ttf/NotoSansKhmer/NotoSansKhmer-Regular.ttf';
            root.pdfMake.fonts = Object.assign({}, root.pdfMake.fonts, {
                NotoSansKhmer: {normal: font, bold: font, italics: font, bolditalics: font}
            });
        }

        const exportOptions = {
            columns: ':visible',
            modifier: {search: 'applied', order: 'applied', page: 'all'},
            escapeExcelFormula: true,
            format: {body: exportText}
        };
        const fileName = 'time-leave-requests';
        const pdfButton = orientation => ({
            extend: 'pdfHtml5',
            text: orientation === 'portrait' ? 'Portrait' : 'Landscape',
            title: labels.title,
            filename: fileName,
            orientation,
            pageSize: 'A4',
            exportOptions,
            customize: doc => {
                doc.defaultStyle.font = 'NotoSansKhmer';
                doc.defaultStyle.fontSize = 9;
                doc.styles.tableHeader.fontSize = 10;
                const content = doc.content.find(item => item.table);
                if (content && content.table.body[0]) {
                    content.table.widths = content.table.body[0].map(() => '*');
                }
            }
        });
        const table = new root.DataTable(element, {
            dom: '<"time-leave-list-toolbar"lBf>rt<"time-leave-table-footer"ip>',
            pageLength: labels.pageLength,
            lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, labels.all || 'All']],
            search: {search: labels.search},
            // Preserve newest requests first until the user chooses a column.
            order: [],
            ordering: true,
            autoWidth: false,
            scrollX: true,
            columnDefs: [{targets: 'time-leave-reason', orderable: false}],
            language: {
                lengthMenu: labels.show + ' _MENU_ ' + labels.entries,
                search: '',
                searchPlaceholder: labels.searchLabel + '…',
                emptyTable: labels.empty,
                zeroRecords: labels.empty,
                info: labels.info,
                infoEmpty: labels.info.replace('_START_', '0').replace('_END_', '0').replace('_TOTAL_', '0'),
                infoFiltered: ''
            },
            buttons: [
                {extend: 'copyHtml5', text: 'Copy', title: null, exportOptions},
                {extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv" aria-hidden="true"></i> Export CSV', filename: fileName, bom: true, exportOptions},
                {extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel" aria-hidden="true"></i> Export Excel', title: labels.title, filename: fileName, exportOptions},
                {
                    extend: 'print', text: '<i class="fa-solid fa-print" aria-hidden="true"></i> Print', title: labels.title,
                    exportOptions: Object.assign({}, exportOptions, {
                        format: {body: (...args) => escapeHtml(exportText(...args))}
                    }),
                    customize: printWindow => {
                        printWindow.document.body.style.fontFamily = '"Noto Sans Khmer", Arial, sans-serif';
                    }
                },
                {extend: 'colvis', text: '<i class="fa-solid fa-table-columns" aria-hidden="true"></i> Column visibility'},
                {extend: 'collection', text: '<i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Export PDF', buttons: [pdfButton('portrait'), pdfButton('landscape')]}
            ]
        });

        const form = document.getElementById('timeLeaveFilterForm');
        const summary = document.getElementById('timeLeaveSummary');
        const sync = () => {
            const info = table.page.info();
            if (summary) {
                summary.textContent = labels.info.replace('_START_', info.recordsDisplay ? info.start + 1 : 0)
                    .replace('_END_', info.end).replace('_TOTAL_', info.recordsDisplay);
            }
            if (form) {
                form.elements.namedItem('search').value = table.search();
                form.elements.namedItem('per_page').value = table.page.len();
            }
        };
        table.on('draw', sync);
        sync();
        // The search input has no visible label in the compact sample toolbar.
        const search = document.querySelector('#timeLeaveTable_wrapper .dt-search input');
        if (search) search.setAttribute('aria-label', labels.searchLabel);
        return table;
    };
})(window);
