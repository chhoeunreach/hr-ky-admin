<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            var status = $(this).prop('checked') === true ? 1 : 0;
            var href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_change_status') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
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

        $('.deleteOfficeTime').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_office_time_confirm') }}',
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

        $('body').on('click', '#showOfficeTimeDetail', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {
                $('.modal-title').html('{{ __('index.office_time_detail') }}');
                $('.opening_time').text(data.data.opening_time);
                $('.closing_time').text((data.data.closing_time));

                $('.checkin_before').text(data.data.checkin_before === null ? '' : `${data.data.checkin_before} minutes`);
                $('.checkin_after').text(data.data.checkin_after === null ? '' : `${data.data.checkin_after} minutes`);
                $('.checkout_before').text(data.data.checkout_before === null ? '' : `${data.data.checkout_before} minutes`);
                $('.checkout_after').text(data.data.checkout_after === null ? '' : `${data.data.checkout_after} minutes`);

                $('.shift').text((data.data.shift));
                $('#addslider').modal('show');
            })
        }).trigger("change");

        $('#apply_rule').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('.late_rule').removeClass('d-none');
            }else{
                $('.late_rule').addClass('d-none');
            }
        });



        $('#is_early_check_in').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#earlyCheckIn').removeClass('d-none');
            }else{
                $('#earlyCheckIn').addClass('d-none');
            }
        });

        $('#is_late_check_in').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#lateCheckIn').removeClass('d-none');
            }else{
                $('#lateCheckIn').addClass('d-none');
            }
        });

        $('#is_early_check_out').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#earlyCheckOut').removeClass('d-none');
            }else{
                $('#earlyCheckOut').addClass('d-none');
            }
        });

        $('#is_late_check_out').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#lateCheckOut').removeClass('d-none');
            }else{
                $('#lateCheckOut').addClass('d-none');
            }
        });
    });

</script>
