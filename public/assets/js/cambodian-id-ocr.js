(function (global) {
    const source = {
        printed: 'printed_id_number',
        khmer: 'khmer_personal_information',
        latin: 'latin_name',
        personal: 'personal_information',
    };
    const weights = [7, 3, 1];

    function confidence(value) {
        return Number.isFinite(value) ? Math.max(0, Math.min(1, Math.round(value) / 100)) : 0;
    }

    function field(value, score, region) {
        return { value: value || null, confidence: value ? confidence(score) : 0, source_region: region };
    }

    function lines(text) {
        return (text || '').split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
    }

    function afterLabel(text, label, nextLabel) {
        const entries = lines(text);
        for (let i = 0; i < entries.length; i++) {
            const match = entries[i].match(label);
            if (!match) continue;
            let value = entries[i].slice(match.index + match[0].length).replace(/^[\s:：/-]+/, '');
            if (nextLabel) value = value.split(nextLabel)[0];
            if (!value && entries[i + 1] && !/(?:ឈ្មោះ|ថ្ងៃខែឆ្នាំ|ភេទ|សញ្ជាតិ|ទីកន្លែង|អាសយដ្ឋាន|សុពលភាព|issue|expiry|name)/iu.test(entries[i + 1])) {
                value = entries[i + 1];
            }
            return value.trim() || null;
        }
        return null;
    }

    function khmerName(text) {
        const nameLine = lines(text).find((line) => /(?:នាម|ឈ្មោះ)/u.test(line));
        if (!nameLine) return null;
        const separator = Math.max(nameLine.lastIndexOf('៖'), nameLine.lastIndexOf(':'), nameLine.lastIndexOf('：'));
        const value = separator >= 0
            ? nameLine.slice(separator + 1)
            : afterLabel(text, /(?:គោ(?:ត្ត|គ្គ)នាម\s*(?:និង|និ)\s*នាម|នាមត្រកូល\s*(?:និង|និ)\s*នាម|ឈ្មោះ)/u, /(?:ថ្ងៃខែឆ្នាំ|ភេទ|សញ្ជាតិ|ទីកន្លែង)/u);
        return value?.match(/[\u1780-\u17a2][\u1780-\u17d3 ]{1,98}/u)?.[0].trim() || null;
    }

    function latinName(text) {
        const candidate = lines(text).find((line) => /^[A-Z]+(?:[ '-][A-Z]+)+$/.test(line) && !/^(KINGDOM|REPUBLIC|NATIONAL|IDENTITY)/.test(line));
        return candidate || null;
    }

    function dateValue(raw, score, region) {
        const item = { raw: raw || null, iso: null, confidence: raw ? confidence(score) : 0, source_region: region };
        if (!raw) return item;
        const digits = raw.replace(/[០-៩]/g, (digit) => String('០១២៣៤៥៦៧៨៩'.indexOf(digit)));
        let match = digits.match(/\b(\d{4})[./-](\d{1,2})[./-](\d{1,2})\b/);
        let year, month, day;
        if (match) {
            [, year, month, day] = match;
        } else {
            match = digits.match(/\b(\d{1,2})[./-](\d{1,2})[./-](\d{4})\b/);
            if (!match) return item;
            [, day, month, year] = match;
        }
        const date = new Date(Number(year), Number(month) - 1, Number(day));
        if (date.getFullYear() === Number(year) && date.getMonth() + 1 === Number(month) && date.getDate() === Number(day)) {
            item.iso = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
        return item;
    }

    function mrzValue(character) {
        if (character === '<') return 0;
        if (/^[0-9]$/.test(character)) return Number(character);
        if (/^[A-Z]$/.test(character)) return character.charCodeAt(0) - 55;
        return null;
    }

    function checkDigit(data, expected) {
        if (!/^[0-9]$/.test(expected || '')) return false;
        let sum = 0;
        for (let i = 0; i < data.length; i++) {
            const value = mrzValue(data[i]);
            if (value === null) return false;
            sum += value * weights[i % 3];
        }
        return sum % 10 === Number(expected);
    }

    function parse(regions) {
        const printed = regions.printed || {};
        const khmer = regions.khmer || {};
        const khmerNameRegion = regions.khmerName || {};
        const latin = regions.latin || {};
        const dates = regions.dates || {};
        const mrzLines = (regions.mrz || []).map((result) =>
            lines(result.text).sort((left, right) =>
                (right.match(/[A-Z0-9<]/g) || []).length - (left.match(/[A-Z0-9<]/g) || []).length)[0] || null);
        const mrz = { line1: mrzLines[0] || null, line2: mrzLines[1] || null, line3: mrzLines[2] || null,
            confidence: confidence(Math.min(...(regions.mrz || []).map((item) => item.confidence || 0))) };
        const mrzName = mrz.line3 && mrz.line3.replace(/\s/g, '').length === 30 && /^[A-Z<\s]{20,}$/.test(mrz.line3)
            ? mrz.line3.replace(/\s/g, '').split('<<').slice(0, 2).join(' ').replace(/<+/g, ' ').trim()
            : null;
        const printedLatin = latinName(latin.text);
        const focusedKhmer = khmerName(khmerNameRegion.text);
        const extractedKhmer = focusedKhmer || khmerName(khmer.text);
        const errors = [];
        const uncertainty = [];
        const printedId = lines(printed.text).map((line) => line.match(/\b\d{9}\b/)?.[0]).find(Boolean) || null;
        const khmerText = khmer.text || '';
        const dateText = [dates.text, khmerText].filter(Boolean).join('\n');
        const sex = afterLabel(khmerText, /ភេទ/u, /(?:សញ្ជាតិ|ទីកន្លែង|ថ្ងៃខែឆ្នាំ)/u)?.match(/(?:ប្រុស|ស្រី)/u)?.[0] || null;
        const nationality = afterLabel(khmerText, /សញ្ជាតិ/u, /(?:ទីកន្លែង|ថ្ងៃខែឆ្នាំ|អាសយដ្ឋាន)/u)?.match(/[\u1780-\u17a2][\u1780-\u17d3 ]*/u)?.[0].trim() || null;
        const birth = dateValue(afterLabel(dateText, /(?:ថ្ងៃខែឆ្នាំកំណើត|កើតថ្ងៃទី|date\s*of\s*birth)/iu, /(?:ភេទ|សញ្ជាតិ|ទីកន្លែង)/u), dates.confidence || khmer.confidence, source.personal);
        const validityLine = lines(dateText).find((line) => /សុពលភាព/u.test(line)) || '';
        const validityDates = validityLine.match(/(?:[០-៩0-9]{1,4}[./-]){2}[០-៩0-9]{2,4}/g) || [];
        const issue = dateValue(afterLabel(dateText, /(?:ថ្ងៃចេញ|ចេញថ្ងៃទី|issue\s*date)/iu, /(?:ផុតកំណត់|expiry)/u) || validityDates[0], dates.confidence || khmer.confidence, source.personal);
        const expiry = dateValue(afterLabel(dateText, /(?:ផុតកំណត់|expiry\s*date|valid\s*until)/iu) || validityDates[1], dates.confidence || khmer.confidence, source.personal);
        const place = afterLabel(khmerText, /(?:ទីកន្លែងកំណើត|កន្លែងកំណើត)/u, /(?:អាសយដ្ឋាន|សុពលភាព)/u);
        const address = afterLabel(khmerText, /(?:អាសយដ្ឋាន|ទីលំនៅ)/u, /(?:ថ្ងៃចេញ|សុពលភាព)/u);
        const result = {
            document_type: 'Cambodian National Identity Card',
            raw_ocr: {
                printed_id_number: printed.text || null,
                khmer_name_region: khmerNameRegion.text || null,
                khmer_personal_information: khmer.text || null,
                latin_name: latin.text || null,
                dates: dates.text || null,
            },
            id_number: field(printedId, printed.confidence, source.printed),
            name: { khmer: field(extractedKhmer, focusedKhmer ? khmerNameRegion.confidence : khmer.confidence, source.khmer),
                latin: field(printedLatin || mrzName, printedLatin ? latin.confidence : mrz.confidence * 100, printedLatin ? source.latin : 'mrz') },
            khmer_name: extractedKhmer,
            english_name: printedLatin || mrzName || null,
            date_of_birth: birth,
            sex: field(sex, khmer.confidence, source.personal),
            nationality: { khmer: nationality, latin: null, confidence: nationality ? confidence(khmer.confidence) : 0 },
            place_of_birth: { khmer: place, confidence: place ? confidence(khmer.confidence) : 0, source_region: source.personal },
            address: { khmer: address, confidence: address ? confidence(khmer.confidence) : 0, source_region: source.personal },
            khmer_other_text: khmerText || null,
            issue_date: issue,
            expiry_date: expiry,
            mrz,
            cross_validation: { id_number_matches_mrz: null, name_matches_mrz: null,
                date_of_birth_matches_mrz: null, expiry_date_matches_mrz: null, sex_matches_mrz: null },
            validation_errors: errors,
            uncertain_fields: uncertainty,
            overall_ocr_confidence: 0,
        };

        const line1 = mrz.line1?.replace(/\s/g, '') || '';
        const line2 = mrz.line2?.replace(/\s/g, '') || '';
        const line3 = mrz.line3?.replace(/\s/g, '') || '';
        if (line1.length === 30 && line1.startsWith('IDKHM')) {
            if (!checkDigit(line1.slice(5, 14), line1[14])) errors.push('MRZ document number check digit failed.');
            const mrzId = line1.slice(5, 14);
            if (printedId) {
                result.cross_validation.id_number_matches_mrz = printedId === mrzId;
                if (printedId !== mrzId) errors.push('Printed ID number differs from MRZ document number.');
            }
        } else if (line1) errors.push('MRZ line 1 format is uncertain.');
        if (line2.length === 30) {
            if (!checkDigit(line2.slice(0, 6), line2[6])) errors.push('MRZ birth date check digit failed.');
            if (!checkDigit(line2.slice(8, 14), line2[14])) errors.push('MRZ expiry date check digit failed.');
            if (line1.length === 30 && !checkDigit(line1.slice(5, 30) + line2.slice(0, 7) + line2.slice(8, 15) + line2.slice(18, 29), line2[29])) {
                errors.push('MRZ composite check digit failed.');
            }
            if (birth.iso) result.cross_validation.date_of_birth_matches_mrz = birth.iso.slice(2).replaceAll('-', '') === line2.slice(0, 6);
            if (expiry.iso) result.cross_validation.expiry_date_matches_mrz = expiry.iso.slice(2).replaceAll('-', '') === line2.slice(8, 14);
            if (sex) result.cross_validation.sex_matches_mrz = (sex === 'ប្រុស' ? 'M' : 'F') === line2[7];
            if (result.cross_validation.date_of_birth_matches_mrz === false) errors.push('Printed birth date differs from MRZ.');
            if (result.cross_validation.expiry_date_matches_mrz === false) errors.push('Printed expiry date differs from MRZ.');
            if (result.cross_validation.sex_matches_mrz === false) errors.push('Printed sex differs from MRZ.');
        } else if (line2) errors.push('MRZ line 2 format is uncertain.');
        if (line3.length === 30) {
            if (printedLatin && mrzName) {
                result.cross_validation.name_matches_mrz = printedLatin.replace(/\s+/g, ' ') === mrzName;
                if (!result.cross_validation.name_matches_mrz) errors.push('Printed Latin name differs from MRZ name.');
            }
        } else if (line3) errors.push('MRZ line 3 format is uncertain.');

        for (const [name, item] of Object.entries({ id_number: result.id_number, khmer_name: result.name.khmer,
            latin_name: result.name.latin, date_of_birth: birth, sex: result.sex,
            nationality: { value: result.nationality.khmer, confidence: result.nationality.confidence },
            place_of_birth: { value: place, confidence: result.place_of_birth.confidence },
            address: { value: address, confidence: result.address.confidence },
            issue_date: issue, expiry_date: expiry })) {
            if (!item.value && !item.raw) uncertainty.push(name);
            else if (item.confidence < 0.7) uncertainty.push(name);
        }
        if (!mrz.line1 || !mrz.line2 || !mrz.line3 || mrz.confidence < 0.7) uncertainty.push('mrz');
        if (!printedLatin && mrzName && !uncertainty.includes('latin_name')) uncertainty.push('latin_name');
        const scores = [result.id_number, result.name.khmer, result.name.latin, birth, issue, expiry]
            .filter((item) => item.value || item.raw).map((item) => item.confidence);
        result.overall_ocr_confidence = scores.length ? Math.round(scores.reduce((sum, value) => sum + value, 0) / scores.length * 100) / 100 : 0;
        return result;
    }

    async function region(bitmap, x, y, width, height, scale = 2) {
        const canvas = document.createElement('canvas');
        canvas.width = Math.floor(bitmap.width * width * scale);
        canvas.height = Math.floor(bitmap.height * height * scale);
        const context = canvas.getContext('2d', { willReadFrequently: true });
        context.fillStyle = '#fff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.imageSmoothingQuality = 'high';
        context.drawImage(bitmap, Math.floor(bitmap.width * x), Math.floor(bitmap.height * y),
            Math.floor(bitmap.width * width), Math.floor(bitmap.height * height), 0, 0, canvas.width, canvas.height);
        const image = context.getImageData(0, 0, canvas.width, canvas.height);
        for (let i = 0; i < image.data.length; i += 4) {
            const gray = 0.299 * image.data[i] + 0.587 * image.data[i + 1] + 0.114 * image.data[i + 2];
            image.data[i] = image.data[i + 1] = image.data[i + 2] = Math.max(0, Math.min(255, (gray - 128) * 1.4 + 128));
        }
        context.putImageData(image, 0, 0);
        return canvas;
    }

    async function recognize(file, assets, progress = () => {}) {
        const bitmap = await createImageBitmap(file);
        let worker;
        const read = async (label, box) => {
            progress(label);
            const data = (await worker.recognize(await region(bitmap, ...box))).data;
            return { text: (data.text || '').trim(), confidence: data.confidence };
        };
        try {
            worker = await global.Tesseract.createWorker('eng', 1, { workerPath: assets.worker,
                corePath: assets.core, langPath: assets.lang, workerBlobURL: false });
            const mrz = [];
            for (const [index, y] of [0.665, 0.755, 0.845].entries()) {
                mrz.push(await read(`Reading MRZ line ${index + 1}...`, [0.03, y, 0.95, 0.09]));
            }
            const printed = await read('Reading ID number...', [0.66, 0.01, 0.32, 0.09]);
            const latin = await read('Reading Latin name...', [0.39, 0.145, 0.38, 0.085]);
            await worker.reinitialize(['eng', 'khm'], 1);
            const khmerName = await read('Reading Khmer name...', [0.23, 0.055, 0.50, 0.11, 3]);
            const khmer = await read('Reading Khmer information...', [0.23, 0.055, 0.76, 0.55]);
            const dates = await read('Reading dates...', [0.23, 0.48, 0.60, 0.12]);
            return parse({ mrz, printed, latin, khmerName, khmer, dates });
        } finally {
            bitmap.close();
            if (worker) await worker.terminate().catch(() => {});
        }
    }

    global.CambodianIdOcr = { parse, recognize, checkDigit };
})(window);
