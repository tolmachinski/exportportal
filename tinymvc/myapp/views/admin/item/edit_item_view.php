<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-tags-input-master/jquery.tagsinput.min.js');?>"></script>

<div class="wr-modal-b">
	<form method="post" id="admin-edit-form" name="form-add" class="relative-b validateModal">
		<div class="wr-form-content w-900 h-550">
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
				<tbody>
					<tr>
						<td colspan="2" class="td-legend tac">General information</td>
					</tr>
					<tr>
					<td>Categories</td>
					<td class="select_category_form">
						<select id="main_cats" data-title="Category" class="categ1_form js-select_cat" level="0" name="category[]">
							<?php
							if(isset($categories) && is_array($categories) && count($categories) > 0){
								foreach($categories as $category){?>
								<option  value="<?php echo $category['category_id']?>" <?php echo selected($category['category_id'], $product_categories[0]); ?>><?php echo $category['name']?></option>
								<?php } ?>
							<?php } ?>
						</select>
						<?php for($i = 1; $i < count($product_categories); $i++){
							if(isset($product_categories[$i])){ ?>
							<div class="subcategories" level="<?php echo $i; ?>" >
								<select class="subcategories_select js-select_cat mr-5 mt-5" data-title="Category" level="<?php echo $i; ?>" name="category[]">
									<?php
									if(isset(${'categories_' . $product_categories[$i-1]}) && is_array(${'categories_' . $product_categories[$i-1]}) && count(${'categories_' . $product_categories[$i-1]}) > 0){
										foreach(${'categories_' . $product_categories[$i-1]} as $category){ ?>
										<option  value="<?php echo $category['category_id']?>" <?php echo selected($category['category_id'], $product_categories[$i]); ?>><?php echo $category['name']?></option>-->
										<?php } ?>
									<?php } ?>
								</select>
							</div>
							<?php }
						} ?>
					</td>
				</tr>
					<tr>
						<td>Title</td>
						<td>
                            <div class="form-group">
                                <input type="text" name="title" class="w-100pr validate[required,maxSize[70],custom[productTitle]] w1Input" value="<?php echo $item['title']?>" placeholder="Product name"/>
                            </div>
                        </td>
					</tr>
                    <?php if (!$item['has_variants']) {?>
                        <tr>
                            <td>Price</td>
                            <td>
                                <div class="form-group">
                                    <input class="w-50pr validate[<?php echo $item['draft'] ?: 'required,'; ?>custom[positive_number]] pull-left" type="text" name="price_in_dol" id="js-add-item-price-in-dol" value="<?php echo $item['price'] > 0 ? $item['price'] : '';?>" placeholder="e.g. 10.55">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Discount price</td>
                            <td>
                                <div class="form-group">
                                    <input
                                        id="js-add-item-final-price"
                                        type="text"
                                        name="final_price"
                                        class="w-50pr validate[custom[positive_number]]"
                                        value="<?php echo isset($item['final_price']) && $item['final_price'] > 0 && $item['final_price'] <  $item['price'] ? $item['final_price'] : null; ?>"
                                        placeholder="e.g. 10.55"
                                    />
                                </div>
                                <div id="js-add-item-discount"><?php if (isset($item['discount'])) { echo cleanOutput($item['discount']); } else { echo '0'; } ?>%</div>
                            </td>
                        </tr>
                    <?php }?>
					<tr>
						<td>Tags</td>
						<td>
							<?php views()->display('new/tags_rule_view');?>
							<input
								id="js-tags"
								name="tags"
								value="<?php if(!empty($item['tags'])){ echo implode(';', explode(',', $item['tags'])); }?>">
						</td>
					</tr>
                    <?php if (!$item['has_variants']) {?>
                        <tr>
                            <td>
                                <span class="txt-red">*</span> Total Quantity in Stock
                            </td>
                            <td>
                                <div class="form-group">
                                    <input class="w-50pr validate[custom[positive_integer]]" type="text" name="quantity" value="<?php echo $item['quantity']?>" id="quantity" placeholder="Total quantity"/>
                                </div>
                            </td>
                        </tr>
                    <?php }?>
					<tr>
						<td>
							Unit type
						</td>
						<td>
							<select class="w-50pr" id="unit_type" name="unit_type">
								<?php foreach($u_types as $type){?>
									<option value="<?php echo $type['id']?>"  <?php echo selected($type['id'], $item['unit_type']);?>><?php echo $type['unit_name']?></option>
								<?php }?>
							</select>
						</td>
					</tr>

					<tr>
						<td>
							<span class="txt-red">*</span> Min. sale quantity
						</td>
						<td>
                            <div class="form-group">
                                <input class="w-50pr validate[min[1],custom[positive_integer]]" type="text" name="min_quantity" value="<?php echo $item['min_sale_q']?>" id="min_quantity" placeholder="Minimum"/>
                            </div>
						</td>
					</tr>

					<tr>
						<td>
							<span class="txt-red">*</span> Max. sale quantity
						</td>
						<td>
                            <div class="form-group">
                                <input class="w-50pr validate[min[1],custom[positive_integer]]" type="text" name="max_quantity" value="<?php echo $item['max_sale_q']?>" id="max_quantity" placeholder="Maximum"/>
                            </div>
						</td>
					</tr>
					<tr>
						<td>
							<span class="txt-red">*</span> Weight (Kg)
						</td>
						<td>
							<input
								id="js-add-item-item-weight"
								class="w-50pr"
								type="number"
								min="0"
								step="0.001"
								name="weight"
								value="<?php echo compareFloatNumbers($item['weight'] ?? 0, 0, '>') ? cleanOutput($item['weight']) : null; ?>"
								placeholder="Weight per unit"/>
						</td>
					</tr>
					<tr>
						<td>
                            <?php echo translate('add_item_distributor_agreement_checkbox');?>
						</td>
						<td>
							<input
                            class="icheck-20-blue"
                            type="checkbox"
                            value="1"
                            name="is_distributor"
                            <?php if (isset($item['is_distributor'])) { echo checked($item['is_distributor'], 1); } ?>/>

						</td>
					</tr>
					<tr>
						<td>Size, cm (LxWxH)</td>
						<td>
							<input
								class="h-25"
								type="number"
								step="0.01"
								min="0.01"
								name="item_length"
								size="4"
								maxlength="7"
								value="<?php echo compareFloatNumbers($item['item_length'] ?? 0, 0, '>') ? cleanOutput($item['item_length']) : null; ?>"
								placeholder="Length">

							<input
								class="h-25"
								type="number"
								step="0.01"
								min="0.01"
								name="item_width"
								size="4"
								maxlength="7"
								value="<?php echo compareFloatNumbers($item['item_width'] ?? 0, 0, '>') ? cleanOutput($item['item_width']) : null; ?>"
								placeholder="Width">

							<input
								class="h-25"
								type="number"
								step="0.01"
								name="item_height"
								min="0.01"
								size="4"
								maxlength="7"
								value="<?php echo compareFloatNumbers($item['item_height'] ?? 0, 0, '>') ? cleanOutput($item['item_height']) : null; ?>"
								placeholder="Height">
						</td>
					</tr>
					<tr>
						<td>Harmonized Tariff Schedule</td>
						<td>
                            <div class="form-group">
                                <input
                                    class="half-input half-input--mw-305 validate[custom[tariffNumber]]"
                                    data-prompt-position="bottomLeft:0"
                                    type="text"
                                    maxlength="13"
                                    name="hs_tariff_number"
                                    value="<?php echo !empty($item['hs_tariff_number']) ? cleanOutput($item['hs_tariff_number']) : (!empty($cat_option['hs_tariff_number']) ? $cat_option['hs_tariff_number'] : null); ?>"
                                    placeholder="Product code"/>
                            </div>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if(!empty($itemVariants['variants'])){?>
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
                    <tr>
                        <td colspan="2" class="td-legend tac">Variants</td>
                    </tr>
                </table>
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
                    <thead>
                        <tr>
                            <td>Variant type</td>
                            <td>Variant options</td>
                        </tr>
                    </thead>

                    <?php foreach($itemVariants['properties'] as $propertiesItem){?>
                        <tr>
                            <td>
                                <div class="form-group">
                                    <input
                                        class="w-100pr validate[required]"
                                        type="text"
                                        name="properties[<?php echo $propertiesItem['id']; ?>][name]"
                                        value="<?php echo cleanOutput($propertiesItem['name']); ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="properties[<?php echo $propertiesItem['id']; ?>][id]"
                                        value="<?php echo $propertiesItem['id']; ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="properties[<?php echo $propertiesItem['id']; ?>][type]"
                                        value="exist"
                                    >
                                </div>
                            </td>
                            <td>
                                <?php foreach($propertiesItem['property_options'] as $propertyOptionsKey => $propertyOptionsItem){?>
                                    <div class="form-group">
                                        <input
                                            class="w-50pr validate[required]"
                                            name="properties[<?php echo $propertyOptionsItem['id_property']; ?>][options][<?php echo $propertyOptionsItem['id']; ?>][name]"
                                            type="text"
                                            value="<?php echo cleanOutput($propertyOptionsItem['name']); ?>"
                                        >
                                        <input
                                            type="hidden"
                                            name="properties[<?php echo $propertyOptionsItem['id_property']; ?>][options][<?php echo $propertyOptionsItem['id']; ?>][id]"
                                            value="<?php echo cleanOutput($propertyOptionsItem['id']); ?>"
                                        >
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                </table>
			<?php } ?>

			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
				<tr>
					<td colspan="2" class="td-legend tac">Item specifics</td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" id="aditional_attributes" class="data table-striped table-bordered w-100pr m-auto vam-table">
				<thead>
					<tr>
						<td>Component</td>
						<td>Specification</td>
						<td>&nbsp;</td>
					</tr>
				</thead>
				<tbody>
					<?php
					if(isset($item['u_attr'])){
						foreach($item['u_attr'] as $key => $u_attr){?>
							<tr>
								<td>
                                    <div class="form-group">
                                        <input class="w-100pr validate[maxSize[50]]" type="text" name="u_attr[name][]" value="<?php echo $u_attr['p_name']?>"/>
                                    </div>
                                </td>
								<td>
                                    <div class="form-group">
                                        <input class="w-100pr validate[maxSize[50]]" type="text" name="u_attr[val][]" value="<?php echo $u_attr['p_value']?>"/>
                                    </div>
                                </td>
								<td><a class='ep-icon ep-icon_remove txt-grey call-function' data-callback='del_row_attr' title="Remove attribute"></a></td>
							</tr>
					<?php }
					} ?>
				</tbody>
				<tfoot>
					<tr><td colspan="3"><a class="call-function" data-callback="addInputUserAttr"><i class="ep-icon ep-icon_plus"></i>add field</a></td></tr>
				</tfoot>
			</table>

			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
				<tr>
					<td colspan="2" class="td-legend tac">Description</td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea name="description" class="js-description"><?php echo $item['description']?></textarea>
					</td>
				</tr>
			</table>

			<?php
			$item_description_array = array();
			if(!empty($item_description)){
				$item_description_array = json_decode($item_description, true);
			}
			if((!empty($item_description) && $item_description_array['status'] != 'removed')){
			?>
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
				<tr>
					<td colspan="2" class="td-legend tac">Translation</td>
				</tr>
				<tr>
					<td>Language</td>
					<td>
                        <div class="form-group">
                            <select class="validate[required] half-input half-input--mw-305" name="translation_language">
                                <?php foreach($languages as $languages_item){?>
                                <option value="<?php echo $languages_item['id_lang'];?>" <?php echo selected($languages_item['id_lang'], $item_description_array['language']); ?>><?php echo $languages_item['lang_name'];?></option>
                                <?php }?>
                            </select>
                        </div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea name="translation_description" class="js-description"><?php echo $item_description_array['description']?></textarea>
					</td>
				</tr>
			</table>
			<?php }?>
		</div>
		<div class="wr-form-btns clearfix">
			<input type="hidden" name="item" value="<?php echo $item["id"];?>"/>
			<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
		</div>
	</form>
</div>
<script type="text/javascript">

	var renewPrice = function (){

		var price = parseFloat($('#js-add-item-price-in-dol').val());
		var priceFinal = parseFloat($('#js-add-item-final-price').val());
		var discountLabel = $('#js-add-item-discount');

		var isZeroPrice = function (value) {
			return Math.abs(0 - parseFloat(value)) <= 1e-3;
		};

		var isGreaterThan = function (a, b) {
			var epsilon = 1e-18;
			if (Math.abs(a - b) < epsilon) {
				return false;
			}

			if (a > b) {
				return true;
			}

			return false;
		};

		if (isNaN(price) ||  isZeroPrice(price)) {
			discountLabel.html(0+'%');

			return;
		}

		if (isNaN(priceFinal) || isZeroPrice(priceFinal)) {
			discountLabel.text(0+'%');

			return;
		}

		if (isGreaterThan(priceFinal, price)) {
			priceFinal = price;
			$('#js-add-item-final-price').val(priceFinal);

			systemMessages('<?php echo translate('system_message_manage_item_final_price_lower_initial_price');?>', 'warning');
		}

		discountLabel.text(parseInt(100 - ((priceFinal * 100) / price))+'%');
	}

	function modalFormCallBack(form, data_table){

		var $form = $(form);
		tinyMCE.triggerSave();

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>items/ajax_item_operation/admin_update_item',
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
	};


	$(function(){

		$.fn.setValHookType = function (type) {
            this.each(function () {
                this.type = type;
            });

            return this;
        };

		$('#js-add-item-price-in-dol, #js-add-item-final-price').on('change', function(){
			renewPrice();
		});

		$('#quantity').on('change', function (){
			var $min_quant = $('#min_quantity');
			$min_quant.removeClass($min_quant.data('validate-class'));
			$min_quant.addClass('validate[min[1],custom[positive_integer],max['+$(this).val()+']]').data('validate-class', 'validate[min[1],custom[positive_integer],max['+$(this).val()+']]');
		});

		$('.select_category_form').on('change', 'select.js-select_cat', function(){
			var select = this;
			var cat = select.value;
			var sClass = select.className;
			var control = select.id; //alert(cat + '-- '+ control);
			var level = $(select).attr('level');
			var select_name = 'category[]';
			$('td.select_category_form div.subcategories').each(function (){
				thislevel = $(this).attr('level');
				if(thislevel > level) $(this).remove();
			});
			if(cat != 0){
				if(cat != control){
					$.ajax({
						type: 'POST',
						url: '/categories/getcategories',
						dataType: 'JSON',
						data: { op : 'select', cat: cat, level : level, cl : sClass, select_name: select_name, not_filter: 1},
						beforeSend: function(){ showLoader('.full_block'); },
						success: function(json){
							if(json.mess_type == 'success'){
								$('.select_category_form').append(json.content);
								$('select.js-select_cat').css('color', 'black');
								$(select).css('color', 'red');
							}else{
								systemMessages(json.message,  'message-' + json.mess_type);
							}
							hideLoader('.full_block');
						},
						error: function(){alert('ERROR')}
					});
				}else{
					$('select.js-select_cat').css('color', 'black');
					$('select.js-select_cat[level='+(level-1)+']').css('color', 'red');
				}
			} else{
				$('.subcategories').remove();
			}

		});

	});

	tinymce.init({
		selector:'.js-description',
		menubar: false,
		statusbar : true,
		height : 300,
		plugins: ["lists charactercount powerpaste"],
		style_formats: [
			{title: 'H3', block: 'h3'},
			{title: 'H4', block: 'h4'},
			{title: 'H5', block: 'h5'},
			{title: 'H6', block: 'h6'},
		],
		powerpaste_html_import: "merge",
		toolbar: "styleselect | bold italic underline | numlist bullist ",
		resize: false
	});


	//remove attr
	var del_row_attr = function(obj){
		var $this = $(obj);
		$this.closest("tr").fadeOut('normal', function(){
			$(this).remove();
		});
	}
	//end remove attr

	//add field attribute
	var addInputUserAttr = function(obj){
		var $thisBtn = $(obj);
		var tbody = $("table#aditional_attributes tbody");

		var row = "<tr>\
                        <td>\
                            <div class='form-group'>\
                                <input class='validate[maxSize[50]] w-100pr' type='text' name='u_attr[name][]'>\
                            </div>\
						</td>\
                        <td>\
                            <div class='form-group'>\
                                <input class='validate[maxSize[50]] w-100pr' type='text' name='u_attr[val][]'>\
                            </div>\
						</td>\
						<td valign='middle'>\
							<a class='ep-icon ep-icon_remove txt-grey call-function' data-callback='del_row_attr' title='Remove attribute'></a>\
						</td>\
					</tr>";
		tbody.append(row);
		validateReInit();
	}

	//end add field attribute
	var $requestTagsSelect = $("#js-tags");
	var $requestTags = $requestTagsSelect.tagsInput({
		defaultText: 'Product tags',
		width: '100%',
		height: 'auto',
		minChars: 3,
		maxChars: 30,
		placeholderColor: '#9e9e9e',
		delimiter: [';'],
		onAddTag: function(tagText) {
			var self = $(this);
			var container = self.siblings('div.tagsinput');
			var tags = container.find('.tag');
			if (tags.length > 10) {
				$requestTags.removeTag(tagText)
				systemMessages("No more than 10 tags are allowed.", 'warning');
			};
		}
	});

	$.valHooks.tagsinput = {
		get: function (el) {
			return $requestTagsSelect.val() || [];
		},
		set: function (el, val) {
			$requestTagsSelect.val(val);
		}
	};
</script>
