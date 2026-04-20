<?php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $themeColor = \App\Helpers\AppHelper::getThemeColor();
?>
<!DOCTYPE html>

<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Digital HR Complete HR Attendance System">
    <meta name="author" content="Digital HR">
    <meta name="keywords" content="Digital HR">

    <title><?php echo $__env->yieldContent('title'); ?></title>
    <style>
        :root {
            --primary-color: <?php echo e($themeColor->primary_color ?? '#0F766E'); ?>;
            --hover-color: <?php echo e($themeColor->hover_color ?? '#115E59'); ?>;
            --dark-primary-color: <?php echo e($themeColor->dark_primary_color ?? '#14B8A6'); ?>;
            --dark-hover-color: <?php echo e($themeColor->dark_hover_color ?? '#0F766E'); ?>;
        }
    </style>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->

    <!-- core:css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendors/core/core.css')); ?>">
    <!-- endinject -->

    <!-- inject:css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather-font/css/iconfont.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendors/flag-icon-css/css/flag-icon.min.css')); ?>">
    <!-- endinject -->

    <!-- Layout styles -->
    <link rel="stylesheet" href="<?php echo e((\App\Helpers\AppHelper::getTheme() == 'dark') ? asset('assets/css/style_dark.css') : asset('assets/css/style.css')); ?>">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" />

    <?php echo $__env->yieldContent('page-styles'); ?>
</head>
<body>

<?php echo $__env->yieldContent('auth-content'); ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?php echo e(asset('assets/vendors/core/core.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendors/feather-icons/feather.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/template.js')); ?>"></script>
    <script>
        $('div.alert.alert-success').not('.alert-important').delay(5000).slideUp(900);
        $('div.alert.alert-danger').not('.alert-important').delay(1000).slideUp(900);
    </script>

<?php echo $__env->yieldContent('page-scripts'); ?>

</body>
</html>
<?php /**PATH /var/www/hr-ky-admin/resources/views/auth/main.blade.php ENDPATH**/ ?>