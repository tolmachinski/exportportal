<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
        <tbody>
			<tr>
                <td class="w-100">Refund</td>
                <td>
					<input type="text" name="money" class="validate[required,custom[positive_number]] w-100pr" value="<?php echo $external_bill['money'];?>"/>
                </td>
            </tr>
			<tr>
                <td class="w-100">Notice</td>
                <td>
                    <textarea class="w-100pr validate[required]"  name="notice" rows="5"></textarea>
                </td>
            </tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php echo $external_bill['id']; ?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">

	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>external_bills/ajax_external_bills_operation/edit_request',
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
						data_table.fnDraw();
				}else{
					hideLoader($form);
				}
			}
        });
	}


</script>
