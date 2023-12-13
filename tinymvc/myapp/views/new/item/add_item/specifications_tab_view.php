<div class="form-group">
	<label
		class="input-label <?php echo form_validation_label($validation, 'title', 'required'); ?>"
	>Item title</label>
    <input
        <?php echo addQaUniqueIdentifier("items-my-add__title")?>
		class="validate[<?php echo form_validation_rules($validation, 'title', 'required'); ?>,<?php echo form_validation_rules($validation, 'title', 'min'); ?>,<?php echo form_validation_rules($validation, 'title', 'max'); ?>,custom[productTitle]]"
		type="text"
		name="title"
		value="<?php if(isset($item['title'])){ echo $item['title']; }?>"
		placeholder="Product name"/>
</div>

<div class="form-group">
	<label class="input-label <?php echo form_validation_label($validation, 'year', 'required'); ?>">Item year</label>
	<?php $year = date('Y') + config('item_year_plus_value'); ?>
    <div class="input-info-right">
        <div class="input-group half-input half-input--mw-305">
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__year")?>
                class="form-control validate[<?php echo form_validation_rules($validation, 'year', 'required'); ?>,max[<?php echo $year;?>],min[1],custom[onlyNumber]]"
                type="number"
                name="year"
                placeholder="Year of production" value="<?php echo cleanOutput($item['year'] ?? null) ?: null; ?>"/>

        </div>
<!--        --><?php //if ($company_info['id_type'] == 7) { ?>
            <div class="add-info-row__col add-info-row__col--mod-margin">
                <label class="checkbox-list__label custom-checkbox">
                    <input
                        type="checkbox"
                        value="1"
                        name="is_distributor"
                        <?php if (isset($item['is_distributor'])) { echo checked($item['is_distributor'], 1); } ?>/>
                    <span class="custom-checkbox__text"><?php echo translate('add_item_distributor_agreement_checkbox');?></span>
                </label>
            </div>
<!--        --><?php //} ?>
    </div>
</div>

<div class="form-group">
	<label class="input-label <?php echo form_validation_label($validation, 'origin_coutry', 'required'); ?>">Country of origin</label>
    <select
        <?php echo addQaUniqueIdentifier("items-my-add__select-country")?>
		class="half-input half-input--mw-305 validate[<?php echo form_validation_rules($validation, 'origin_coutry', 'required'); ?>]"
        name="origin_country"
	>
		<?php echo getCountrySelectOptions(
							$port_country,
							empty($item['origin_country']) ? 0 : $item['origin_country'],
							array(),
							'Select product country'
						);?>
	</select>
</div>

<div class="form-group" <?php echo addQaUniqueIdentifier("items-my-add__tags")?>>
	<label class="input-label input-label--info <?php echo form_validation_label($validation, 'tags', 'required'); ?>">
		<span class="input-label__text">Tags</span><a
			class="info-dialog ep-icon ep-icon_info"
			data-content="#js-info-dialog__tags-on-product"
			data-title="<?php echo cleanOutput($block_info['about_tag_info']['title_block']);?>"
			title="<?php echo cleanOutput($block_info['about_tag_info']['title_block']);?>" href="#"></a>
		<div class="display-n" id="js-info-dialog__tags-on-product">
			<?php echo $block_info['about_tag_info']['text_block'];?>
		</div>
    </label>

    <?php views()->display('new/tags_rule_view');?>

    <input
        <?php echo addQaUniqueIdentifier("items-my-add__tags-input")?>
		id="js-suggestions-tags--formfield--tags"
		name="tags"
		value="<?php if(!empty($item['tags'])){ echo implode(';', explode(',', $item['tags'])); }?>"
	>
</div>


