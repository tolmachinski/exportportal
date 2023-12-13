<div>
	<ul class="product-comments" id="user-comments-list">
		<?php if(!empty($comments)){?>
			<?php tmvc::instance()->controller->view->display('new/items_comments/item_view', array('comments' => $comments));?>
		<?php }else{?>
			<li>
				<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> No comments yet.</div>
			</li>
		<?php }?>
	</ul>
</div><!-- list-b -->
