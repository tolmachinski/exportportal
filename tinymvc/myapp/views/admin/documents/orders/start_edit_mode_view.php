<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/style_administration_scss.min.css'); ?>"/>
<div class="body-wrapper">
    <div id="content-body">
    </div>
</div>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/core-js-3-6-5/bundle.js'); ?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-1-12-0/jquery-1.12.0.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/js/scripts_general.js');?>"></script>
<script>
    (function() {
        $.ajaxSetup({
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": "<?php echo session()->csrfToken; ?>",
            },
        });
        showLoader('#content-body');
        postRequest('<?php echo $url; ?>', { envelope: '<?php echo $envelopeId; ?>' }, "json")
            .then(function (response) {
                var message = response.message || null;
                var messageType = response.mess_type || null;
                var redirectUrl = response.redirectUrl || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (null === redirectUrl) {
                    throw new Error('The response body does not contain the redirrect URL.');

                    return;
                }

                location.href = redirectUrl;
            })
            .catch(function (e) {
                window.opener.dispatchEvent(new CustomEvent('edit-tabs:error', { detail: { error : e } }));
                window.dispatchEvent(new CustomEvent('edit-tabs:error', { detail: { error : e } }));
                window.close();
            });
    })();
</script>
