<script>
    $(document).ready(function () {
        const branchSelect = $('#branch_id');

        $('select#branch_id').select2({
            placeholder: @json(__('index.select_branch'))
        });

        const showBranchHeads = () => {
            const selectedValue = branchSelect.val();
            const branchIds = (Array.isArray(selectedValue) ? selectedValue : [selectedValue])
                .filter(Boolean).map(String);

            $('.department-head-row').each(function () {
                const row = $(this);
                const selected = branchIds.includes(String(row.attr('data-branch-id')));
                const headSelect = row.find('.department-head-select');
                row.toggle(selected);
                headSelect.prop('disabled', !selected);

                if (selected && !headSelect.hasClass('select2-hidden-accessible')) {
                    headSelect.select2({
                        width: '100%',
                        minimumResultsForSearch: 0
                    });
                }
            });
            $('#departmentHeadBranchHint').toggle(branchIds.length === 0);
        };

        branchSelect.on('change', showBranchHeads);
        showBranchHeads();
    });
</script>
