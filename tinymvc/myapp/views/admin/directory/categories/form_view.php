<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<?php if(!isset($category_info)){?>
			<tr>
				<td class="w-130">Industry</td>
				<td>
					<select class="industry w-100pr validate[required]" name="industry" >
						<option value="-1">Select industry</option>
					<?php if(isset($industries) && is_array($industries) && count($industries)){
						foreach($industries as $industry){?>
							<option  value="<?php echo $industry['category_id']?>"><?php echo $industry['name']?></option>
						<?php }?>
					<?php }?>
					</select>
				</td>
			</tr>
			<?php }?>
			<tr>
				<td>Category</td>
				<td>
					<?php if(!isset($category_info)){?>
						<select class="category w-100pr validate[required]" name="category" >
							<option value="0">Select industry first</option>
						</select>
					<?php }else{?>
						<span> <?php echo $category_info['name'] ?></span>
					<?php }?>
				</td>
			</tr>
			<tr>
				<td>H1</td>
				<td><input class="w-100pr validate[required,maxSize[100]]" type="text" name="h1" value="<?php echo $category_info['h1_category']?>" /></td>
			</tr>
			<tr>
				<td>Keywords</td>
				<td>
					<textarea class="w-100pr h-100 validate[required,maxSize[255]]" name="keywords"  ><?php echo $category_info['keywords_category']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea class="w-100pr h-100 validate[required,maxSize[255]]" name="description"  ><?php echo $category_info['description_category']?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($category_info)){?>
			<input type="hidden" name="id_category" value="<?php echo $category_info['id_category']?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$('select.industry').on('change', function(){
        var industry = $(this).val();
        if(industry == -1){
			$('select.category').html('<option value"0">Select industry first</option>');
		}else{			
	        $.ajax({
	            type: 'POST',
	            url: 'directory/ajax_company_category_operation/get_categories_by_industry',
	            data: { industry : industry},
	            success: function(data){
	                $('select.category').html(data);
	            },
	            error: function(){alert('ERROR')}
	        });
        }
    });
});
	<?php if(isset($category_info)){?>
		var url = 'directory/ajax_company_category_operation/update_company_category';
	<?php }else{?>
		var url = 'directory/ajax_company_category_operation/create_company_category';
	<?php }?>

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();

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
					<?php if(isset($category_info)){?>
						callbackUpdateCategory(resp);
					<?php }else{?>
						callbackAddCategory(resp);
					<?php }?>
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
