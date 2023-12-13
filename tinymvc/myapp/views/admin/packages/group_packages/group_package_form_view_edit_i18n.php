<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" id="group-packages-localization--form" class="validateModal relative-b">
        <input type="hidden" name="package" value="<?php echo $package['idpack']; ?>"/>
        <input type="hidden" name="language" value="<?php echo $language['id_lang'] ?>"/>
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <div class="form-control">
                        <?php echo $language['lang_name']; ?>
                    </div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Description</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control" id="group-packages-localization--form-input--original" disabled><?php echo $package['original']?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control validate[required]" id="group-packages-localization--form-input--description" name="description"><?php echo $package['description']?></textarea>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="group-packages-localization--form-action--submit">
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
    }

	$(function(){
        var originalTextId = '#group-packages-localization--form-input--original';
        var descriptionId = '#group-packages-localization--form-input--description';

        tinymce.remove(originalTextId);
        tinymce.remove(descriptionId);
		tinymce.init({
			selector: originalTextId,
            readonly: true,
            menubar: false,
			statusbar: false,
			height: 250,
			dialog_type: "modal",
			toolbar: false,
			resize: false
		});
		tinymce.init({
			selector: descriptionId,
            menubar: false,
			statusbar : false,
			height : 250,
			plugins: ["image code autolink lists link textcolor preview fullscreen"],
			dialog_type : "modal",
			toolbar: "code fullscreen | fontsizeselect | bold italic underline forecolor backcolor link | numlist bullist | alignleft aligncenter alignright alignjustify",
			fontsize_formats: '8px 10px 12px 14px 16px 18px 20px 22px 24px 36px',
			resize: false
		});
	});
</script>
