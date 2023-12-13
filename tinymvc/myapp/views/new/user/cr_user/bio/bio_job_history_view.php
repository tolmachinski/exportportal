<form class="validengine inputs-40" data-callback="update_skills">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_jobs_limit;?> jobs experience.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<a class="btn btn-dark mnw-150 fancybox.ajax fancyboxValidateModal" data-title="Add job experience" href="<?php echo __SITE_URL;?>cr_user/popup_forms/add_job"><i class="ep-icon ep-icon_plus-circle"></i> Add</a>
		</div>
	</div>

	<div id="user_jobs-wr" class="cr-user-history flex-w--w flex-display pt-0 row">
		<?php if(!empty($user_aditional)){?>
			<?php $user_jobs = json_decode($user_aditional['user_jobs'], true);?>
			<?php if(!empty($user_jobs)){?>
				<?php foreach($user_jobs as $user_jobs_item){
					$date_to = strtotime($user_jobs_item['date_to']);?>
					<div id="bio-jobs-<?php echo $user_jobs_item['id_job'];?>" class="cr-user-history__item col-12 col-md-6">
						<h3 class="cr-user-history__ttl">
							<?php echo $user_jobs_item['place'];?>

							<div class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu dropdown-menu-right">
									<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit job experience" href="<?php echo __SITE_URL;?>cr_user/popup_forms/edit_job/<?php echo $user_jobs_item['id_job'];?>">
										<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
									</a>
									<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this job experience?" data-callback="delete_job" data-job="<?php echo $user_jobs_item['id_job'];?>">
										<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>
									</a>
								</div>
							</div>
						</h3>
						<div class="cr-user-history__top pb-10">
							<div class="cr-user-history__name"><?php echo $user_jobs_item['position'];?></div>
							<div class="cr-user-history__date">
								<?php echo formatDate($user_jobs_item['date_from'],'M Y');?> - <?php echo ($date_to)?formatDate($user_jobs_item['date_to'],'M Y'):'Present';?>
							</div>
						</div>

						<?php if(!empty($user_jobs_item['skills'])){?>
						<ul class="cr-user-history__skills">
							<?php foreach($user_jobs_item['skills'] as $skill_item){?>
							<li class="cr-user-history__skills-item">- <?php echo $skill_item;?></li>
							<?php }?>
						</ul>
						<?php }?>
					</div>
				<?php }?>
			<?php }?>
		<?php }?>
	</div>
</form>
<script>
	function manage_jobs_callback(resp, id_jobs){
		if(id_jobs == undefined || id_jobs == null) {
			id_jobs = 0;
		}

		var $parent = $('#user_jobs-wr');
		var jobs = [];
		var template = '';

		if(Object.keys(resp.user_jobs).length > 0){
			$.each(resp.user_jobs, function(id_job, user_job){
				template = '<div id="bio-jobs-' + id_job + '" class="cr-user-history__item col-12 col-md-6">\
					<h3 class="cr-user-history__ttl">\
						' + user_job.place
						+ '<div class="dropdown">\
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">\
								<i class="ep-icon ep-icon_menu-circles"></i>\
							</a>\
							<div class="dropdown-menu dropdown-menu-right">\
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit job experience" href="cr_user/popup_forms/edit_job/'+user_job.id_job+'">\
									<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>\
								</a>\
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this job experience?" data-callback="delete_job" data-job="'+user_job.id_job+'">\
									<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>\
								</a>\
							</div>\
						</div>\
					</h3>\
					<div class="cr-user-history__top pb-10">\
						<div class="cr-user-history__name">' + user_job.position + '</div>\
						<div class="cr-user-history__date">\
							' + user_job.date_from + ' - ' + user_job.date_to
						+ '</div>\
					</div>';

				if(user_job.skills != ""){
					template += '<ul class="cr-user-history__skills">';
					$.each(user_job.skills, function(id_skill, skill_item){
						template += '<li class="cr-user-history__skills-item">- ' + skill_item + '</li>';
					});
					template += '</ul>';
				}

				template += '</div>';

				// jobs.push(template);
			});
		}

		if(id_jobs != 0){
			$('#user_jobs-wr #bio-jobs-' + id_jobs).replaceWith(template);
		}else{
			// $parent.append(jobs.join(''));
			$parent.append(template);
		}
	}

	var delete_job = function(btn){
		var $this = $(btn);
		var id_job = $this.data('job');
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/delete_job',
			dataType: 'JSON',
			data: {id_job:id_job},
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