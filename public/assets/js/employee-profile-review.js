(function () {
    function initReviewForms() {
        // Keep dialogs outside tab panes so hidden tabs cannot hide their dialogs.
        ['addPerformanceReviewModal', 'addSalaryHistoryModal'].forEach(function (id) {
            const modal = document.getElementById(id);
            if (modal) document.body.appendChild(modal);
        });

        const params = new URLSearchParams(window.location.search);
        const reviewForm = document.getElementById('reviewCreateForm');
        if (reviewForm) {
            const rows = reviewForm.querySelector('[data-review-criteria]');
            const template = reviewForm.querySelector('[data-review-criterion-template]');
            function updateRemoveButtons() {
                const buttons = rows.querySelectorAll('[data-remove-review-criterion]');
                buttons.forEach(function (button) { button.disabled = buttons.length <= 1; });
            }
            reviewForm.querySelector('[data-add-review-criterion]').addEventListener('click', function () {
                rows.appendChild(template.content.cloneNode(true));
                updateRemoveButtons();
                rows.lastElementChild.querySelector('[name="criteria[]"]').focus();
            });
            rows.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-review-criterion]');
                if (button && rows.children.length > 1) {
                    button.closest('[data-review-criterion]').remove();
                    updateRemoveButtons();
                }
            });
            rows.addEventListener('input', function (event) {
                if (!event.target.matches('[data-review-score], [data-review-max]')) return;
                const row = event.target.closest('[data-review-criterion]');
                const maximum = row.querySelector('[data-review-max]');
                const score = row.querySelector('[data-review-score]');
                score.max = maximum.value;
                if (maximum.value !== '' && Number(score.value) > Number(maximum.value)) {
                    score.value = maximum.value;
                }
            });
            updateRemoveButtons();
        }
        const salaryForm = document.querySelector('[data-salary-increase-form]');
        if (salaryForm) {
            const fields = salaryForm.elements;
            const review = fields.namedItem('performance_review_id');
            const status = fields.namedItem('approval_status');
            const oldSalary = fields.namedItem('old_base_salary');
            const newSalary = fields.namedItem('new_base_salary');
            const amount = fields.namedItem('increase_amount');
            const percentage = fields.namedItem('increase_percentage');
            function updateSalaryRequest() {
                const linked = Boolean(review.value);
                if (linked) status.value = 'pending';
                Array.from(status.options).forEach(function (option) {
                    option.disabled = linked && option.value !== 'pending';
                });
                oldSalary.required = linked;
                fields.namedItem('effective_date').required = linked;
                amount.readOnly = linked;
                percentage.readOnly = linked;
                if (linked) {
                    const before = Number(oldSalary.value);
                    const after = Number(newSalary.value);
                    amount.value = oldSalary.value !== '' && newSalary.value !== '' ? (after - before).toFixed(2) : '';
                    percentage.value = amount.value !== '' && before > 0 ? ((after - before) / before * 100).toFixed(2) : '';
                    newSalary.setCustomValidity(newSalary.value !== '' && after <= before ? salaryForm.dataset.salaryIncreaseError : '');
                } else {
                    newSalary.setCustomValidity('');
                }
            }
            review.addEventListener('change', updateSalaryRequest);
            oldSalary.addEventListener('input', updateSalaryRequest);
            newSalary.addEventListener('input', updateSalaryRequest);
            updateSalaryRequest();
            if (params.has('salary_review') && review.value && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(document.getElementById('addSalaryHistoryModal')).show();
            }
        }
        const modal = document.getElementById('addPerformanceReviewModal');
        if (params.get('review_create') === '1' || modal?.dataset.reopen === '1') {
            if (modal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReviewForms);
    } else {
        initReviewForms();
    }
})();
