(function () {
    'use strict';

    async function compress(file) {
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return file;

        const url = URL.createObjectURL(file);
        try {
            const image = await new Promise((resolve, reject) => {
                const preview = new Image();
                preview.onload = () => resolve(preview);
                preview.onerror = () => reject(new Error('Could not read the photo. Please select it again.'));
                preview.src = url;
            });
            const scale = Math.min(1, 2400 / Math.max(image.naturalWidth, image.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            const context = canvas.getContext('2d');
            if (!context) throw new Error('Could not prepare the photo. Please try again.');
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
            if (!blob) throw new Error('Could not compress the photo. Please try again.');
            if (blob.size >= file.size) return file;
            return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
                type: 'image/jpeg', lastModified: file.lastModified,
            });
        } finally {
            URL.revokeObjectURL(url);
        }
    }

    function describe(original, prepared) {
        const size = (file) => `${(file.size / 1024).toFixed(1)} KB`;
        return prepared.size < original.size
            ? `Photo compressed: ${size(original)} → ${size(prepared)}.`
            : `Photo ready: ${size(prepared)} (already optimized).`;
    }

    window.EmployeeDocumentCompression = { compress, describe };
})();
