<script>
document.querySelectorAll('[data-daily-picker]').forEach(picker => {
    const input = picker.querySelector('[data-picker-search]');
    const output = picker.querySelector('[data-picker-results]');
    const selected = picker.querySelector('[data-picker-selected]');
    const field = picker.dataset.pickerName;
    const multiple = field.endsWith('[]');
    let choices = new Map(JSON.parse(picker.dataset.pickerInitial).map(employee => [Number(employee.id), employee.name]));
    let pending, generation = 0;

    const button = (label, click, style = 'outline-secondary') => {
        const element = document.createElement('button');
        element.type = 'button';
        element.className = 'btn btn-sm btn-' + style;
        element.textContent = label;
        element.addEventListener('click', click);
        return element;
    };
    const render = () => {
        selected.replaceChildren();
        for (const [id, name] of choices) {
            const value = document.createElement('input');
            value.type = 'hidden';
            value.name = field;
            value.value = id;
            selected.append(value, button(name + ' ×', () => {
                choices.delete(id);
                render();
            }));
        }
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
            for (const employee of result.data) {
                const label = [employee.name, employee.department, employee.location].filter(Boolean).join(' · ');
                output.append(button(label, () => {
                    if (!multiple) choices.clear();
                    choices.set(Number(employee.id), employee.name);
                    input.value = '';
                    output.replaceChildren();
                    render();
                }, 'outline-primary'));
            }
            if (!result.data.length) output.textContent = 'No employees found.';
            if (result.next_page_url) output.append(button('Next results', () => search(page + 1, ++generation)));
            if (page > 1) output.append(button('Previous results', () => search(page - 1, ++generation)));
        } catch (_) {
            if (request === generation) output.textContent = 'Unable to load employees. Try searching again.';
        }
    };
    input.addEventListener('input', () => {
        clearTimeout(pending);
        const request = ++generation;
        output.replaceChildren();
        if (input.value.trim()) pending = setTimeout(() => search(1, request), 250);
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
        }
    });
    render();
});
</script>
