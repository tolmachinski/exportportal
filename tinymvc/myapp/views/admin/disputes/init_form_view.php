<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
        <tbody>
            <tr>
                <td class="w-100">Notice</td>
                <td>
					<textarea class="w-100pr h-100 validate[required,maxSize[500]] textcounter-dispute_notice" data-max="500" name="message"></textarea>
                </td>
            </tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="dispute" value="<?php echo $id_dispute; ?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	$(function(){
		$('.textcounter-dispute_notice').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>dispute/ajax_operation/init',
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
