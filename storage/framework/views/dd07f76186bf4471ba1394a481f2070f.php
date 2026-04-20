

<script src="<?php echo e(asset('assets/vendors/tinymce/tinymce.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/tinymce.js')); ?>"></script>

<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '<?php echo app('translator')->get('index.change_status_confirm'); ?>',
                showDenyButton: true,
                confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })

        $('.deleteNotification').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '<?php echo app('translator')->get('index.delete_confirmation'); ?>',
                showDenyButton: true,
                confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.sendNotification').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '<?php echo app('translator')->get('index.confirm_notification_send'); ?>',
                showDenyButton: true,
                confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#showNotificationDescription', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {
                console.log(data);
                $('.modal-title').html(data.data.title);
                $('#description').text((data.data.description));
                $('#notifiedUser').text(data.user);
                $('#addslider').modal('show');
            })
        }).trigger("change");

    });

</script>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/notification/common/scripts.blade.php ENDPATH**/ ?>