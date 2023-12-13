<?php $is_recaptcha_enabled = filter_var(config('env.RECAPTCHA_ENABLED', false), FILTER_VALIDATE_BOOLEAN);?>

<script>
    var recaptcha_parameters = {
        public_token: "<?php echo config('env.RECAPTCHA_PUBLIC_TOKEN_REGISTER'); ?>",
        enabled_status: Boolean(~~parseInt('<?php echo (int) $is_recaptcha_enabled; ?>', 10))
    }
</script>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/validation/google_recaptcha_validation.js');?>"></script>

<?php if ($is_recaptcha_enabled) { ?>
    <script src='https://www.recaptcha.net/recaptcha/api.js?render=<?php echo config('env.RECAPTCHA_PUBLIC_TOKEN_REGISTER'); ?>'></script>
<?php } ?>
