
<script>
    $('document').ready(function(){
        $('body').on('click', '.deleteWarning', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_confirmation') }}',
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
        });


        // $('.promotionStatusUpdate').on('click', function (event) {
        //     event.preventDefault();
        //     let url = $(this).data('href');
        //     let status = $(this).data('status');
        //     let reason = $(this).data('reason');
        //
        //     $('.modal-title').html('Resignation Status Update');
        //     $('#updatePromotionStatus').attr('action',url)
        //     $('#status').val(status)
        //     $('#remark').val(reason)
        //
        //
        //     $('#statusUpdate').modal('show');
        // });


    });


</script>
