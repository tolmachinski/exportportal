<?php if (!empty($unsubscribeMap)) { ?>
    <div class="unsubscribe footer-connect">
        <form id="js-unsubscribe-form" class="unsubscribe-user-form validengine" data-sto="-60" method="post" data-callback="onUnsubscribe">
            <?php if ('zoho' === $unsubscribeMap) { ?>
                <div class="unsubscribe-user-form__ttl">
                    <h1><?php echo translate('unsubscribe_form_ttl'); ?></h1>
                    <b><?php echo translate('unsubscribe_zoho_subttl'); ?></b>

                    <div class="info-alert-b tal mt-25">
                        <i class="ep-icon ep-icon_info-stroke"></i>
                        <?php echo translate('unsubscribe_zoho_info_text'); ?>
                    </div>
                </div>
            <?php } else { ?>
                <div class="unsubscribe-user-form__ttl">
                    <h1><?php echo translate('unsubscribe_form_ttl'); ?></h1>

                    <div class="info-alert-b tal mt-25">
                        <i class="ep-icon ep-icon_info-stroke"></i>
                        <?php echo translate('unsubscribe_user_info_txt'); ?>
                    </div>
                </div>
            <?php } ?>

        <?php views()->display("new/unsubscribe/{$unsubscribeMap}/inputs_view"); ?>

            <button <?php echo addQaUniqueIdentifier("unsubscribe__form-submit"); ?> class="btn btn-primary btn-block" type="submit"><?php echo translate('unsubscribe_form_ttl'); ?></button>
        </form>
    </div>

    <?php views()->display("new/unsubscribe/{$unsubscribeMap}/scripts_view"); ?>
<?php } ?>
