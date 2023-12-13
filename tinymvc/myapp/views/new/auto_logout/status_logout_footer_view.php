<?php if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) { ?>
    <div class="modal-flex__btns">
        <div class="modal-flex__btns-left">
            <label class="checkbox">
                <input class="js-no-more-show" type="checkbox" name="dont_show_more">
                <span class="custom-checkbox__text">Do not show again today</span>
            </label>
        </div>

        <div class="modal-flex__btns-right">
            <button class="btn btn-dark btn-sm call-action" data-js-action="close-autologout-modal:event">Continue Session</button>
        </div>
    </div>
<?php } else { ?>
    <div class="modal-flex__btns m-0 inputs-40 flex-ai--c">
        <label class="custom-checkbox">
            <input class="js-no-more-show" type="checkbox" name="dont_show_more">
            <span class="custom-checkbox__text">Do not show again today</span>
        </label>

        <div class="modal-flex__btns-right">
            <button class="btn btn-dark call-action mnw-130" data-js-action="close-autologout-modal:event" type="button">Continue Session</button>
        </div>
    </div>
<?php } ?>

<?php echo dispatchDynamicFragment(
    'close-autologout-modal:boot',
    null,
    true
); ?>
