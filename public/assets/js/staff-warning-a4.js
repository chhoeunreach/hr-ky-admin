(() => {
    if (window.StaffWarningA4) return;
    const selector = '[data-warning-a4]';
    const fit = paper => {
        const content = paper.querySelector('[data-warning-a4-content]');
        if (!content || !paper.clientHeight || getComputedStyle(content).position !== 'absolute') return;
        const style = getComputedStyle(paper);
        const available = paper.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
        const scale = Math.min(1, (available - 2) / Math.max(content.scrollHeight, 1));
        paper.style.setProperty('--warning-a4-scale', String(Math.max(scale, 0.01)));
    };
    const fitAll = () => document.querySelectorAll(selector).forEach(fit);
    const refresh = paper => {
        paper.querySelectorAll('textarea').forEach(field => {
            field.style.height = 'auto';
            field.style.height = Math.ceil(field.scrollHeight + 2) + 'px';
        });
        fit(paper);
    };
    window.StaffWarningA4 = { fit, fitAll, refresh };
    const ready = async () => {
        if (document.fonts) await document.fonts.ready;
        const papers = Array.from(document.querySelectorAll(selector));
        await Promise.all(papers.flatMap(paper => Array.from(paper.querySelectorAll('img'), image => image.decode ? image.decode().catch(() => {}) : Promise.resolve())));
        papers.forEach(paper => {
            refresh(paper);
            const content = paper.querySelector('[data-warning-a4-content]');
            if (content && window.ResizeObserver) new ResizeObserver(() => fit(paper)).observe(content);
        });
        fitAll();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
    else ready();
    window.addEventListener('resize', fitAll);
    window.addEventListener('beforeprint', fitAll);
    document.addEventListener('shown.bs.tab', fitAll);
    document.addEventListener('shown.bs.modal', fitAll);
    document.addEventListener('input', event => {
        const paper = event.target.closest(selector);
        if (paper) requestAnimationFrame(() => refresh(paper));
    });
})();
