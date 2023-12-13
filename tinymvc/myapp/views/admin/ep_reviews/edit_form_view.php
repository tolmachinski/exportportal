<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-900 mh-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
            <tr>
                <td>Message</td>
				<td>
                    <textarea class="validate[required, maxSize[250]]" name="message"><?php echo cleanOutput($review['message']);?></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
        <input type="hidden" name="id" value="<?php echo $review['id'];?>">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
	</div>
</form>

<script type="text/javascript">
    function modalFormCallBack(form, dataTable) {
		var form = $(form);

		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_reviews/ajax_operations/edit_review';?>',
			data: form.serialize(),
            beforeSend: function () {
                showLoader(form);
            },
            dataType: 'json',
			success: function(data){
                hideLoader(form);
				systemMessages( data.message, 'message-' + data.mess_type );

				if (data.mess_type == 'success') {
					closeFancyBox();
					if (dataTable != undefined) {
                        dataTable.fnDraw();
                    }
				}
			}
        });
    }
</script>
