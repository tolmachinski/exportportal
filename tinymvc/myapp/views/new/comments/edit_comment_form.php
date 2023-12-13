<div class="wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        id="js-comments-form"
        data-js-action="comments:form-action"
        data-type="edit"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('comments_form_add_comment'); ?></label>
            <textarea
                class="validate[required,maxSize[1000]] js-textcounter"
                data-max="1000"
                name="comment"
                placeholder="<?php echo translate('comments_form_add_comment_placeholder'); ?>"
                <?php echo addQaUniqueIdentifier('global__common-comments_form-edit_input-comment'); ?>
            ><?php echo cleanOutput($comment['text']);?></textarea>
        </div>

        <div class="modal-flex__btns">
            <input type="hidden" name="comment_id" value="<?php echo $comment['id'];?>">
			<div class="modal-flex__btns-right">
				<button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('global__common-comments_form-edit_button-submit'); ?>
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
