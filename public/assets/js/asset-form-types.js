(() => {
    const initialize = () => {
        const branch = document.getElementById('branch_id');
        const type = document.getElementById('type');
        const configElement = document.getElementById('assetTypeConfig');
        if (!branch || !type || !configElement) return;
        const config = JSON.parse(configElement.textContent);
        const status = document.getElementById('assetTypeStatus');
        const buttons = type.closest('form')?.querySelectorAll('[type="submit"]') || [];
        const $ = window.jQuery;
        let generation = 0;
        const refresh = () => { if ($?.fn.select2) $(type).trigger('change.select2'); };
        const load = async (selectedType = '') => {
            const current = ++generation;
            const branchId = branch.value;
            const previousOptions = Array.from(type.options).map(option => ({text: option.text, value: option.value}));
            const previousValue = type.value;
            type.disabled = true;
            buttons.forEach(button => {button.disabled = true;});
            if (status) status.textContent = branchId ? config.loading : '';
            if (!branchId) {
                type.replaceChildren(new Option(config.placeholder, ''));
                buttons.forEach(button => {button.disabled = false;});
                refresh(); return;
            }
            try {
                const url = config.url + '/' + encodeURIComponent(branchId)
                    + (config.assetId ? '?asset_id=' + encodeURIComponent(config.assetId) : '');
                const response = await fetch(url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
                if (!response.ok) throw new Error('Types unavailable');
                const result = await response.json();
                if (current !== generation) return;
                type.replaceChildren(new Option(config.placeholder, ''));
                (result.types || []).forEach(item => type.add(new Option(item.name, String(item.id))));
                type.value = selectedType && (result.types || []).some(item => String(item.id) === selectedType) ? selectedType : '';
                if (status) status.textContent = result.types?.length ? '' : config.empty;
            } catch (_) {
                if (current !== generation) return;
                if (selectedType && previousValue === selectedType) {
                    type.replaceChildren(...previousOptions.map(item => new Option(item.text, item.value)));
                    type.value = previousValue;
                } else type.replaceChildren(new Option(config.placeholder, ''));
                if (status) status.textContent = config.error;
            } finally {
                if (current === generation) {
                    type.disabled = false;
                    buttons.forEach(button => {button.disabled = false;});
                    refresh();
                }
            }
        };
        if ($?.fn.select2) $(branch).on('change', () => load());
        else branch.addEventListener('change', () => load());
        load(String(type.value || config.initialType || ''));
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
