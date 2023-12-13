<form  id="add-notice-form" method="post" class="validateModal relative">
	<div class="wr-form-content w-700 mh-430">
			<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
				<tbody>
					<tr>
						<td class="w-100">Status</td>
						<td>
							<select name="status" class="validate[required] w-100pr">
								<option value="in_process" <?php echo selected('in_process', $complain['status'])?>>In process</option>
								<option value="confirmed" <?php echo selected('confirmed', $complain['status'])?>>Confirmed</option>
								<option value="declined" <?php echo selected('declined', $complain['status'])?>>Declined</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="w-100">Notice</td>
						<td><textarea name="message" class="validate[required] w-100pr h-100" placeholder="Notice"></textarea></td>
					</tr>
				<tbody>
			</table>
			<div class="user-notices w-100pr mh-250 overflow-y-a">
			<?php if(!empty($complain['notice'])){?>
			<ul>
				<?php foreach ($complain['notice'] as $notice) {?>
					<li class="pb-5 pt-5 bdb-1-gray lh-16 txt-blue"><strong><?php echo $notice['add_date'] ?></strong> - <u>by <?php echo $notice['add_by'] ?></u> : <?php echo $notice['notice'] ;?> (Status changed to <?php echo $notice['status'];?>)</li>
				<?php }?>
			</ul>
			<?php }else{ ?>
			<strong>Has not notices for this user</strong>
			<?php } ?>
		</div>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="complain" value="<?php echo $complain['id'];?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
	function modalFormCallBack(form){
		var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');
		var fdata = $form.serialize();

		$.ajax({
			type: 'POST',
			url: 'complains/ajax_complains_operations/add_notice/',
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform);
			},
			success: function(resp){
				hideFormLoader($wrform);
                systemMessages(resp.message, 'message-' + resp.mess_type);
                if (resp.mess_type == 'success') {
                    dtReports.fnDraw();
                    closeFancyBox();
                }
			}
		});
	}
</script>
