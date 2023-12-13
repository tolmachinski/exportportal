<?php $cookie = tmvc::instance()->controller->cookies; ?>

<div id="order-detail-<?php echo $inquiry['id_inquiry']?>" class="order-detail">
	<div class="order-detail__scroll">
		<div class="order-detail__top">
			<ul class="order-detail__params">
				<li class="order-detail__params-item order-detail__params-item--double">
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Number:</div>
						<div class="order-detail__number"><?php echo orderNumber($inquiry['id_inquiry']);?></div>
					</div>
					<div class="order-detail__param-col">
						<div class="order-detail__params-name">Status:</div>
						<div class="order-detail__status <?php echo $inquiry_status['title_color'];?>">
							<span class="info-dialog-100 cur-pointer" data-title="What's next in <span class='txt-gray'><?php echo $inquiry_status['title'];?></span>" data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions"><?php echo $inquiry_status['title'];?></span>
						</div>

						<div class="display-n">
							<div id="js-hidden-status-text">
								<div class="order-status__content">
									<?php if(!empty($inquiry_status['whats_next'])){?>
										<?php if(!empty($inquiry_status['whats_next'][$inquiry_status_user]['text']['mandatory'])){?>
											<?php echo $inquiry_status['whats_next'][$inquiry_status_user]['text']['mandatory'];?>
										<?php }?>

										<?php if(!empty($inquiry_status['whats_next'][$inquiry_status_user]['text']['optional'])){?>
											<h3 class="order-status__ttl">Optional</h3>
											<?php echo $inquiry_status['whats_next'][$inquiry_status_user]['text']['optional'];?>
										<?php }?>
									<?php } else if(!empty($inquiry_status['description'])){?>
										<?php echo $inquiry_status['description'];?>
									<?php }?>
								</div>
							</div>

							<div id="js-hidden-status-actions">
								<?php if(!cookies()->exist_cookie('_ep_view_inquiry_status')){?>
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
					<span class="btn btn-light btn-block info-dialog-100" data-title="What's next in <span class='txt-gray'><?php echo cleanOutput($inquiry_status['title']);?></span>"  data-content="#js-hidden-status-text" data-actions="#js-hidden-status-actions" href="#">
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
							<span class="pl-5 pr-5">Action on Inquiry</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<?php $finished_status = array('declined', 'completed'); ?>

							<?php if(have_right('buy_item') && ($inquiry['status'] == 'prototype_confirmed')){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL; ?>inquiry/popup_forms/ship_to/<?php echo $inquiry['id_inquiry'];?>" data-title="Start order">
									<i class="ep-icon ep-icon_marker-stroke2"></i>
									<span class="txt">Start order</span>
								</a>
							<?php }?>

							<?php if(have_right('manage_seller_inquiries') && $inquiry['status'] == 'initiated'){?>
								<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to activate the prototype?" data-callback="activate_prototype" data-prototype="<?php echo $prototype['id_prototype'];?>" href="#" data-title="Activate the Prototype">
									<i class="ep-icon ep-icon_ok-circle"></i>
									<span class="txt">Activate the Prototype</span>
								</a>
							<?php }?>

							<?php if(have_right('manage_seller_inquiries') && $prototype['status_prototype'] == 'in_progress'){?>
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

							<?php if(!in_array($inquiry['status'],$finished_status)){?>
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>inquiry/popup_forms/resend_inquiry/<?php echo $inquiry['id_inquiry'];?>" data-title="Discuss the Inquiry">
									<i class="ep-icon ep-icon_comment-stroke"></i>
									<span class="txt">Discuss</span>
								</a>
								<a class="dropdown-item confirm-dialog" data-callback="declineInquiry" data-inquiry="<?php echo $inquiry['id_inquiry'];?>" data-message="Are you sure you want decline this Inquiry?">
									<i class="ep-icon ep-icon_remove-circle"></i>
									<span class="txt">Decline</span>
								</a>
							<?php }?>

							<?php
								if(have_right('buy_item')){
									$user_state = 'state_buyer';
								} elseif(have_right('manage_seller_inquiries')){
									$user_state = 'state_seller';
								}
							?>
							<?php if(in_array($inquiry['status'], $finished_status) && $inquiry[$user_state] == 0){?>
								<a class="dropdown-item confirm-dialog" href="#" data-inquiry="<?php echo $inquiry['id_inquiry'];?>" data-message="Are you sure you want to Archive this Inquiry?" data-callback="archiveInquiry">
									<i class="ep-icon ep-icon_folder"></i>
									<span class="txt">Add to archive</span>
								</a>
							<?php }?>

							<?php if(in_array($inquiry['status'], $finished_status) && in_array($inquiry[$user_state], array(0,1))){?>
								<a class="dropdown-item confirm-dialog" href="#" data-inquiry="<?php echo $inquiry['id_inquiry'];?>" data-message="Are you sure you want to Delete this Inquiry?" data-callback="deleteInquiry">
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
								<img class="h-20 vam" src="<?php echo getDisplayImageLink(array('{ID}' => $buyer_info['idu'], '{FILE_NAME}' => $buyer_info['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $buyer_info['user_group'] ));?>" alt="<?php echo cleanOutput($buyer_info['user_name']);?>" />
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
							<?php if($inquiry['id_prototype'] > 0){ ?>
								<a class="order-detail__prod-link" href="<?php echo __SITE_URL; ?>prototype/item/<?php echo $inquiry['id_prototype']; ?>" target="_blank"><?php echo $inquiry['title']?></a>
							<?php } else{?>
								<a class="order-detail__prod-link" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($inquiry['title']).'-'.$inquiry['id_item']; ?>" target="_blank"><?php echo $inquiry['title']?></a>
							<?php }?>
							<div class="order-detail__product-rating">
								<?php if(!empty($inquiry['detail_item'])){?>
									<span class="order-detail__product-rating-item">
										<?php echo $inquiry['detail_item'];?>
									</span>
								<?php }?>
							</div>
						</td>
						<td><?php echo $inquiry['quantity']?></td>
						<td>
							<?php echo get_price($inquiry['price']);?>
							<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
							*
							<?php }?>
						</td>
					</tr>
					<?php if($cookie->cookieArray['currency_key'] !== 'USD'){?>
					<tr>
						<td colspan="4">*<span class="fs-10">Real price for payment is $ <?php echo get_price($inquiry['price']*$inquiry['quantity'], false);?></span></td>
					</tr>
					<?php }?>
				</tbody>
			</table>

			<?php if(!empty($inquiry['log'])){?>
				<table class="table table-bordered mt-25">
					<caption class="tac mb-10"><i class="ep-icon ep-icon_clock fs-16 vat lh-22"></i> Inquiry timeline</caption>
					<thead>
						<tr>
							<th class="w-250 tac">Date</th>
							<th class="w-100 tac">User</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($inquiry['log'] as $inquiry_timeline){ ?>
						<tr>
							<td class="tac"><?php echo formatDate($inquiry_timeline['date']); ?></td>
							<td class="tac"><?php echo $inquiry_timeline['user'];?></td>
						</tr>
						<tr>
							<td class="fs-14 bdb-1-black" colspan="2">
								<?php if(isset($inquiry_timeline['price'])){?>
									<strong>Price: </strong> $ <?php echo get_price($inquiry_timeline['price'], false); ?> <br>
								<?php }?>
								<strong>Message: </strong> <?php echo cleanOutput($inquiry_timeline['message']); ?> <br>
								<?php if(isset($inquiry_timeline['changes'])){?>
									<strong>Changes:</strong> <?php echo cleanOutput($inquiry_timeline['changes']); ?> <br>
								<?php }?>
								<?php if(isset($inquiry_timeline['comment'])){?>
									<strong>Comment:</strong> <?php echo cleanOutput($inquiry_timeline['comment']); ?>
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

		window.vatingInquiryStatusModal = ({
			init: function (params) {
				vatingInquiryStatusModal.self = this;
				vatingInquiryStatusModal.dontShow = false;
				vatingInquiryStatusModal.$modal = $(params[0]);
				vatingInquiryStatusModal.$hiddenInquiryStatusActionsModal = $('#js-hidden-status-actions');
				vatingInquiryStatusModal.$mainInquiryStatusModal = vatingInquiryStatusModal.$modal.find('.js-order-status-modal');

				vatingInquiryStatusModal.self.initListiners();
				// vatingInquiryStatusModal.self.openVaitingActivation();
			},
			initListiners: function(){
				vatingInquiryStatusModal.$mainInquiryStatusModal.on('click', '.js-btn-close', function(e){
					e.preventDefault();
					var $this = $(this);

                    vatingInquiryStatusModal.dontShow = $('.modal .js-dont-show-more').prop('checked');
					vatingInquiryStatusModal.self.closeAndSetOrderStatusView($this);
				});
			},
			closeAndSetOrderStatusView: function(){
				if(
					vatingInquiryStatusModal.dontShow
					&& !existCookie('_ep_view_inquiry_status')
				){
					vatingInquiryStatusModal.$hiddenInquiryStatusActionsModal.html("");
					setCookie('_ep_view_inquiry_status', 1, 7);
				}

				BootstrapDialog.closeAll();
			}
		});

	}());

	var ifExitsInquiryStatusView = function(){
		if(!existCookie('_ep_view_inquiry_status')){
			if($('.order-detail__status .info-dialog-100').length){
				$('.order-detail__status .info-dialog-100').trigger('click');
			}
		}
	}

	var showStatusModal = function(dialog){
		window.vatingInquiryStatusModal.init(dialog.getModalFooter());
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

		ifExitsInquiryStatusView();
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
