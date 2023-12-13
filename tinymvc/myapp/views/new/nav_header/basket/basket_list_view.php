<div class="popup-basket-wr">
<?php if(!empty($companies)){?>
	<?php foreach($companies as $company){ ?>
	<div id="HeaderBasket-<?php echo $company['id_user']?>" class="popup-basket">
		<?php $total = 0;?>
		<div class="popup-basket__title">
			<div class="popup-basket__title-name">
				Items by
				<a class="name" target="_blank" href="<?php echo __SITE_URL;?>basket/my/<?php echo $company['id_user'];?>">
					<?php echo $company['name_company'];?>
				</a>
			</div>

			<div class="popup-basket__title-count">
				<span><?php echo count($items[$company['id_user']])?></span>
				items
			</div>
		</div>

		<ul class="popup-basket-list">

		<?php
            $total = 0;
            foreach($items[$company['id_user']] as $item){
                if(!$item['is_out_of_stock']){
                    $total += $item['price_item'] * $item['quantity'];
                }?>
            <li class="popup-basket-list__item <?php echo $item['is_out_of_stock']  ? 'popup-basket-list--stock-out' : ''?>"
                data-item="<?php echo $item['id_basket_item']?>">
				<div class="popup-basket-list__detail flex-card">
					<div class="popup-basket-list__img flex-card__fixed image-card3">
						<a class="link" href="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id_item']?>" target="_blank">
							<?php $item_img_link = getDisplayImageLink(array('{ID}' => $item['id_item'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));?>
							<img
								class="image"
								src="<?php echo $item_img_link; ?>"
								alt="<?php echo $item['title'] ?>"
							/>
						</a>
					</div>
					<div class="popup-basket-list__text flex-card__float">

                        <div class="popup-basket-list__header">
                            <div class="popup-basket-list__name">
                                <a class="link"
                                   href="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id_item']?>" target="_blank">
                                        <?php echo $item['title']?>
                                </a>
                            </div>

                            <div class="dropdown dropup">
								<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu dropdown-menu-right">
                                    <?php if(in_array($item['id_item'], $saved_items)){?>
                                        <button
                                            class="js-products-favorites-btn dropdown-item call-action"
                                            title="<?php echo translate('item_card_remove_from_favorites_tag_title', null, true);?>"
                                            data-js-action="favorites:remove-product"
                                            data-item="<?php echo $item['id_item'];?>"
                                            type="button"
                                        >
                                            <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite', [17, 17]);?></i>
                                            <span>Favorited</span>
                                        </button>
                                    <?php }else{?>
                                        <button
                                            class="js-products-favorites-btn dropdown-item call-action"
                                            title="<?php echo translate('item_card_add_to_favorites_tag_title', null, true);?>"
                                            data-js-action="favorites:save-product"
                                            data-item="<?php echo $item['id_item'];?>"
                                            type="button"
                                        >
                                            <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite-empty', [17, 17]);?></i>
                                            <span>Favorite</span>
                                        </button>
                                    <?php }?>

                                    <button
                                        class="dropdown-item call-function call-action"
                                        title="Share"
                                        data-callback="userSharePopup"
                                        data-js-action="user:share-popup"
                                        data-type="item"
                                        data-item="<?php echo $item['id_item'];?>"
                                        type="button"
                                    >
                                        <i class="ep-icon ep-icon_share-stroke3"></i> Share this
                                    </button>

									<a class="dropdown-item confirm-dialog" href="#" data-item="<?php echo $item['id_basket_item']?>" data-callback="removeBasketItemPopup" data-js-action="basket:remove-item" data-message="Are you sure you want to delete this item?">
										<i class="ep-icon ep-icon_trash-stroke"></i>
										<span><?php echo translate('header_navigation_popup_basket_link_item_remove');?></span>
									</a>
								</div>
							</div>
                        </div>

                        <?php if ($item['is_out_of_stock']) { ?>
                            <div class="popup-basket-list__stock-out">
                                This item is out of stock.
                                <a
                                    class="confirm-dialog"
                                    data-message="Do you really want to get an email when this item is available?"
                                    data-callback="notifyOutOfStock"
                                    data-resource="<?php echo $item['id_item'];?>"
                                    data-href="<?php echo __SITE_URL . 'items/ajax_item_operation/email_when_available'; ?>">
                                    Click here to be notified when it's available.
                                </a>
                                <?php if($item['samples']) { ?>
                                    <span>Samples are available for this item.
                                        <a
                                            class="fancybox.ajax fancyboxValidateModal"
                                            data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/request_order/{$item['id_item']}"); ?>"
                                            data-title="Request & Quote for Sample Order"
                                            title="Request & Quote for Sample Order">Place a sample order here.
                                        </a>
                                    </span>
                                <?php }?>
                            </div>
                        <?php } else { ?>
                            <div class="popup-basket-list__additional">
                                <?php if(!empty($item['detail'])){?>
                                    <?php echo $item['detail']?>
                                <?php }?>
                            </div>

                            <div class="popup-basket-list__actions">
                                <div class="popup-basket-list__price">
                                    <span class="txt-gray"><?php echo $item['quantity']?> x</span>
                                    <span><?php echo get_price($item['price_item'])?></span>
                                </div>
                            </div>
                        <?php } ?>
					</div>
				</div>
			</li>
		<?php } ?>
		</ul>

		<div class="popup-basket__total">
			<div class="popup-basket__total-price">
				<span class="txt-gray txt-normal">Total:</span>
				<?php echo get_price($total);?>
			</div>

			<!-- <a class="btn btn-light w-200 fancybox.ajax fancyboxValidateModal" data-mw="450" data-title="Ship to:" href="<?php echo __SITE_URL; ?>basket/popup_forms/ship_to/all/<?php echo $company['id_user']?>">Start order</a> -->
			<a class="btn btn-primary w-200" href="<?php echo __SITE_URL; ?>basket/my/<?php echo $company['id_company']?>">Start order</a>
		</div>
	</div>
	<?php } ?>
<?php }else{ ?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> No items in the basket.</div>
<?php } ?>
</div>

<?php
    if (isset($webpackData) && $webpackData) {
        echo dispatchDynamicFragment("popup:basket-popup", null, true);
    } else {
?>
    <script src="<?php echo asset(__SITE_URL . "public/plug/js/notify/notify-out-of-stock.js", "legacy"); ?>"></script>
    <script src="<?php echo asset(__SITE_URL . "public/plug/js/basket/remove-item.js", "legacy"); ?>"></script>
<?php } ?>
