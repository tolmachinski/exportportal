<form class="validateModal relative-b" id="add-notice">
	<div class="wr-form-content w-700">
        <div class="mh-400 overflow-y-a pt-15 pb-15 user-notices">
            <?php if(!empty($notices)){?>
                <ul class="list-group mb-0">
                    <?php foreach($notices as $notice){?>
                        <?php if(!empty($notice)){?>
                            <li class="list-group-item">
                                <strong><?php echo $notice['add_date'] ?></strong> - <u>by <?php echo $notice['add_by'] ?></u> : <?php echo $notice['notice'] ?>
                            </li>
                        <?php }?>
                    <?php }?>
                </ul>
            <?php }else{ ?>
            <div class="info-alert-b">
                <i class="ep-icon ep-icon_info"></i>
                <strong>This user does not have any notices.</strong>
            </div>
            <?php } ?>
        </div>
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">
                        Calling status
					</td>
					<td>
						<select class="form-control w-100pr" name="calling_status">
                            <option value="">Select status</option>
                            <?php foreach($calling_statuses as $calling_status){?>
                                <option value="<?php echo $calling_status['id_status'];?>" <?php echo selected($user['calling_status'], $calling_status['id_status']);?>><?php echo $calling_status['status_title'];?></option>
                            <?php }?>
                        </select>
					</td>
				</tr>
				<tr>
					<td class="w-100">
                        Notice
					</td>
					<td>
						<textarea name="notice" class="w-100pr h-100" placeholder="Notice"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id_user" value="<?php echo $id_user?>" />
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Add notice</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
		$.ajax( {
			"dataType": "JSON",
			"type": "POST",
			"url": "<?php echo __SITE_URL ?>users/ajax_operations/add_calling_notices",
			"data": $form.serialize(),
			"beforeSend": function(){
				showLoader(form);
			},
			"success": function (json) {
				if(json.mess_type != 'error'){
					if($('.user-notices ul').length == 0){
						$('.user-notices').html('<ul></ul>');
					}
					$('.user-notices ul').prepend(json.content);
					$form[0].reset();
					if(data_table != undefined)
                    $(data_table).DataTable().draw(false);
				}
				systemMessages(json.message, 'message-' + json.mess_type);
				hideLoader(form);
			},

		});
}
</script>
