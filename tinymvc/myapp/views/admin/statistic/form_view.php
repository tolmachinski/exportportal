<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-600">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tbody>
				<tr>
					<td>Title</td>
					<td>
						<input type="text" name="comment" class="validate[required] w-98pr" value="<?php if(isset($parameter)) echo $parameter['com'] ?>"><br>* Will be used as comment in the DB
					</td>
				</tr>
				<?php if(!isset($parameter)){ ?>
				<tr>
					<td>Short name</td>
					<td>
						<input type="text" name="short_name" class="validate[required] w-98pr" value=""><br>*example: review_count(without space). Will be used in code
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($parameter)){?><input type="hidden" name="short_name" value="<?php echo $parameter['col'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>user_statistic/ajax_columns_operation/<?php echo (isset($parameter) ? 'edit' : 'add')?>',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(typeof data_table !== 'undefined')
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
