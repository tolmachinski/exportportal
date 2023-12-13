<div class="form-group">
    <label class="input-label input-label--required">
        <span class="input-label__text"><?php echo translate('order_documents_dashboard_edit_popup_title_label', null, true); ?></span>
        <a
            class="ep-icon ep-icon_info lh-16 info-dialog"
            title="<?php echo translate('order_documents_dashboard_edit_popup_title_details_title', null, true); ?>"
            data-title="<?php echo translate('order_documents_dashboard_edit_popup_title_details_dialog_title', [
                "[[HIGHLIGHT]]"  => "<span class=\"txt-gray\">",
                "[[/HIGHLIGHT]]" => "</span>",
            ], true); ?>"
            data-message="<?php echo translate('order_documents_dashboard_edit_popup_title_details_dialog_message', null, true); ?>">
        </a>
    </label>
    <input
        id="<?php echo $prefix; ?>--formfield--title"
        type="text"
        name="title"
        maxlength="200"
        class="validate[required,maxSize[200]]"
        value="<?php echo cleanOutput($envelope['display_title'] ?? null); ?>"
        placeholder="<?php echo translate('order_documents_dashboard_edit_popup_title_placeholder', null, true); ?>">
</div>

<div class="form-group">
    <label class="input-label input-label--required">
        <?php echo translate('order_documents_dashboard_edit_popup_type_label', null, true); ?>
        <a
            class="ep-icon ep-icon_info lh-16 info-dialog"
            title="<?php echo translate('order_documents_dashboard_edit_popup_type_details_title', null, true); ?>"
            data-title="<?php echo translate('order_documents_dashboard_edit_popup_type_details_dialog_title', [
                "[[HIGHLIGHT]]"  => "<span class=\"txt-gray\">",
                "[[/HIGHLIGHT]]" => "</span>",
            ], true); ?>"
            data-content="#info-dialog__order-document-type">
        </a>
        <div class="display-n" id="info-dialog__order-document-type">
            <p><?php echo translate('order_documents_dashboard_edit_popup_type_details_dialog_message_line_1'. null, true); ?></p>
            <p><?php echo translate('order_documents_dashboard_edit_popup_type_details_dialog_message_line_2'. null, true); ?></p>
            <p><?php echo translate('order_documents_dashboard_edit_popup_type_details_dialog_message_line_3'. null, true); ?></p>
        </div>
    </label>
    <input
        id="<?php echo $prefix; ?>--formfield--type"
        type="text"
        name="type"
        maxlength="200"
        class="validate[required,maxSize[200]]"
        value="<?php echo cleanOutput($envelope['display_type'] ?? null); ?>"
        placeholder="<?php echo translate('order_documents_dashboard_edit_popup_description_placeholder', null, true); ?>">
</div>

<div class="form-group">
    <label class="input-label input-label--required"><?php echo translate('order_documents_dashboard_edit_popup_description_label', null, true); ?></label>
    <textarea
        id="<?php echo $prefix; ?>--formfield--description"
        name="description"
        data-max="500"
        class="validate[required,maxSize[500]] textcounter-document_comment js-description"
        placeholder="<?php echo translate('order_documents_dashboard_edit_popup_description_placeholder', null, true); ?>"
        ><?php echo cleanOutput($envelope['display_description'] ?? null); ?></textarea>
</div>
