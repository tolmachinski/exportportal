<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700 mh-450">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table mt-15">
			<tbody>
				<tr >
					<td class="w-100">Golden 12:</td>
					<td id="<?php if(isset($category)) echo 'js-golden-12-update'; else echo 'js-golden-12';?>">
                        <input type="hidden" id="golden_control" value="<?php if(isset($categoryGolden)) echo $categoryGolden['id_group'];?>"/>
						<?php if(!empty($categoryGroups)){
							foreach ($categoryGroups as $categoryGroupsItem){
                                if ($categoryGroupsItem['id_group'] == $categoryGolden['id_group']) {
                                    $outGolden[] = $categoryGroupsItem['title'];
                                }
							}
							echo implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $outGolden);?>
							<span class="ep-icon ep-icon_pencil txt-blue js-edit-golden-12 pull-right"></span>
							<span class="ep-icon ep-icon_remove txt-blue js-cancel-cat-select pull-right" style="display: none;"></span>
						<?php }?>
                        <select id="js-select-golden-category" class="w-100pr validate[required] <?php if(isset($category)) echo 'js-golden-update'; else echo 'js-golden-add';?> pull-left" <?php if(!empty($category['breadcrumbs'])){?>style="display: none;"<?php }?> name="golden_12_parent">
                            <?php if(isset($categoryGolden)){ ?>
							<option value="<?php echo $categoryGolden['id_group']; ?>">Current Golden 12 parent</option>
							<?php }else{ ?>
                            <option value="0">No golden 12 parent</option>
                            <?php } ?>
							<?php if(count($categoryGroups)){
                                foreach($categoryGroups as $categoryGroupsItem) { ?>
                                <option value="<?php echo $categoryGroupsItem['id_group']?>">
                                    <?php echo $categoryGroupsItem['title']; ?>
                                </option>
								<?php }?>
							<?php }?>
                        </select>
					</td>
				</tr>
				<tr >
					<td class="w-100">Parent:</td>
					<td id="<?php if(isset($category)) echo 'categories_update'; else echo 'categories';?>">
						<input type="hidden" id="cat_control" value="<?php if(isset($category)) echo $category['parent'];?>"/>
						<?php if(!empty($category['breadcrumbs'])){
							foreach ($category['breadcrumbs'] as $bread){
								foreach ($bread as $cat_id => $cat_title){
									$out[] = $cat_title;
								}
							}
							echo implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);?>
							<span class="ep-icon ep-icon_pencil txt-blue edit-categories pull-right"></span>
							<span class="ep-icon ep-icon_remove txt-blue cancel-cat-select pull-right" style="display: none;"></span>
						<?php }?>
						<select <?php if(!empty($category['breadcrumbs'])){?>style="display: none;"<?php }?> name="parent" class="js-select-category-parent w-100pr validate[required] <?php if(isset($category)) echo 'categ_update'; else echo 'categ_add';?> pull-left" level="1">
                            <?php if(isset($category)){ ?>
							<option value="<?php echo $category['parent']?>">Current parent</option>
							<?php }else{ ?>
							<option value="0">No parent</option>
							<?php }
							if(count($categories)){
								foreach($categories as $cat){?>
								<option  value="<?php echo $cat['category_id']?>"><?php echo $cat['name']?></option>
								<?php }?>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Title:</td>
					<td><input type="text" name="name" value="<?php if(isset($category)) echo $category['name']?>" class="w-100pr validate[required,maxSize[255]]" /></td>
	   			 </tr>
				<tr>
					<td>Is restricted (18+)</td>
					<td>
                        <select name="is_restricted" class="w-100pr validate[required] <?php if(isset($category)) echo 'categ_update'; else echo 'categ_add';?> pull-left" level="1">
                            <option value="">Is restricted</option>
							<option value="0" <?php echo isset($category) && !$category['is_restricted'] ? 'selected' : '';?>>False</option>
							<option value="1" <?php echo isset($category) && $category['is_restricted'] ? 'selected' : '';?>>True</option>
						</select>
                    </td>
	   			 </tr>
				<tr>
					<td>HTS:</td>
					<td>
						<div class="form-group mb-0">
							<div class="input-group">
								<div class="input-group-addon"><span class="ep-icon ep-icon_info m-0 txt-blue" title="Harmonized Tariff Schedule"></span></div>
								<input type="text" name="hs_tariff_number" class="form-control w-100pr validate[maxSize[50]]" value="<?php if(isset($category)) echo $category['hs_tariff_number']?>"/>
								<div class="input-group-addon"><a href="https://hts.usitc.gov" target="_blank" class="ep-icon ep-icon_link m-0" title="https://hts.usitc.gov"></a></div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>URL:</td>
					<td>
						<div class="form-group mb-0">
							<div class="input-group">
								<div class="input-group-addon"><?php echo __SITE_URL?></div>
								<input type="text" name="link"  value="<?php if(isset($category)) echo $category['link'] ?>" class="form-control validate[maxSize[255]]" />

								<span class="input-group-btn">
									<button type="button" class="btn btn-default h-30 call-function" data-callback="preview_category_link"><i class="ep-icon ep-icon_visible m-0"></i></button>
								</span>

							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>Product/Motor:</td>
					<td>
						<label><input class="type validate[required]" type="radio" name="p_or_m" value="1" <?php if(isset($category)){ echo checked($category['p_or_m'], 1); } else {?>checked="checked"<?php } ?>/>Products</label>
						<label><input class="type validate[required]" type="radio" name="p_or_m" value="2" <?php if(isset($category)) echo checked($category['p_or_m'], 2); ?>/>Motors</label>
					</td>
				</tr>
				<tr>
					<td>Type:</td>
					<td>
						<select name="cat_type" class="w-100pr validate[required]">
							<option value="3" <?php if(isset($category)) echo selected($category['cat_type'], 3)?>>Simple</option>
							<option value="1" <?php if(isset($category)) echo selected($category['cat_type'], 1)?>>Make</option>
							<option value="2" <?php if(isset($category)) echo selected($category['cat_type'], 2)?>>Model</option>
						</select>
					</td>
				</tr>
				<tr class="vin_tr <?php if(!isset($category) || $category['p_or_m'] != 2){ ?>display-n<?php } ?>">
					<td>VIN:</td>
					<td>
						<input <?php if(!isset($category) || $category['p_or_m'] != 2){ ?>disabled="disabled"<?php } ?> class="vin validate[required]" type="radio" name="vin" value="0" <?php if(isset($category)){ echo checked($category['vin'], 0); } else {?>checked="checked"<?php } ?>/>Disable
						<input <?php if(!isset($category) || $category['p_or_m'] != 2){ ?>disabled="disabled"<?php } ?> class="vin validate[required]" type="radio" name="vin" value="1" <?php if(isset($category)) echo checked($category['vin'], 1); ?>/>Enable
					</td>
				</tr>
				<tr>
					<td>Meta params info</td>
					<td>This replaces should be used in meta title, meta keywords and meta description <br/><br/><?php
						foreach($meta_rules as $mr_key => $mr_value){
							echo "<strong>{$mr_key}</strong>: {$mr_value}<br/>";
						}
					?>
					<br/>To change the texts for these params go to <a href="<?php echo __SITE_URL ?>meta/administration" target="_blank">Meta pages administration</a> and edit the meta for key category_index</td>
				</tr>
				<tr>
					<td>Meta Title:</td>
					<td><textarea name="title" class="w-100pr h-100 validate[maxSize[255]]" ><?php if(isset($category)) echo $category['title']?></textarea></td>
				</tr>
				<tr>
					<td>H1:</td>
					<td><input type="text" name="h1" class="w-100pr validate[required,maxSize[50]]" value="<?php if(isset($category)) echo $category['h1']?>"/></td>
				</tr>
				<tr>
					<td>Meta Description:</td>
					<td>
						<textarea name="description" class="w-100pr h-100" ><?php if(isset($category)) echo $category['description']?></textarea>
					</td>
				</tr>
				<tr>
					<td>Meta Keywords:</td>
					<td>
						<textarea name="keywords" class="w-100pr h-100" ><?php if(isset($category)) echo $category['keywords']?></textarea>
					</td>
				</tr>
			</tbody>
		</table>

	   </div>
		<div class="wr-form-btns clearfix">
			<label class="lh-30" for="close-on-submit"><input type="checkbox" id="close-on-submit" checked="checked">Close on submit</label>
			<?php if(isset($category)){ ?>
				<input type="hidden" name="category_id" value="<?php echo $category['category_id'];?>" />
				<button class="pull-right btn btn-default ml-10" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
				<button class="pull-right btn btn-default" id="reset-form"><span class="ep-icon ep-icon_refresh"></span> Reset</button>
			<?php }else{?>
				<label class="lh-30" for="clear-on-submit" class="pl-10"><input type="checkbox" id="clear-on-submit" checked="checked">Clear on submit</label>
				<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
			<?php }?>
		</div>
</form>


<script>

var preview_category_link = function(btn){
	var $this = $(btn);
	var $form = $this.closest('form');
	var title = $form.find('input[name="name"]').val();
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>categories/ajax_category_operation/preview_category_link',
		dataType: 'JSON',
		data: {title:title},
		beforeSend: function(){
			$this.addClass('disabled');
		},
		success: function(resp){
			$this.removeClass('disabled');
			if(resp.mess_type == 'success'){
				$this.closest('.input-group').find('input[name="link"]').val(resp.category_link);
			}else{
				systemMessages(resp.message,  'message-' + resp.mess_type);
			}
		}
	});

	return false;
}

