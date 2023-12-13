<div class="modal-flex__btns m-0 inputs-40 flex-ai--c">
    <?php if((int)library('Cookies')->getCookieParam($cookie_name) != 1){?>
        <label class="custom-checkbox">
            <input class="js-what-next" type="checkbox" name="dont_show_more">
            <span class="custom-checkbox__text">Don’t show this again</span>
        </label>
    <?php }?>

    <div class="modal-flex__btns-right">
        <button
            class="btn btn-dark mnw-130 call-action"
            <?php echo addQaUniqueIdentifier("whats-next_popup_close_btn"); ?>
            data-js-action="modal:call-close-modal"
            type="button"
        >Ok</button>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "account:what-next-popup-footer",
        [$cookie_name],
        true
    );
?>

