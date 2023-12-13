<div class="form-group">
    <label class="input-label input-label--info">
        <?php echo translate('unsubscribe_enter_email_address'); ?>
        <a class="info-dialog ep-icon ep-icon_info pl-5"
            data-message="<?php echo translate('unsubscribe_info_message', null, true); ?>"
            data-title="<?php echo translate('unsubscribe_info_modal_ttl', null, true); ?>"
            title="<?php echo translate('unsubscribe_info_modal_ttl', null, true); ?>"
            href="#"
        ></a>
    </label>
    <input
        <?php echo addQaUniqueIdentifier("unsubscribe-zoho__form-email"); ?>
        class="mb-0 validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
        type="text"
        name="email"
        placeholder="<?php echo translate('unsubscribe_email_imput_placeholder', null, true); ?>"
    />

    <label class="input-label"><?php echo translate('unsubscribe_reason_label'); ?></label>
    <select id="js-select-reason" <?php echo addQaUniqueIdentifier("unsubscribe-zoho__form-select-reason"); ?> name="reason" class="validate[required]">
        <option></option>
        <?php foreach($reasonMessages as $key => $val) { ?>
            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
        <?php } ?>
    </select>

    <div id="js-reason-message" class="display-n mt-20">
        <input
            <?php echo addQaUniqueIdentifier("unsubscribe-zoho__form-reason-message"); ?>
            class="validate[required, maxSize[200]]"
            name="message"
            type="text"
            placeholder="<?php echo translate('unsubscribe_enter_reason_placeholder', null, true); ?>"
        >
    </div>
</div>
