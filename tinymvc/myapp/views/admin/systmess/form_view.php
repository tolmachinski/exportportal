
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js?<?php echo time();?>"></script>
<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td>Message's type</td>
					<td>
						<select name="mess_type" class="validate[required] w-98pr">
							<option value="">Select messages type</option>
							<option value="notice" <?php echo selected('notice',$message['mess_type']); ?>>Notice</option>
							<option value="warning" <?php echo selected('warning',$message['mess_type']); ?>>Warning</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Message's module</td>
					<td>
						<select name="module" class="validate[required] w-98pr">
							<option value="">Select messages module</option>
							<?php foreach($modules as $module){?>
							<option value="<?php echo $module['id_module']?>" <?php echo selected($module['id_module'], $message['module']); ?>><?php echo $module['name_module']?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Message's code</td>
					<td><input type="text" name="mess_code" class="validate[required] w-98pr" value="<?php if(isset($message)) echo $message['mess_code']?>" /><br />*example: message_code(without space - will be used in code)</td>
				</tr>
				<tr>
					<td>Title</td>
					<td><input type="text" name="title" class="validate[required] w-98pr" value="<?php if(isset($message)) echo $message['title']?>" /></td>
				</tr>
				<tr>
					<td>Message</td>
					<td>
						<textarea name="message"  class="validate[required] w-98pr h-100"><?php if(isset($message)) echo $message['message']?></textarea>
					</td>
                </tr>
				<tr>
					<td>Triggered Actions</td>
					<td>
						<textarea name="triggered_actions" class="triggered w-98pr h-100"><?php if(isset($message)) echo $message['triggered_actions']?></textarea>
					</td>
                </tr>
                <?php if(have_right('manage_proofread')){?>
                <tr>
                    <td>Proofread</td>
					<td><input type="checkbox" name="proofreaded" class="w-98pr" <?php echo checked(1, $message['is_proofread'])?> value="1" /></td>
                </tr>
                <?php }?>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($message)){?><input type="hidden" name="idmess" value="<?php echo $message['idmess']?>" /><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
$(function(){
    tinymce.init({
        selector:'.triggered',
        menubar: false,
        statusbar : false,
        height : 250,
        force_p_newlines : true,
        plugins: ["lists link textcolor"],
        dialog_type : "modal",
        toolbar: " bold italic underline link media | numlist bullist"
    });
});
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "<?php echo __SITE_URL?>systmess/ajax_systmessages_operation/<?php echo (isset($message) ? 'edit' : 'add')?>",
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
