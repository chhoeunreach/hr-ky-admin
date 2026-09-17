document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('employeeDocumentsForm');
    if (!form) return;

    const rows = document.getElementById('employeeDocumentRows');
    const template = document.getElementById('employeeDocumentRowTemplate');
    const addButton = document.getElementById('addEmployeeDocumentRow');
    const modalElement = document.getElementById('employeeDocumentPhotoModal');
    const preview = document.getElementById('employeeDocumentPhotoPreview');
    const useButton = document.getElementById('useEmployeeDocumentPhoto');
    const submitButton = form.querySelector('[type="submit"]');
    document.body.appendChild(modalElement);
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const ocrFields = new Set();
    const photoFiles = new WeakMap();
    const readingRows = new WeakSet();
    let nextIndex = 0;
    let currentRow = null;
    let originalFile = null;
    let objectUrl = null;
    let cropper = null;
    let photoApplied = false;
    let activeReads = 0;

    function addRow() {
        if (rows.children.length >= 20) return;
        const container = document.createElement('div');
        container.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const row = container.firstElementChild;
        rows.appendChild(row);
        const title = row.querySelector('.document-title');
        title.value = row.querySelector('.document-type').selectedOptions[0].textContent.trim();
        title.dataset.autoTitle = title.value;
        window.feather?.replace();
        addButton.disabled = rows.children.length >= 20;
        return row;
    }

    function setStatus(row, message) {
        row.querySelector('.document-file-choice').textContent = message;
    }

    function destroyCropper() {
        if (cropper) cropper.destroy();
        cropper = null;
    }

    function setCropMode() {
        destroyCropper();
        if (modalElement.querySelector('[name="document_photo_mode"]:checked').value !== 'crop') return;
        if (!window.Cropper) {
            setStatus(currentRow, 'Crop tool unavailable. The original photo can still be uploaded.');
            modalElement.querySelector('[value="original"]').checked = true;
            return;
        }
        cropper = new Cropper(preview, { viewMode: 1, autoCropArea: 0.9, responsive: true });
    }

    function openPhoto(row, file) {
        currentRow = row;
        originalFile = file;
        photoApplied = false;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        modalElement.querySelector('[value="original"]').checked = true;
        modal.show();
    }

    function toBlob(canvas) {
        return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.92));
    }

    function asciiDigits(value) {
        return value.replace(/[០-៩]/g, (digit) => String('០១២៣៤៥៦៧៨៩'.indexOf(digit)));
    }

    function parseDate(value) {
        const digits = asciiDigits(value);
        let match = digits.match(/\b(20\d{2}|19\d{2})[\/.-](\d{1,2})[\/.-](\d{1,2})\b/);
        let year, month, day;
        if (match) {
            [, year, month, day] = match;
        } else {
            match = digits.match(/\b(\d{1,2})[\/.-](\d{1,2})[\/.-](20\d{2}|19\d{2})\b/);
            if (match) {
                [, day, month, year] = match;
            } else {
                match = digits.match(/\b(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(20\d{2}|19\d{2})\b/i);
                if (!match) return null;
                day = match[1];
                month = String(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'].indexOf(match[2].toLowerCase()) + 1);
                year = match[3];
            }
        }
        const date = new Date(Number(year), Number(month) - 1, Number(day));
        if (date.getFullYear() !== Number(year) || date.getMonth() + 1 !== Number(month) || date.getDate() !== Number(day)) return null;
        return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function fillIfEmpty(id, value) {
        const field = document.getElementById(id);
        if (field && value && !field.value.trim()) {
            field.value = value;
            ocrFields.add(id);
        }
    }

    function applyIdText(text, row) {
        const lines = asciiDigits(text).split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
        let found = 0;
        const idLine = lines.find((line) => /(?:national\s*id|identity\s*(?:no|number)|id\s*(?:no|number)|អត្តសញ្ញាណប័ណ្ណ|លេខអត្តសញ្ញាណ)/i.test(line));
        const labelledDigits = idLine?.match(/(?:\d[\s-]?){8,12}/)?.[0].replace(/\D/g, '');
        const id = labelledDigits || lines.map((line) => line.match(/(?:\d[\s-]?){9}/)?.[0].replace(/\D/g, '')).find((value) => value?.length === 9);
        if (id) { fillIfEmpty('national_id', id); found++; }

        const nationalityLine = lines.find((line) => /nationality|សញ្ជាតិ/i.test(line));
        let nationality = nationalityLine?.match(/(?:nationality|សញ្ជាតិ)\s*[:：]?\s*(Cambodian|Cambodia|Khmer|កម្ពុជា|ខ្មែរ)/i)?.[1];
        if (!nationality && /Cambodian|Cambodia|Khmer|សញ្ជាតិ\s*ខ្មែរ/i.test(text)) nationality = 'Cambodian';
        if (nationality) { fillIfEmpty('nationality', nationality); found++; }

        const expiryIndex = lines.findIndex((line) => /expir|valid\s*(?:until|thru|to)|ផុតកំណត់/i.test(line));
        let expiry = expiryIndex >= 0 ? parseDate(`${lines[expiryIndex]} ${lines[expiryIndex + 1] || ''}`) : null;
        if (!expiry) {
            const dates = lines.map(parseDate).filter(Boolean).sort();
            expiry = dates.filter((date) => date >= new Date().toISOString().slice(0, 10)).at(-1) || null;
        }
        if (expiry) {
            fillIfEmpty('nationalIdExpiryDate', expiry);
            const documentExpiry = row.querySelector('.document-expiry');
            if (!documentExpiry.value) documentExpiry.value = expiry;
            found++;
        }
        return found;
    }

    async function recognizeId(file, row) {
        if (readingRows.has(row)) return;
        const panel = row.querySelector('.document-ocr-result');
        const output = row.querySelector('.document-ocr-text');
        const retry = row.querySelector('.retry-document-ocr');
        panel.classList.remove('d-none');
        readingRows.add(row);
        retry.disabled = true;
        activeReads++;
        submitButton.disabled = true;
        if (!window.Tesseract) {
            setStatus(row, 'OCR library did not load. Check the browser connection, then try again.');
            readingRows.delete(row);
            retry.disabled = false;
            activeReads--;
            submitButton.disabled = activeReads > 0;
            return;
        }
        setStatus(row, 'Reading National ID photo...');
        let worker;
        try {
            worker = await Tesseract.createWorker('eng', 1, {
                workerPath: form.dataset.ocrWorker,
                corePath: form.dataset.ocrCore,
                langPath: form.dataset.ocrLang,
                workerBlobURL: false,
                logger: (progress) => {
                    if (progress.status === 'recognizing text' && progress.progress) {
                        setStatus(row, `Reading National ID photo... ${Math.round(progress.progress * 100)}%`);
                    }
                },
            });
            const result = await worker.recognize(file);
            if (row.querySelector('.document-type').value !== 'national_id') return;
            let text = (result.data.text || '').trim();
            let count = applyIdText(text, row);
            if (count < 3) {
                try {
                    setStatus(row, 'Checking Khmer text on National ID...');
                    await worker.reinitialize(['eng', 'khm'], 1);
                    const khmerText = (await worker.recognize(file)).data.text.trim();
                    if (khmerText && khmerText !== text) {
                        text += `${text ? '\n\n' : ''}${khmerText}`;
                        count += applyIdText(khmerText, row);
                    }
                } catch (khmerError) {
                    console.warn('Khmer OCR unavailable:', khmerError);
                }
            }
            output.textContent = text || 'No text detected.';
            setStatus(row, count ? 'ID details filled from the photo. Review them before uploading.' : 'Text was read, but no ID fields matched. Review the extracted text.');
        } catch (error) {
            output.textContent = '';
            setStatus(row, `OCR could not run: ${error.message || 'unknown error'}`);
        } finally {
            if (worker) await worker.terminate().catch(() => {});
            readingRows.delete(row);
            retry.disabled = false;
            activeReads--;
            submitButton.disabled = activeReads > 0;
        }
    }

    addButton.addEventListener('click', addRow);
    form.addEventListener('submit', function () {
        const hiddenFields = document.getElementById('employeeDocumentOcrFields');
        hiddenFields.replaceChildren();
        const profileNames = { national_id: 'national_id', nationality: 'nationality', nationalIdExpiryDate: 'national_id_expiry_date' };
        ocrFields.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `ocr_profile[${profileNames[id]}]`;
            input.value = document.getElementById(id).value;
            hiddenFields.appendChild(input);
        });
    });
    rows.addEventListener('click', function (event) {
        const retryButton = event.target.closest('.retry-document-ocr');
        if (retryButton) {
            const row = retryButton.closest('.employee-document-row');
            const file = photoFiles.get(row);
            if (file && row.querySelector('.document-type').value === 'national_id') recognizeId(file, row);
            return;
        }
        const removeButton = event.target.closest('.remove-document-row');
        if (!removeButton) return;
        removeButton.closest('.employee-document-row').remove();
        addButton.disabled = false;
        if (!rows.children.length) addRow();
    });
    rows.addEventListener('change', function (event) {
        const row = event.target.closest('.employee-document-row');
        if (!row) return;
        if (event.target.matches('.document-type')) {
            const title = row.querySelector('.document-title');
            const oldAutoTitle = title.dataset.autoTitle || '';
            const newAutoTitle = event.target.selectedOptions[0].textContent.trim();
            if (!title.value || title.value === oldAutoTitle) title.value = newAutoTitle;
            title.dataset.autoTitle = newAutoTitle;
        }
        if (event.target.matches('.document-file')) {
            const file = event.target.files[0];
            if (!file) return;
            setStatus(row, file.name);
            if (file.type.startsWith('image/')) openPhoto(row, file);
        }
    });

    modalElement.addEventListener('shown.bs.modal', setCropMode);
    modalElement.querySelectorAll('[name="document_photo_mode"]').forEach((option) => option.addEventListener('change', setCropMode));
    modalElement.addEventListener('hidden.bs.modal', function () {
        destroyCropper();
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        if (!photoApplied && currentRow) {
            currentRow.querySelector('.document-file').value = '';
            setStatus(currentRow, '');
        }
        currentRow = null;
        originalFile = null;
    });

    useButton.addEventListener('click', async function () {
        if (!currentRow || !originalFile) return;
        useButton.disabled = true;
        try {
            let file = originalFile;
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({ maxWidth: 3200, maxHeight: 3200, imageSmoothingQuality: 'high' });
                const blob = canvas && await toBlob(canvas);
                if (!blob) throw new Error('Crop failed');
                file = new File([blob], originalFile.name.replace(/\.[^.]+$/, '') + '-crop.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                currentRow.querySelector('.document-file').files = transfer.files;
            }
            const row = currentRow;
            photoFiles.set(row, file);
            setStatus(row, cropper ? 'Cropped photo selected' : 'Original photo selected');
            photoApplied = true;
            modal.hide();
            if (row.querySelector('.document-type').value === 'national_id') recognizeId(file, row);
        } catch (error) {
            setStatus(currentRow, 'Could not prepare photo. Choose the original or try again.');
        } finally {
            useButton.disabled = false;
        }
    });

    addRow();
});
