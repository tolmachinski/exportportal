<li class="feedbacks-main-list__res-item" id="li-question-reply-<?php echo $question['id_q']?>">
	<div class="feedbacks-main-list__res-ttl">
		<div class="feedbacks-main-list__res-name">replied by Seller </div>
		<div class="feedbacks-main-list__res-date"><?php echo formatDate($question['reply_date']);?></div>
	</div>

	<div class="feedbacks-main-list__res-text"><?php echo $question['reply'];?></div>

	<div class="clearfix">
		<?php if(logged_in()){?>
		<div class="did-help <?php if(isset($helpful[$question['id_q']])){?>rate-didhelp<?php }?> pull-left">
			<div class="did-help__txt pull-left pr-15">Did it help?</div>
			<?php
				$disable_class = "";
				if($question['id_user'] == id_session())
					$disable_class = "disabled";
			?>
			<span class="i-up didhelp-btn
				<?php if($disable_class == "disabled"){
					echo $disable_class;
				}elseif(isset($helpful[$question['id_q']])){
					echo equals($helpful[$question['id_q']],1,'disabled');
				}?>"
				data-item="<?php echo $question['id_q']?>" data-page="items_questions" data-type="question" data-action="y">
				<span class="counter-b"><?php echo $question['count_plus']?></span>
				<a class="ep-icon ep-icon_up"></a>
			</span>
			<span class="i-down didhelp-btn
				<?php if($disable_class == "disabled"){
					echo $disable_class;
				}elseif(isset($helpful[$question['id_q']])){
					echo equals($helpful[$question['id_q']],0,'disabled');
				}?>"
				data-item="<?php echo $question['id_q']?>" data-page="items_questions" data-type="question" data-action="n">
				<a class="ep-icon ep-icon_down"></a>
				<span class="counter-b"><?php echo $question['count_minus']?></span>
			</span>
		</div>
		<?php } ?>

        <?php $can_edit = is_privileged('user', $question['id_seller'], 'reply_questions') && $question['status'] != 'moderated'; ?>
        <?php $can_report = !is_privileged('user', $question['id_seller'], 'reply_questions'); ?>
		<?php if(logged_in() && ($can_edit || $can_report)){ ?>
		    <div class="ep-actions pull-right">
                <?php if($can_edit){?>
                    <a class="ep-actions__link fancyboxValidateModal fancybox.ajax"
                        data-fancybox-href="<?php echo __SITE_URL?>items_questions/popup_forms/leave_reply_to_item_question/<?php echo $question['id_q']; ?>"
                        data-title="Edit question reply"
                        title="Edit question reply">
                        <i class="ep-icon ep-icon_pencil"></i> Edit
                    </a>
                <?php } ?>

                <?php if($can_report){?>
                    <a class="ep-actions__link fancyboxValidateModal fancybox.ajax txt-red"
                        data-fancybox-href="<?php echo __SITE_URL?>complains/popup_forms/add_complain/item_questions/<?php echo $question['id_q']; ?>/<?php echo $question['id_seller']; ?>"
                        data-title="Report this question">
                        <i class="ep-icon ep-icon_megaphone"></i> Report this
                    </a>
                <?php }?>
		    </div>
		<?php }?>
	</div>
</li>