<label class="input-label">
	Add custom item specifics
	<a
		class="info-dialog ep-icon ep-icon_info"
		data-content="#js-info-dialog-custom-item-specifics"
		data-title="Add custom item specifics"
		href="#"
	></a>

	<div class="display-n" id="js-info-dialog-custom-item-specifics">
		<p>Item specifications represent a set of characteristics related to your product. Here you can add all possible details which describe your item.<br> The picture below shows you the way the added information will be displayed on the page. </p>
		<img src="<?php echo __IMG_URL; ?>public/img/products/info/product-information.jpg" alt="Add custom item specifics">
	</div>
</label>

<p class="fs-14 txt-gray">Add unique item details which might increase buyers' interest.</p>
<div class="info-alert-b mt-5">
	<i class="ep-icon ep-icon_info-stroke"></i>
	<div class="ep-tinymce-text">
		<strong>How to add item specifications:</strong>

		<ol class="pb-5">
			<li>Fill out the Component and Specification fields with specifics.</li>
			<li>Click the Add button to add the specification.</li>
		</ol>

		<strong>Note:</strong> The Component and Specification fields cannot contain more than 50 characters.
	</div>
</div>

<div class="container-fluid-modal">
	<div class="add-info-row">
		<div class="add-info-row__col">
			<label class="input-label">Component</label>
			<input <?php echo addQaUniqueIdentifier("items-my-add__component")?> class="validate[maxSize[50]]" type="text" name="u_attr_name" maxlength="50" placeholder="e.g. Color, Material, or Year"/>
		</div>
		<div class="add-info-row__col">
			<label class="input-label">Specification</label>
			<input <?php echo addQaUniqueIdentifier("items-my-add__specification")?> class="validate[maxSize[50]]" type="text" name="u_attr_val" maxlength="50" placeholder="e.g. Yellow, Plastic, or <?php echo date('Y') ?>"/>
		</div>
		<div class="add-info-row__col add-info-row__action-col add-info-row__col--130">
			<label class="input-label dn-md_i">&nbsp;</label>
			<a <?php echo addQaUniqueIdentifier("items-my-add__custom-item-specific-add-btn")?> class="call-function btn btn-dark btn-block" data-callback="addInputUserAttr" href="#">Add <span class="dn-md-min">specification</span></a>
		</div>
	</div>

	<div id="js-item-additional-information" class="add-info-row-wr add-info-row-wr--pd-20">
		<?php if(isset($item['u_attr'])){?>
			<?php foreach($item['u_attr'] as $key => $u_attr){?>
			<div class="add-info-row">
				<div class="add-info-row__col add-info-row__col--simple">
					<input <?php echo addQaUniqueIdentifier("items-my-add__component-change")?> class="validate[maxSize[50]]" type="text" name="u_attr[name][]" maxlength="50" value="<?php echo $u_attr['p_name']?>" placeholder="Name"/>
				</div>
				<div class="add-info-row__col add-info-row__col--simple">
					<input <?php echo addQaUniqueIdentifier("items-my-add__specification-change")?> class="validate[maxSize[50]]" type="text" name="u_attr[val][]" maxlength="50" value="<?php echo $u_attr['p_value']?>" placeholder="Value"/>
				</div>
				<div class="add-info-row__col add-info-row__col--simple add-info-row__col--130">
					<div class="add-info-row__actions">
						<a <?php echo addQaUniqueIdentifier("items-my-add__custom-item-specific-remove-btn")?> class="btn btn-light confirm-dialog" data-callback="del_row_attr" data-attr="<?php echo $key?>" data-message="Are you sure you want to delete this attribute ?"><i class="ep-icon ep-icon_trash-stroke"></i></a>
					</div>
				</div>
			</div>
			<?php }?>
		<?php }?>
	</div>
</div>

