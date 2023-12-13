<form class="validengine inputs-40" data-callback="update_skills">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_skills_limit;?> skills.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<button class="btn btn-dark mnw-150 call-function" data-callback="add_skills"><i class="ep-icon ep-icon_plus-circle mr-5"></i> Add</button>
			<button class="btn btn-primary mnw-150" type="submit">Save</button>
		</div>
	</div>
	
	<div id="user_skills-wr" class="row">
		<?php if(!empty($user_aditional)){?>
			<?php $user_skills = json_decode($user_aditional['user_skills'], true);?>
			<?php if(!empty($user_skills)){?>
				<?php foreach($user_skills as $user_skill){?>
				<div class="col-12 col-md-6 user_skills-item">
					<div class="input-group mb-8">
						<input class="form-control mr-8 validate[required]" type="text" name="user_skills[]" value="<?php echo $user_skill;?>" placeholder="Skill name"/>
						<span class="input-group-btn">
							<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="delete_skill"><i class="ep-icon ep-icon_trash-stroke"></i></a>
						</span>
					</div>
				</div>
				<?php }?>
			<?php }?>
		<?php }?>
	</div>
</form>
<script>
	var skills_limit = intval(<?php echo $cr_skills_limit;?>);
	var add_skills = function(btn){
		var $this = $(btn);

		if($('#user_skills-wr .user_skills-item').length < skills_limit){
			var index = uniqid();
			var template = '<div class="col-12 col-md-6 user_skills-item">\
								<div class="input-group mb-8">\
									<input class="form-control mr-8 validate[required] mb-0" type="text" name="user_skills[]" value="" placeholder="Skill name"/>\
									<span class="input-group-btn">\
										<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="delete_skill"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
									</span>\
								</div>\
							</div>';
			$('#user_skills-wr').append(template);
			validateReInit();
		}
	}
	var delete_skill = function(btn){
		var $this = $(btn);
		$this.closest('.user_skills-item').remove();
	}

	var update_skills = function(form){
		var $form = $(form);
		var fdata = $form.serialize();
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/update_skills',
			dataType: 'JSON',
			data: fdata,
			beforeSend : function(xhr, opts){},
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);
			}
		});
	}
</script>