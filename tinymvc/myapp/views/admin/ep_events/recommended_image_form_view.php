<form method="post" class="validateModal relative-b" id="recommendedImageForm">
	<div class="wr-form-content w-900 mh-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
            <tr>
                <td>Recommended image</td>
                <td>
                    <?php views()->display('new/user/photo_cropper2_view'); ?>
                </td>
            </tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
        <input type="hidden" name="id_event" value="<?php echo $event['id'];?>">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
	</div>
</form>

<script type="text/javascript">
    function modalFormCallBack(form, data_table){
		var form = $(form);

		$.ajax({
            type: 'POST',
            url: '<?php echo $submitFormUrl;?>',
			data: form.serialize(),
            beforeSend: function () {
                showLoader(form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw(false);
				}else{
					hideLoader(form);
				}
			}
        });
    }
</script>
