<div class="modal fade" id="eventDetail" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title meetingTitle" id="exampleModalLabel" ></h5>
                </div>
                <div class="modal-body">
                    <table id="dataTableExample" class="table">
                        <tbody>

                        <tr>
                            <td><?php echo e(__('index.event_title')); ?></td>
                            <td class="title"></td>
                        </tr>

                        <tr>
                            <td>Date</td>
                            <td class="start_date"></td>
                        </tr>
                        <tr>
                            <td>Time</td>
                            <td class="end_date"></td>
                        </tr>
                        <tr>
                            <td><?php echo e(__('index.event_host')); ?></td>
                            <td class="host"></td>
                        </tr>
                            <tr>
                            <td><?php echo e(__('index.event_location')); ?></td>
                            <td class="venue"></td>
                        </tr>

                        <tr>
                            <td><?php echo e(__('index.description')); ?></td>
                            <td class="description"></td>
                        </tr>

                        <tr>
                            <td><?php echo e(__('index.image')); ?></td>
                            <td><img class="image" src="" alt="alt" style="object-fit: contain"> </td>
                        </tr>

                        <tr>
                            <td><?php echo e(__('index.creator')); ?></td>
                            <td class="creator"> </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/event/show.blade.php ENDPATH**/ ?>