<ul class="product-comments" id="comments-0-block">
	<?php if (!empty($comments)) { ?>
		<?php views()->display('new/items_comments/my/item_main_comment_view', ['comments' => $comments]); ?>

		<?php if (intval($count_comments) > 2 && !$page_comments_all) { ?>
            <li>
                <a class="product-comments__more btn btn-light btn-new16" href="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id'] . '/comments'; ?>"><?php echo translate('item_details_show_all_replies_btn'); ?></a>
            </li>
		<?php } ?>
	<?php } else { ?>
		<li class="mt-10" id="no-comment-item">
			<div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> No comments yet.</div>
		</li>
	<?php } ?>
</ul>
