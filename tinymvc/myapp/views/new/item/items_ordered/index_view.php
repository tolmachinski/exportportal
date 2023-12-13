<div class="warning-alert-b">
	<i class="ep-icon ep-icon_warning-circle-stroke"></i>
	<span>
		<?php if($item['type'] == 'item'){?>
			This page is a "Snapshot" of the <strong>"<?php echo $item['title']?>"</strong> item, generated  on <strong>"<?php echo formatDate($item['date_created'])?>"</strong>.
			You can view the current item <a class="txt-bold" href="<?php echo __SITE_URL;?>item/<?php echo strForURL($item["title"]).'-'.$item["id_item"]; ?>">here</a>.
		<?php }elseif($item['type'] == 'prototype'){?>
			This page is a "Snapshot" of the <strong>"Prototype"</strong> item, generated  on <strong>"<?php echo $item['date_created']?>"</strong>.
			You can view the current prototype <a class="txt-bold hover-underline" href="<?php echo __SITE_URL;?>prototype/item/<?php echo $item["additional_id"]; ?>" target="_blank">here</a> and current item <a class="txt-bold hover-underline" href="<?php echo __SITE_URL;?>item/<?php echo strForURL($item["title"]).'-'.$item["id_item"]; ?>" target="_blank">here</a>.
		<?php }?>
	</span>
</div>

<div class="product__header-top">
	<div class="title-public">
		<h1 class="title-public__txt"><?php echo $item['title']?></h1>
	</div>
</div>

<div class="product__flex-card">
	<div class="product__flex-card-fixed">
		<div class="wr-item-gallery">
            <?php $pathSnapshot = getDisplayImageLink(array('{ID}' => $item['id_snapshot'], '{FILE_NAME}' => $item['main_image']), 'items.snapshot');?>
			<ul class="item-gallery">
				<li class="image-card3">
					<a
                        class="link fancyboxGallery"
                        href="<?php echo $pathSnapshot;?>"
                    >
						<img
                            class="image"
                            src="<?php echo $pathSnapshot;?>"
                            alt="<?php echo $item['title']?>"
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
					<div class="js-product-price-new product__price-new"><?php echo get_price($item['price']);?></div>

					<div class="product__param-val">
						<span> / <?php echo $item['unit_name'] ?></span> <br>
						<?php if(cookies()->getCookieParam('currency_key') !== 'USD'){?>
						<span>*Real price for payment is $ <span class="js-item-real-price"><?php echo get_price($item['price'], false);?></span></span>
						<?php } ?>
					</div>
				</div>
			</div>

			<?php if(
				isset($member_of_order)
				&& (bool) $member_of_order
				&& !empty($item['detail_ordered'])){?>
			<div class="product__param">
				<span class="product__param-name">Ordered item details:</span>
				<div class="product__param-detail">
					<div class="fs-18"><?php echo $item['detail_ordered']?></div>
				</div>
			</div>
			<?php }?>

			<div class="product__param">
				<span class="product__param-name">Departure point:</span>
				<div class="product__param-detail">
					<div class="fs-18"><?php echo $item['country']?></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="product__flex-card">
	<div class="product__flex-card-fixed">

		<div class="title-public">
			<h2 class="title-public__txt">Sold by</h2>
		</div>

		<?php tmvc::instance()->controller->view->display('new/directory/list_item_view', array('item' => $company_info));?>

		<?php if (!empty($last_viewed_items)) { ?>
			<div class="title-public">
				<h2 class="title-public__txt">Last viewed</h2>
			</div>
			<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js');?>"></script>
			<script type="text/javascript">
				$(document).ready(function() {
					$('#last-viewed').hideMaxListItems();
				});
			</script>
			<div id="last-viewed" class="products-mini">
			<?php foreach ($last_viewed_items as $key => $viewed_item) {
					$link_last_viewed = __SITE_URL.'item/'.strForURL($viewed_item['title']).'-'. $key;
					tmvc::instance()->controller->view->display('new/item/list_mini_item_view', array('mini_link' => $link_last_viewed, 'mini_item' => $viewed_item));
			}?>
			</div>
		<?php } ?>
	</div>

	<div class="product__flex-card-float pt-40">
		<?php if(!empty($aditional_info)) {?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Product information</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<ul class="product-info">
				<?php if($item['type'] == 'item'){
					if(!empty($aditional_info['attr_info'])) {
							foreach($aditional_info['attr_info'] as $attr_info) {
								$attr_info = explode(':',$attr_info);
							?>
							<li class="product-info__item">
								<span class="product-info__name"><?php echo $attr_info[0]?>:</span>
								<span class="product-info__value"><?php echo $attr_info[1]?></span>
							</li>
					<?php	}
						}else{ ?>
							<li class="w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> No additional info.</div></li>
					<?php } ?>

				<?php }elseif($item['type'] == 'prototype'){
						foreach($aditional_info as $key => $attr_info) {?>
						<li class="product-info__item">
							<span class="product-info__name"><?php echo $key?>:</span>
							<span class="product-info__value"><?php echo $attr_info['current_value']?></span>
						</li>
				<?php }
						} ?>
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
				<ul>
				<?php foreach($aditional_info['vin_info'] as $vin_attr){
					$vin_attr = explode(':',$vin_attr);
				?>
					<li class="identifier"><span class="type"><?php echo $vin_attr[0]?></span>:<strong class="value"><?php echo $vin_attr[1]?></strong></li>
				<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<?php if(!empty($item['description'])){?>
		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Description</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<div class="ep-tinymce-text">
					<?php echo $item['description']?>
				</div>
			</div>
		</div>
		<?php } ?>

		<div class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name">Photos</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<img
                    class="photo"
                    src="<?php echo $pathSnapshot;?>"
                    alt="<?php echo $item['title']?>"
                />
			</div>
		</div>
	</div>
</div>
