<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped temp w-100pr" >
			<tr>
				<td class="w-120">Video title</td>
				<td><input type="text" name="title_video" class="w-100pr validate[required,maxSize[255]]" value="<?php echo $video_info['title_video']?>" /></td>
			</tr>
			<tr>
				<td>Short name</td>
				<td>
					<input type="text" name="short_name" class="w-100pr validate[required,maxSize[30]]" value="<?php echo $video_info['short_name']?>" />
					<div>sample: short_name</div>
				</td>
			</tr>
			<tr>
				<td>Description video</td>
				<td>
					<textarea name="description_video" class="w-100pr h-100"><?php echo $video_info['description_video']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Link video</td>
				<td><input type="text" name="link_video" class="validate[required, custom[url]] w-100pr" value="<?php echo get_video_link($video_info['link_video'], $video_info['src_video'])?>" /></td>
			</tr>
			<tr>
				<td>Source video</td>
				<td>
					<select class="w-100pr validate[required]" name="src_video">
						<option name="src_video" value="YouTube" <?php echo selected($video_info['src_video'], 'youtube')?>>YouTube</option>
						<option name="src_video" value="Vimeo" <?php echo selected($video_info['src_video'], 'vimeo')?>>Vimeo</option>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($video_info)){?>
			<input type="hidden" name="id_video" value="<?php echo $video_info['id_video']?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	<?php if(isset($video_info)){?>
		var url = 'video/ajax_video_operation/update_video';
	<?php }else{?>
		var url = 'video/ajax_video_operation/create_video';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Sending video...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				<?php if(isset($video_info)){?>
					callbackUpdateVideo(resp);
				<?php }else{?>
					callbackCreateVideo(resp);
				<?php }?>

			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
