function modalFormCallBack(form, data_table){
    var $form = $(form);

    if (parseInt($(".js-select-template-structure").find(":selected"), 10)) {
        sentAjax($form, data_table);
    } else {
        saveContent()
            .then(function () {
                sentAjax($form, data_table);
            });
    }
}

var sentAjax = function ($form, data_table) {
    tinymce.triggerSave();

    $.ajax({
        type: 'POST',
        url: "emails_template/ajax_operation/update_template",
        data: $form.serialize(),
        beforeSend: function () {
            showLoader($form);
        },
        dataType: 'json',
        success: function (data) {
            systemMessages(data.message, 'message-' + data.mess_type);

            if (data.mess_type == 'success') {
                closeFancyBox();
                if (data_table != undefined)
                    data_table.fnDraw(false);
            } else {
                hideLoader($form);
            }
        }
    });
}

var addPreviewTemplateData = function ($this) {
    var removeBtn = '';

    if (haveRemoveBtn) {
        removeBtn = '<a class="btn btn-danger call-function" data-callback="removePreviewTemplateData"><i class="ep-icon ep-icon_remove"></i></a>';
    }

    $('#js-preview-data-wrapper').append('<div class="js-preview-data-item flex-display">\
                                                <input class="w-30pr" type="text" name="preview_template_data['+templateDataCount+'][name]">\
                                                <input class="w-70pr" type="text" name="preview_template_data['+templateDataCount+'][value]">'
        + removeBtn +
        '</div>');
    templateDataCount++;
}

var removePreviewTemplateData = function ($this) {
    $this.closest('.js-preview-data-item').remove();
}

$(function(){
    $(".js-select-template-structure").on("change", function() {
        var that = $(this).find(":selected");
        var jsonBlock = $(".js-block-content-json");
        var htmlBlock = $(".js-block-content-html");

        if (parseInt(that.data("json"), 10)) {
            jsonBlock.removeClass("display-n");
            htmlBlock.addClass("display-n");
        } else {
            htmlBlock.removeClass("display-n");
            jsonBlock.addClass("display-n");
        }
    });

    tinymce.init({
        selector: '#js-triggered-information',
        menubar: false,
        statusbar: false,
        height: 250,
        plugins: ["autolink lists link textcolor"],
        dialog_type: "modal",
        toolbar: "bold italic underline forecolor backcolor link | numlist bullist",
        resize: false
    });
});
