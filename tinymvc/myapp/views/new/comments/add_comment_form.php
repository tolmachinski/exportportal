<div class="wr-modal-flex inputs-40">
	<form
        id="js-comments-form"
        class="modal-flex__form validateModal"
        data-js-action="comments:form-action"
        data-type="add"
    >
		<div class="modal-flex__content">
            <?php if (!logged_in()) { ?>
                <label class="input-label input-label--required"><?php echo translate('comments_form_add_name'); ?></label>
			    <input
                    class="validate[required,custom[validUserName],minSize[2],maxSize[150]]"
                    data-prompt-position="bottomLeft:0"
                    type="text"
                    name="name"
                    placeholder="<?php echo translate('comments_form_add_name_placeholder'); ?>"
                    <?php echo addQaUniqueIdentifier('global__common-comments_form-add_input-name'); ?>
                >

                <label class="input-label input-label--required"><?php echo translate('comments_form_add_email'); ?></label>
                <input
                    class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[254]]"
                    type="text"
                    name="email"
                    placeholder="<?php echo translate('comments_form_add_email_placeholder'); ?>"
                    <?php echo addQaUniqueIdentifier('global__common-comments_form-add_input-email'); ?>
                >
            <?php } ?>

			<label class="input-label input-label--required"><?php echo translate('comments_form_add_comment'); ?></label>
			<textarea
                class="validate[required,maxSize[1000]] js-textcounter"
                data-max="1000"
                name="comment"
                placeholder="<?php echo translate('comments_form_add_comment_placeholder'); ?>"
                <?php echo addQaUniqueIdentifier('global__common-comments_form-add_input-comment'); ?>
            ></textarea>
        </div>

		<div class="modal-flex__btns">
            <input type="hidden" name="resource" value="<?php echo cleanOutput($resource['id']); ?>">

			<div class="modal-flex__btns-right">
				<button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('global__common-comments_form-add_button-submit'); ?>
                ><?php echo translate('comments_form_add_button_submit'); ?></button>
			</div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragment(
        "comments:init-form",
        null,
        true
    );
?>
