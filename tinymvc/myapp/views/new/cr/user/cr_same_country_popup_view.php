<?php
	$showList = count($domain_users);
?>
<div class="wr-modal-flex">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<div class="container-fluid-modal wr-ambassador-block relative-b">
				<ul class="ambassador-blocks">
					<?php tmvc::instance()->controller->view->display('new/cr/user/cr_same_country_popup_item_view'); ?>
				</ul>
			</div>
		</div>
		<?php if ($showList < $domain_users_count) { ?>
			<div class="modal-flex__btns">
				<div class="modal-flex__btns-right">
					<a class="btn btn-light call-function" data-callback="more_cr_users" data-user="<?php echo $current_user;?>">view more</a>
				</div>
			</div>
		<?php } ?>
	</form>
</div>
<script>
    var more_cr_users = function(btn){
		var user = $(btn).data('user');
		var start = $('.modal-flex__form .ambassador-block').length;

		$.ajax({
			type: 'POST',
			async: false,
			url: "<?php echo __SITE_URL;?>cr_users/ajax_operations/same_country",
			data: {user : user, start : start},
			dataType: 'JSON',
			beforeSend: function(){
				showLoader('.modal-flex__form .wr-ambassador-block');
			},
			success: function(resp){
				$('.modal-flex__form .ambassador-blocks').append(resp.html);
				hideLoader('.modal-flex__form .wr-ambassador-block');
				if($('.modal-flex__form .ambassador-block').length == resp.count){
                    $(btn).closest('.modal-flex__btns').remove();
                }
			},

		});

    }
</script>