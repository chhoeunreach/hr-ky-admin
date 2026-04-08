<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {

        let oldData = {{!isset($taskDetail) && old('name') ? 1 : 0 }};
        if(oldData){
            loadProjectMember();
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#taskMember").select2({
            placeholder: "@lang('index.task_member_placeholder')"
        });

        $("#project").select2({
            placeholder: "@lang('index.project_placeholder')"
        });

        $("#filter").select2({
            placeholder: "@lang('index.search_by_member')"
        });

        $("#projectFilter").select2({
            placeholder: "@lang('index.project_filter_placeholder')"
        });

        $("#taskName").select2({
            placeholder: "@lang('index.task_name_placeholder')"
        });

        $("#image-uploadify").imageuploadify();

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        $('.formChecklist').hide();

        let project = $('#project').val();
        (project != null) ? $('.taskMemberAssignDiv').show():  $('.taskMemberAssignDiv').hide();

        $('#project').on('change',function(e){
            e.preventDefault();
            loadProjectMember();
        });

        function loadProjectMember(){
            let projectSelected = $('#project option:selected').val();
            if (projectSelected) {
                $('.taskMemberAssignDiv').show();
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/projects/get-assigned-members/') }}" + '/' + projectSelected ,
                }).done(function(response) {
                    $('#taskMember').empty();
                    response.data.forEach(function(data) {
                        $('#taskMember').append('<option  value="'+data.id+'" >'+(data.name)+'</option>');
                    });
                });
            }
        }

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.change_task_status_confirm')',
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

        $('body').on('click', '#checklistToggle', function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') ? 1 : 0;
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.change_status_confirm')',
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
                title: '@lang('index.delete_task_detail_confirm')',
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

        $('body').on('click', '#delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.delete_confirm')'+title+'?',
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

        $('#createChecklist').click(function(e){
            $('.formChecklist').removeClass('d-none');
            let text = $(this).text();
            (text == '@lang('index.create_checklist_text')') ? $(this).text('@lang('index.close_checklist_text')') : $(this).text('@lang('index.create_checklist_text')');
            $('.formChecklist').toggle(500);
        })

        $('#addChecklist').on('click',function(event){
            event.preventDefault();
            let removeButton = '<div class="col-lg-2 col-md-2 removeButton mb-4">'
                +'<button type="button" class="btn btn-sm btn-danger remove" title="@lang('index.remove_checklist_title')" id="removeChecklist">@lang('index.remove_checklist_text')</button>'+
                '</div>';
            $(".checklist").first().clone().find("input").val("").end().append(removeButton).appendTo("#addTaskCheckList");
            $(".addButtonSection:last").remove();
        })

        $("#addTaskCheckList").on('click', '.remove', function(){
            $(this).closest(".checklist").remove();
        });

        $(".checklistAdd").click(function(e) {
            e.preventDefault();
            $('.formChecklist').removeClass('d-none');
            $('.formChecklist').show();
            $('html,body').animate({
                scrollTop: $('#taskAdd').offset().top - 100
            }, 600);
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#taskName').val('');
            $('#status').val('');
            $('#priority').val('');
            $('#projectFilter').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#projectFilter").select2({
                placeholder: "@lang('index.project_filter_placeholder')"
            });
            $('#filter').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#filter").select2({
                placeholder: "@lang('index.filter_placeholder')"
            });
            $('#taskName').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#taskName").select2({
                placeholder: "@lang('index.task_name_placeholder')"
            });
        });
    });

    $('.startNpDate').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 20,
        disableAfter: "2089-12-30",
    });

    $('.npDeadline').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 20,
        disableAfter: "2089-12-30",
    });

    document.getElementById('withTaskNotification').addEventListener('click', function (event) {

        document.getElementById('taskNotification').value = 1;
    });

</script>
