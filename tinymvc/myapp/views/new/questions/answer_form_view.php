<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal h-auto" <?php echo addQaUniqueIdentifier("community-question-answer__form_popup"); ?> data-js-action="community:add_answer">
		<div class="modal-flex__content">
            <div class="form-group">
                <textarea
                        class="mt-8 mt-0-sm pt-15 validate[required,minSize[3],maxSize[1000]] js-text-counter"
                        data-max="1000"
                        data-prompt-position="bottomLeft:0"
                        name="text"
                        <?php echo addQaUniqueIdentifier("popup__add-asnwer__form_textarea"); ?>
                        placeholder="<?php echo translate('community_questions_answer_form_label_message');?>"><?php if(!empty($answer)){echo cleanOutput($answer['text_answer']);}?></textarea>
            </div>
        </div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button
                    class="btn btn-primary mnw-150 mnw-110-sm"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__add-answer__form_submit-btn'); ?>
                >
                    <?php echo translate('general_modal_button_submit_text'); ?>
                </button>
			</div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('community:add-answer-form', array(__COMMUNITY_URL . 'community_questions/ajax_answers_operation/add_answer/' . $id_question));?>
