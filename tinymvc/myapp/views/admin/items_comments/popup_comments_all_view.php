<div class="wr-modal-b">
	<div class="modal-b__content w-700 mh-600">
		<h1 class="pt-10 mb-15"><?php echo $title_item; ?></h1>

		<ul class="feedbacks-main-list" id="comments-0-block">
            <?php if(!empty($comments)) { ?>
                <?php foreach($comments as $item) { ?>
                    <?php tmvc::instance()->controller->view->display('admin/items_comments/main_comment_view', array('item' => $item)); ?>
                <?php } ?>
            <?php } else {?>
                <li>
                    <div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> No comments yet.</div>
                </li>
            <?php } ?>
		</ul>
	</div>
</div>
<script type="text/javascript">
	if(typeof block !== "undefined") {
		scrollToElementModal("#" + block, ".fancybox-inner");
    }
</script>
