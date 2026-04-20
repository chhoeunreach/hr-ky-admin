<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Digital HR Complete HR Attendance System">
    <meta name="author" content="Digital HR">
    <meta name="keywords" content="Attendance, Digital HR">

    <title>403</title>
    <?php echo $__env->make('admin.section.head_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
<section class="403-error py-5">
    <div class="container">
        <div class="403-error-inner w-75 mx-auto text-center">
            <img src="<?php echo e(asset('assets/images/403-error.png')); ?>" class="w-50" alt="403">
            <h3 class="mt-2 mb-0">403 Permission Denied</h3>
            <a href="<?php echo e(route('admin.dashboard')); ?>">Back to home</a>
        </div>
    </div>
</section>
<?php echo $__env->make('admin.section.body_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/errors/403.blade.php ENDPATH**/ ?>