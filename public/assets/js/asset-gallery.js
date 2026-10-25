(() => {
    const viewer = document.getElementById('assetImageViewer');
    if (!viewer) return;
    const full = viewer.querySelector('[data-image-full]');
    const stage = viewer.querySelector('[data-image-stage]');
    const error = viewer.querySelector('[data-image-error]');
    const zoom = viewer.querySelector('[data-image-zoom]');
    const previous = viewer.querySelector('[data-image-previous]');
    const next = viewer.querySelector('[data-image-next]');
    let items = [], index = 0, trigger = null;
    const fit = () => { stage.classList.remove('is-zoomed'); zoom.textContent = viewer.dataset.zoomLabel; zoom.setAttribute('aria-pressed', 'false'); };
    const display = position => {
        index = (position + items.length) % items.length;
        const item = items[index];
        fit(); full.hidden = false; error.hidden = true;
        full.alt = item.querySelector('img')?.alt || viewer.dataset.imageLabel;
        viewer.querySelector('#assetImageViewerTitle').textContent = full.alt;
        viewer.querySelector('[data-image-counter]').textContent = `${index + 1} / ${items.length}`;
        viewer.querySelector('[data-image-original]').href = item.href;
        viewer.querySelector('[data-image-download]').href = item.href;
        previous.disabled = next.disabled = items.length < 2;
        stage.scrollTop = stage.scrollLeft = 0;
        full.src = item.href;
    };
    window.AssetGallery = {
        render(container, urls, name) {
            if (!container) return;
            container.replaceChildren(); container.setAttribute('data-asset-gallery', '');
            if (!urls.length) {
                const empty = document.createElement('p'); empty.className = 'asset-gallery-empty';
                empty.textContent = viewer.dataset.empty; container.appendChild(empty); return;
            }
            urls.forEach((url, position) => {
                const link = document.createElement('a');
                link.href = url; link.className = 'asset-gallery-card';
                link.setAttribute('data-asset-image', '');
                link.setAttribute('aria-label', `${name} · ${viewer.dataset.imageLabel} ${position + 1}`);
                const image = document.createElement('img');
                image.src = url; image.alt = name; image.loading = position ? 'lazy' : 'eager';
                const count = document.createElement('span'); count.className = 'asset-gallery-number';
                count.textContent = `${position + 1} / ${urls.length}`;
                link.appendChild(image); link.appendChild(count); container.appendChild(link);
            });
        },
    };
    document.addEventListener('click', event => {
        const link = event.target.closest('a[data-asset-image]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        if (typeof viewer.showModal !== 'function') return;
        const gallery = link.closest('[data-asset-gallery]');
        if (!gallery) return;
        event.preventDefault(); trigger = link;
        items = Array.from(gallery.querySelectorAll('a[data-asset-image]'));
        // Keep focus inside the parent Bootstrap modal while the native dialog is open.
        (link.closest('.modal') || document.body).appendChild(viewer);
        display(items.indexOf(link)); viewer.showModal();
        viewer.querySelector('[data-image-close]').focus();
    });
    previous.addEventListener('click', () => display(index - 1));
    next.addEventListener('click', () => display(index + 1));
    zoom.addEventListener('click', () => {
        const active = stage.classList.toggle('is-zoomed');
        zoom.textContent = active ? viewer.dataset.fitLabel : viewer.dataset.zoomLabel;
        zoom.setAttribute('aria-pressed', String(active));
    });
    viewer.querySelector('[data-image-close]').addEventListener('click', () => viewer.close());
    viewer.addEventListener('keydown', event => {
        event.stopPropagation();
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault(); display(index + (event.key === 'ArrowLeft' ? -1 : 1));
        }
    });
    viewer.addEventListener('close', () => { full.removeAttribute('src'); items = []; trigger?.focus(); });
    full.addEventListener('error', () => { full.hidden = true; error.hidden = false; });
})();
