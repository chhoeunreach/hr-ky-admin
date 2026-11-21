<script>
(() => {
    const i18nPicker = {
        noEmployees: @json(__('daily_records.no_employees_found')),
        unableToLoad: @json(__('daily_records.unable_to_load_employees')),
        prev: @json(__('daily_records.prev')),
        next: @json(__('daily_records.next'))
    };

    document.querySelectorAll('[data-daily-picker]').forEach(picker => {
        const input = picker.querySelector('[data-picker-search]');
        const output = picker.querySelector('[data-picker-results]');
        const selected = picker.querySelector('[data-picker-selected]');
        const field = picker.dataset.pickerName;
        const multiple = field.endsWith('[]');
        let choices = new Map(JSON.parse(picker.dataset.pickerInitial).map(employee => [Number(employee.id), employee.name]));
        let pending, generation = 0;

        const render = () => {
            selected.replaceChildren();
            for (const [id, name] of choices) {
                const pill = document.createElement('span');
                pill.className = 'dr-picker-pill';
                
                const text = document.createElement('span');
                text.textContent = name;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '&times;';
                removeBtn.title = 'Remove ' + name;
                removeBtn.setAttribute('aria-label', 'Remove ' + name);
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    choices.delete(id);
                    render();
                });

                const valueInput = document.createElement('input');
                valueInput.type = 'hidden';
                valueInput.name = field;
                valueInput.value = id;

                pill.append(text, removeBtn, valueInput);
                selected.append(pill);
            }
        };

        const showOutput = () => {
            output.style.removeProperty('display');
            output.style.display = 'grid';
        };

        const hideOutput = () => {
            output.style.setProperty('display', 'none', 'important');
        };

        const search = async (page, request) => {
            const url = new URL(picker.dataset.pickerUrl);
            url.searchParams.set('search', input.value.trim());
            url.searchParams.set('page', page);
            try {
                const response = await fetch(url, {headers: {Accept: 'application/json'}});
                if (!response.ok) throw Error();
                const result = await response.json();
                if (request !== generation) return;
                output.replaceChildren();
                if (result.data && result.data.length > 0) {
                    showOutput();
                    for (const employee of result.data) {
                        const item = document.createElement('div');
                        item.className = 'd-flex align-items-center justify-content-between p-2 rounded text-start text-dark border-bottom cursor-pointer';
                        item.style.cursor = 'pointer';
                        item.style.transition = 'background-color 0.15s ease';
                        item.addEventListener('mouseenter', () => item.style.backgroundColor = 'var(--dr-surface-subtle, #f1f5f9)');
                        item.addEventListener('mouseleave', () => item.style.backgroundColor = 'transparent');
                        
                        const nameSpan = document.createElement('span');
                        nameSpan.className = 'fw-bold small';
                        nameSpan.textContent = employee.name;

                        const metaSpan = document.createElement('span');
                        metaSpan.className = 'text-muted small';
                        metaSpan.textContent = [employee.department, employee.location].filter(Boolean).join(' · ');

                        item.append(nameSpan, metaSpan);
                        item.addEventListener('click', () => {
                            if (!multiple) choices.clear();
                            choices.set(Number(employee.id), employee.name);
                            input.value = '';
                            output.replaceChildren();
                            hideOutput();
                            render();
                        });
                        output.append(item);
                    }
                } else {
                    showOutput();
                    const empty = document.createElement('div');
                    empty.className = 'p-2 text-center text-muted small';
                    empty.textContent = i18nPicker.noEmployees;
                    output.append(empty);
                }

                if (result.next_page_url || page > 1) {
                    const navRow = document.createElement('div');
                    navRow.className = 'd-flex justify-content-between gap-2 p-1 border-top mt-1';
                    if (page > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.type = 'button';
                        prevBtn.className = 'btn btn-xs btn-outline-secondary small py-1 px-2';
                        prevBtn.textContent = i18nPicker.prev;
                        prevBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            search(page - 1, ++generation);
                        });
                        navRow.append(prevBtn);
                    }
                    if (result.next_page_url) {
                        const nextBtn = document.createElement('button');
                        nextBtn.type = 'button';
                        nextBtn.className = 'btn btn-xs btn-outline-secondary small py-1 px-2 ms-auto';
                        nextBtn.textContent = i18nPicker.next;
                        nextBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            search(page + 1, ++generation);
                        });
                        navRow.append(nextBtn);
                    }
                    output.append(navRow);
                }
            } catch (_) {
                if (request === generation) {
                    showOutput();
                    output.textContent = i18nPicker.unableToLoad;
                }
            }
        };

        input.addEventListener('input', () => {
            clearTimeout(pending);
            const request = ++generation;
            if (input.value.trim().length > 0) {
                pending = setTimeout(() => search(1, request), 250);
            } else {
                output.replaceChildren();
                hideOutput();
            }
        });

        input.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                clearTimeout(pending);
                search(1, ++generation);
            }
            if (event.key === 'Escape') {
                generation++;
                output.replaceChildren();
                hideOutput();
            }
        });

        document.addEventListener('click', (event) => {
            if (!picker.contains(event.target)) {
                hideOutput();
            }
        });

        render();
    });
})();
</script>
