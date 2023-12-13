var editor = null,
    contentEmailTemplateTextarea = null,
    templateDataCount = 0;

var saveContent = function () {
    return new Promise(function (resolve) {
        contentEmailTemplateTextarea.val(editor.getValue());
        resolve(true);
    });
};

$(function(){
    contentEmailTemplateTextarea = $('#js-content-email-template-textarea');
    templateDataCount = $('#js-preview-data-wrapper .js-preview-data-item').length;

    editor = ace.edit("js-content-email-template");
    editor.setTheme("ace/theme/chrome");
    var htmlMode = ace.require("ace/mode/html").Mode;
    editor.session.setMode(new htmlMode());
});

