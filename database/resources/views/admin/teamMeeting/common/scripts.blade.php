<script>

    $(document).ready(function () {



        $("#team_meeting").select2({
            placeholder: "Select Meeting Participants"
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{__('index.delete_team_meeting_confirmation')}}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.removeImage').click(function (event){
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.image_delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('body').on('click', '#showMeetingDescription', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {
                $('.meetingTitle').html('Meeting Detail');
                $('.title').text(data.data.title);
                $('.date').text(data.data.meeting_date);
                $('.time').text(data.data.time);
                $('.venue').text(data.data.venue);
                $('.publish_date').text(data.data.meeting_published_at);
                $('.description').text(data.data.description);
                $('.creator').text(data.data.creator);
                $('.image').attr('src',data.data.image);

                $('#meetingDetail').modal('show');
            })
        }).trigger("change");

        $('.reset').click(function(event){
            event.preventDefault();
            $('#participator').val('');
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
    document.getElementById('withTeamNotification').addEventListener('click', function (event) {

        document.getElementById('teamNotification').value = "1";
    });
</script>
