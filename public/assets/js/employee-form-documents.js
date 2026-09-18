document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('employeeFormDocumentsModal');
    if (!modal) return;

    const rows = document.getElementById('employeeFormDocumentRows');
    const template = document.getElementById('employeeFormDocumentTemplate');
    const addButton = document.getElementById('employeeFormAddDocument');
    const count = document.getElementById('employeeFormDocumentCount');
    const employeeForm = document.getElementById('employeeDetail');
    let nextIndex = 0;
    let activeReads = 0;
    const originalPhotos = new WeakMap();
    const preparedPhotos = new WeakMap();
    const cropperByRow = new WeakMap();
    const previewUrls = new WeakMap();
    const photoModes = new WeakMap();
    const scanVersions = new WeakMap();

    function updateCount() {
        count.textContent = rows.children.length ? `${rows.children.length} document(s) selected` : '';
        addButton.disabled = rows.children.length >= 20;
        [...rows.children].forEach((row, index) => {
            row.querySelector('.document-number').textContent = index + 1;
        });
    }

    function setEmployeeName(id, value) {
        const input = document.getElementById(id);
        if (!input || !value) return false;
        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function addRow() {
        if (rows.children.length >= 20) return;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const row = wrapper.firstElementChild;
        rows.appendChild(row);
        const title = row.querySelector('.document-title');
        title.value = row.querySelector('.document-type').selectedOptions[0].textContent.trim();
        title.dataset.autoTitle = title.value;
        window.feather?.replace();
        updateCount();
    }

    function destroyCropper(row) {
        cropperByRow.get(row)?.destroy();
        cropperByRow.delete(row);
    }

    function clearPreview(row) {
        scanVersions.set(row, (scanVersions.get(row) || 0) + 1);
        destroyCropper(row);
        if (previewUrls.has(row)) URL.revokeObjectURL(previewUrls.get(row));
        previewUrls.delete(row);
        row.querySelector('.document-photo-preview').removeAttribute('src');
        row.querySelector('.document-photo').classList.add('d-none');
        originalPhotos.delete(row);
        preparedPhotos.delete(row);
        photoModes.delete(row);
    }

    function startCrop(row) {
        destroyCropper(row);
        if (!window.Cropper) {
            row.querySelector('.photo-status').textContent = 'Crop tool unavailable. Choose Original.';
            photoModes.delete(row);
            row.querySelector('.use-document-photo').classList.add('d-none');
            row.querySelector('.document-rotate-tools').classList.add('d-none');
            return;
        }
        row.querySelector('.document-rotate-tools').classList.remove('d-none');
        const image = row.querySelector('.document-photo-preview');
        if (!image.complete || !image.naturalWidth) {
            image.addEventListener('load', () => {
                if (photoModes.get(row) === 'crop') startCrop(row);
            }, { once: true });
            return;
        }
        cropperByRow.set(row, new Cropper(image, { viewMode: 1, autoCropArea: 0.9, responsive: true }));
    }

    function showPhoto(row, file) {
        clearPreview(row);
        originalPhotos.set(row, file);
        const url = URL.createObjectURL(file);
        previewUrls.set(row, url);
        askPhotoMode(row, file, true);
    }

    async function askPhotoMode(row, file, cancelClears = false) {
        if (!window.Swal) {
            row.querySelector('.photo-status').textContent = 'Photo choice is unavailable.';
            return;
        }
        const photoPanel = row.querySelector('.document-photo');
        const wasVisible = !photoPanel.classList.contains('d-none');
        photoPanel.classList.add('d-none');
        const result = await Swal.fire({
            title: modal.dataset.photoTitle,
            text: file.name,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: modal.dataset.originalLabel,
            denyButtonText: modal.dataset.cropLabel,
            cancelButtonText: modal.dataset.cancelLabel,
            reverseButtons: true,
            width: '400px',
            padding: '1rem',
            didOpen: () => {
                Swal.getContainer().style.zIndex = '2000';
            },
        });
        if (!row.isConnected || originalPhotos.get(row) !== file) return;
        if (!result.isConfirmed && !result.isDenied) {
            if (cancelClears) {
                row.querySelector('.document-file').value = '';
                clearPreview(row);
            } else if (wasVisible) photoPanel.classList.remove('d-none');
            return;
        }

        row.querySelector('.document-photo-preview').src = previewUrls.get(row);
        photoPanel.classList.remove('d-none');
        scanVersions.set(row, (scanVersions.get(row) || 0) + 1);
        preparedPhotos.delete(row);
        row.querySelector('.document-ocr').classList.add('d-none');
        const mode = result.isDenied ? 'crop' : 'original';
        photoModes.set(row, mode);
        row.querySelector('.use-document-photo').classList.toggle('d-none', mode !== 'crop');
        if (mode === 'crop') {
            row.querySelector('.photo-status').textContent = 'Adjust the crop, then apply.';
            startCrop(row);
        } else {
            destroyCropper(row);
            row.querySelector('.document-rotate-tools').classList.add('d-none');
            usePhoto(row);
        }
    }

    async function usePhoto(row) {
        const original = originalPhotos.get(row);
        if (!original) return;
        const button = row.querySelector('.use-document-photo');
        button.disabled = true;
        try {
            let file = original;
            if (photoModes.get(row) === 'crop') {
                const cropper = cropperByRow.get(row);
                if (!cropper) throw new Error('Crop tool is not ready.');
                const canvas = cropper.getCroppedCanvas({ maxWidth: 3200, maxHeight: 3200, imageSmoothingQuality: 'high' });
                const blob = await new Promise((resolve) => canvas?.toBlob(resolve, 'image/jpeg', 0.92));
                if (!blob) throw new Error('Crop failed.');
                file = new File([blob], original.name.replace(/\.[^.]+$/, '') + '-crop.jpg', { type: 'image/jpeg' });
            }
            if (row.querySelector('.document-file').files[0] !== file) {
                const transfer = new DataTransfer();
                transfer.items.add(file);
                row.querySelector('.document-file').files = transfer.files;
            }
            preparedPhotos.set(row, file);
            if (file === original) destroyCropper(row);
            row.querySelector('.photo-status').textContent = file === original ? 'Original photo selected.' : 'Cropped photo selected.';
            if (row.querySelector('.document-type').value === 'national_id') recognize(row, file);
        } catch (error) {
            row.querySelector('.photo-status').textContent = error.message || 'Could not prepare photo.';
        } finally {
            button.disabled = false;
        }
    }

    async function recognize(row, file) {
        const panel = row.querySelector('.document-ocr');
        const status = row.querySelector('.ocr-status');
        const version = (scanVersions.get(row) || 0) + 1;
        scanVersions.set(row, version);
        panel.classList.remove('d-none');
        row.querySelector('.ocr-text').textContent = '';
        row.querySelector('.copy-ocr-json').disabled = true;
        row.querySelector('.retry-document-ocr').disabled = true;
        if (!window.Tesseract || !window.CambodianIdOcr) {
            status.textContent = 'OCR is unavailable.';
            return;
        }

        activeReads++;
        try {
            const report = await CambodianIdOcr.recognize(file, {
                worker: modal.dataset.ocrWorker,
                core: modal.dataset.ocrCore,
                lang: modal.dataset.ocrLang,
            }, (message) => {
                if (scanVersions.get(row) === version) status.textContent = message;
            });
            if (scanVersions.get(row) !== version || preparedPhotos.get(row) !== file || row.querySelector('.document-type').value !== 'national_id') return;
            row.querySelector('.ocr-text').textContent = JSON.stringify(report, null, 2);
            row.querySelector('.copy-ocr-json').disabled = false;
            const khmer = report.name.khmer.value || '';
            const english = report.name.latin.value || '';
            row.querySelector('.ocr-khmer').value = khmer;
            row.querySelector('.ocr-english').value = english;
            const filledKhmer = setEmployeeName('name', khmer);
            const latinMismatch = report.cross_validation.name_matches_mrz === false;
            const filledEnglish = !latinMismatch && setEmployeeName('english_name', english);
            status.textContent = latinMismatch
                ? 'English name differs from MRZ. Review it, then use Apply Again.'
                : filledKhmer || filledEnglish
                    ? 'Names auto-filled in Personal Detail. Review them before saving.'
                    : 'No names detected. Enter the names here, then use Apply Again.';
        } catch (error) {
            if (scanVersions.get(row) === version) status.textContent = `OCR could not read this image: ${error.message || 'unknown error'}`;
        } finally {
            activeReads--;
            if (scanVersions.get(row) === version) row.querySelector('.retry-document-ocr').disabled = false;
        }
    }

    addButton.addEventListener('click', addRow);
    modal.addEventListener('shown.bs.modal', () => {
        if (!rows.children.length) addRow();
    });
    rows.addEventListener('click', (event) => {
        const row = event.target.closest('.employee-form-document-row');
        if (!row) return;
        if (event.target.closest('.remove-document')) {
            clearPreview(row);
            row.remove();
            updateCount();
        }
        if (event.target.closest('.use-document-photo')) usePhoto(row);
        if (event.target.closest('.change-photo-mode')) {
            const file = originalPhotos.get(row);
            if (file) askPhotoMode(row, file);
        }
        if (event.target.closest('.retry-document-ocr')) {
            const file = preparedPhotos.get(row);
            if (file && row.querySelector('.document-type').value === 'national_id') recognize(row, file);
        }
        if (event.target.closest('.rotate-document-left')) cropperByRow.get(row)?.rotate(-90);
        if (event.target.closest('.rotate-document-right')) cropperByRow.get(row)?.rotate(90);
        if (event.target.closest('.copy-ocr-json')) {
            navigator.clipboard.writeText(row.querySelector('.ocr-text').textContent).then(() => {
                row.querySelector('.ocr-status').textContent = 'OCR JSON copied.';
            }).catch(() => {
                row.querySelector('.ocr-status').textContent = 'Could not copy OCR JSON.';
            });
        }
        if (event.target.closest('.apply-ocr-names')) {
            const khmer = row.querySelector('.ocr-khmer').value.trim();
            const english = row.querySelector('.ocr-english').value.trim();
            setEmployeeName('name', khmer);
            setEmployeeName('english_name', english);
            row.querySelector('.ocr-status').textContent = 'Names applied to the employee form.';
        }
    });
    rows.addEventListener('change', (event) => {
        const row = event.target.closest('.employee-form-document-row');
        if (!row) return;
        if (event.target.matches('.document-type')) {
            scanVersions.set(row, (scanVersions.get(row) || 0) + 1);
            const title = row.querySelector('.document-title');
            const label = event.target.selectedOptions[0].textContent.trim();
            if (!title.value || title.value === title.dataset.autoTitle) title.value = label;
            title.dataset.autoTitle = label;
            const file = preparedPhotos.get(row);
            row.querySelector('.document-ocr').classList.add('d-none');
            if (event.target.value === 'national_id' && file?.type.startsWith('image/')) recognize(row, file);
        }
        if (event.target.matches('.document-file')) {
            const file = event.target.files[0];
            clearPreview(row);
            const panel = row.querySelector('.document-ocr');
            panel.classList.add('d-none');
            row.querySelector('.ocr-text').textContent = '';
            row.querySelector('.copy-ocr-json').disabled = true;
            row.querySelector('.retry-document-ocr').disabled = true;
            row.querySelector('.ocr-khmer').value = '';
            row.querySelector('.ocr-english').value = '';
            if (file?.type.startsWith('image/')) showPhoto(row, file);
        }
    });
    employeeForm.addEventListener('submit', (event) => {
        const pendingPhoto = [...rows.children].some((row) => originalPhotos.has(row) && !preparedPhotos.has(row));
        if (activeReads || pendingPhoto || rows.querySelector(':invalid')) {
            event.preventDefault();
            event.stopImmediatePropagation();
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }, true);
    employeeForm.addEventListener('invalid', (event) => {
        if (rows.contains(event.target)) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }, true);
});
