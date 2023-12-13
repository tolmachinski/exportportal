<?php if(empty($comments)){?>
	<?php if(!logged_in()){?>
		<li class="info-alert-b mt-10" <?php echo addQaUniqueIdentifier("page__community-detail__question-answers_comment")?>>
            <i class="ep-icon ep-icon_info-stroke"></i>
            <?php $translated_link_login = '<a class="txt-medium txt-black fancybox.ajax fancyboxValidateModal call-action"
				data-js-action="lazy-loading:login"
                data-mw="400"
                data-title="' . translate('header_navigation_link_login', null, true) . '"
                href="' . __SITE_URL . 'login' . '">' . translate('community_sign_in_button') . '</a>';
            echo translate('community_no_comments_yet_text', array('[[LINK_REPLACE]]' => $translated_link_login)); ?>
		</li>
	<?php }else{?>
		<li class="info-alert-b mt-10" <?php echo addQaUniqueIdentifier("page__community-detail__question-answers_comment")?>>
			<i class="ep-icon ep-icon_info-stroke"></i>
			<?php echo translate('community_be_the_first_text'); ?>
		</li>
	<?php }?>
<?php }else{?>
    <?php views()->display('new/questions/item_comment_view',array('comments' => $comments, 'answer' => $answer));?>
<?php }?>
