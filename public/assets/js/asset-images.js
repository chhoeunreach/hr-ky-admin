(() => {
    const input = document.getElementById('images');
    const previews = document.getElementById('asset-image-previews');
    if (!input || !previews) return;
    const choose = document.getElementById('assetFileChoose');
    const status = document.getElementById('assetFileStatus');
    if (choose) choose.addEventListener('click', () => input.click());
    let urls = [];
    const clear = () => { urls.forEach(url => URL.revokeObjectURL(url)); urls = []; previews.replaceChildren(); };
    input.addEventListener('change', () => {
        clear();
        const files = Array.from(input.files || []);
        if (status) status.textContent = files.length ? files.map(file => file.name).join(', ') : status.dataset.empty;
        const existing = document.querySelectorAll('[name="remove_images[]"]');
        const retained = Array.from(existing).filter(field => !field.checked).length;
        input.setCustomValidity(files.length + retained > 10 ? document.getElementById('assetFileHelp').textContent : '');
        files.forEach(file => {
            const url = URL.createObjectURL(file);
            urls.push(url);
            const link = document.createElement('a');
            link.href = url;
            link.title = file.name;
            if (/^image\/(jpeg|png|webp)$/.test(file.type)) {
                const img = document.createElement('img');
                img.src = url; img.alt = file.name; img.title = file.name;
                img.width = 110; img.height = 90; img.style.objectFit = 'contain';
                link.setAttribute('data-asset-image', '');
                link.appendChild(img);
            } else {
                link.className = 'border rounded p-3 d-flex flex-column align-items-center gap-2';
                link.download = file.name;
                const icon = document.createElement('i');
                icon.className = 'fa-regular fa-file-lines fa-2x';
                icon.setAttribute('aria-hidden', 'true');
                const name = document.createElement('span');
                name.className = 'small text-break'; name.textContent = file.name;
                link.appendChild(icon); link.appendChild(name);
            }
            previews.appendChild(link);
        });
    });
    document.querySelectorAll('[name="remove_images[]"]').forEach(field => field.addEventListener('change', () => input.dispatchEvent(new Event('change'))));
    window.addEventListener('pagehide', clear);
})();
