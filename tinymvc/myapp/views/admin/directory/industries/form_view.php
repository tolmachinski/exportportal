<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<tr>
				<td>Industry</td>
				<td>
					<?php if(!isset($industry_info)){?>
						<select class="w-100pr validate[required]" name="industry" >
						<?php if(isset($categories) && is_array($categories) && count($categories) > 0){
							foreach($categories as $category){?>
								<?php if(!in_array($category['category_id'],$categories_seo_id)){?>
									<option  value="<?php echo $category['category_id']?>"><?php echo $category['name']?></option>
								<?php }?>
							<?php }?>
						<?php }?>
						</select>
					<?php }else{?>
						<span> <?php echo $industry_info['name'] ?></span>
					<?php }?>
				</td>
			</tr>
			<tr>
				<td>H1</td>
				<td><input class="w-100pr validate[required,maxSize[100]]" type="text" name="h1" value="<?php echo $industry_info['h1_category']?>" /></td>
			</tr>
			<tr>
				<td>Keywords</td>
				<td>
					<textarea class="w-100pr h-100 validate[required,maxSize[255]]" name="keywords"  ><?php echo $industry_info['keywords_category']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea class="w-100pr h-100 validate[required,maxSize[255]]" name="description" ><?php echo $industry_info['description_category']?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($industry_info)){?>
			<input type="hidden" name="id_category" value="<?php echo $industry_info['id_category']?>">
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
function modalFormCallBack(form){
	var $form = $(form);
	var fdata = $form.serialize();

	<?php if(isset($industry_info)){?>
		var url = 'directory/ajax_company_industry_operation/update_company_industry';
	<?php }else{?>
		var url = 'directory/ajax_company_industry_operation/create_company_industry';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				<?php if(isset($industry_info)){?>
					callbackUpdateIndustry(resp);
				<?php }else{?>
					callbackAddIndustry(resp);
				<?php }?>
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