<div class="form-group">
	<label
		class="input-label <?php echo form_validation_label($validation, 'purchase_options', 'required'); ?>"
	>Choose at least 1 Purchase Option</label>
	<div class="checkbox-list">
		<div class="custom-checkbox-wrap">
			<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-add-to-basket")?>>
				<input
					class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
					type="checkbox"
					value="order_now"
					name="purchase_options[]"
					<?php if (isset($item['order_now'])) { echo checked($item['order_now'], 1); } else { echo 'checked'; } ?>/>
				<span class="custom-checkbox__text">Add to Basket</span>
			</label><a
				class="info-dialog ep-icon ep-icon_info"
				data-message="<?php echo cleanOutput($block_info['what_is_add_to_basket']['text_block']); ?>"
				data-title="<?php echo $block_info['what_is_add_to_basket']['title_block']; ?>"
				title="<?php echo $block_info['what_is_add_to_basket']['title_block']; ?>"></a>
		</div>

		<?php if (have_right('manage_seller_inquiries')) { ?>
			<div class="custom-checkbox-wrap">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-send-inquiry")?>>
					<input
						class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
						type="checkbox"
						value="inquiry"
						name="purchase_options[]"
						<?php if (isset($item['inquiry'])) { echo checked($item['inquiry'], 1); } ?>/>
					<span class="custom-checkbox__text">Send Inquiry</span>
				</label><a
					class="info-dialog ep-icon ep-icon_info"
					data-message="<?php echo cleanOutput($block_info['what_is_inquiry']['text_block']); ?>"
					data-title="<?php echo $block_info['what_is_inquiry']['title_block']; ?>"
					title="<?php echo $block_info['what_is_inquiry']['title_block']; ?>"></a>
			</div>
		<?php } ?>

		<?php if (have_right('manage_seller_estimate')) { ?>
			<div class="custom-checkbox-wrap">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-get-estimate")?>>
					<input
						class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
						type="checkbox"
						value="estimate"
						name="purchase_options[]"
						<?php if (isset($item['estimate'])) { echo checked($item['estimate'], 1); } ?>/>
					<span class="custom-checkbox__text">Get Estimate</span>
				</label><a
					class="info-dialog ep-icon ep-icon_info"
					data-message="<?php echo cleanOutput($block_info['what_is_estimate']['text_block']); ?>"
					data-title="<?php echo $block_info['what_is_estimate']['title_block']; ?>"
					title="<?php echo $block_info['what_is_estimate']['title_block']; ?>"></a>
			</div>
		<?php } ?>

		<?php if (have_right('manage_seller_offers')) { ?>
			<div class="custom-checkbox-wrap">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-send-offer")?>>
					<input
						class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
						type="checkbox"
						value="offers"
						name="purchase_options[]"
						<?php if (isset($item['offers'])) { echo checked($item['offers'], 1); } ?>/>
					<span class="custom-checkbox__text">Send Offer</span>
				</label><a
					class="info-dialog ep-icon ep-icon_info"
					data-message="<?php echo cleanOutput($block_info['what_is_offer']['text_block']); ?>"
					data-title="<?php echo $block_info['what_is_offer']['title_block']; ?>"
					title="<?php echo $block_info['what_is_offer']['title_block']; ?>"></a>
			</div>
		<?php } ?>

		<?php if (have_right('create_sample_order')) { ?>
			<div class="custom-checkbox-wrap">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-sample-order")?>>
					<input
						class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
						type="checkbox"
						value="samples"
						name="purchase_options[]"
						<?php if (isset($item['samples'])) { echo checked($item['samples'], 1); } ?>/>
					<span class="custom-checkbox__text">Sample Order</span>
				</label><a
					class="info-dialog ep-icon ep-icon_info"
					data-message="<?php echo cleanOutput($block_info['what_is_sample_order']['text_block']); ?>"
					data-title="<?php echo $block_info['what_is_sample_order']['title_block']; ?>"
					title="<?php echo $block_info['what_is_sample_order']['title_block']; ?>"></a>
			</div>
		<?php } ?>

		<?php if (have_right('manage_seller_po')) { ?>
			<div class="custom-checkbox-wrap">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add__purchase-option-producing-request")?>>
					<input
						class="validate[<?php echo form_validation_rules($validation, 'purchase_options', 'required'); ?>]"
						type="checkbox"
						value="po"
						name="purchase_options[]"
						<?php if (isset($item['po'])) { echo checked($item['po'], 1); } ?>/>
					<span class="custom-checkbox__text">Producing Request</span>
				</label><a
					class="info-dialog ep-icon ep-icon_info"
					data-message="<?php echo cleanOutput($block_info['what_is_producing_request']['text_block']); ?>"
					data-title="<?php echo $block_info['what_is_producing_request']['title_block']; ?>"
					title="<?php echo $block_info['what_is_producing_request']['title_block']; ?>"></a>
			</div>
		<?php } ?>
	</div>
