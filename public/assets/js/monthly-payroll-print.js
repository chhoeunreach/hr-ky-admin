(() => {
    const pageStyle = document.getElementById('payrollPrintPageStyle');
    const sheet = document.querySelector('[data-payroll-print-sheet]');
    const paper = document.getElementById('payrollPrintPaper');
    const orientation = document.getElementById('payrollPrintOrientation');
    const button = document.querySelector('[data-print-payroll]');
    if (!pageStyle || !sheet || !paper || !orientation || !button) return;
    const updatePaper = () => {
        const size = paper.value === 'A4' ? 'A4' : 'A3';
        const direction = orientation.value === 'portrait' ? 'portrait' : 'landscape';
        pageStyle.textContent = `@page { size: ${size} ${direction}; margin: 6mm; }`;
        const width = size === 'A4' ? (direction === 'landscape' ? 297 : 210) : (direction === 'landscape' ? 420 : 297);
        sheet.style.setProperty('--payroll-sheet-width', `${width}mm`);
    };
    const ready = async () => {
        if (document.fonts) await document.fonts.ready;
        await Promise.all(Array.from(sheet.querySelectorAll('img')).map(image => image.decode ? image.decode().catch(() => {}) : Promise.resolve()));
    };
    let printing = false;
    const print = async () => {
        if (printing) return;
        printing = true; button.disabled = true;
        try {await ready(); window.print();}
        finally {printing = false; button.disabled = false;}
    };
    paper.addEventListener('change', updatePaper);
    orientation.addEventListener('change', updatePaper);
    button.addEventListener('click', print);
    updatePaper();
    window.addEventListener('load', () => {if (sheet.dataset.autoPrint === '1') print();});
})();
