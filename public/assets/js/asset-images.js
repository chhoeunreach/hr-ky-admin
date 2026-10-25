(() => {
    const input = document.getElementById('images');
    const previews = document.getElementById('asset-image-previews');
    if (!input || !previews) return;
    let urls = [];
    const clear = () => { urls.forEach(url => URL.revokeObjectURL(url)); urls = []; previews.replaceChildren(); };
    input.addEventListener('change', () => {
        clear();
        const files = Array.from(input.files || []);
        const existing = document.querySelectorAll('[name="remove_images[]"]');
        const retained = Array.from(existing).filter(field => !field.checked).length;
        input.setCustomValidity(files.length + retained > 10 ? input.closest('div').querySelector('.form-text').textContent : '');
        files.forEach(file => {
            const img = document.createElement('img');
            const url = URL.createObjectURL(file);
            urls.push(url);
            img.src = url; img.alt = file.name; img.title = file.name;
            img.width = 110; img.height = 90; img.style.objectFit = 'contain';
            const link = document.createElement('a');
            link.href = url; link.setAttribute('data-asset-image', '');
            link.appendChild(img); previews.appendChild(link);
        });
    });
    document.querySelectorAll('[name="remove_images[]"]').forEach(field => field.addEventListener('change', () => input.dispatchEvent(new Event('change'))));
    window.addEventListener('pagehide', clear);
})();
