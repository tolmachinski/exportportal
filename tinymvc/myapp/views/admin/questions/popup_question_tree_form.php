<div class="wr-form-content w-700 h-430">
	<div class="popup-question-list-b mt-10">
		<ul class="clearfix bg-blue-lighter">
			<li id="item-question-<?php echo $question['id_question'];?>">

				<div class="title-b clearfix">
                    <a class="name-b pull-left w-150"
                       href="<?php echo __SITE_URL.'usr/'.strForURL($question['fname'].' '.$question['lname']).'-'.$question['id_user'];?>" target="_blank">
                       <?php echo $question['fname'].' '.$question['lname'];?>
                    </a>

					<span class="country-b pull-left w-25">
                        <img
                            class="mt-2"
                            width="24"
                            height="24"
                            src="<?php echo getCountryFlag($question['country']); ?>"
                            alt="<?php echo $question['country']?>"
                            title="<?php echo $question['country']?>"
                        />
                    </span>

					<span class="category-b pull-left w-250">
                        <?php echo $question['title_cat'] ?>
                    </span>

					<span class="date-b pull-right w-150">
                        <?php echo formatDate($question['date_question'])?>
                    </span>

					<?php if(logged_in() && (have_right('moderate_content') || is_my($question['id_user']))){?>
					<div class="ep-actions pull-right mt-7">
						<?php if(have_right('moderate_content')){?>
							<?php if(!$question['moderated']){?>
                                <a class="ep-actions__link ep-icon ep-icon_sheild-ok txt-green confirm-dialog"
                                   data-callback="moderate_question_modal"
                                   data-message="Are you sure want moderate this question?"
                                   data-type="questions"
                                   data-action="moderate"
                                   id="<?php echo $question['id_question']?>"
                                   title="Moderate question"></a>
							<?php }?>
                            <a class="ep-actions__link ep-icon ep-icon_remove txt-red confirm-dialog"
                               data-callback="delete_question_modal"
                               data-message="Are you sure want delete this question?"
                               data-type="questions"
                               data-action="delete"
                               id="<?php echo $question['id_question']?>"
                               href="#"
                               title="Delete question">
                            </a>
						<?php }?>
					</div>
					<?php } ?>
				</div>

				<div class="subject-b"><?php echo cleanOutput($question['title_question']) ?></div>
				<div class="text-b"><?php echo cleanOutput($question['text_question']) ?></div>
			</li>
		</ul>
		<?php if(isset($question['answers']) && count($question['answers'])){?>
			<div class="answers-b">

            <h3>Answers(<span class="answers-nr" id="answers-counter"><?php echo count($question['answers']); ?></span>)</h3>

			<ul class="clearfix bg-green-lighter ml-40">
				<?php foreach($question['answers'] as $answer){?>
					<li class="answer-<?php echo $answer['id_answer'] ?>" id="modal-answer-<?php echo $answer['id_answer'] ?>">

						<div class="title-b clearfix">
                            <a class="name-b pull-left"
                               href="<?php echo __SITE_URL.'usr/'.strForURL($answer['fname'].' '.$answer['lname']).'-'.$answer['id_user'];?>"
                               target="_blank"
                               title="<?php echo $answer['fname'].' '.$answer['lname'];?>">by <?php echo $answer['fname'].' '.$answer['lname'];?>
                            </a>

                            <span class="date-b pull-right w-150"><?php echo formatDate($answer['date_answer'])?></span>

							<?php if(logged_in() && (have_right('moderate_content') || is_my($answer['id_user']))){?>
								<div class="ep-actions pt-7 pull-right">
									<?php if(have_right('moderate_content')){?>
										<?php if(!$answer['moderated']){?>
                                            <a class="ep-actions__link ep-icon ep-icon_sheild-ok txt-green confirm-dialog"
                                               data-callback="moderate_question_modal"
                                               data-message="Are you sure want moderate this answer?"
                                               data-type="answers"
                                               data-action="moderate"
                                               id="<?php echo $answer['id_answer'] ?>"
                                               href="#"
                                               title="Moderate answers">
                                            </a>
										<?php }?>
                                        <a class="ep-actions__link ep-icon ep-icon_remove txt-red confirm-dialog"
                                           data-callback="delete_question_modal"
                                           data-message="Are you sure want delete this answer?"
                                           data-type="answers"
                                           data-action="delete"
                                           id="<?php echo $answer['id_answer'] ?>"
                                           href="#"
                                           title="Delete answers">
                                        </a>
									<?php }?>
								</div>
							<?php } ?>
                        </div>

                        <div class="subject-b"><?php echo $answer['title_answer']?></div>

                        <div class="text-b"><?php echo cleanOutput($answer['text_answer'])?></div>

						<?php if(count($answer['comments'])){?>
						<div class="comments-b comments-container">

                            <h3>Comments(<span class="comments-nr comments-counter"><?php echo count($answer['comments']); ?></span>)</h3>

							<ul class="clearfix bg-orange-lighter ml-40">
								<?php foreach($answer['comments'] as $comment){?>
									<li class="comment-<?php echo $comment['id_comment'] ?>" id="modal-comment-<?php echo $comment['id_comment']?>">
										<div class="title-b clearfix">
                                            <a class="name-b pull-left"
                                               href="<?php echo __SITE_URL.'usr/'.strForURL($comment['fname'].' '.$comment['lname']).'-'.$comment['id_user'];?>" target="_blank"
                                               title="<?php echo $comment['fname'].' '.$comment['lname'];?>">by <?php echo $comment['fname'].' '.$comment['lname']?>
                                            </a>
											<span class="date-b pull-right w-150">
                                                <?php echo formatDate($comment['date_comment'])?>
                                            </span>
											<?php if(tmvc::instance()->controller->session->loggedIn && (have_right('moderate_content') || ($comment['id_user'] == tmvc::instance()->controller->session->id))){?>
												<div class="ep-actions pt-7 pull-right">
												<?php if(!$comment['moderated']){?>
													<?php if(have_right('moderate_content')){?>
                                                        <a class="ep-actions__link ep-icon ep-icon_sheild-ok txt-green confirm-dialog"
                                                           data-callback="moderate_question_modal"
                                                           data-message="Are you sure want moderate this comment?"
                                                           data-type="comments"
                                                           data-action="moderate"
                                                           id="<?php echo $comment['id_comment']?>"
                                                           href="#"
                                                           title="Moderate comment">
                                                        </a>
													<?php }
												} ?>
                                                    <a class="ep-actions__link ep-icon ep-icon_remove txt-red confirm-dialog"
                                                       data-callback="delete_question_modal"
                                                       data-message="Are you sure want delete this comment?"
                                                       data-type="comments"
                                                       data-action="delete"
                                                       id="<?php echo $comment['id_comment']?>"
                                                       href="#"
                                                       title="Delete comment">
                                                    </a>
												</div>
											<?php } ?>
										</div>
										<div class="text-b"><?php echo cleanOutput($comment['text_comment']);?></div>
									</li>
								<?php } ?>
							</ul>
						</div>
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
			</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		if($('#modal-<?php echo $scroll_block;?>').length !== 0)
			scrollToElementModal('#modal-<?php echo $scroll_block;?>', '.wr-form-content');
	});

	var moderate_question_modal = function(opener){
        var $this = $(opener);
        var params = {};
        var type = $this.data('type');
        var action = $this.data('action');

        switch(type) {
            case "questions":
                params.key = 'question',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_questions_operation/moderate';
                break;
            case "answers":
                params.key = 'answer',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_answers_operation/moderate';
                break;
            case "comments":
                params.key = 'comment',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_comments_operation/moderate';
                break;
        }

		$.ajax({
            type: 'POST',
            url: params.url,
            data: {[params.key]: $this.attr('id')},
            dataType: "JSON",
            success: function(data) {
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type != 'error'){
                    $this.parent('.ep-actions ').find('.ep-icon_sheild-ok').remove();
                }
            }
		});
	}


	var delete_question_modal = function(opener){
		var $this = $(opener);
        var params = {};
        var type = $this.data('type');
        var action = $this.data('action');
        var answersCounter = $("#answers-counter");
        var commentsCounter = $($this).parents(".comments-container").find(".comments-counter");

        switch(type) {
            case "questions":
                params.key = 'question',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_questions_operation/delete';
                break;
            case "answers":
                params.key = 'answer',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_answers_operation/delete';

                decreaseCommentsCounter(answersCounter, $this, '#modal-answer-');
                break;
            case "comments":
                params.key = 'comment',
                params.url = '<?php echo __SITE_URL ?>community_questions/ajax_comments_operation/delete';

                decreaseCommentsCounter(commentsCounter, $this, '#modal-comment-');
                break;
        }

		$.ajax({
			type: 'POST',
			url: params.url,
            data: {[params.key]: $this.attr('id')},
			dataType: "JSON",
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
			}
		});
	}

    var decreaseCommentsCounter = function(element, $this, type) {
        element.html(parseInt(element.text(), 10) - 1);
        $($this).parents(type + $this.attr('id')).remove();
    }
</script>
