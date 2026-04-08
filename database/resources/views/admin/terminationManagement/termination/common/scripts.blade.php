<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){



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
        })

        tinymce.init({
            selector: '#tinymceExample',
            height: 200,
        });


        $('body').on('click', '.terminationStatusUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let reason = $(this).data('reason');

            $('.modal-title').html('Leave Status Update');
            $('#updateTerminationStatus').attr('action',url)
            $('#status').val(status)
            $('#admin_remark').val(reason)

            $('#statusUpdate').modal('show');
        });


        $('.nepaliDate').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });
    });


</script>
