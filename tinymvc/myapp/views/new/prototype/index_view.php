<?php if(isset($activated) && $activated == false){?>
	<div class="warning-alert-b mt-50"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <span><?php echo $message; ?></span></div>
<?php }else{?>
<script type="text/javascript">
	<?php if(have_right('buy_item') && ($prototype["status_prototype"] == 'in_progress')){?>
		var decline_prototype = function(obj){
			var $this = $(obj);
			var prototype = $this.data('prototype');

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>prototype/ajax_prototype_operation/decline_prototype',
				data: { prototype : <?php echo $prototype['id_prototype'];?> },
				beforeSend: function(){  },
				dataType: 'json',
				success: function(data){
					systemMessages( data.message, data.mess_type );
					if(data.mess_type == 'success')
						$this.parent('div').remove();
				}
			});
		};

		var confirm_prototype = function(obj){
			var $this = $(obj);

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>prototype/ajax_prototype_operation/confirm_prototype',
				data: { prototype : <?php echo $prototype['id_prototype'];?> },
				beforeSend: function(){  },
				dataType: 'json',
				success: function(data){
					systemMessages( data.message, data.mess_type );
					if(data.mess_type == 'success')
						$this.parent('div').remove();
				}
			});
		};
	<?php }?>
</script>

<div class="info-alert-b">
	<i class="ep-icon ep-icon_info-stroke"></i>
	<span>
		This page is a "Prototype" of the <strong>"<?php echo $prototype['title']?>"</strong> item, generated  on <strong>"<?php echo formatDate($prototype['date']);?>"</strong>.
		You can view the current item <a class="txt-bold" href="<?php echo __SITE_URL;?>item/<?php echo strForURL($prototype["title"]).'-'.$prototype["id_item"]; ?>" target="_blank">here</a>.
	</span>
</div>

<div class="product__header-top">
	<div class="title-public">
		<h1 class="title-public__txt"><?php echo $prototype['title']?></h1>
	</div>
</div>

<?php $item_prototype_img_link = getDisplayImageLink(array('{ID}' => $prototype['id_prototype'], '{FILE_NAME}' => $prototype['image']), 'items.prototype'); ?>
<div class="product__flex-card">
	<div class="product__flex-card-fixed">
		<div class="wr-item-gallery">
			<ul class="item-gallery">
				<li class="image-card3">
					<a
                        class="link fancyboxGallery"
                        href="<?php echo $item_prototype_img_link;?>"
                    >
						<img
                            class="image"
                            src="<?php echo $item_prototype_img_link;?>"
                            alt="<?php echo $prototype['title']?>"
                        />
					</a>
				</li>
			</ul>
		</div>
	</div>

	<div class="product__flex-card-float z-1">
		<div class="detail-info">
			<div class="product__param">
				<span class="product__param-name">Price:</span>
				<div class="product__param-detail">
					<div class="js-product-price-new product__price-new"><?php echo get_price($prototype['price']);?></div>
				</div>
			</div>

			<div class="product__param">
				<span class="product__param-name">Quantity:</span>
				<div class="product__param-detail">
					<div class="fs-18"><?php echo $prototype['quantity']?></div>
				</div>
			</div>

			<div class="product__param">
				<span class="product__param-name">Size (LxWxH, cm):</span>
				<div class="product__param-detail">
					<div class="fs-18"><?php echo $prototype['prototype_length']?> x <?php echo $prototype['prototype_width']?> x <?php echo $prototype['prototype_height']?></div>
				</div>
			</div>

			<div class="product__param">
				<span class="product__param-name">Weight (kg):</span>
				<div class="product__param-detail">
					<div class="fs-18"><?php echo $prototype['prototype_weight']?></div>
				</div>
			</div>
		</div>


		<?php if(is_my($prototype['id_buyer']) && $prototype['status_prototype'] == 'in_progress'){?>
			<div class="tac">
				<a class="btn btn-primary btn-lg w-200 confirm-dialog" data-message="Are you sure you want to Confirm this prototype?" data-callback="confirm_prototype">Confirm</a>
				<a class="btn btn-light btn-lg w-200 confirm-dialog" data-message="Are you sure you want to Decline this prototype?" data-callback="decline_prototype">Decline</a>
			</div>
		<?php }?>
	</div>
</div>

<div class="product__flex-card">
	<div class="product__flex-card-fixed">

		<div class="title-public">
			<h2 class="title-public__txt">Sold by</h2>
		</div>

		<div class="pb-40">
			<?php tmvc::instance()->controller->view->display('new/directory/list_item_view', array('item' => $company_info));?>
		</div>

		<div class="title-public">
			<h2 class="title-public__txt">Prototype timeline</h2>
		</div>

		<ul class="mh-600 overflow-y-a">
			<?php foreach($prototype['log'] as $key => $prototype_timeline){ ?>
				<li class="flex-display flex-jc--sb mb-5 pb-5 bdb-1-gray">
					<div>"<?php echo $prototype_timeline['message']; ?>"</div>
					<div class="w-80 tar lh-20 txt-gray fs-12"> <?php echo formatDate($prototype_timeline['date']); ?></div>
				</li>
			<?php }?>
		</ul>
	</div>

	<div class="product__flex-card-float pt-40">
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Changes</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<ul class="product-info">
					<?php if (!empty($prototype['changes'])) { ?>
						<?php foreach (arrayGet($prototype, 'changes', array()) as $change) { ?>
							<li class="product-info__item">
								<span class="product-info__name"><?php echo $change['name']; ?>:</span>
								<span class="product-info__value">
									<?php echo $change['current_value']; ?>
									<?php if (!empty($change['old_values'])) { ?>
										<span class="cur-pointer ep-icon ep-icon_visible fs-16 txt-blue2" data-trigger="hover" data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-content="<strong>Previous values:</strong> <br /><?php echo $change['old_values']; ?>"></span>
									<?php } ?>
								</span>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if(!empty($aditional_info['attr_info'])){?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Product information</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<ul class="product-info">
					<?php foreach($aditional_info['attr_info'] as $attr_info){?>
						<?php $attr_info = explode(':',$attr_info);?>
						<li class="product-info__item">
							<span class="product-info__name"><?php echo $attr_info[0];?>:</span>
							<span class="product-info__value"><?php echo $attr_info[1]?></span>
						</li>
					<?php }?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<?php if(!empty($aditional_info['vin_info'])){?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">VIN decoded</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<table class="table table-striped">
				<?php foreach($aditional_info['vin_info'] as $vin_attr){?>
					<?php $vin_attr = explode(':',$vin_attr);?>
					<tr class="identifier">
						<td class="type txt-bold w-50pr"><?php echo $vin_attr[0];?></td>
						<td class="value"><?php echo $vin_attr[1];?></td>
					</tr>
				<?php }?>
				</table>
			</div>
		</div>
		<?php } ?>

		<?php if(!empty($prototype['description'])){?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Description</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<div class="ep-tinymce-text">
					<?php echo $prototype['description']?>
				</div>
			</div>
		</div>
		<?php }?>

		<?php if($prototype['image']){?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Photos</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
                <img class="photo image-item" src="<?php echo getDisplayImageLink(array('{ID}' => $prototype['id_prototype'], '{FILE_NAME}' => $prototype['image']), 'items.prototype');?>" alt="<?php echo $item['title']?>"/>
			</div>
		</div>
		<?php }?>
	</div>
</div>
<?php }?>
