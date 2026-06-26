(function ($) {
    'use strict';

    var tesseractScriptUrl = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
    var tesseractLoadPromise = null;
    var currentSerials = [];
    var currentImageUrl = '';
    var currentOcrText = '';
    var currentWords = [];
    var ocrIsRunning = false;

    var selectors = {
        modal: '#sellOutOcrModal',
        image: '#sellOutOcrImage',
        title: '#sellOutOcrModalLabel',
        progress: '#sellOutOcrProgress',
        text: '#sellOutOcrText',
        serials: '#sellOutDetectedSerials',
        copyText: '#sellOutCopyOcrText',
        copyFirstSerial: '#sellOutCopyFirstSerial',
        quickActions: '#sellOutOcrQuickActions',
        quickMessage: '#sellOutOcrQuickMessage',
        quickCopyText: '#sellOutQuickCopyText',
        quickCopySerial: '#sellOutQuickCopySerial',
        hideQuickActions: '#sellOutHideQuickActions',
        wordLayer: '#sellOutOcrWordLayer'
    };

    function loadTesseract() {
        if (window.Tesseract) {
            return Promise.resolve(window.Tesseract);
        }

        if (tesseractLoadPromise) {
            return tesseractLoadPromise;
        }

        tesseractLoadPromise = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = tesseractScriptUrl;
            script.async = true;
            script.onload = function () {
                if (window.Tesseract) {
                    resolve(window.Tesseract);
                    return;
                }

                reject(new Error('Tesseract.js loaded, but OCR engine was not available.'));
            };
            script.onerror = function () {
                reject(new Error('Unable to load Tesseract.js. Please check the internet connection.'));
            };
            document.head.appendChild(script);
        });

        return tesseractLoadPromise;
    }

    function openModal() {
        var modal = document.querySelector(selectors.modal);

        if (!modal) {
            return;
        }

        if ($ && $.fn && $.fn.modal) {
            $(modal).modal('show');
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }

    function setProgress(message) {
        $(selectors.progress).text(message || '');
    }

    function setCopyButtonsEnabled(enabled) {
        $(selectors.copyText).prop('disabled', !enabled);
        $(selectors.copyFirstSerial).prop('disabled', !enabled || currentSerials.length === 0);
        $(selectors.quickCopyText).prop('disabled', !enabled);
        $(selectors.quickCopySerial).prop('disabled', !enabled || currentSerials.length === 0);
    }

    function resetOcrState(imageUrl, imageTitle) {
        currentImageUrl = imageUrl;
        currentSerials = [];
        currentOcrText = '';
        currentWords = [];
        ocrIsRunning = true;

        $(selectors.title).text(imageTitle ? 'Image OCR - ' + imageTitle : 'Image OCR');
        $(selectors.image).attr('src', imageUrl);
        $(selectors.wordLayer).empty();
        $(selectors.quickActions).removeClass('is-visible');
        $(selectors.quickMessage).hide();
        $(selectors.text)
            .val('')
            .attr('placeholder', 'Reading text from image...');
        $(selectors.serials)
            .removeClass('text-dark')
            .addClass('text-muted')
            .text('No serials detected yet.');
        setProgress('Reading text from image...');
        setCopyButtonsEnabled(false);
    }

    function detectSerials(text) {
        var matches = (String(text || '').toUpperCase().match(/[A-Z0-9]{8,25}/g) || []);
        var seen = {};

        return matches.filter(function (serial) {
            if (seen[serial]) {
                return false;
            }

            seen[serial] = true;
            return true;
        });
    }

    function renderSerials(serials) {
        var container = $(selectors.serials);
        container.empty();

        if (!serials.length) {
            container
                .removeClass('text-dark')
                .addClass('text-muted')
                .text('No serials detected.');
            return;
        }

        container.removeClass('text-muted').addClass('text-dark');

        serials.forEach(function (serial) {
            var row = $('<div class="d-flex align-items-center justify-content-between sell-out-serial-row mb-2"></div>');
            var value = $('<code class="text-break"></code>').text(serial);
            var button = $('<button type="button" class="btn btn-outline-primary btn-sm">Copy</button>');

            button.on('click', function () {
                copyValue(serial, 'Serial copied successfully');
            });

            row.append(value, button);
            container.append(row);
        });
    }

    function loadImageForOcr(imageUrl) {
        return new Promise(function (resolve, reject) {
            var image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = function () {
                resolve(image);
            };
            image.onerror = function () {
                reject(new Error('Unable to load image for OCR.'));
            };
            image.src = imageUrl;
        });
    }

    function preprocessImageForOcr(image) {
        var maxSide = 2200;
        var scale = Math.min(2, maxSide / Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height));
        var width = Math.max(1, Math.round((image.naturalWidth || image.width) * scale));
        var height = Math.max(1, Math.round((image.naturalHeight || image.height) * scale));
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d', { willReadFrequently: true });

        canvas.width = width;
        canvas.height = height;
        context.drawImage(image, 0, 0, width, height);

        try {
            var imageData = context.getImageData(0, 0, width, height);
            var data = imageData.data;

            for (var i = 0; i < data.length; i += 4) {
                var gray = (data[i] * 0.299) + (data[i + 1] * 0.587) + (data[i + 2] * 0.114);
                var contrast = (gray - 128) * 1.55 + 128;
                var value = Math.max(0, Math.min(255, contrast));

                data[i] = value;
                data[i + 1] = value;
                data[i + 2] = value;
            }

            context.putImageData(imageData, 0, 0);
        } catch (error) {
            return {
                src: image.src,
                width: image.naturalWidth || image.width,
                height: image.naturalHeight || image.height
            };
        }

        return {
            src: canvas.toDataURL('image/png'),
            width: width,
            height: height
        };
    }

    function extractWords(result, sourceWidth, sourceHeight) {
        var words = result && result.data && Array.isArray(result.data.words) ? result.data.words : [];

        return words
            .filter(function (word) {
                return word.text && word.text.trim() && word.bbox;
            })
            .map(function (word) {
                return {
                    text: word.text.trim(),
                    bbox: word.bbox,
                    sourceWidth: sourceWidth,
                    sourceHeight: sourceHeight
                };
            });
    }

    function renderWordLayer() {
        var image = document.querySelector(selectors.image);
        var layer = $(selectors.wordLayer);

        layer.empty();

        if (!image || !currentWords.length || !image.naturalWidth || !image.naturalHeight) {
            return;
        }

        var imageRect = image.getBoundingClientRect();
        var layerRect = layer[0].getBoundingClientRect();
        var naturalWidth = currentWords[0].sourceWidth || image.naturalWidth;
        var naturalHeight = currentWords[0].sourceHeight || image.naturalHeight;
        var renderScale = Math.min(imageRect.width / naturalWidth, imageRect.height / naturalHeight);
        var renderedWidth = naturalWidth * renderScale;
        var renderedHeight = naturalHeight * renderScale;
        var offsetLeft = (layerRect.width - renderedWidth) / 2;
        var offsetTop = (layerRect.height - renderedHeight) / 2;

        currentWords.forEach(function (word) {
            var bbox = word.bbox;
            var left = offsetLeft + (bbox.x0 * renderScale);
            var top = offsetTop + (bbox.y0 * renderScale);
            var width = Math.max(12, (bbox.x1 - bbox.x0) * renderScale);
            var height = Math.max(12, (bbox.y1 - bbox.y0) * renderScale);
            var element = $('<button type="button" class="sell-out-ocr-word"></button>');

            element.text(word.text);
            element.attr('title', 'Click to copy "' + word.text + '"');
            element.css({
                left: left + 'px',
                top: top + 'px',
                width: width + 'px',
                minHeight: height + 'px'
            });
            element.on('click', function (event) {
                event.stopPropagation();
                copyValue(word.text, 'Text copied successfully');
            });

            layer.append(element);
        });
    }

    function runOcr(imageUrl) {
        loadTesseract()
            .then(function (Tesseract) {
                return loadImageForOcr(imageUrl).then(function (image) {
                    return {
                        Tesseract: Tesseract,
                        source: preprocessImageForOcr(image)
                    };
                });
            })
            .then(function (payload) {
                return payload.Tesseract.recognize(payload.source.src, 'eng', {
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789:-./ ',
                    preserve_interword_spaces: '1',
                    logger: function (message) {
                        if (message.status === 'recognizing text' && message.progress !== undefined) {
                            setProgress('OCR Progress: ' + Math.round(message.progress * 100) + '%');
                        } else if (message.status) {
                            setProgress(message.status);
                        }
                    }
                }).then(function (result) {
                    return {
                        result: result,
                        sourceWidth: payload.source.width,
                        sourceHeight: payload.source.height
                    };
                });
            })
            .then(function (payload) {
                if (imageUrl !== currentImageUrl) {
                    return;
                }

                var result = payload.result;
                var text = result && result.data && result.data.text ? result.data.text.trim() : '';
                currentOcrText = text;
                currentWords = extractWords(result, payload.sourceWidth, payload.sourceHeight);
                currentSerials = detectSerials(text);
                ocrIsRunning = false;

                $(selectors.text)
                    .val(text)
                    .attr('placeholder', text ? '' : 'No readable text found.');

                renderSerials(currentSerials);
                renderWordLayer();
                setProgress('OCR complete');
                setCopyButtonsEnabled(true);
            })
            .catch(function (error) {
                currentOcrText = '';
                currentSerials = [];
                currentWords = [];
                ocrIsRunning = false;

                $(selectors.text)
                    .val('')
                    .attr('placeholder', error.message || 'OCR failed. Please try again.');
                $(selectors.wordLayer).empty();
                renderSerials(currentSerials);
                setProgress('OCR failed');
                setCopyButtonsEnabled(false);
            });
    }

    function showQuickMessage(message) {
        var element = $(selectors.quickMessage);

        element.text(message).stop(true, true).fadeIn(120);
        setTimeout(function () {
            element.fadeOut(150);
        }, 1600);
    }

    function showQuickActions() {
        if (ocrIsRunning) {
            showQuickMessage('Reading text from image...');
            return;
        }

        if (!currentOcrText) {
            showQuickMessage('No readable text found.');
            return;
        }

        $(selectors.quickActions).addClass('is-visible');
    }

    function copyValue(value, successMessage) {
        value = String(value || '').trim();

        if (!value) {
            showToast('Nothing to copy', 'warning');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value)
                .then(function () {
                    showToast(successMessage);
                })
                .catch(function () {
                    fallbackCopy(value, successMessage);
                });
            return;
        }

        fallbackCopy(value, successMessage);
    }

    function fallbackCopy(value, successMessage) {
        var textarea = document.createElement('textarea');
        textarea.value = value;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            showToast(successMessage);
        } catch (error) {
            showToast('Copy failed. Please select and copy manually.', 'error');
        }

        document.body.removeChild(textarea);
    }

    function showToast(message, type) {
        type = type || 'success';

        if (window.Swal && Swal.fire) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
            return;
        }

        var alertClass = type === 'error' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-success');
        var toast = $('<div class="alert ' + alertClass + ' sell-out-copy-toast" role="alert"></div>')
            .text(message)
            .css({
                position: 'fixed',
                right: '1rem',
                top: '1rem',
                zIndex: 2000
            });

        $('body').append(toast);
        setTimeout(function () {
            toast.fadeOut(200, function () {
                toast.remove();
            });
        }, 1800);
    }

    $(document).on('click', '.sell-out-ocr-trigger', function (event) {
        event.preventDefault();

        var trigger = $(this);
        var imageUrl = trigger.data('ocr-image-url');
        var imageTitle = trigger.data('ocr-image-title');

        if (!imageUrl) {
            return;
        }

        resetOcrState(imageUrl, imageTitle);
        openModal();
        runOcr(imageUrl);
    });

    $(document).on('keydown', '.sell-out-photo-thumb', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            $(this).closest('.sell-out-ocr-trigger').trigger('click');
        }
    });

    $(document).on('click', selectors.image, function () {
        showQuickActions();
    });

    $(document).on('click', selectors.hideQuickActions, function () {
        $(selectors.quickActions).removeClass('is-visible');
    });

    $(document).on('click', selectors.quickCopyText, function () {
        copyValue(currentOcrText, 'Text copied successfully');
    });

    $(document).on('click', selectors.quickCopySerial, function () {
        copyValue(currentSerials[0] || '', 'Serial copied successfully');
    });

    $(document).on('click', selectors.copyText, function () {
        copyValue($(selectors.text).val(), 'Text copied successfully');
    });

    $(document).on('click', selectors.copyFirstSerial, function () {
        copyValue(currentSerials[0] || '', 'Serial copied successfully');
    });

    $(window).on('resize', function () {
        renderWordLayer();
    });
})(window.jQuery);
