<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
			<table cellpadding="5" cellspacing="0" class="data table-striped temp w-100pr">
				<tr>
					<td>Category:</td>
					<td id="categories2">
						<select name="parent" class="categ2 w-98pr" level="1" id="0" style="float: left;">
							<option value="<?php echo $attr['category']?>">Present category</option>
							<?php
							if(isset($categories) && is_array($categories) && count($categories) > 0){
								foreach($categories as $cat){?>
									<option  value="<?php echo $cat['category_id']?>"><?php echo $cat['name']?></option>
								<?php }?>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="10%">Attribute name:</td>
					<td><input type="text" name="attribute" value="<?php echo $attr['attr_name']?>" class="w-98pr validate[required,maxSize[255]]"/> e.g.: Color</td>
				</tr>
				<tr>
					<td>Required/Optional:</td>
					<td>
						<input type="radio" class="attr_req" name="attr_req" value="1" <?php echo checked($attr['attr_req'], 1);?> /> Required
						<input type="radio" class="attr_req" name="attr_req" value="0" <?php echo checked($attr['attr_req'], 0);?>/> Optional
					</td>
				</tr>
				<tr>
					<td>Attribute Type:</td>
					<td>
						<input type="radio" class="upattr_type" name="type" value="text" <?php echo checked($attr['attr_type'],'text')?> <?php echo checked(empty($attr), true)?>/> Text
						<input type="radio" class="upattr_type" name="type" value="range" <?php echo checked($attr['attr_type'],'range')?>/> Range
						<input type="radio" class="upattr_type" name="type" value="select" <?php echo checked($attr['attr_type'],'select')?> /> 	Dropdown
						<input type="radio" class="upattr_type" name="type" value="multiselect" <?php echo checked($attr['attr_type'],'multiselect')?> /> Multiselect dropdown
					</td>
				</tr>
				<tr class="uponly_select">
					<td>Attribute's Values:</td>
					<td><textarea name="values" class="w-98pr" id="upattr_val" cols="50" rows="5"></textarea> e.g.: red; green</td>
				</tr>
				<tr class="uponly_select">
					<td colspan="2"><span>* If you want to insert many attribute's values, please use a semicolon (;) as delimiter.</span></td>
				</tr>
				<tr class="uponly_input">
					<td>Attribute sample:</td>
					<td>
						<input type="text" name="attr_sample"  class="w-98pr validate[maxSize[255]]" value="<?php echo $attr['attr_sample']?>"/>
					</td>
				</tr>
				<tr  class="uponly_input" style="display: none;">
					<td>Attribute's Value Type:</td>
					<td>
						<select name="vtype" id="upvtype" class="w-98pr validate[required]">
							<option value="1" <?php if($attr['attr_value_type'] == 1){ ?> selected="selected" <?php }?>>Letters & Numbers</option>
							<option value="2" <?php if($attr['attr_value_type'] == 2){ ?> selected="selected" <?php }?>>Only Letters</option>
							<option value="3" <?php if($attr['attr_value_type'] == 3){ ?> selected="selected" <?php }?>>Only Numbers</option>
						</select>
					</td>
				</tr>
			</table>
	<div class="wr-form-btns clearfix">
		<?php if(isset($attr)){ ?>
			<input type="hidden" name="id" value="<?php echo $attr['id'];?>" />
		<?php }?>
			<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
	/* changes by condition from radio butons from Add and Update Attribute*/
function changeFormField($obj){
	var type = $obj.val();
	var className = $obj.attr('class');

	if(className == 'upattr_type')
		var prefix = 'up';
	else
		var prefix = '';

	if($.inArray(type, ['select', 'multiselect'])!= -1){
		$('tr.'+prefix+'only_input').hide('slow');
		$('select#'+prefix+'vtype').prop('disabled',true);
		$('textarea#'+prefix+'attr_val').prop('disabled',false);
		$('tr.'+prefix+'only_select').show('slow');
	}else{
		$('tr.'+prefix+'only_select').hide('slow');
		$('textarea#'+prefix+'attr_val').prop('disabled',true);
		$('select#'+prefix+'vtype').prop('disabled',false);
		$('tr.'+prefix+'only_input').show('slow');

		if(type == 'range'){
			$('select#'+prefix+'vtype option').prop('disabled',true).prop('selected', false);
			$('select#'+prefix+'vtype option[value=3]').prop('disabled',false).prop('selected', true);
		}else{
			$('select#'+prefix+'vtype option').prop('disabled',false).prop('selected', false);
		}
	}
}

$('input.attr_type, input.upattr_type').on('click',function(){
	changeFormField($(this));
});

changeFormField($('input.upattr_type:checked'));

function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: 'catattr/ajax_attr_operation/<?php echo ((isset($attr) ? "edit" : "add"))?>_attr',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				closeFancyBox();

				dtCategoryAttribute.fnDraw();

				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{

			}
		}
	});
}

$('#categories2').on('change', 'select.categ2', function(){
	var select = this;
	var cat = select.value;
	var sClass = select.className;
	var control = select.id; //alert(cat + '-- '+ control);
	var level = $(select).attr('level');

	$('td#categories2 div.subcategories').each(function (){
		thislevel = $(this).attr('level');
		if(thislevel > level) $(this).remove();
	});

	if(cat != 0){
		if(cat != control){
			$.ajax({
				type: 'POST',
				url: '/categories/getcategories',
				data: { op : 'select', cat: cat, level : level, cl : sClass},
				beforeSend: function(){ showLoader('#cat_update'); },
				dataType: 'json',
				success: function(data){
					$('td#categories2').append(data.content);
					$('select.categ2').css('color', 'black');
					$(select).css('color', 'red');
					hideLoader('#cat_update');
				},
			});
		}else{
			$('select.categ2').css('color', 'black');
			$('select.categ2[level='+(level-1)+']').css('color', 'red');
		}
	}
});
</script>
