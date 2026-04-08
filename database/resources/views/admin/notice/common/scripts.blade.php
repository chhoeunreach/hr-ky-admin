<script>
    $(document).ready(function () {

        $('#notice').select2();

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
                title: '@lang('index.confirm_change_notice_status')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
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

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_delete_notice')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.sendNotice').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_send_notice')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#showNoticeDescription', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {


                $('.modal-title').html(@json(__('index.notice_detail_modal_title', ['title' => '']))+': ' + data.data.title );
                $('#description').text((data.data.description));
                $('#addslider').modal('show');
            })
        }).trigger("change");

        $('input[type="checkbox"]').click(function(){
            if($(this).is(":checked")){
                $('#notice').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            }
            else if($(this).is(":not(:checked)")){
                $('#notice').select2('destroy').find('option').prop('selected', false).end().select2();
            }
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#notice_receiver').val('');
            $('.fromDate').val('');
            $('.toDate').val('');
        });

        $('#fromDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#toDate').nepaliDatePicker({
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
