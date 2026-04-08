<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        $("#image-uploadify").imageuploadify();



        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        // $('#status').change(function(){
        //    let status = $(this).val();
        //    manipulateDomBasedOnStatus(status)
        // });
        //
        // function manipulateDomBasedOnStatus(status){
        //     if(status == 'approved'){
        //         $('.releaseAmount').show();
        //         $('.document').show();
        //         $('.reason').show();
        //         $('.amountReleased').prop('required', 'true');
        //         $('.attachment').prop('required', 'true');
        //         $('.remark').prop('required', 'true');
        //
        //         const files = $('.imageuploadify-container input[type="file"]')[0]?.files;
        //         if (!files || files.length === 0) {
        //             e.preventDefault();
        //             alert('Please upload at least one document when status is approved');
        //             return false;
        //         }
        //
        //     }
        //
        //     if(status == 'rejected'){
        //         $('.reason').show();
        //         $('.remark').prop('required', 'true');
        //         $('.releaseAmount').hide();
        //         $('.document').hide();
        //         $('.amountReleased').removeAttr('required');
        //         $('.attachment').removeAttr('required');
        //     }
        //
        //     if(status == 'processing'){
        //         $('.releaseAmount').hide();
        //         $('.reason').hide();
        //         $('.document').hide();
        //         $('.remark').removeAttr('required');
        //         $('.amountReleased').removeAttr('required');
        //         $('.attachment').removeAttr('required');
        //     }
        // }
        $('#status').change(function(e) {
            let status = $(this).val();
            handleStatusChange(status);
        });

        // Initial state setup based on current status
        handleStatusChange($('#status').val());

        // Form submission handler
        $('#updateAdvanceSalaryRequestStatus').on('submit', function(e) {
            const status = $('#status').val();

            if (status === 'approved') {
                const files = $('.imageuploadify-container input[type="file"]')[0]?.files;
                if (!files || files.length === 0) {
                    e.preventDefault();
                    alert('Please upload at least one document when status is approved');
                    return false;
                }
            }
        });
    });
    function handleStatusChange(status) {
        const releaseAmount = $('.releaseAmount');
        const documentSection = $('.document');
        const reason = $('.reason');
        const amountReleased = $('.amountReleased');
        const attachment = $('.attachment');
        const remark = $('.remark');

        // Reset all fields first
        releaseAmount.hide();
        documentSection.hide();
        reason.hide();

        // Remove required attributes
        amountReleased.prop('required', false);
        attachment.prop('required', false);
        remark.prop('required', false);

        // Clear values when hiding
        if (status !== 'approved') {
            amountReleased.val('');
            attachment.val('');
        }
        if (status === 'processing') {
            remark.val('');
        }

        // Set visibility and requirements based on status
        switch(status) {
            case 'approved':
                releaseAmount.show();
                documentSection.show();
                reason.show();
                amountReleased.prop('required', true);
                attachment.prop('required', true);
                remark.prop('required', true);
                break;

            case 'rejected':
                reason.show();
                remark.prop('required', true);
                break;

            case 'processing':
                // All fields are hidden and not required by default
                break;
        }
    }
</script>