$('input[name="name"]').on("input", function(){
	$('input[name="link"]').val('');
})

$('.cancel-cat-select').on('click', function(){
    $('.categ_update option[value="<?php echo $category['parent']; ?>"]').prop('selected', true);
	$('.categ_update').hide();
	$('.edit-categories').show();
	$(this).hide();
	$('.subcategories').remove();
});

$('.edit-categories').on('click', function(){
	$('.categ_update').show();
	$(this).hide();
	$('.cancel-cat-select').show();
});

$('.js-cancel-cat-select').on('click', function(){
    $('.js-golden-update option[value="<?php echo $categoryGolden['id_group']; ?>"]').prop('selected', true);
	$('.js-golden-update').hide();
	$('.js-edit-golden-12').show();
	$(this).hide();
});

$('.js-edit-golden-12').on('click', function(){
	$('.js-golden-update').show();
	$(this).hide();
	$('.js-cancel-cat-select').show();
});

$('#js-golden-12').on('change', '#js-select-golden-category', function(){
    if ($(this).val() != 0) {
        $('.js-select-category-parent').val('0');
        $('#categories .subcategories').each(function (){
		    $(this).remove();
	    });
    }
})

$('#js-golden-12-update').on('change', '#js-select-golden-category', function(){
    if ($(this).val() != 0) {
        $('#categories .subcategories').each(function (){
		    $(this).remove();
	    });
    }
})

