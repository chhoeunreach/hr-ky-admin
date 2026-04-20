
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<!-- End fonts -->


<link rel="stylesheet" href="<?php echo e(asset('assets/vendors/select2/select2.min.css')); ?>">

<!--nepali datePicker -->
<link rel="stylesheet" href=" <?php echo e(asset('assets/css/nepaliDatepicker.min.css')); ?> " type="text/css">
<!-- end-->

<!-- core:css -->
<link rel="stylesheet" href=" <?php echo e(asset('assets/vendors/core/core.css')); ?> ">
<!-- end -->

<!-- Plugin css for this page -->
<link rel="stylesheet" href="<?php echo e(asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')); ?>">
<!-- End plugin css for this page -->

<!-- inject:css -->
<link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather-font/css/iconfont.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/vendors/flag-icon-css/css/flag-icon.min.css')); ?>">
<!-- endinject -->

<!-- Layout styles -->

<link rel="stylesheet" href="<?php echo e((\App\Helpers\AppHelper::getTheme() == 'dark') ? asset('assets/css/style_dark.css') : asset('assets/css/style.css')); ?>" id="themeColor">

<!-- End layout styles -->
<!-- RTL -->
<?php if(in_array(App::getLocale(),['ar','fa'])): ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/rtl_style.css')); ?>" id="rtl">
<?php endif; ?>


<link rel="shortcut icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" />
<link rel="stylesheet" href="<?php echo e(asset('assets/vendors/sweetalert2/sweetalert2.min.css')); ?>"/>














<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/section/head_links.blade.php ENDPATH**/ ?>