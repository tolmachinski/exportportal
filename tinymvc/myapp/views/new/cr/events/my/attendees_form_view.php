<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__content mnh-335 mh-700">
        <div class="container-fluid-modal">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs nav--borders" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#meeting-b2b" aria-controls="title" role="tab" data-toggle="tab">
                                <?php echo translate('cr_events_dashboard_modal_attendees_tab_registered_users_label'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about-b2b" aria-controls="title" role="tab" data-toggle="tab">
                                <?php echo translate('cr_events_dashboard_modal_attendees_tab_new_users_label'); ?>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content tab-content--borders">
                        <div role="tabpanel" class="tab-pane fade" id="about-b2b">
                            <?php if (empty($new_users)) { ?>
                                <div class="info-alert-b">
                                    <i class="ep-icon ep-icon_info-stroke"></i>
                                    <span>
                                        <?php echo translate('cr_events_dashboard_modal_attendees_no_users_label'); ?>
                                    </span>
                                </div>
                            <?php } else { ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_name_label'); ?></th>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_phone_label'); ?></th>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_email_label'); ?></th>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_status_label'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($new_users as $new_user) { ?>
                                            <tr>
                                                <td><?php echo $new_user['attend_fullname']; ?></td>
                                                <td>
                                                    <?php if(null !== $new_user['attend_phone_link']) { ?>
                                                        <a href="<?php echo $new_user['attend_phone_link']; ?>"
                                                            class="link-black txt-medium text-nowrap"
                                                            title="<?php echo translate("cr_events_dashboard_modal_attendees_action_phone_to_text", array('{attendee}' => $new_user['attend_fullname']), true); ?>">
                                                            <?php echo $new_user['attend_phone']; ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        <?php echo $new_user['attend_phone']; ?>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo $new_user['attend_email_link']; ?>"
                                                        class="link-black txt-medium text-nowrap"
                                                        title="<?php echo translate("cr_events_dashboard_modal_attendees_action_email_to_text", array('{attendee}' => $new_user['attend_fullname']), true); ?>">
                                                        <?php echo $new_user['attend_email']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo translate("cr_events_dashboard_modal_attendees_status_{$new_user['attend_status']}_text"); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                        <div role="tabpanel" class="tab-pane fade show active" id="meeting-b2b">
                            <?php if (empty($registered_users)) { ?>
                                <div class="info-alert-b">
                                    <i class="ep-icon ep-icon_info-stroke"></i>
                                    <span>
                                        <?php echo translate('cr_events_dashboard_modal_attendees_no_users_label'); ?>
                                    </span>
                                </div>
                            <?php } else { ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_name_label'); ?></th>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_phone_label'); ?></th>
                                            <th><?php echo translate('cr_events_dashboard_modal_attendees_column_email_label'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registered_users as $registered_user) { ?>
                                            <tr>
                                                <td><?php echo $registered_user['attend_fullname']; ?></td>
                                                <td>
                                                    <?php if(null !== $registered_user['attend_phone_link']) { ?>
                                                        <a href="<?php echo $registered_user['attend_phone_link']; ?>"
                                                            class="link-black txt-medium text-nowrap"
                                                            title="<?php echo translate("cr_events_dashboard_modal_attendees_action_phone_to_text", array('{attendee}' => $registered_user['attend_fullname']), true); ?>">
                                                            <?php echo $registered_user['attend_phone']; ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        <?php echo $registered_user['attend_phone']; ?>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo $registered_user['attend_email_link']; ?>"
                                                        class="link-black txt-medium text-nowrap"
                                                        title="<?php echo translate("cr_events_dashboard_modal_attendees_action_email_to_text", array('{attendee}' => $registered_user['attend_fullname']), true); ?>">
                                                        <?php echo $registered_user['attend_email']; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