$('#categories').on('change', '.categ_add', function(){
	var select = this;
	var cat = select.value;
	var control = select.id;// alert(cat + '-- '+ control);
	var sClass = select.className;
    var level = $(select).attr('level');

    if ($(this).val() != 0) {
        $('#js-select-golden-category').val("0");
    }

	$('#categories .subcategories').each(function (){
		thislevel = $(this).attr('level');
		if(thislevel > level) $(this).remove();
	});

	if(cat != 0){
		if(cat != control){

			$.ajax({
				type: 'POST',
				url: '/categories/getcategories',
				dataType: 'JSON',
				beforeSend: function(){
					$(select).prop('disabled', true);
					showLoader('#create_forms');
				},
				data: { op : 'select', cat: cat, level : level, cl : sClass},
				success: function(json){
					$(select).prop('disabled', false);
					if(json.mess_type == 'success'){
						$('#categories').append(json.content);
						$('.categ_add').css('color', 'black');
						$(select).css('color', 'red');
					}else{
						systemMessages(json.message,  'message-' + json.mess_type);
					}
					hideLoader('#create_forms');
				}
			});
		}else{
			$('.categ_add').css('color', 'black');
			$('.categ_add[level='+(level-1)+']').css('color', 'red');
		}
	}
});

$('#categories_update').on('change', '.categ_update', function(){
	var select = this;
	var cat = select.value;
	var sClass = select.className;
	var control = select.id; //alert(cat + '-- '+ control);
    var level = $(select).attr('level');

    if ($(this).val() != 0) {
        $('#js-select-golden-category').val("0");
    }

	$('#categories_update .subcategories').each(function (){
		thislevel = $(this).attr('level');
		if(thislevel > level)
			$(this).remove();
	});

	if(cat == 0)
		return;

	if(cat != control){
		$.ajax({
			type: 'POST',
			url: '/categories/getcategories',
			dataType: 'JSON',
			beforeSend: function(){
				$(select).prop('disabled', true);
				showLoader('#cat_update');
			},
			data: { op : 'select', cat: cat, level : level, cl : sClass},
			success: function(json){
				$(select).prop('disabled', false);
				if(json.mess_type == 'success'){
					$('#categories_update').append(json.content);
					$('.categ_update').css('color', 'black');
					$(select).css('color', 'red');
				}else{
					systemMessages(json.message,  'message-' + json.mess_type);
				}
				hideLoader('#cat_update');
			},
			error: function(){alert('ERROR')}
		});
	}else{
		$('.categ_update').css('color', 'black');
		$('.categ_update[level='+(level-1)+']').css('color', 'red');
	}
});


$('#reset-form').on('click', function(){
	$(this).closest('form')[0].reset();
	$('.subcategories').remove();
	return false;
});

function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>categories/ajax_category_operation/<?php echo ((isset($category) ? "update" : "add"))?>_category',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				var $close_elem = $('#close-on-submit');
				if($close_elem.is(':checked'))
					closeFancyBox();

				if($('#clear-on-submit').is(':checked')){
					var need_to_check = false;
					if($close_elem.is(':checked'))
						need_to_check = true;

					$form[0].reset();

					$close_elem.prop('checked', need_to_check);
					$('.subcategories').remove();
				}

				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{

			}
		}
	});
}
</script>
