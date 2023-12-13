<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content <?php if(!empty($user_feedbacks)){?>pb-0<?php }?> mh-500">
			<ul class="product-comments">
				<?php tmvc::instance()->controller->view->display('new/users_feedbacks/item_view');?>
			</ul>
		</div>
		<?php if(empty($user_feedbacks)){?>
			<div class="modal-flex__btns">
				<div class="modal-flex__btns-right">
					<a class="btn btn-primary fancybox.ajax fancyboxValidateModal" data-dashboard-class="inputs-40" data-title="Add feedback" href="<?php echo __SITE_URL;?>feedbacks/popup_forms/add_feedback/order/<?php echo $id_order;?>" title="Add feedback">
						Leave Feedback
					</a>
				</div>
			</div>
	   	<?php }?>
   </div>
</div>
<script>
    $(document).ready(function(){
		$('.rating-bootstrap').rating();

		$('.rating-bootstrap').each(function () {
			var $this = $(this);
			ratingBootstrap($this);
		});
    });
</script>