</div>

<div id="js-add-item-dynamic-vin" class="display-n">
	<?php app()->view->display('new/item/add_item/dynamic_vin_view'); ?>
</div>

<script>
	$(function(){
		var $requestTagsSelect = $("#js-suggestions-tags--formfield--tags");
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

		$requestTags
			.next('.tagsinput')
			.addClass('validate[<?php echo form_validation_rules($validation, 'tags', 'required'); ?>]')
			.setValHookType('tagsinput');

		$.valHooks.tagsinput = {
			get: function (el) {
				return $requestTagsSelect.val() || [];
			},
			set: function (el, val) {
				$requestTagsSelect.val(val);
			}
		};

		<?php if (have_right('manage_seller_offers') || have_right('manage_seller_estimate') || have_right('manage_seller_inquiries') || have_right('manage_seller_po')) { ?>
			$('input[type=checkbox]').on('change', function () {
				var $this = $(this);

				if($this.hasClass('validengine-border')){
					$this.removeClass('validengine-border');
				}

				var $purchaseOptions = $this.closest('.custom-checkbox');

				if($purchaseOptions.length){
					$purchaseOptions.find('.validengine-border').removeClass('validengine-border');
					$purchaseOptions.siblings('.js-purchase-options').find('.validengine-border').removeClass('validengine-border');
				}
			});
		<?php } ?>
	});

	//remove attr
	var del_row_attr = function(obj){
		var $this = $(obj);
		$this.closest(".add-info-row").fadeOut('normal', function(){
			$(this).remove();
		});
	}
	//remove attr

	//add field attribute
	var addInputUserAttr = function(obj){
		var $thisBtn = $(obj);
		var $row = $thisBtn.closest('.add-info-row');
		var $u_attr_name = $row.find('input[name="u_attr_name"]');
		var $u_attr_val = $row.find('input[name="u_attr_val"]');
		var name = $u_attr_name.val().trim();
		var val = $u_attr_val.val().trim();

		if($u_attr_name.hasClass('validengine-border') || $u_attr_val.hasClass('validengine-border')){
			return false;
		}

		if(name == "" || val == ""){
			$u_attr_name.val("");
			$u_attr_val.val("");
			systemMessages( "Please fill in the Component and Specification first.", "warning" );
			return false;
		}

		var row = '<div class="add-info-row">\
						<div class="add-info-row__col add-info-row__col--simple">\
							<input class="validate[maxSize[50]]" type="text" name="u_attr[name][]" maxlength="50" value="' + htmlEscape(name) + '" placeholder="Name"/>\
						</div>\
						<div class="add-info-row__col add-info-row__col--simple">\
							<input class="validate[maxSize[50]]" type="text" name="u_attr[val][]" maxlength="50" value="' + htmlEscape(val) + '" placeholder="Value"/>\
						</div>\
						<div class="add-info-row__col add-info-row__col--simple add-info-row__col--130">\
							<div class="add-info-row__actions">\
								<a class="btn btn-light call-function" data-callback="del_row_attr" title="Remove attribute"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
							</div>\
						</div>\
					</div>';
		$('#js-item-additional-information').append(row);

		$u_attr_name.val("");
		$u_attr_val.val("");

		validateTabInit();
	}
	//end add field attribute
</script>
