<form class="validengine inputs-40" data-callback="update_speak_languages">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_speak_langs_limit;?> languages.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<button class="btn btn-dark mnw-150 call-function" data-callback="add_speak_language"><i class="ep-icon ep-icon_plus-circle mr-5"></i> Add</button>
			<button class="btn btn-primary mnw-150" type="submit">Save</button>
		</div>
	</div>
	
	<div id="speak_languages-wr" class="row">
	<?php if(!empty($user_aditional)){?>
		<?php $speak_langs = json_decode($user_aditional['user_speak_langs'], true);?>
		<?php if(!empty($speak_langs)){?>
			<?php foreach($speak_langs as $speak_lang_key => $speak_lang){?>
				<div class="col-12 col-md-6 speak_language-item">
					<div class="input-group mb-8">
						<input class="form-control mr-8 validate[required]" type="text" name="speak_lang[<?php echo $speak_lang_key;?>][name]" value="<?php echo $speak_lang['name'];?>" placeholder="Language"/>
						<select class="form-control mr-8 validate[required]" name="speak_lang[<?php echo $speak_lang_key;?>][proficiency]">
							<option value="">Select proficiency</option>
							<?php foreach($langs_proficiencies as $lang_proficiency_key => $lang_proficiency){?>
								<option value="<?php echo $lang_proficiency_key;?>" <?php echo selected($lang_proficiency_key, $speak_lang['proficiency']);?>><?php echo $lang_proficiency;?></option>
							<?php }?>
						</select>
						<div class="input-group-append">
							<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this language?" data-callback="delete_speak_language"><i class="ep-icon ep-icon_trash-stroke"></i></a>
						</div>
					</div>
				</div>
			<?php }?>
		<?php }?>
	<?php }?>
	</div>
</form>
<script>
	var speak_langs_limit = intval(<?php echo $cr_speak_langs_limit;?>);
	var add_speak_language = function(btn){
		var $this = $(btn);
		
		if($('#speak_languages-wr .speak_language-item').length < speak_langs_limit){
			var index = uniqid();
			var template = '<div class="col-12 col-md-6 speak_language-item">\
								<div class="input-group mb-8">\
									<input class="form-control mr-8 validate[required]" type="text" name="speak_lang['+index+'][name]" value="" placeholder="Language">\
									<select class="form-control mr-8 validate[required]" name="speak_lang['+index+'][proficiency]">\
										<option value="">Select proficiency</option>\
										<?php foreach($langs_proficiencies as $lang_proficiency_key => $lang_proficiency){?>
											<option value="<?php echo $lang_proficiency_key;?>"><?php echo $lang_proficiency;?></option>\
										<?php }?>
									</select>\
									<div class="input-group-append">\
										<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this language?" data-callback="delete_speak_language"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
									</div>\
								</div>\
							</div>';
			$('#speak_languages-wr').append(template);
			validateReInit();
		}
	}
	var delete_speak_language = function(btn){
		var $this = $(btn);
		$this.closest('.speak_language-item').remove();
	}

	var update_speak_languages = function(form){
		var $form = $(form);
		var fdata = $form.serialize();
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/update_languages',
			dataType: 'JSON',
			data: fdata,
			beforeSend : function(xhr, opts){},
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);
			}
		});
	}
</script>