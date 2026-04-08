<script>
    $('document').ready(function(){



        let leaveAllocated = $('#leave_allocated').val();
        (leaveAllocated != '') ? $('.leaveAllocated').show():  $('.leaveAllocated').hide();

         $('#leave_paid').on('change',function(){
            let leavePaid =  $(this).val();
            if(leavePaid == 0 ){
                $('#leave_allocated').val('');
                $('.leaveAllocated').hide();
                $('#leave_allocated').removeAttr('required');
            }else{
                $('.leaveAllocated').show();
                $('#leave_allocated').prop('required', 'true');
            }
        });
    })
</script>
