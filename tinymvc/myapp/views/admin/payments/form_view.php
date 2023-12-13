<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-900">
		<table class="data table-striped table-bordered w-100pr" >
			<tr>
				<td>Name</td>
				<td>
					<input class="w-100pr validate[required]" type="text" name="name_method" value="<?php echo $method['method'];?>">
				</td>
			</tr>
			<tr>
				<td>Instructions</td>
				<td>
					<textarea class="w-100pr h-250 validate[required]" id="text_block" name="instructions"><?php echo $method['instructions']?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($method)){?>
		<input type="hidden" name="method" value="<?php echo $method['id']?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	tinymce.init({
		selector:'#text_block',
		menubar: false,
		statusbar : false,
		plugins: [
				"autolink lists link textcolor code table"
		],
		toolbar: "code bold italic underline forecolor backcolor | numlist bullist | table",
		resize: false
	});

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();

		<?php if(isset($method)){?>
		var url = "payments/ajax_methods/edit";
		<?php }else{?>
		var url = "payments/ajax_methods/insert";
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

					<?php if(isset($method)){?>
					callbackUpdateMethod(resp);
					<?php }else{?>
					callbackCreateMethod(resp);
					<?php }?>
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
