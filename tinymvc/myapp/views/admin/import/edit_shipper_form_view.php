<?php
$shipper_data = json_decode($shipper['import_data'], true);
?>
<form class="relative-b validateModal">
	<div class="wr-form-content w-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Freight Forwarder name</td>
                    <td colspan="2">
                        <input type="text" name="shipper_name" class="validate[required,maxSize[100]] w-100pr" value="<?php echo $shipper_data['shipper_name'];?>" placeholder="Freight Forwarder name"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Email</td>
                    <td colspan="2">
                        <input type="text" name="email" class="validate[required,maxSize[100],custom[noWhitespaces],custom[emailWithWhitespaces]] w-100pr" value="<?php echo $shipper_data['email'];?>" placeholder="Email"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Phone</td>
                    <td colspan="2">
                        <input type="text" name="phone" class="validate[required,minSize[12],maxSize[25]] w-100pr" value="<?php echo $shipper_data['phone'];?>" placeholder="Phone"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Fax</td>
                    <td colspan="2">
                        <input type="text" name="fax" class="validate[required,minSize[12],maxSize[25]] w-100pr" value="<?php echo $shipper_data['fax'];?>" placeholder="Fax"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Freight Forwarder logo</td>
                    <td colspan="2">
                        <input type="text" name="logo" class="w-100pr" value="<?php echo $shipper_data['logo'];?>" placeholder="Freight Forwarder logo"/>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
	    <input type="hidden" name="id_import" value="<?php echo $shipper['id'];?>">
		<a title="Cancel" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Cancel</a>
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
    function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>admin_import/ajax_operations/edit_data',
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
