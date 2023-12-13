<script type="text/javascript" src="<?php echo __SITE_URL ?>public/plug/js/notify/notify-out-of-stock.js"></script>
<?php $cookie = tmvc::instance()->controller->cookies; ?>
<div id="basket-user" class="basket-user">
	<?php if(!empty($companies)){?>
		<?php foreach($companies as $company){ ?>
		<div class="basket-user__item" data-company="<?php echo $company['id_company']?>">
			<div class="basket-user__title">
                <div class="basket-user__title-inner">
                    <div class="basket-user__title-left">
                        <a class="link" target="_blank" href="<?php if(!empty($company['index_name'])) echo __SITE_URL . $company['index_name']; else echo __SITE_URL . 'seller/' . strForURL($company['name_company']) . '-' . $company['id_company'];?>"><?php echo $company['name_company']?></a>

                        <div class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="ep-icon ep-icon_menu-circles"></i>
                            </a>

                            <div class="dropdown-menu">
                                <?php echo !empty($company['btnChat']) ? $company['btnChat'] : ''; ?>

                                <?php if (null !== ($group_key = getBacketItemsKey($company['id_user'], dataGet($items, "{$company['id_user']}.*.id_item")))) { ?>
                                    <a class="dropdown-item"
                                        data-title="Shipping estimates"
                                        href="<?php echo getBacketGroupUrl($group_key, $company['id_user']); ?>"
                                        target="_blank">
                                        <i class="ep-icon ep-icon_truck"></i><span class="txt">Shipping estimate requests</span>
                                    </a>
                                <?php } ?>
                                <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-fancybox-href="<?php echo __SITE_URL;?>shippers/popup_forms/create_estimate/basket"
                                    data-before-callback="get_items"
                                    data-action="by_seller"
                                    data-title="Request shipping estimate"
                                    title="Request shipping estimate">
                                    <i class="ep-icon ep-icon_truck"></i><span class="txt">Request a shipping estimate</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="basket-user__title-nr">
                        <span class="basket-user__title-nr-val"><?php echo count($items[$company['id_user']])?></span> item(s)
                    </div>
                </div>
			</div>

			<ul class="basket-user__list">
			<?php $total_price = 0;
				$total_items_company = count($items[$company['id_user']]);
				foreach($items[$company['id_user']] as $key =>  $item){
                    $total = 0;
                    if(!$item['is_out_of_stock']){
                        (float)$total = $item['price_item']  * $item['quantity'];
                    }
				    $total_price += $total; ?>
                <li class="basket-user__list-item <?php echo $item['is_out_of_stock'] ? 'basket-user--stock-out' : ''?>"
                    id="item-<?php echo $item['id_basket_item']?>"
                    data-item="<?php echo $item['id_item']?>"
                    data-basket-item="<?php echo $item['id_basket_item']?>">
					<div class="flex-card">

                        <!------------------ Item thumbnail ------------------>

						<div class="basket-user__list-img image-card3 flex-card__fixed">
							<span class="link">
								<?php
									$item_img_link = getDisplayImageLink(array('{ID}' => $item['id_item'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
								?>
								<img
									class="image"
									src="<?php echo $item_img_link;?>"
									alt="<?php echo $item['title'] ?>"
								/>
							</span>
                        </div>

						<div class="basket-user__list-detail flex-card__float">

                            <!------------------ Title and options dropdown ------------------>

                            <div class="basket-user__title-row">
                                <a class="basket-user__item-title"
                                   href="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id_item']?>">
                                   <?php echo $item['title']?>
                                </a>
                                <div class="dropdown dropup">
                                    <a class="dropdown-toggle"
                                       data-toggle="dropdown"
                                       aria-haspopup="true"
                                       aria-expanded="false"
                                       data-flip="false"
                                       data-display="static"
                                       href="#">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                           data-title="Start order"
                                           title="Create order by this item"
                                           href="<?php echo __SITE_URL; ?>basket/popup_forms/ship_to/one/<?php echo $item['id_basket_item']?>">
                                            <i class="ep-icon ep-icon_file"></i><span class="txt">Order this item</span>
                                        </a>
                                        <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                           href="<?php echo __SITE_URL;?>shippers/popup_forms/create_estimate/item"
                                           data-before-callback="get_items"
                                           data-item="<?php echo $item['id_item']?>"
                                           data-action="by_item"
                                           data-title="Request shipping estimate"
                                           title="Request shipping estimate">
                                            <i class="ep-icon ep-icon_truck"></i><span class="txt">Request a shipping estimate</span>
                                        </a>
                                        <a class="dropdown-item"
                                           href="<?php echo __SITE_URL;?>item/<?php echo strForURL($item['title']) . '-' . $item['id_item']?>" title="Details item"
                                           target="_blank">
                                            <i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Item detail</span>
                                        </a>
                                        <a class="dropdown-item confirm-dialog"
                                           data-item="<?php echo $item['id_basket_item']?>"
                                           data-callback="removeBasketItem"
                                           data-message="Are you sure you want to delete this item?"
                                           title="Remove item">
                                            <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Remove this item</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <?php if ($item['is_out_of_stock']) { ?>

                                <!------------------ Item stock out ------------------>

                                <div class="basket-user__stock-out">
                                    This item is out of stock.
                                    <a class="confirm-dialog"
                                       data-message="Do you really want to get an email when this item is available?"
                                       data-callback="notifyOutOfStock"
                                       data-resource="<?php echo $item['id_item'];?>"
                                       data-href="<?php echo __SITE_URL . 'items/ajax_item_operation/email_when_available'; ?>">
                                       Click here to be notified when it's available.
                                    </a>
                                    <?php if($item['samples']){?>
                                        <span>Samples are available for this item.
                                            <a class="fancybox.ajax fancyboxValidateModal"
                                            data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/request_order/{$item['id_item']}"); ?>"
                                            data-title="Request & Quote for Sample Order"
                                            title="Request & Quote for Sample Order">
                                            Place a sample order here.
                                            </a>
                                        </span>
                                    <?php }?>
                                </div>
                            <?php } else { ?>

                                <!------------------ Item in stock ------------------>

                                <div class="basket-user__info">
                                    <div class="basket-user__shipping-info">
                                        <div class="basket-user__list-country">Ships from <?php echo $item['country']?></div>
                                        <p class="basket-user__list-txt"><?php echo $item['detail']?></p>
                                    </div>

                                    <div class="basket-user__list-params">
                                        <div class="basket-user__list-param">
                                            <span class="basket-user__list-param-name">Price:</span>
                                            <span class="basket-user__list-param-val"><?php echo get_price( $item['price_item'])?></span>
                                        </div>
                                        <div class="basket-user__list-param">
                                            <span class="basket-user__list-param-name">Quantity:</span>
                                            <span class="basket-user__list-param-val">
                                                <input
                                                    class="quantity-val w-50"
                                                    type="number"
                                                    step="1"
                                                    value="<?php echo $item['quantity']?>"/>
                                            </span>
                                        </div>
                                        <div class="basket-user__list-param">
                                            <span class="basket-user__list-param-name">Total:</span>
                                            <span class="basket-user__list-param-val total-val"><?php echo get_price($total)?></span>
                                        </div>
                                        <?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
                                        <div class="basket-user__list-param basket-user__list-real-price">
                                            <span class="basket-user__list-param-name">Real price:</span>
                                            <span class="basket-user__list-param-val real-val">$ <span class="value"><?php echo get_price($total, false)?></span></span>
                                        </div>
                                        <?php }?>
                                        <input class="minmax-val" type="hidden" name="" value="<?php echo $item['min_sale_q']?>-<?php echo $item['disp_q']?>"/>
                                        <input class="price-val" type="hidden" name="price" value="<?php echo $item['price_item'];?>">
                                    </div>
                                </div>

                            <?php } ?>
						</div>
                    </div>
				</li>
			<?php } ?>
			</ul>

			<div class="basket-user__footer">
				<span class="basket-user__total">
					<span class="name">Total:</span>
					<span class="value"><?php echo get_price($total_price)?></span>
				</span>
				<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
				<span class="basket-user__real-price">
					<span class="name">Real price:</span>
					<span class="value">$ <?php echo get_price($total_price, false)?></span>
				</span>
				<?php }?>
				<a class="btn btn-primary fancybox.ajax fancyboxValidateModal" data-title="Start order" href="<?php echo __SITE_URL; ?>basket/popup_forms/ship_to/all/<?php echo $company['id_user']?>">Order from this seller</a>
			</div>
		</div>
		<?php } ?>
	<?php }else{ ?>
		<div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No items in the basket.</span></div>
	<?php } ?>
</div>

<div id="js-similar-container-wr"></div>

<script>
    var similarList = <?php echo json_encode($similarItems, true) ?? '[]'; ?>;
</script>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/basket/similar-products-slider.js'); ?>"></script>
