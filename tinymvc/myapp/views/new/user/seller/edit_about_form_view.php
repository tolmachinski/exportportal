<div class="wr-modal-flex" id="edit-about--forwrapper">
	<form
        id="edit-about--form"
        class="modal-flex__form validateModal"
        data-callback="editAboutFormCallBack"
        autocomplete="off"
    >
		<input type="hidden" name="block_name" value="<?php echo cleanOutput($block_name); ?>"/>

		<div class="modal-flex__content">
			<label class="input-label input-label--required">Description block</label>
			<textarea class="validate[required]" name="text" id="edit_about_text_block" placeholder="Write your text here">
				<?php echo arrayGet($about_block, "text_{$block_name}", ''); ?>
			</textarea>
		</div>

		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Save</button>
			</div>
		</div>
	</form>
</div>

<script>
	$(function () {
		var onSaveContent = function (form) {
			var onRequestStart = function () {
				showLoader(wrapper);
				submitButton.addClass('disabled');
			};
			var onRequestEnd = function () {
				hideLoader(wrapper);
				submitButton.removeClass('disabled');
			};
			var onRequestSuccess = function (response) {
				systemMessages(response.message, response.mess_type);
				if ('success' === response.mess_type) {
					callbackEditStandartAboutBlock(response);
					closeFancyBox();
				}
			};
			onRequestStart();

			return postRequest(__group_site_url + 'seller_about/ajax_about_operation/edit_about_block', form.serializeArray())
				.then(onRequestSuccess)
				.catch(onRequestError)
				.then(onRequestEnd);
		};

		var form = $('#edit-about--form');
		var wrapper = $('#edit-about--forwrapper');
		var submitButton = form.find('button[type=submit]');
		var descriptionFieldId = '#edit_about_text_block';
		var editorOptions = {
			selector: descriptionFieldId,
			dialog_type: "modal",
			statusbar: false,
			menubar: false,
			resize: false,
			height: 250,
			plugins: ["autolink lists link"],
			toolbar: "bold italic underline link | numlist bullist | removeformat ",
		};

		mix(window, { editAboutFormCallBack: onSaveContent }, false);
		if (typeof tinymce !== 'undefined') {
			tinymce.remove(descriptionFieldId);
			tinymce.init(editorOptions);
		}
	});
</script>
