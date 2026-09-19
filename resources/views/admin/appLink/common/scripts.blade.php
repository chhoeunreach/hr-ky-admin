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
                title: 'Are you sure you want to change link status?',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                }
            });
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: 'Are you sure you want to delete this link?',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        $('#image').change(function () {
            const [file] = this.files;
            if (file) {
                $('#image-preview').removeClass('d-none');
                $('#image-preview').attr('src', URL.createObjectURL(file));
            }
        });
    });
</script>
