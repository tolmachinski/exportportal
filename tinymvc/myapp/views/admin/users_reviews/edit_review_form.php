<form class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr">
		<tr>
			<td class="w-100">Title</td>
			<td>
				<input class="w-100pr" type="text" name="title" value="<?php echo $review['rev_title']?>" placeholder="Review's Title"/>
			</td>
		</tr>
		<tr>
			<td>Text</td>
			<td>
				<textarea class="validate[required] w-100pr h-100" name="text" placeholder="Review's text"><?php echo $review['rev_text']?></textarea>
			</td>
		</tr>
		<tr>
		    <td>Seller's reply</td>
		    <td>
				<textarea name="reply" class="w-100pr h-100" placeholder="Seller's reply"><?php echo $review['reply']?></textarea>
		    </td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="review" value="<?php echo $review['id_review']?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>reviews/ajax_reviews_administration_operation/edit/',
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
