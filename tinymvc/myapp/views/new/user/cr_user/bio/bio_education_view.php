<form class="validengine inputs-40" data-callback="update_skills">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_education_limit;?> education.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<a class="btn btn-dark mnw-150 fancybox.ajax fancyboxValidateModal" data-title="Add education" href="<?php echo __SITE_URL;?>cr_user/popup_forms/add_education"><i class="ep-icon ep-icon_plus-circle"></i> Add</a>
		</div>
	</div>

	<div id="user_educations-wr" class="cr-user-history flex-w--w flex-display pt-0 row">
		<?php if(!empty($user_aditional)){?>
			<?php $user_educations = json_decode($user_aditional['user_educations'], true);?>
			<?php if(!empty($user_educations)){?>
				<?php foreach($user_educations as $user_educations_item){?>
				<div id="bio-educations-<?php echo $user_educations_item['id_education'];?>" class="cr-user-history__item col-12 col-md-6">
					<h3 class="cr-user-history__ttl">
						<?php echo $user_educations_item['school'];?>

						<div class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit education" href="<?php echo __SITE_URL;?>cr_user/popup_forms/edit_education/<?php echo $user_educations_item['id_education'];?>">
									<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
								</a>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this education?" data-callback="delete_education" data-education="<?php echo $user_educations_item['id_education'];?>">
									<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>
								</a>
							</div>
						</div>
					</h3>
					<div class="cr-user-history__top pb-10">
						<div class="cr-user-history__name">
							<span class="txt-gray">Degree:</span>
							<?php echo $user_educations_item['degree'];?>
						</div>
						<div class="cr-user-history__date">
							<?php echo $user_educations_item['year_from'];?>
							<span>-</span>
							<?php echo $user_educations_item['year_to'];?>
						</div>
					</div>

					<div class="pl-15 pr-15">
						<div class="pb-10">
							<span class="txt-gray">Study:</span>
							<?php echo $user_educations_item['field_of_study'];?>
						</div>

						<?php if(!empty($user_educations_item['grade'])){?>
						<div class="pb-10">
							<span class="txt-gray">Grade:</span>
							<?php echo $user_educations_item['grade'];?>
						</div>
						<?php }?>

						<?php if(!empty($user_educations_item['description'])){?>
						<div class="lh-20">
							<?php echo $user_educations_item['description'];?>
						</div>
						<?php }?>
					</div>
				</div>

				<?php }?>
			<?php }?>
		<?php }?>
	</div>
</form>
<script>
	function manage_educations_callback(resp, id_educations){
		if(id_educations == undefined || id_educations == null) {
			id_educations = 0;
		}

		var $parent = $('#user_educations-wr');
		var educations = [];
		var template = '';

		if(Object.keys(resp.user_educations).length > 0){
			$.each(resp.user_educations, function(id_education, user_education){
				template = '<div id="bio-educations-' + id_education + '" class="cr-user-history__item col-12 col-md-6">\
						<h3 class="cr-user-history__ttl">\
							' + user_education.school
							+ '<div class="dropdown">\
								<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">\
									<i class="ep-icon ep-icon_menu-circles"></i>\
								</a>\
								<div class="dropdown-menu dropdown-menu-right">\
									<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit education" href="cr_user/popup_forms/edit_education/' + user_education.id_education + '">\
										<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>\
									</a>\
									<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this education?" data-callback="delete_education" data-education="' + user_education.id_education + '">\
										<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>\
									</a>\
								</div>\
							</div>\
						</h3>\
						<div class="cr-user-history__top pb-10">\
							<div class="cr-user-history__name">\
								<span class="txt-gray">Degree:</span>\
								' + user_education.degree
							+ '</div>\
							<div class="cr-user-history__date">\
								' + user_education.year_from
								+ '<span>-</span>\
								' + user_education.year_to
							+ '</div>\
						</div>\
						<div class="pl-15 pr-15">\
							<div class="pb-10">\
								<span class="txt-gray">Study:</span>\
								' + user_education.field_of_study
							+ '</div>';


							if(user_education.grade != ""){
								template += '<div class="pb-10">\
										<span class="txt-gray">Grade:</span>\
										' + user_education.grade
									+ '</div>';
							}

							if(user_education.description != ""){
								template += '<div class="lh-20">\
									' + user_education.description
								+ '</div>';
							}
						template += '</div>\
					</div>';

				// educations.push(template);
			});
		}

		if(id_educations != 0){
			$('#user_educations-wr #bio-educations-' + id_educations).replaceWith(template);
		}else{
			// $parent.append(educations.join(''));
			$parent.append(template);
		}
	}

	var delete_education = function(btn){
		var $this = $(btn);
		var id_education = $this.data('education');
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/delete_education',
			dataType: 'JSON',
			data: {id_education:id_education},
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