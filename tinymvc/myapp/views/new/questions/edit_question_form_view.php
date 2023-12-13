<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal js-add-question-form"
        data-js-action="community:add_question"
        data-callback="questionsEditFormCallBack"
    >
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label input-label--required"><?php echo translate('community_category_label'); ?></label>
                            <select class="validate[required]" name="category">
                                <option selected="selected" value=""><?php echo translate('community_select_category_option'); ?></option>
                                <?php foreach($quest_cats as $category){?>
                                    <option value="<?php echo $category['idcat']?>" <?php echo !empty($question)?selected($category['idcat'],$question['id_category']):'';?>><?php echo $category['title_cat']?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

					<div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label input-label--required"><?php echo translate('community_country_label'); ?></label>
                            <select class="validate[required]" name="country" >
                                <option selected="selected" value=""><?php echo translate('community_select_country_option'); ?></option>
                                <?php echo getCountrySelectOptions($countries, empty($question['id_country']) ? 0 : $question['id_country'], array('include_default_option' => false));?>
                            </select>
                        </div>
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
                    name="title"/>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('community_description_label'); ?></label>
                <textarea
                        class="validate[required, minSize[3], maxSize[1000]] js-text-counter"
                        data-max="1000"
                        placeholder="Your Text Here"
                        name="text"><?php echo !empty($question)?$question['text_question']:'';?></textarea>
            </div>
        </div>
		<div class="modal-flex__btns">
			<?php if(!empty($question)){?>
				<input type="hidden" name="id_question" value="<?php echo $question['id_question'] ?>"/>
			<?php }?>

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit"><?php echo translate('general_modal_button_submit_text'); ?></button>
			</div>
		</div>
	</form>
</div>

<script>
    $(function(){
        $('.js-text-counter').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    });

    function questionsEditFormCallBack(form){
        var $form = $(form);
        var $wrform = $form.closest('.js-modal-flex');
        var fdata = $form.serialize();

        $.ajax({
            type: 'POST',
            url: 'community_questions/ajax_questions_operation/edit_question',
            data: fdata,
            dataType: 'JSON',
            beforeSend: function(){
                showLoader($wrform);
            },
            success: function(resp){
                hideLoader($wrform);
                systemMessages( resp.message, resp.mess_type );

                if(resp.mess_type == 'success'){
                    if(typeof dtQuestions !== 'undefined'){
                        dtQuestions.fnDraw(false);
                    } else{
                        _notifyContentChangeCallback();
                    }

                    closeFancyBox();
                }else{
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>
