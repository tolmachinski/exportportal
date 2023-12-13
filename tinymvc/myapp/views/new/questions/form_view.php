<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal h-auto js-add-question-form" data-js-action="community:add_question">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-sm-6">
                        <label class="input-label input-label--required mt-0"><?php echo translate('community_category_label'); ?></label>
                        <select
                            class="validate[required]"
                            name="category"
                            <?php echo addQaUniqueIdentifier('popup__ask-question__form_category-select'); ?>
                        >
                            <option selected="selected" value=""><?php echo translate('community_select_category_option'); ?></option>
                            <?php foreach($quest_cats as $category){?>
                                <option value="<?php echo $category['idcat']?>" <?php echo !empty($question)?selected($category['idcat'],$question['id_category']):'';?>><?php echo $category['title_cat']?></option>
                            <?php } ?>
                        </select>
					</div>
					<div class="col-12 col-sm-6">
						<label class="input-label input-label--required mt-sm-0"><?php echo translate('community_country_label'); ?></label>
						<select
                            class="validate[required]"
                            name="country"
                            <?php echo addQaUniqueIdentifier('popup__ask-question__form_country-select'); ?>
                        >
							<option selected="selected" value=""><?php echo translate('community_select_country_option'); ?></option>
							<?php echo getCountrySelectOptions($countries, empty($question['id_country']) ? 0 : $question['id_country'], array('include_default_option' => false));?>
						</select>
					</div>
				</div>
			</div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('community_question_title_label'); ?></label>
                <input
                    class="validate[required, minSize[3], maxSize[100]] js-text-counter"
                    data-max="100"
                    value="<?php echo !empty($question)?cleanOutput($question['title_question']):'';?>"
                    type="text"
                    name="title"
                    <?php echo addQaUniqueIdentifier('popup__ask-question__form_question-title-input'); ?>
                />
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('community_description_label'); ?></label>
                <textarea
                    class="validate[required, minSize[3], maxSize[1000]] js-text-counter"
                    data-max="1000"
                    placeholder="Your Text Here"
                    name="text"
                    <?php echo addQaUniqueIdentifier('popup__ask-question__form_description-textarea'); ?>
                ><?php echo !empty($question)?$question['text_question']:'';?></textarea>
            </div>
        </div>
		<div class="modal-flex__btns">
			<?php if(!empty($question)){?>
				<input type="hidden" name="id_question" value="<?php echo $question['id_question'] ?>"/>
			<?php }?>

			<div class="modal-flex__btns-right">
				<button
                    class="btn btn-primary mnw-150"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__ask-question__form_submit-btn'); ?>
                >
                    <?php echo translate('general_modal_button_submit_text'); ?>
                </button>
			</div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragment('community:add-question-form', array(__COMMUNITY_URL . 'community_questions/ajax_questions_operation/add_question'));
?>
