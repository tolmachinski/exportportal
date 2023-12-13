<script>
	<?php if(have_right_or('write_reviews')){?>
		function addReviewCallback(resp){
			if(typeof dtReviews === 'undefined'){
				_notifyContentChangeCallback();
			} else{
				dtReviews.fnDraw();
			}
		}

		function editReviewCallback(resp){
			if(typeof dtReviews === 'undefined'){
				_notifyContentChangeCallback();
			} else{
				dtReviews.fnDraw();
			}
		}
	<?php }?>


	<?php if(have_right_or('reply_reviews')){?>
		function addReviewReplyCallback(resp){
			if(typeof dtReviews === 'undefined'){
				_notifyContentChangeCallback();
			} else{
				dtReviews.fnDraw();
			}
		}

		function editReviewReplyCallback(resp){
			if(typeof dtReviews === 'undefined'){
				_notifyContentChangeCallback();
			} else{
				dtReviews.fnDraw();
			}
		}
	<?php }?>

	<?php if(have_right('moderate_content')){?>
		var moderate_review = function(obj){
			var $this = $(obj);
			var review = $this.data('review');

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>reviews/ajax_reviews_administration_operation/moderate',
				data: { checked_reviews : review},
				dataType: 'json',
				success: function(data){
					systemMessages( data.message, data.mess_type );

					if(data.mess_type == 'success'){
						$this.remove();
					}
				}
			});
		}
	<?php } ?>
</script>
