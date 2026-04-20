<?php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $themeColor = \App\Helpers\AppHelper::getThemeColor();
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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
    <?php echo $__env->make('admin.section.head_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body>
<div id="preloader" >
    <?php echo $__env->make('admin.section.preloader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>

<div class="main-wrapper">
    <?php echo $__env->make('admin.section.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="page-wrapper">
        <?php echo $__env->make('admin.section.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="page-content">
            <?php echo $__env->make('admin.section.page_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('main-content'); ?>
        </div>

        <!-- partial -->
        <?php echo $__env->make('admin.section.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<?php echo $__env->make('admin.section.body_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('layouts.nav_notification_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layouts.nav_search_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layouts.theme_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

<?php echo $__env->yieldContent('scripts'); ?>
<script type="text/javascript">
    let url = "<?php echo e(route('admin.language.change')); ?>";

    $(".changeLang").click(function() {
        let lang = $(this).data('lang');
        window.location.href = url + "?lang=" + lang;
    });
</script>
<script src="<?php echo e(asset('assets/vendors/select2/select2.min.js')); ?>"></script>

</body>

</html>


<?php /**PATH /var/www/hr-ky-admin/resources/views/layouts/master.blade.php ENDPATH**/ ?>