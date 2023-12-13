<?php
    /**
     * @author Vasile Cristel
     * @todo Remove [13.11.2021]
     * old view that is no longer used
    */
?>

<!-- <li class="minfo-questions-answers__item answer-<?php //echo $answer['id_answer'] ?>">
	<div class="minfo-questions-answers__item-in">
		<div class="minfo-questions-answers__imgb">
			<img class="minfo-questions-answers__img" src="<?php //echo __IMG_URL.getImage( 'public/img/users/' . $answer['id_user'] . '/thumb_Rx80_' . $answer['user_photo'], thumbNoPhoto($answer['user_group']))?>" alt="<?php //echo $answer['fname']?>" />
			<?php //if($answer['user_type'] == 'user'){?>
				<a class="minfo-questions-answers__uname" href="<?php //echo __SITE_URL.'usr/'.strForURL($answer['fname'].' '.$answer['lname']).'-'.$answer['id_user'];?>" title="<?php //echo $answer['fname'].' '.$answer['lname'];?>"><?php //echo translate('conjunction_label_by');?> <?php //echo $answer['fname']?></a>
			<?php //} else{?>
				<span class="minfo-questions-answers__uname" title="<?php //echo $answer['fname'].' '.$answer['lname'];?>"><?php //echo $answer['fname'];?></span>
			<?php //}?>

			<?php //if(logged_in()){?>
				<div class="did-help <?php //if(isset($question['helpful_answers'][$answer['id_answer']])){?>rate-didhelp<?php }?>">
					<div class="did-help__txt"><?php //echo translate('community_questions_answer_help_text');?></div>

					<?php
						// $disable_class = "";
						// if($answer['id_user'] == id_session())
						// 	$disable_class = "disabled";
					?>
					<span class="i-up didhelp-btn
						<?php //if($disable_class == "disabled"){
							//echo $disable_class;
						//}elseif(isset($question['helpful_answers'][$answer['id_answer']])){
							//echo equals($question['helpful_answers'][$answer['id_answer']],1,'disabled');
						//}?>"
						data-item="<?php //echo $answer['id_answer']?>" data-page="community_questions" data-type="answers" data-action="y">
						<span class="counter-b"><?php //echo $answer['count_plus']?></span>
						<a class="ep-icon ep-icon_up"></a>
					</span>
					<span class="i-down didhelp-btn
						<?php //if($disable_class == "disabled"){
							//echo $disable_class;
						//}elseif(isset($question['helpful_answers'][$answer['id_answer']])){
							//echo equals($question['helpful_answers'][$answer['id_answer']],0,'disabled');
						//}?>"
						data-item="<?php //echo $answer['id_answer']?>" data-page="community_questions" data-type="answers" data-action="n">
						<a class="ep-icon ep-icon_down"></a>
						<span class="counter-b"><?php //echo $answer['count_minus']?></span>
					</span>
				</div>
			<?php //} ?>
		</div>
		<div class="minfo-questions-answers__text">
			<div class="row">
				<div class="col-8 minfo-questions-answers__subject"><?php //echo $answer['title_answer']?></div>
				<div class="col-4 minfo-questions-answers__date"><?php //echo formatDate($answer['date_answer'])?></div>
			</div>
			<p class="minfo-questions-answers__message"><?php //echo $answer['text_answer']?></p>

			<div class="minfo-questions-answers__actions clearfix">
				<?php //if(logged_in()){?>
					<div class="minfo-questions-answers__dropdawn dropdown pull-right">
						<a class="dropdown-toggle" data-toggle="dropdown">
							<i class="ep-icon ep-icon_menu-circles "></i>
						</a>
						<ul class="dropdown-menu">
							<?php //if(!is_privileged('user', $answer['id_user'])){?>
								<li>
									<a class="fancybox.ajax fancyboxValidateModal txt-red" href="<?php //echo __SITE_URL; ?>complains/popup_forms/add_complain/question_answer/<?php //echo $answer['id_answer']; ?>/<?php //echo $answer['id_user'];?>" data-title="<?php //echo translate('general_button_report_text');?>">
										<i class="ep-icon ep-icon_megaphone"></i> <?php //echo translate('general_button_report_text');?>
									</a>
								</li>
							<?php }?>

							<?php //if((is_privileged('user', $answer['id_user']) || have_right('community_questions_administration')) && !$answer['moderated']){?>
								<li><a class="fancybox.ajax fancyboxValidateModal" data-title="<?php //echo translate('general_button_edit_text');?>" title="<?php ///echo translate('general_button_edit_text');?>" href="<?php //echo __SITE_URL?>community_questions/popup_forms/edit_answer/<?php //echo $answer['id_answer']?>"><i class="ep-icon ep-icon_pencil"></i> <?php //echo translate('general_button_edit_text');?></a></li>
							<?php }?>

							<?php //if(have_right('community_questions_administration') && !$answer['moderated']){?>
								<li><a class="confirm-dialog txt-green" data-message="Are you sure you want to moderate this answer?" data-callback="moderate_answer" data-answer="<?php //echo $answer['id_answer'] ?>" href="#"><i class="ep-icon ep-icon_sheild-ok"></i> <?php //echo translate('general_button_moderate_text');?></a></li>
							<?php //}?>

							<?php //if((is_privileged('user', $answer['id_user'], 'manage_community_questions') || have_right('community_questions_administration')) && $answer['count_comments'] == 0){?>
								<li><a class="confirm-dialog txt-red" data-message="Are you sure you want to delete this answer?" data-callback="delete_answer" data-answer="<?php //echo $answer['id_answer'] ?>" href="#"><i class="ep-icon ep-icon_remove"></i> <?php //echo translate('general_button_delete_text');?></a></li>
							<?php }?>
						</ul>
					</div>
				<?php //} ?>

				<div class="ep-actions ep-actions--align-right <?php //if(logged_in()){?>ep-actions--last-border<?php } ?> pt-2">
					<?php //if(logged_in()){?>
						<a class="ep-actions__link <?php //echo ($answer['count_comments'] >= 1)? 'load-ajax txt-orange':'load-hide txt-gray-nlight'?>" href="#" data-answer="<?php //echo $answer['id_answer'] ?>">
							<i class="ep-icon ep-icon_comment"></i> <?php //echo translate('community_questions_answer_comments_count');?> (<span><?php //echo $answer['count_comments']?></span>)
						</a>
						<?php //if(have_right_or('manage_community_questions,community_questions_administration')){?>
							<a class="ep-actions__link fancybox.ajax fancyboxValidateModal" data-title="<?php //echo translate('community_questions_answer_add_comment_title');?>" href="<?php //echo __SITE_URL;?>community_questions/popup_forms/add_comment/<?php //echo $answer['id_answer']?>">
								<i class="ep-icon ep-icon_reply"></i> <?php //echo translate('community_questions_answer_add_comment_title');?>
							</a>
						<?php }?>
					<?php //} else{?>
						<a class="ep-actions__link load-ajax <?php //echo ($answer['count_comments'] >= 1)? 'txt-orange':'txt-gray-nlight'?>" href="#" data-answer="<?php //echo $answer['id_answer'] ?>">
							<i class="ep-icon ep-icon_comment"></i> <?php //echo translate('community_questions_answer_comments_count');?> (<span><?php //echo $answer['count_comments']?></span>)
						</a>
						<a class="ep-actions__link call-systmess" data-message="<?php //echo translate("systmess_error_should_be_logged", null, true); ?>" data-type="error" href="#" title="Reply">
							<i class="ep-icon ep-icon_reply"></i> <?php //echo translate('community_questions_answer_add_comment_title');?>
						</a>
					<?php //} ?>
				</div>
			</div>
		</div>
	</div>

	<ul class="minfo-questions-comments" style="display: none;"></ul>
</li> -->
