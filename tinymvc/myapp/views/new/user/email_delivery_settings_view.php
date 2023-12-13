<div class="container-center dashboard-container inputs-40">
    <div class="dashboard-line mb-20">
        <h1 class="dashboard-line__ttl">
            Email delivery settings
        </h1>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="info-alert-b">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <span><?php echo translate('user_email_delivery_settings_description'); ?></span>
            </div>

            <form class="validengine" method="post" data-callback="saveNotificationEmail">
                <label class="input-label input-label--required">Notification Email</label>
                <?php if(!empty($notification_email_change)){?>
                <div class="info-alert-b mb-15"><i class="ep-icon ep-icon_info-stroke"></i> <span>You sent query for change Notification email to <strong><?php echo $notification_email_change['email'];?></strong>. Please verify and confirm this email.</span></div>
                <?php }?>
                <input
                    class="mw-370 validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
                    type="text"
                    name="email"
                    value="<?php echo cleanOutput($user['email']); ?>"
                    placeholder="Email">

                <div class="pt-15">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="email_notifications_once_day" data-action="send_notify" <?php echo checked(notify_email(), 1); ?>>
                        <span class="custom-checkbox__text">Send me info about all notifications in one email once a day</span>
                    </label>
                </div>

                <div class="pt-15">
                    <button class="btn btn-primary mnw-150" type="submit">Save</button>
                </div>
            </form>

            <form class="validengine mt-20" method="post" data-callback="saveSettings">
                <div class="title-public pt-20 pb-20">
                    <h2 class="title-public__txt">Send me important notifications instantly</h2>
                </div>

                <ul class="email-settings-list">
                <?php foreach($modules as $module) { ?>
                    <li class="email-settings-list__item">
                        <label class="custom-checkbox">
                            <input
                                type="checkbox"
                                name="modules[<?php echo $module['id_module'] ?>]"
                                data-action="send_email_notify"
                                data-id_module="<?php echo $module['id_module'] ?>"
                                <?php echo checked($systmess_settings[$module['id_module']]['email_notification'], 1); ?>
                            >
                            <span class="custom-checkbox__text"><?php echo $module['title_module'] ?></span>
                        </label>
                    </li>
                <?php } ?>
                </ul>

                <div class="pt-20">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="subscription" data-action="subscription" <?php echo checked(subscription_email(), 1); ?>>
                        <span class="custom-checkbox__text">
                            Subscription <a class="fancybox-ttl-inside fancybox.ajax" data-w="1040" data-h="400" href="<?php echo __SITE_URL; ?>terms_and_conditions/tc_subscription_terms_of_conditions?page=modal" data-title="Subscription Terms and Conditions">Terms And Conditions</a>
                        </span>
                    </label>
                </div>

                <div class="pt-15">
                    <button class="btn btn-primary mnw-150" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function saveSettings($form) {
        showLoader($form);

        $.ajax({
            type: 'POST',
            url: __current_sub_domain_url + 'systmess/ajax_systmess_operation/save_notifications_settings',
            data: $form.serialize(),
            dataType: 'json',
            success: function(resp) {
                hideLoader($form);
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    function saveNotificationEmail($form) {
        showLoader($form);

        $.ajax({
            type: 'POST',
            url: __current_sub_domain_url + 'user/ajax_preferences_operation/save_email',
            data: $form.serialize(),
            dataType: 'json',
            success: function(resp) {
                hideLoader($form);
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }
</script>
