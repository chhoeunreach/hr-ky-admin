<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        let assignToVal = $('#assigned_to').val();
        let warranty = $('#warranty_available').val();
        (assignToVal != '' ) ?  $('#assigned_date').attr('required','true') :$('#assigned_date').removeAttr('required');
        (warranty != '' || warranty == 0) ?  $('#warranty_end_date').attr('required','true') :$('#warranty_end_date').removeAttr('required');



        $('#image').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })

        $('#warranty_available').change(function(event){
            event.preventDefault()
            let warrantyAvailable = $(this).val();
            if(warrantyAvailable == 0){
                $('#warranty_end_date').val('');
               $('#warranty_end_date').removeAttr('required')
           }else{
               $('#warranty_end_date').attr('required','true')
           }
        });

        $('#assigned_to').change(function(event){
            event.preventDefault()
            let assignedTo = $(this).val();
            if(assignedTo == ''){
                $('#assigned_date').val('');
                $('#assigned_date').removeAttr('required')
            }else{
                $('#assigned_date').attr('required','true')
            }
        });

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
                title: '{{ __('index.delete_tada_confirm', ['title' => ':name']) }}'.replace(':name', title),
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

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.change_availability_status') ',
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
