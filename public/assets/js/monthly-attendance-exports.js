(() => {
    // Resolve controls on every click because attendance filters and the toolbar
    // are replaced when the report refreshes without a full page navigation.
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-monthly-export]');
        if (!link || event.defaultPrevented) return;
        const url = new URL(window.location.href);
        const form = document.getElementById('monthlyAttendanceFilters');
        if (form) {
            url.search = '';
            for (const [key, value] of new FormData(form)) {
                if (String(value).trim() !== '') url.searchParams.append(key, value);
            }
        }
        const search = document.getElementById('table_search');
        if (search) {
            url.searchParams.delete('search');
            if (search.value.trim() !== '') url.searchParams.set('search', search.value.trim());
        }
        // Export every matching employee, independent of the current table page.
        url.searchParams.delete('page');
        url.searchParams.delete('per_page');
        url.searchParams.delete('orientation');
        url.searchParams.set('export', link.dataset.monthlyExport);
        if (link.dataset.monthlyExport === 'pdf') {
            url.searchParams.set('orientation', new URL(link.href, window.location.href).searchParams.get('orientation') || 'landscape');
        }
        link.href = url.toString();
    });
})();
