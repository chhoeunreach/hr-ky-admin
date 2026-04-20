<script>
    

    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    

    $(document).ready(function () {
        let theme = '<?php echo e(\App\Helpers\AppHelper::getTheme()); ?>';
        loadTheme(theme);

        $('#moon').click(function() {
            changeTheme();
        });

        $('#sun').click(function() {
            changeTheme();
        });

        function changeTheme() {
            $.ajax({
                type: "GET",
                url: "<?php echo e(route('admin.change-theme')); ?>",
                success: function(data) {
                    $("#themeColor").remove();
                    loadTheme(data.theme);
                    location.reload();
                }
            });
        }

        function loadTheme(theme) {
            if (theme === 'light') {
                $('head').append('<link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" id="themeColor" />');
                $('#moon').show();
                $('#sun').hide();
            } else {
                $('head').append('<link rel="stylesheet" href="<?php echo e(asset('assets/css/style_dark.css')); ?>" id="themeColor" />');
                $('#sun').show();
                $('#moon').hide();
            }
        }
    });

</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/layouts/theme_scripts.blade.php ENDPATH**/ ?>