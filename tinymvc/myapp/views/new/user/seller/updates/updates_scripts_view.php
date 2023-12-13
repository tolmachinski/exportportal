<?php if ($seller_view && have_right('have_updates')){ ?>
    <?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

    <script type="text/javascript">
        var callbackAddUpdate = function (resp) {
            _notifyContentChangeCallback();
        }

        var callbackEditUpdate = function (resp) {
            _notifyContentChangeCallback();
        }
    </script>
<?php } ?>

