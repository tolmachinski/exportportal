<div class="wr-modal-b">
    <form method="post" action="<?php echo __SITE_URL;?>faq/ajax_faq_operation/edit_faq_i18n" id="faq-translation--form" class="validateModal relative-b">
        <input type="hidden" name="id" id="faq-translation--form-input--method" value="<?php echo $faq['id']; ?>">
		<div class="modal-b__content pb-0 w-900 mh-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <div class="form-control"><?php echo $language['lang_name']; ?></div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Question</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control mnh-100 h-100" id="faq-translation--form-input--question-original"><?php echo $faq['original_question']; ?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control validate[required,maxSize[250]] mnh-100 h-100" name="question" placeholder="Question text"><?php echo $faq['question']; ?></textarea>
                    </div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Answer</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control h-250" id="faq-translation--form-input--answer-original"><?php echo $faq['original_answer']; ?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control h-250 validate[required]" id="faq-translation--form-input--answer" name="answer" placeholder="Answer text"><?php echo $faq['answer']; ?></textarea>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="faq-translation--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script type="text/javascript">
	var modalFormCallBack = function (formNode, dataGrid){
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();
        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                closeFancyBox();
                if(dataGrid) {
                    $(dataGrid).DataTable().draw(false);
                }
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
    };

    $(document).ready(function() {
        var previewOptions = {
            readonly: true,
            menubar: false,
			statusbar: false,
			height: 250,
			dialog_type: "modal",
			toolbar: false,
			resize: false
        };
        var editorOptions = {
            menubar: false,
            statusbar : false,
            plugins: ["autolink lists link"],
            toolbar: "bold italic underline | link | numlist bullist",
            resize: false
        }

        tinymce.remove('#faq-translation--form-input--answer');
        tinymce.remove('#faq-translation--form-input--answer-original');
        tinymce.remove('#faq-translation--form-input--question-original');
        tinymce.init($.extend({}, previewOptions, { selector: '#faq-translation--form-input--answer-original' }));
        tinymce.init($.extend({}, previewOptions, { selector: '#faq-translation--form-input--question-original', height: 100 }));
        tinymce.init($.extend({}, editorOptions, { selector: '#faq-translation--form-input--answer' }));
    });
</script>
