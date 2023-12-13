<?php $cookie = tmvc::instance()->controller->cookies; ?>

<div id="order-detail-<?php echo $offer['id_offer']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($offer['id_offer']);?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status <?php echo $offer_status['title_color'];?>">
							<span class="info-dialog-100 cur-pointer" data-title="What's next in <span class='txt-gray'><?php echo $offer_status['title'];?></span>" data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions"><?php echo $offer_status['title'];?></span>
						</div>

						<div class="display-n">
							<div id="js-hidden-status-text">
								<div class="order-status__content">
									<?php if(!empty($offer_status['whats_next'])){?>
										<?php if(!empty($offer_status['whats_next'][$offer_status_user]['text']['mandatory'])){?>
											<?php echo $offer_status['whats_next'][$offer_status_user]['text']['mandatory'];?>
										<?php }?>

										<?php if(!empty($offer_status['whats_next'][$offer_status_user]['text']['optional'])){?>
											<h3 class="order-status__ttl">Optional</h3>
											<?php echo $offer_status['whats_next'][$offer_status_user]['text']['optional'];?>
										<?php }?>
									<?php } else if(!empty($offer_status['description'])){?>
										<?php echo $offer_status['description'];?>
									<?php }?>
								</div>
							</div>

							<div id="js-hidden-status-actions">
								<?php if(!cookies()->exist_cookie('_ep_view_offer_status')){?>
									<div class="js-order-status-modal inputs-40 order-status__btns flex-ai--c">
										<label class="custom-checkbox">
											<input class="js-dont-show-more" type="checkbox" name="dont_show_more">
											<span class="custom-checkbox__text">Don't show more</span>
										</label>

										<a class="btn btn-dark js-btn-close w-130" href="#">Ok</a>
									</div>
								<?php }?>
							</div>
						</div>
					</div>
				</li>

				<li class="order-detail__params-item">
					<div class="order-detail__params-name">Remaining:</div>
					<div class="order-detail__time">
						<?php if($offer['state_buyer'] == 1 && $offer['state_seller'] == 1){?>
							<div class="lh-20 txt-red">This Offer will be deleted on <?php echo getDateFormat(date_plus(180, 'days', $offer['update_op']), "Y-m-d H:i:s"); ?></div>
						<?php } else if($offer['status'] == 'initiated'){?>
							<?php if(have_right('make_offers') && $offer['state_buyer'] == 1 || have_right('manage_seller_offers') && $offer['state_seller'] == 1){?>
								<div class="order-detail__status-timer"></div>
							<?php } else{?>
								<div class="lh-20 txt-red">This Offer will be moved to archive on <?php echo getDateFormat(date_plus(7, 'days', $offer['update_op']), "Y-m-d H:i:s"); ?></div>
							<?php }?>
						<?php } else{?>
							<div class="order-detail__status-timer"></div>
						<?php }?>
					</div>
				</li>
			</ul>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<span class="btn btn-light btn-block info-dialog-100" data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($offer_status['title']);?></span>"  data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions" href="#">
						<i class="ep-icon ep-icon_info txt-gray fs-16"></i>
						<span class="pl-5">What's next</span>
					</span>
				</div>

				<?php if(have_right('manage_messages')){?>
					<div class="order-detail__top-btns-item">
						<div class="dropdown">
							<a class="btn btn-light btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ep-icon txt-gray ep-icon_chat2 fs-16"></i>
								<span class="pl-5 pr-5">Write to</span>
								<i class="ep-icon ep-icon_arrow-down fs-9"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<?php echo !empty($btnChatSeller) ? $btnChatSeller : ''; ?>
								<?php echo !empty($btnChatBuyer) ? $btnChatBuyer : ''; ?>
							</div>
						</div>
					</div>
				<?php }?>

				<div class="order-detail__top-btns-item">
					<div class="dropdown">
						<a class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
							<span class="pl-5">Action on Offer</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php $status_finished = array('accepted','expired','declined','initiated','archived');
								$nodetail_actions = true;
								if((have_right('manage_seller_offers') || have_right('buy_item')) && !in_array($offer['status'],$status_finished) ){?>

								<?php if(have_right('buy_item') && $offer['status'] == 'wait_buyer' || have_right('manage_seller_offers') && in_array($offer['status'], array('new', 'wait_seller'))){?>
								<a class="dropdown-item confirm-dialog" href="#" data-offer="<?php echo $offer['id_offer'];?>" data-message="Are you sure you want to accept this offer?" data-callback="accept_offer">
									<i class="ep-icon ep-icon_ok-circle"></i>
									<span class="txt">Accept</span>
								</a>
								<?php }?>

								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>offers/popup_forms/resend_offer/<?php echo $offer['id_offer'];?>" data-title="Discuss the Offer">
									<i class="ep-icon ep-icon_comments-stroke"></i>
									<span class="txt">Discuss</span>
								</a>

								<a class="dropdown-item confirm-dialog" href="#" data-offer="<?php echo $offer['id_offer'];?>" data-message="You can not restore a Declined offer. Are you sure you want to Decline this offer?" data-callback="decline_offer">
									<i class="ep-icon ep-icon_remove-circle"></i>
									<span class="txt">Decline</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if($offer['status'] == 'accepted'){?>
								<?php if(have_right('manage_seller_offers')){?>
									<span class="dropdown-item">
										<div class="info-alert-b">
											<i class="ep-icon ep-icon_info-stroke"></i>
											<span class="lh-30">Waiting for buyer to start the order.</span>
										</div>
									</span>
									<?php $nodetail_actions = false;?>
								<?php }?>

								<?php if(have_right('buy_item')){?>
									<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL; ?>offers/popup_forms/ship_to/<?php echo $offer['id_offer'];?>" data-title="Start order">
										<i class="ep-icon ep-icon_marker-stroke2"></i>
										<span class="txt">Start Order</span>
									</a>
									<?php $nodetail_actions = false;?>
								<?php }?>
							<?php }?>

							<?php
								if(have_right('buy_item')){
									$user_state = 'state_buyer';
								} else{
									$user_state = 'state_seller';
								}
							?>
							<?php if(in_array($offer['status'], array('initiated', 'declined', 'expired')) && $offer[$user_state] == 0){?>
								<a class="dropdown-item confirm-dialog" href="#" data-offer="<?php echo $offer['id_offer'];?>" data-message="Are you sure you want to archive this offer?" data-callback="archive_offer">
									<i class="ep-icon ep-icon_folder"></i>
									<span class="txt">Add to archive</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if(in_array($offer['status'], array('initiated', 'declined', 'expired')) && in_array($offer[$user_state], array(0,1))){?>
								<a class="dropdown-item confirm-dialog" href="#" data-offer="<?php echo $offer['id_offer'];?>" data-message="Are you sure you want to delete this offer?" data-callback="delete_offer">
									<i class="ep-icon ep-icon_trash-stroke"></i>
									<span class="txt">Delete</span>
								</a>
								<?php $nodetail_actions = false;?>
							<?php }?>

							<?php if($nodetail_actions){?>
								<span class="dropdown-item">No available actions</span>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<ul>
				<?php if(have_right('make_offers')){?>
					<li class="order-detail__params-item">
						<div class="order-detail__params-name">Seller:</div>
						<div class="order-detail__time">
							<a class="link-black" href="<?php echo getCompanyURL($seller_info);?>" target="_blank" title="<?php echo cleanOutput($seller_info['name_company']);?> contact page">
								<img
									class="h-20 vam"
									src="<?php echo getDisplayImageLink(array('{ID}' => $seller_info['id_company'], '{FILE_NAME}' => $seller_info['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));?>"
									alt="<?php echo cleanOutput($seller_info['name_company']);?>">
								<?php echo $seller_info['name_company'];?>
							</a>

							<?php echo !empty($btnChatSeller2) ? $btnChatSeller2 : ''; ?>
						</div>
					</li>
				<?php } elseif(have_right('manage_seller_offers')){?>
					<li class="order-detail__params-item">
						<div class="order-detail__params-name">Buyer:</div>
						<div class="order-detail__time">
							<a class="link-black" href="<?php echo getUserLink($buyer_info['user_name'], $buyer_info['idu'], 'buyer');?>">
								<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $buyer_info['idu'], '{FILE_NAME}' => $buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $buyer_info['user_group'] ));?>" alt="<?php echo cleanOutput($buyer_info['user_name']);?>" />
								<?php echo $buyer_info['user_name'];?>
							</a>

							<?php echo !empty($btnChatBuyer2) ? $btnChatBuyer2 : ''; ?>
						</div>
					</li>
				<?php }?>
			</ul>

			<div class="order-detail__ship"></div>

			<table class="order-detail-table order-detail__table">
				<thead>
					<tr>
						<th>Product name</th>
						<th class="w-75 tar">Quantity</th>
						<th class="w-135 tar">Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<a class="order-detail__prod-link" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($offer['title']).'-'.$offer['id_item']; ?>" target="_blank"><?php echo $offer['title']?></a>
							<div class="order-detail__product-rating">
								<?php if(!empty($offer['detail_item'])){?>
									<span class="order-detail__product-rating-item">
										<?php echo $offer['detail_item'];?>
									</span>
								<?php }?>
							</div>
						</td>
						<td><?php echo $offer['quantity']?></td>
						<td>
							<?php echo get_price($offer['new_price']);?>
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*
							<?php }?>
						</td>
					</tr>
					<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
					<tr>
						<td colspan="4">*<span class="fs-10">Real price for payment is $ <?php echo get_price($offer['new_price'], false);?></span></td>
					</tr>
					<?php }?>
				</tbody>
			</table>

			<?php if(!empty($offer['comments'])){?>
				<table class="table table-bordered mt-25">
					<caption class="tac mb-10"><i class="ep-icon ep-icon_clock fs-16 vat lh-22"></i> Offer timeline</caption>
					<thead>
						<tr>
							<th class="w-250 tac">Date</th>
							<th class="w-100 tac">User</th>
							<th class="tac">Offer</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($offer['comments'] as $offer_timeline){ ?>
						<tr>
							<td class="tac"><?php echo getDateFormat($offer_timeline['date']); ?></td>
							<td class="tac"><?php echo $offer_timeline['user'];?></td>
							<td class="tac">
								<?php echo get_price($offer_timeline['price']); ?> for <?php echo $offer_timeline['quantity']; ?> item(s)

								<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
									div class="fs-10 lh-12">*Real price for payment is $ <?php echo get_price($offer['new_price'], false);?></div>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="fs-14 bdb-1-black" colspan="3">
								<?php echo $offer_timeline['message']; ?>
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			<?php }?>
		</div>
	</div>
</div>

<script>
	(function() {
		"use strict";

		window.vatingOfferStatusModal = ({
			init: function (params) {
				vatingOfferStatusModal.self = this;
				vatingOfferStatusModal.dontShow = false;
				vatingOfferStatusModal.$modal = $(params[0]);
				vatingOfferStatusModal.$hiddenOfferStatusActionsModal = $('#js-hidden-status-actions');
				vatingOfferStatusModal.$mainOfferStatusModal = vatingOfferStatusModal.$modal.find('.js-order-status-modal');

				vatingOfferStatusModal.self.initListiners();
				// vatingOfferStatusModal.self.openVaitingActivation();
			},
			initListiners: function(){
				vatingOfferStatusModal.$mainOfferStatusModal.on('click', '.js-btn-close', function(e){
					e.preventDefault();
					var $this = $(this);
                    vatingOfferStatusModal.dontShow = $('.modal .js-dont-show-more').prop('checked');
					vatingOfferStatusModal.self.closeAndSetOrderStatusView($this);
				});
			},
			closeAndSetOrderStatusView: function(){
				if(
					vatingOfferStatusModal.dontShow
					&& !existCookie('_ep_view_offer_status')
				){
					vatingOfferStatusModal.$hiddenOfferStatusActionsModal.html("");
					setCookie('_ep_view_offer_status', 1, 7);
				}

				BootstrapDialog.closeAll();
			}
		});

	}());

	var ifExitsOfferStatusView = function(){
		if(!existCookie('_ep_view_offer_status')){
			if($('.order-detail__status .info-dialog-100').length){
				$('.order-detail__status .info-dialog-100').trigger('click');
			}
		}
	}

	var showStatusModal = function(dialog){
		window.vatingOfferStatusModal.init(dialog.getModalFooter());
	}

	$(function(){
		$('.order-detail').on('click', ".info-dialog-100", function(e){
			var $thisBtn = $(this);
			e.preventDefault();

			var storedMessage = $thisBtn.data('message') || null;
			var storedContent = $thisBtn.data('content') || null;
			var storedActions = $thisBtn.data('actions') || null;
			var message = '';
			var actions = '';

			if(null !== storedMessage){
				message = storedMessage;
			} else if(null !== storedContent){
				message = $(storedContent).html();
			}

			if(null !== storedActions){
				actions = ($(storedActions).html() || '').trim();
			}

			open_info_dialog_100($thisBtn.data('title'), message, actions);
		});

		$('.order-popover').popover({
			container: 'body',
			trigger: 'hover'
		});

		if(($('.order-detail-table').length > 0) && ($(window).width() < 768)){
			$('.order-detail-table').addClass('order-detail__table--mobile');
		}

		mobileDataTable($('.order-detail-table'));

		ifExitsOfferStatusView();
	});

	jQuery(window).on('resizestop', function () {

		if($('.order-detail-table').length > 0){
			if($(window).width() < 768){
				$('.order-detail-table').addClass('order-detail__table--mobile');
			}else{
				$('.order-detail-table').removeClass('order-detail__table--mobile');
			}
		}
	});
</script>
