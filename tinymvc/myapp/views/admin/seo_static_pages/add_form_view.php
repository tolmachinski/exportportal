<form class="relative-b validateModal" data-action="<?php if(isset($seo_info)){?>edit<?php }else{?>save<?php }?>">
	<div class="wr-form-content w-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
				<tr>
					<td class="w-150">Short key</td>
					<td>
						<input class="w-100pr validate[required]" type="text" name="short_key" value="<?php echo $seo_info['short_key']?>"/>
					</td>
				</tr>
            	<tr>
					<td class="w-150">Meta title</td>
					<td>
						<input class="w-100pr validate[required]" type="text" name="meta_title" value="<?php echo $seo_info['meta_title']?>"/>
					</td>
				</tr>
				<tr>
					<td class="w-150">Meta description</td>
					<td>
						<textarea class="w-100pr h-130 validate[required]" name="meta_description"><?php echo $seo_info['meta_description']?></textarea>
					</td>
				</tr>
				<tr>
					<td class="w-150">Meta keys</td>
					<td>
						<textarea class="w-100pr h-130 validate[required]" name="meta_keys"><?php echo $seo_info['meta_keys']?></textarea>
					</td>
				</tr>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(!empty($seo_info)){?>
			<input type="hidden" name="seo" value="<?php echo $seo_info['id']?>"/>
		<?php }?>
        <button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		var action = $form.data('action');
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>seo_static_pages/ajax_seo_operation/'+action+'_seo',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
				console.log(data);
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
