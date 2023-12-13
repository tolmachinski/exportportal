<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__form">
        <div class="modal-flex__content">
            <label class="input-label">Copy this code, and paste it on your page the the widget must be shown</label>
            <textarea id="widget-code-textarea"><div data-key="<?php echo $widget['widget_key']; ?>" data-height="<?php echo $widget['height']; ?>" data-width="<?php echo $widget['width']; ?>" class="ep-widget"></div><script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "<?php echo __SITE_URL; ?>dashboard/widget_script"; fjs.parentNode.insertBefore(js, fjs); }(document, "script", "exportportal-js"));</script></textarea>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <a id="copy-widget-code" class="btn btn-primary" href="#">Copy code</a>
            </div>
        </div>
    </div>
</div>
