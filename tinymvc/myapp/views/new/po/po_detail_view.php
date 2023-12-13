<?php $cookie = tmvc::instance()->controller->cookies; ?>

<div id="order-detail-<?php echo $po['id_po']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-det\ail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($po['id_po']);?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status <?php echo $producing_request_status['title_color'];?>">
							<span class="info-dialog-100 cur-pointer" data-title="What's next in <span class='txt-gray'><?php echo $producing_request_status['title'];?></span>" data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions"><?php echo $producing_request_status['title'];?></span>
						</div>

						<div class="display-n">
							<div id="js-hidden-status-text">
								<div class="order-status__content">
									<?php if(!empty($producing_request_status['whats_next'])){?>
										<?php if(!empty($producing_request_status['whats_next'][$producing_request_status_user]['text']['mandatory'])){?>
											<?php echo $producing_request_status['whats_next'][$producing_request_status_user]['text']['mandatory'];?>
										<?php }?>

										<?php if(!empty($producing_request_status['whats_next'][$producing_request_status_user]['text']['optional'])){?>
											<h3 class="order-status__ttl">Optional</h3>
											<?php echo $producing_request_status['whats_next'][$producing_request_status_user]['text']['optional'];?>
										<?php }?>
									<?php } else if(!empty($producing_request_status['description'])){?>
										<?php echo $producing_request_status['description'];?>
									<?php }?>
								</div>
							</div>

							<div id="js-hidden-status-actions">
								<?php if(!cookies()->exist_cookie('_ep_view_producing_request_status')){?>
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
			</ul>

			<div class="order-detail__top-btns">
				<div class="order-detail__top-btns-item">
					<span class="btn btn-light btn-block info-dialog-100" data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($producing_request_status['title']);?></span>"  data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions" href="#">
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
							<span class="pl-5 pr-5">Producing Request's Actions</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php $finished_status = array('declined','order_initiated','archived'); ?>
							<?php if(have_right('buy_item') && ($po['status'] == 'prototype_confirmed')){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL; ?>po/popup_forms/ship_to/<?php echo $po['id_po'];?>" data-title="Start order">
									<i class="ep-icon ep-icon_marker-stroke2"></i>
									<span class="txt">Start order</span>
								</a>
							<?php }?>

							<?php if(have_right('manage_seller_po') && $po['status'] == 'initiated'){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to activate the prototype?" data-callback="activate_prototype" data-prototype="<?php echo $po['id_prototype'];?>" href="#" data-title="Activate the Prototype">
									<i class="ep-icon ep-icon_ok-circle"></i>
									<span class="txt">Activate the Prototype</span>
								</a>
							<?php }?>

							<?php if(have_right('manage_seller_po') && $prototype['status_prototype'] == 'in_progress'){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>prototype/popup_forms/edit_prototype/<?php echo $prototype['id_prototype'];?>" data-title="Edit the Prototype">
									<i class="ep-icon ep-icon_pencil"></i>
									<span class="txt">Edit the Prototype</span>
								</a>
							<?php }?>

							<?php if($prototype['changed'] == 0){?>
								<a class="dropdown-item" href="<?php echo __SITE_URL; ?>prototype/item/<?php echo $prototype['id_prototype'];?>" target="_blank">
									<i class="ep-icon ep-icon_file-text"></i>
									<span class="txt">View the Prototype</span>
								</a>
							<?php }?>

							<?php if(!in_array($po['status'], $finished_status)){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>po/popup_forms/resend_po/<?php echo $po['id_po'];?>" data-title="Discuss Producing Request">
									<i class="ep-icon ep-icon_comments-stroke"></i>
									<span class="txt">Discuss</span>
								</a>
								<a class="dropdown-item confirm-dialog" data-po="<?php echo $po['id_po'];?>" href="#" data-message="Are you sure you want declined this Producing Request?" data-callback="change_status" data-action="declined_po">
									<i class="ep-icon ep-icon_remove-circle"></i>
									<span class="txt">Decline</span>
								</a>
							<?php }?>

							<?php if(have_right('buy_item')){
									$user_state = 'state_buyer';
								} elseif(have_right('manage_seller_po')){
									$user_state = 'state_seller';
								}?>

							<?php if($po['status'] == 'order_initiated' && $po[$user_state] == 0){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want add to archive this Producing Request?" data-callback="change_status" data-action="archived_po" data-po="<?php echo $po['id_po'];?>" href="#">
									<i class="ep-icon ep-icon_folder"></i>
									<span class="txt">Add to archive</span>
								</a>
							<?php }?>

							<?php if(in_array($po['status'], $finished_status) && in_array($po[$user_state], array(0,1))){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this Producing Request?" data-callback="remove_po" data-po="<?php echo $po['id_po'];?>" href="#">
									<i class="ep-icon ep-icon_trash-stroke"></i>
									<span class="txt">Delete</span>
								</a>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="order-detail__scroll-padding">
			<ul>
				<?php if(have_right('buy_item')){?>
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
				<?php } elseif(have_right('manage_seller_inquiries')){?>
					<li class="order-detail__params-item">
						<div class="order-detail__params-name">Buyer:</div>
						<div class="order-detail__time">
							<a class="link-black" href="<?php echo getUserLink($buyer_info['user_name'], $buyer_info['idu'], 'buyer');?>">
								<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $buyer_info['idu'], '{FILE_NAME}' => $buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $buyer_info['user_group']));?>" alt="<?php echo cleanOutput($buyer_info['user_name']);?>" />
								<?php echo $buyer_info['user_name'];?>
							</a>


							<?php echo !empty($btnChatBuyer2) ? $btnChatBuyer2 : ''; ?>
						</div>
					</li>
				<?php }?>
			</ul>
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
							<div class="grid-text">
								<div class="grid-text__item">
									<a class="order-detail__prod-link" href="<?php echo __SITE_URL; ?>prototype/item/<?php echo $po['id_prototype']; ?>" target="_blank"><?php echo $po['title']?></a>
								</div>
							</div>
							<div class="order-detail__product-rating">
								<?php if(!empty($po['detail_item'])){?>
									<span class="order-detail__product-rating-item">
										<?php echo $po['detail_item'];?>
									</span>
								<?php }?>
							</div>
						</td>
						<td><?php echo $po['quantity']?></td>
						<td>
							<?php echo get_price($po['price']);?>
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*
							<?php }?>
						</td>
					</tr>
					<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
					<tr>
						<td colspan="4">*<span class="fs-10">Real price for payment is $ <?php echo get_price($po['price']*$po['quantity'], false);?></span></td>
					</tr>
					<?php }?>
				</tbody>
			</table>

			<?php if(!empty($po['log'])){?>
				<table class="table table-bordered mt-25">
					<caption class="tac mb-10"><i class="ep-icon ep-icon_clock fs-16 vat lh-22"></i> Producing Request timeline</caption>
					<thead>
						<tr>
							<th class="w-250 tac">Date</th>
							<th class="w-100 tac">User</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($po['log'] as $key => $po_timeline){ ?>
						<tr>
							<td class="tac"><?php echo formatDate($po_timeline['date']); ?></td>
							<td class="tac">
								<?php if(isset($po_timeline['user'])){?>
									<?php echo $po_timeline['user']; ?>
								<?php } else{?>
									System
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="fs-14 bdb-1-black" colspan="2">
								<?php if(isset($po_timeline['price'])){?>
									<strong>Price: </strong> $<?php echo $po_timeline['price']; ?> <br>
								<?php }?>
								<?php if(isset($po_timeline['quantity'])){?>
									<strong>Quantity: </strong> <?php echo $po_timeline['quantity']; ?> <br>
								<?php }?>
								<strong>Message: </strong><?php echo $po_timeline['message']; ?> <br>
								<?php if(isset($po_timeline['changes'])){?>
									<strong>Changes:</strong> <?php echo cleanOutput($po_timeline['changes']); ?> <br>
								<?php }?>
								<?php if(isset($po_timeline['comment'])){?>
									<strong>Comment:</strong> <?php echo cleanOutput($po_timeline['comment']); ?>
								<?php }?>
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

		window.vatingProducingRequestStatusModal = ({
			init: function (params) {
				vatingProducingRequestStatusModal.self = this;
				vatingProducingRequestStatusModal.dontShow = false;
				vatingProducingRequestStatusModal.$modal = $(params[0]);
				vatingProducingRequestStatusModal.$hiddenProducingRequestStatusActionsModal = $('#js-hidden-status-actions');
				vatingProducingRequestStatusModal.$mainProducingRequestStatusModal = vatingProducingRequestStatusModal.$modal.find('.js-order-status-modal');
				vatingProducingRequestStatusModal.self.initListiners();
				// vatingProducingRequestStatusModal.self.openVaitingActivation();
			},
			initListiners: function(){
				vatingProducingRequestStatusModal.$mainProducingRequestStatusModal.on('click', '.js-btn-close', function(e){
					e.preventDefault();
					var $this = $(this);
                    vatingProducingRequestStatusModal.dontShow = $('.modal .js-dont-show-more').prop('checked');
					vatingProducingRequestStatusModal.self.closeAndSetOrderStatusView($this);
				})
			},
			closeAndSetOrderStatusView: function(){
				if(
					vatingProducingRequestStatusModal.dontShow
					&& !existCookie('_ep_view_producing_request_status')
				){
					vatingProducingRequestStatusModal.$hiddenProducingRequestStatusActionsModal.html("");
					setCookie('_ep_view_producing_request_status', 1, 7);
				}

				BootstrapDialog.closeAll();
			}
		});

	}());

	var ifExitsProducingReuqestStatusView = function(){
		if(!existCookie('_ep_view_producing_request_status')){
			if($('.order-detail__status .info-dialog-100').length){
				$('.order-detail__status .info-dialog-100').trigger('click');
			}
		}
	}

	var showStatusModal = function(dialog){
		window.vatingProducingRequestStatusModal.init(dialog.getModalFooter());
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

		ifExitsProducingReuqestStatusView();
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
