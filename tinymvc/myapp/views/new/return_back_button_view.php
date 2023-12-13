<div class="modal-flex__btns-left">
    <a class="fancyboxValidateModal fancybox.ajax btn btn-dark"
        id="<?php echo !empty($id) ? $id : "formaction--go-back"; ?>"
        data-fancybox-href="<?php echo arrayGet($reference, 'url', '#'); ?>"
        <?php if (arrayHas($reference, 'titles.modal'))  { ?>
            data-title="<?php echo arrayGet($reference, 'titles.modal'); ?>"
        <?php } ?>
        <?php if (!empty($reference['options']) && is_array($reference['options'])) { ?>
            <?php foreach ($reference['options'] as $key => $option) { ?>
                data-<?php echo cleanOutput($key); ?>="<?php echo cleanOutput($option); ?>"
            <?php } ?>
        <?php } ?>
        title="<?php echo arrayGet($reference, 'titles.link', "Go back"); ?>">
        Back
    </a>
</div>