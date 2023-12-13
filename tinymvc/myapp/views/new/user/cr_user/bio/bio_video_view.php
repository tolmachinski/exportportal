<form class="validengine inputs-40" data-callback="update_video">
	<div class="input-group mb-10">
		<?php $user_video = json_decode($user_aditional['user_video'], true);?>
		<input class="form-control mr-8" type="text" name="user_video" value="<?php if(!empty($user_video)){ echo $user_video['url'];};?>" placeholder="https://www.youtube.com/watch?v=wUi0jB_MslA"/>
		<span class="input-group-btn">
			<button class="btn btn-primary mnw-150 pull-right" type="submit">Save</button>
		</span>
	</div>
</form>
<script>
	var update_video = function(form){
		var $form = $(form);
		var fdata = $form.serialize();
		$.ajax({
			type: 'POST',
			url: 'cr_user/ajax_operations/update_video',
			dataType: 'JSON',
			data: fdata,
			beforeSend : function(xhr, opts){},
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);
			}
		});
	}
</script>