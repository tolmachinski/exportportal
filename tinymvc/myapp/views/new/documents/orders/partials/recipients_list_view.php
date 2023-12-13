<div class="container-fluid-modal">
    <div class="add-info-row mb-15 js-recipients-ui">
        <div class="add-info-row__col pb-15-md">
            <select class="form-control js-recipients-assignees" data-validate="validate[required]" data-border="validengine-border">
                <option value="" selected disabled><?php echo translate('order_documents_dashboard_edit_popup_recipients_assignee_palcaholder', null, true); ?></option>
                <?php foreach ($assignees ?? [] as $assignee) { ?>
                    <option
                        value="<?php echo cleanOutput($assignee['id']); ?>"
                        data-name="<?php echo cleanOutput($assignee['name']); ?>"
                        data-group="<?php echo cleanOutput($assignee['group']); ?>"
                        data-color="<?php echo cleanOutput($assignee['color']); ?>">
                        <?php echo cleanOutput($assignee['title']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="add-info-row__col">
            <select class="form-control js-recipients-types" data-validate="validate[required]" data-border="validengine-border">
                <option value="" selected disabled><?php echo translate('order_documents_dashboard_edit_popup_recipients_type_palcaholder', null, true); ?></option>
                <option value="<?php echo \App\Envelope\RecipientTypes::SIGNER; ?>">
                    <?php echo translate('order_documents_recipient_types_signer_list_option_text', null, true); ?>
                </option>
                <option value="<?php echo \App\Envelope\RecipientTypes::VIEWER; ?>">
                    <?php echo translate('order_documents_recipient_types_viewer_list_option_text', null, true); ?>
                </option>
            </select>
        </div>

        <div class="add-info-row__col add-info-row__action-col add-info-row__col--130">
            <a href="#"
                class="btn btn-dark btn-block text-nowrap call-function call-action js-recipients-add-assignee"
                data-callback="addRecipientToEnvelope"
                data-js-action="documents:process-envelope:add-recipient">
                <?php echo translate('order_documents_dashboard_edit_popup_recipients_add_button_text', null, true); ?>
            </a>
        </div>
    </div>

    <div class="add-info-row-wr add-info-row-wr--pd-no-first add-info-row-wr--pd-20 js-recipients-container"></div>
</div>


<script type="text/template" data-name="recipientEntry">
    <div class="add-info-row">
        <div class="add-info-row__col add-info-row__col--w100pr">
            <div class="add-info-row__item-simple flex-display flex-jc--sb flex-ai--c p-0">
                <span class="add-info-row__ttl">
                    <span>{{position}}.</span>
                    <span>{{assigneeName}}</span>
                    <div class="{{assigneeGroupColor}}">{{assigneeGroup}}</div>
                </span>

                <div class="add-info-row__ttl tar">
                    <div class="badge badge-primary">{{recipientType}}</div>
                    <div class="fs-14 txt-gray">{{expiresAt}}</div>
                </div>
            </div>
        </div>
        <div class="add-info-row__col add-info-row__col--180">
            <div class="add-info-row__actions add-info-row__actions--3">
                <input
                    class="js-recipient-due-date flex-as--fe d-none-full"
                    type="text"
                    data-index="{{index}}"
                    value="{{expiresAt}}"
                    readonly>
                <button
                    class="btn btn-light call-function call-action"
                    title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_due_date_title', null, true); ?>"
                    data-index="{{index}}"
                    data-callback="showDatePicker"
                    data-js-action="documents:process-envelope:show-date-picker">
                    <i class="ep-icon ep-icon_calendar"></i>
                </button>
                <a
                    class="btn btn-light call-function call-action"
                    title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_down_button_title', null, true); ?>"
                    data-index="{{index}}"
                    data-callback="changeRecipientRoutingOrderInEnvelope"
                    data-js-action="documents:process-envelope:change-recipient-order"
                    data-direction="down">
                    <i class="ep-icon ep-icon_arrow-line-down"></i>
                </a>
                <a
                    class="btn btn-light call-function call-action"
                    title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_up_button_title', null, true); ?>"
                    data-index="{{index}}"
                    data-callback="changeRecipientRoutingOrderInEnvelope"
                    data-js-action="documents:process-envelope:change-recipient-order"
                    data-direction="up">
                    <i class="ep-icon ep-icon_arrow-line-up"></i>
                </a>
                <a
                    class="btn btn-light call-function call-action"
                    title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_remove_button_title', null, true); ?>"
                    data-index="{{index}}"
                    data-callback="removeRecipientFromEnvelope"
                    data-js-action="documents:process-envelope:remove-recipient">
                    <i class="ep-icon ep-icon_trash-stroke"></i>
                </a>
            </div>
        </div>
    </div>
</script>
