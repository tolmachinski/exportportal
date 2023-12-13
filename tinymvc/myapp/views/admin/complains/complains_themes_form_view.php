<form method="post" class="validateModal relative">
<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
		<tbody>
			<tr>
				<td class="w-100">Type</td>
				<td>
					<select class="complain-type select-tags w-100pr" data-placeholder="Select themes" multiple name="types[]" class="validate[required] w-100pr">
						<?php foreach($types as $type){?>
							<option value="<?php echo $type['id_type'];?>" <?php echo (in_array($type['id_type'], $complain_theme['types']) ? 'selected="selected"' : '')?>><?php echo $type['type']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="w-100">Theme</td>
				<td><input type="text" name="theme" value="<?php echo ((isset($complain_theme['theme'])) ? $complain_theme['theme'] : '')?>" class="validate[required] w-100pr"/></td>
			</tr>
		<tbody>
	</table>
</div>
<div class="wr-form-btns clearfix">
	<?php if(isset($complain_theme)){?>
	<input type="hidden" name="id" value="<?php echo $complain_theme['id_theme'];?>"/>
	<?php }?>
	<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
</div>
</form>

<script type="text/javascript">
	$(document).ready(function(){
		$(".complain-type").select2({minimumResultsForSearch: -1});
	});

function modalFormCallBack(form){
	var $form = $(form);
	var fdata = $form.serialize();

	<?php if(isset($complain_theme)){?>
	var url = 'complains/ajax_complains_operations/edit_theme';
	<?php }else{?>
	var url = 'complains/ajax_complains_operations/add_theme';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				dtReportsThemes.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
