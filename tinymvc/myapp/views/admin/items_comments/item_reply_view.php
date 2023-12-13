<?php foreach($comments as $item){ ?>
<li class="feedbacks-main-list__res-item <?php if($item['new_comment']){ echo 'display-n'; }?>" id="li-comment-<?php echo $item['id_comm']?>">
	<div class="pl-40 clearfix">
		<div class="feedbacks-main-list__res-img pull-left">
			<img class="image" src="<?php echo $makeUserPhoto((int) $item['idu'], $item['user_photo']); ?>" alt="<?php echo $item['username'];?>" />
		</div>

		<div class="feedbacks-main-list__res-detail-item">
			<a class="feedbacks-main-list__res-name" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($item['username']).'-'.$item['id_user'];?>" <?php if (isset($title_item)){ ?>target="_blank" <?php }?>><?php echo $item['username'];?></a>
			<div class="feedbacks-main-list__res-date"><?php echo formatDate($item['comment_date']);?></div>
		</div>
	</div>

	<div class="feedbacks-main-list__res-text">
		<?php echo $item['comment'];?>
	</div>

	<div class="clearfix">
		<div class="ep-actions pull-right">
            <?php if(logged_in()) { ?>
                <?php $is_my = is_my($item['id_user']); ?>
                <?php $is_not_moderated = $item['status'] != 'moderated'; ?>
                <?php $can_moderate = have_right('moderate_content'); ?>
                <?php $can_change = $is_not_moderated && empty($item['replies']); ?>
                <?php $can_edit = $can_change && $is_my; ?>
                <?php $can_delete = ($can_change && $is_my) || $can_moderate; ?>
                <?php $can_report = !$is_my; ?>
                <?php $can_reply = have_right('write_comments') && !$can_moderate && $item['level'] < 9; ?>

                <?php if($can_edit) { ?>
                    <a class="ep-actions__link fancybox.ajax fancyboxValidateModal"
                        data-fancybox-href="<?php echo __SITE_URL; ?>items_comments/popup_forms/edit_reply/<?php echo $item['id_comm']?>"
                        data-title="Edit comment"
                        title="Edit comment">
                        <i class="ep-icon ep-icon_pencil"></i> Edit
                    </a>
                <?php } ?>

                <?php if($can_moderate) { ?>
                    <a class="ep-actions__link txt-green confirm-dialog"
                        data-message="Are you sure want moderate this comment?"
                        data-callback="moderate_comment"
                        data-comment="<?php echo $item['id_comm']?>"
                        title="Moderate comment">
                        <i class="ep-icon ep-icon_sheild-ok"></i> Moderate
                    </a>
                <?php } ?>

                <?php if($can_reply) { ?>
                    <a class="ep-actions__link fancybox.ajax fancyboxValidateModal"
                        data-fancybox-href="<?php echo __SITE_URL; ?>items_comments/popup_forms/add_reply/<?php echo $item['id_comm'];?>"
                        data-title="Add reply"
                        title="Add reply">
                        <i class="ep-icon ep-icon_reply"></i> Reply
                    </a>
                <?php } ?>

                <?php if($can_report) { ?>
                    <a class="ep-actions__link fancybox.ajax fancyboxValidateModal txt-red"
                        data-fancybox-href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/item_comment/<?php echo $item['id_item']?>/<?php echo $item['id_user'];?>"
                        data-title="Report this comment"
                        title="Report this comment">
                        <i class="ep-icon ep-icon_megaphone"></i> Report this
                    </a>
                <?php } ?>

                <?php if($can_delete) { ?>
                    <a class="ep-actions__link txt-red confirm-dialog"
                        data-message="Are you sure want delete this comment?"
                        data-callback="delete_comment"
                        data-comment="<?php echo $item['id_comm']?>"
                        data-item="<?php echo $item['id_item']?>"
                        title="Delete comment">
                        <i class="ep-icon ep-icon_remove"></i> Delete
                    </a>
                <?php } ?>

            <?php } else { ?>
                <a rel="nofollow"
                    class="dropdown-item call-systmess"
                    data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                    data-type="error"
                    title="Add reply">
                    <i class="ep-icon ep-icon_reply-left-empty"></i> Reply
                </a>
            <?php } ?>
		</div>
	</div>
	<ul class="feedbacks-main-list__res" id="comments-<?php echo $item['id_comm']?>-block">
		<?php if(!empty($item['replies']) && $item['level'] < 9){?>
            <?php views('admin/items_comments/item_reply_view', ['comments' => $item['replies'], 'is_modal' => $is_modal]);?>
		<?php }?>
	</ul>
</li>
<?php }?>
