<?php views()->display('new/items_comments/comments_scripts_view'); ?>

<script type="text/javascript">
var reUpload = true;
var order = 'date_desc';

$(document).ready(function () {
	$('#order-by-comments').on('change', function () {
		order = $(this).val();
		reUpload = true;
		getComments();
	});
});

	function getComments() {
		if (!reUpload)
			return false;

		reUpload = false;
		showLoader('.comments-load');
		$.ajax({
			url: 'items_comments/ajax_comment_operation/show',
			type: 'POST',
			data: {comments: <?php echo $item['id']; ?>, order: order},
			dataType: 'json',
			success: function (resp) {
				if(resp.mess_type == "success"){
					$('.comments-load').html(resp.html);
				}else{
					$('.comments-load').html('<div class="error-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> '+resp.message+'.</div>');
				}

				hideLoader('.comments-load');
			}
		});
	}
</script>

<?php if (!empty($item['description'])) { ?>
<div class="display-n" itemprop="description">
	<?php echo strip_tags(truncWords($item['description'])); ?>
</div>
<?php } ?>

<a class="comments-f" name="comments-f"></a>

<div class="product-detail__comments-page">
	<div class="title-public pt-0">
		<h2 class="title-public__txt">Comments</h2>

		<?php if (logged_in() && have_right('write_comments_on_item')) { ?>
			<span class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier("items-comments__leave-comment__open-dropdown") ?>>
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<span class="dropdown-menu">
					<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add comment" href="items_comments/popup_forms/add_main_comment/<?php echo $item["id"]; ?>" title="Add comment" <?php echo addQaUniqueIdentifier("items-comments__leave-comment") ?>>
						<i class="ep-icon ep-icon_pencil"></i>
						Leave Comment
					</a>
				</span>
			</span>
		<?php } ?>
	</div>

<!--
	<div class="clearfix">
		<div class="pull-right" <?php if(empty($comments)){?>style="display:none;"<?php }?>>
			<div class="pull-left lh-40 pr-10">Sort by </div>
			<select class="pagination-per-page w-auto pull-right" id="order-by-comments" name="order">
				<option value="date_desc">Date descending</option>
				<option value="date_asc">Date ascending</option>
			</select>
		</div>
	</div>
-->

	<div class="comments-load">
		<?php tmvc::instance()->controller->view->display('new/items_comments/list_comments_view');?>
	</div>
</div>
