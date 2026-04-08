<script>

    $(document).ready(function (e) {

        $('.error').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_support_confirm', ['title' => ':name']) }}'.replace(':name', title),
                showDenyButton: true,
                confirmButtonText: `@lang('yes')`,
                denyButtonText: `@lang('no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#showDetail', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let url = $(this).data('href');
            let creator = $(this).data('submitted');
            let title = $(this).data('title');
            let description = $(this).data('description');
            let branch = $(this).data('branch');
            let department = $(this).data('department');
            let requested = $(this).data('requested');
            let status = $(this).data('status');
            let action = $(this).data('action');
            $.get(url, function (data) {
                if(data.status_code == 200){
                    $('.modal-title').html(title);
                    $('.creator').text(creator);
                    $('.branch').text(branch);
                    $('.department').text(department);
                    $('.requested').text(requested);
                    $('.description').text(description);
                    $('.status').text(status);
                    $('#statusChange').attr('action',action);
                    $('.status'+id+'').css('font-weight','');
                    $('.status'+id+'').css('background','');
                    $('#addslider').modal('show');
                }
            }).fail(function(error){
                let errorMessage = error.responseJSON.message;
                $('.error').removeClass('d-none');
                $('.error').show();
                $('.errorMessageDelete').text(errorMessage);
                $('div.alert.alert-danger').not('.alert-important').delay(1000).slideUp(900);
            })
        }).trigger("change");

        $('.reset').click(function(event){
            event.preventDefault();
            $('#is_seen').val('');
            $('#status').val('');
            $('.queryFrom').val('');
            $('.queryTo').val('');

        });

        $('#nepali-datepicker-from').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#nepali-datepicker-to').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });
    });



</script>
