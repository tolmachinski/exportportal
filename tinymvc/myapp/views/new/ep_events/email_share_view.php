<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-js-action="global:share-email-form-popup-submit"
        data-action="<?php echo $action ?? '';?>"
        novalidate
    >
        <div class="modal-flex__content">
            <?php if ($type === "email") {?>
                <div class="form-group">
                    <label class="input-label input-label--required"><?php echo translate('event_email_popup_input_email_label');?></label>
                    <input
                        class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo config('email_this_max_email_count');?>]]"
                        type="email"
                        name="emails"
                        value=""
                        placeholder="Insert the email address you would like to share the event with"
                    />
                    <p class="fs-12 txt-red"><?php echo translate('events_email_popup_input_email_auxiliary_text');?></p>
                </div>
            <?php }?>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('events_email_popup_input_message_label');?></label>
                <textarea
                    class="validate[required,maxSize[1000]] js-textcounter-message"
                    data-max="1000"
                    name="message"
                    placeholder="Message"
                ><?php echo translate('events_email_popup_input_message_default');?></textarea>
            </div>
            <input type="hidden" value="<?php echo $eventId;?>" name="event_id" />
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('events_email_popup_submit_btn');?></button>
            </div>
        </div>
    </form>
</div>

<?php
echo dispatchDynamicFragment(
    'global:share-email-form-popup'
);
?>
