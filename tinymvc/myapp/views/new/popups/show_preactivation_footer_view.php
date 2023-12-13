<div class="modal-flex__btns m-0 inputs-40 flex-ai--c">
    <label class="custom-checkbox">
        <input class="js-what-next" type="checkbox" name="dont_show_more">
        <span class="custom-checkbox__text">Don’t show this again</span>
    </label>

    <div class="modal-flex__btns-right">
        <button
            class="btn btn-dark mnw-130 call-action"
            data-js-action="popup:confirm-show-preactivation"
            type="button"
        >Ok</button>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:show_preactivation",
        null,
        true
    );
?>
