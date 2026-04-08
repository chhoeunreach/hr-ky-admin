<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#image-uploadify").imageuploadify();

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.tada_status_change')',
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
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

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_tada_confirm', ['title' => ':name']) }}'.replace(':name', title),
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#updateStatus', function (e) {
            e.preventDefault();
            let status = $(this).data('status');
            let action = $(this).data('action');
            let title = $(this).data('title');
            let reason = $(this).data('reason');
            if(status == 'pending'){
                $('.update').prop('disabled',true)
            }
            $('#addslider').modal('show');
            $('#updateTadaStatus').attr('action',action);
            $('.modal-title').html(title);
            $('#tada_status').val(status);
            $('#reason').val(reason);
        }).trigger("change");

        $('#tada_status').change(function(e){
           e.preventDefault();
           let status = $(this).val();
           if(status == 'accepted'){
               $('.remark').removeAttr('required')
           }else{
               $('.remark').attr('required','required');
           }
           (status == 'pending') ? $('.update').prop('disabled',true) : $('.update').prop('disabled',false);
        });


        $('.reset').click(function(event){
            event.preventDefault();
            $('#status').val('');
            $('#employee').val('');

        });
    });

</script>
