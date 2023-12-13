<?php if (config('env.SHOW_COMPARE_FUNCTIONALITY')) {?>
    <script>
        <?php require_once(App\Common\PUBLIC_PATH . DS . 'plug' . DS . 'js' . DS . 'compare' . DS . 'compare.js');?>

        var cookie_compare_name = '<?php echo logged_in() ? 'user_' . id_session() . '_compare' : 'ep_compare';?>';

        $(function(){
            _init_compare();
        });
    </script>
<?php }?>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/maintenance-mode/index.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/customize-scroll/index.js');?>"></script>
