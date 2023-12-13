<form class="validengine inputs-40" data-callback="update_skills">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_awards_limit;?> awards.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<a class="btn btn-dark mnw-150 fancybox.ajax fancyboxValidateModal" data-title="Add Awards/Acknowledgements" href="<?php echo __SITE_URL;?>cr_user/popup_forms/add_adward"><i class="ep-icon ep-icon_plus-circle mr-5"></i> Add</a>
		</div>
	</div>

	<div id="user_awards-wr" class="cr-user-history row">
		<?php if(!empty($user_aditional)){?>
			<?php $user_awards = json_decode($user_aditional['user_awards'], true);?>
			<?php if(!empty($user_awards)){?>
				<?php foreach($user_awards as $user_award){?>
				<div id="bio-awards-<?php echo $user_award['id_award'];?>" class="cr-user-history__item col-12 col-md-6">
					<h3 class="cr-user-history__ttl">
						<?php echo $user_award['title'];?>

						<div class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit Awards/Acknowledgements" href="<?php echo __SITE_URL;?>cr_user/popup_forms/edit_adward/<?php echo $user_award['id_award'];?>">
									<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
								</a>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this Awards/Acknowledgements?" data-callback="delete_award" data-award="<?php echo $user_award['id_award'];?>">
									<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>
								</a>
							</div>
						</div>
					</h3>

					<div class="cr-user-history__top">
						<?php if(!empty($user_award['issuer'])){?>
						<div class="cr-user-history__name">
							<span class="txt-gray">Issuer:</span>
							<?php echo $user_award['issuer'];?>
						</div>
						<?php }?>
						<div class="cr-user-history__date">
							<?php echo formatDate($user_award['award_date'],'M Y');?>
						</div>
					</div>

					<?php if(!empty($user_award['description'])){?>
					<div class="cr-user-history__short">
						<?php echo $user_award['description'];?>
					</div>
					<?php }?>
				</div>
				<?php }?>
			<?php }?>
		<?php }?>
	</div>
</form>
<script>
	function manage_awards_callback(resp, id_awards){
		if(id_awards == undefined || id_awards == null) {
			id_awards = 0;
		}

		var $parent = $('#user_awards-wr');
		//var awards = [];
		var template = '';

		if(Object.keys(resp.user_awards).length > 0){
			$.each(resp.user_awards, function(id_award, user_award){
				template = '<div id="bio-awards-' + id_award + '" class="cr-user-history__item col-12 col-md-6">\
					<h3 class="cr-user-history__ttl">\
						' + user_award.title
						+ '<div class="dropdown">\
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">\
								<i class="ep-icon ep-icon_menu-circles"></i>\
							</a>\
							<div class="dropdown-menu dropdown-menu-right">\
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit Awards/Acknowledgements" href="cr_user/popup_forms/edit_adward/' + user_award.id_award + '">\
									<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>\
								</a>\
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this Awards/Acknowledgements?" data-callback="delete_award" data-award="' + user_award.id_award + '">\
									<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>\
								</a>\
							</div>\
						</div>\
					</h3>\
					<div class="cr-user-history__top">';

				if(user_award.issuer != ""){
					template += '<div class="cr-user-history__name">\
							<span class="txt-gray">Issuer:</span>\
							' + user_award.issuer
						+ '</div>';
				}

				template += '<div class="cr-user-history__date">\
							' + user_award.award_date
						+ '</div>\
					</div>';

				if(user_award.description != ""){
					template += '<div class="cr-user-history__short">\
						' + user_award.description
					+ '</div>';
				}

				template += '</div>';

				//awards.push(template);
			});
		}

		if(id_awards != 0){
			$('#user_awards-wr #bio-awards-'+id_awards).replaceWith(template);
		}else{
			// $parent.append(awards.join(''));
			$parent.append(template);
		}
	}

	var delete_award = function(btn){
		var $this = $(btn);
		var id_award = $this.data('award');
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/delete_award',
			dataType: 'JSON',
			data: {id_award:id_award},
			beforeSend : function(xhr, opts){},
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);

				if(resp.mess_type == 'success'){
					$this.closest('.cr-user-history__item').remove();
				}
			}
		});
	}
</script>