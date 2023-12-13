<?php $session = tmvc::instance()->controller->session;?>

<script>
$(document).ready(function () {
//START all messages
	<?php if(have_right('manage_messages')){?>
	//select all checkbox
	$('body').on('ifChecked', '.all-notifications .checkbox-17-blue', function (event) {
		$('.wr-system-mess-list .checkbox-17-blue, .all-notifications .checkbox-17-blue').iCheck('check');

		var btnRemoveNotification = $('.wr-system-mess-list .remove-notification');
		if(btnRemoveNotification.length)
			btnRemoveNotification.data('callback', 'remove_notification').removeClass('call-function').addClass('confirm-dialog');

		var btnReadNotification = $('.wr-system-mess-list .read-notification');
		if(btnReadNotification.length)
			btnReadNotification.data('callback', 'read_notification').removeClass('call-function').addClass('confirm-dialog');
	});

	$('body').on('ifUnchecked', '.all-notifications .checkbox-17-blue', function (event) {
		$('.wr-system-mess-list .checkbox-17-blue, .all-notifications .checkbox-17-blue').iCheck('uncheck');

		var btnRemoveNotification = $('.wr-system-mess-list .remove-notification');
		if(btnRemoveNotification.length)
			btnRemoveNotification.data('callback', 'no_remove_notification').removeClass('confirm-dialog').addClass('call-function');

		var btnReadNotification = $('.wr-system-mess-list .read-notification');
		if(btnReadNotification.length)
			btnReadNotification.data('callback', 'no_read_notification').removeClass('confirm-dialog').addClass('call-function');
	});
	//end select all checkbox
	<?php }?>
//END all messages

//START all notifications
	//notifications navigation
	$('body').on('click', '.ico-wr li a', function (e) {
		var $This = $(this);
		var type = $This.attr('href');
		$This.parent('li').addClass('active').siblings('li').removeClass('active');

		loadNotificationList(type);
		e.preventDefault();
	});
	//end notifications navigation

	//end notifications detail
	$('body').on('click', '.system-mess-list__ttl-txt', function (e) {
		e.preventDefault();

		var $This = $(this);
		var mess = $This.attr('href');
		var liParent = $This.closest('li');
		var active = $('.ico-wr').find('li.active').children('a').attr('href');

		if (!liParent.hasClass('system-mess-list__item--seen') && (active != 'deleted')) {
			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL ?>systmess/ajax_systmess_operation/notification_seen',
				data: {message: mess},
				dataType: 'json',
				success: function (resp) {
					liParent.addClass('system-mess-list__item--seen');

					updateCountersNotifications(resp);

					var $newAll = $('.ico-wr .i-all').find('span');
					$newAll.text(parseInt($newAll.text()) - 1);
				}
			});
		}

		liParent.find('.system-mess-list__txt').slideToggle('slow');
	});
	//end notifications detail

	//notifications pagination
	$('body').on('click', '.messages-pagination a', function (e) {
		e.preventDefault();
		loadNotificationList($(this).data('type'), $(this).data('page'));
	});
	//end notifications pagination

	//check send notify on email
	$('body').on('ifClicked','#check-send-notification .checkbox-17-blue', function (event) {
		var $this = $(this);
		if($this.prop('checked'))
			checkSendNotification(0);
		else
			checkSendNotification(1);
	});
	//check send notify on email

	//START check if has checked notify for btn remove
	$('body').on('ifChecked', '.system-mess-list .checkbox-17-blue', function (event) {
		var btnRemoveNotification = $('.wr-system-mess-list .remove-notification');
		if(btnRemoveNotification.length)
			btnRemoveNotification.data('callback', 'remove_notification').removeClass('call-function').addClass('confirm-dialog');

		var btnReadNotification = $('.wr-system-mess-list .read-notification');
		if(btnReadNotification.length)
			btnReadNotification.data('callback', 'read_notification').removeClass('call-function').addClass('confirm-dialog');
	});

	$('body').on('ifUnchecked', '.system-mess-list .checkbox-17-blue', function (event) {
		var nrNewDeleted = 0;

		$('.system-mess-list input[type=checkbox]').each(function () {
			var $inputCheck = $(this);
			if ($inputCheck.prop('checked')) {
					nrNewDeleted++;
			}
		});

		if (nrNewDeleted == 0) {
			var btnRemoveNotification = $('.wr-system-mess-list .remove-notification');
			if(btnRemoveNotification.length)
				btnRemoveNotification.data('callback', 'no_remove_notification').removeClass('confirm-dialog').addClass('call-function');

			var btnReadNotification = $('.wr-system-mess-list .read-notification');
			if(btnReadNotification.length)
				btnReadNotification.data('callback', 'no_read_notification').removeClass('confirm-dialog').addClass('call-function');

			$('.all-notifications .checkbox-17-blue').iCheck('uncheck');
		}
	});
	//END check if has checked notify for btn remove

//END all notifications

	//menu dashboard
	$('body').on('click', '.header-nav-top a.btnDashboard', function (e) {
		var $This = $(this);

		if ($This.hasClass('active')) {
			headerNavRef();
			$('.popup-header-nav-top .wr-dashboard-nav-b').hide();
		} else {
			if (!$('.popup-header-nav-top .dashboard-nav-b').length) {
				$.ajax({
					url: '<?php echo __SITE_URL; ?>dashboard/ajax_view_<?php echo (user_type('ep_staff')) ? "admin_" : ""; ?>dashboard',
					type: 'POST',
					dataType: 'JSON',
					data: {},
					success: function (resp) {
						headerNavRef();
						if(resp.mess_type == 'success'){
							$('.popup-header-nav-top .wr-dashboard-nav-b').html(resp.menu_content);
							$('.shadow-header-top, .popup-header-nav-top, .wr-dashboard-nav-b').show();
							$This.addClass('active');
						} else{
							systemMessages( resp.message, 'message-' + resp.mess_type );
						}
					}
				});
			} else {
				headerNavRef();
				$('.shadow-header-top, .popup-header-nav-top, .popup-header-nav-top .wr-dashboard-nav-b').show();
				if($('.header-nav-widget__top').length)
					$('.header-nav-widget__top').show();
				$This.addClass('active');
			}
		}

		e.preventDefault();
	});
	// end menu dashboard

	//likes pagination
	$('body').on('click', ".nr-page-b a", function (e) {
		e.preventDefault();
		var page = $(this).data('page');
		var type = $(this).data('type');
		if (page == undefined)
			return false;
		laodSavedList(type, page);
	});
	//end likes pagination
});

//START all notifications
	//notifications
	var notificationBlock = function(obj){
		var $this = $(obj);

		if ($this.hasClass('active')) {
			headerNavRef();
			$('.inbox-content').hide();
		} else {
			$.ajax({
				url: '<?php echo __SITE_URL; ?>systmess/ajax_systmess_operation/show_notification_block',
				type: 'POST',
				data: {type: 'all'},
				dataType: 'json',
				success: function (resp) {
					headerNavRef();
					$('.popup-header-nav-top .inbox-content').html(resp.block);
					$('.shadow-header-top, .popup-header-nav-top, .inbox-content').show();
					$('.checkbox-17-blue').iCheck({checkboxClass: 'wr-checkbox-17-blue', increaseArea: '20%' });
					$this.addClass('active');
					$('html').addClass('fancybox-margin fancybox-lock');
					$('.main-socials-list').addClass('fancybox-margin');

					updateCountersNotifications(resp);
				}
			});
		}
	};
	//end notifications

	function updateCountersNotifications(resp){
		if (resp.count_notifications != undefined) {
			var $btn = $('.btn-new-notifications');
			var count = resp.count_notifications.count_new;
			var count_all = resp.count_notifications.count_all;
			var current_count = parseInt($btn.find('strong').text());
			var current_all_count = parseInt($btn.find('span').text());

			if(current_count != count){
				$btn.find('strong').text(count);
				if(count > 0)
					$btn.addClass('bg-red');
				else
					$btn.removeClass('bg-red');
			}

			if(current_all_count != count_all)
				$btn.find('span').text(count_all);
		}
	}

	//load notifications
	function loadNotificationList(type, page) {
		if (page === undefined)
			page = 1;
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>systmess/ajax_systmess_operation/show_notification_block',
			data: {type: type, page: page},
			dataType: "JSON",
			beforeSend: function () {
				showLoader('.wr-system-mess-list', 'Loading...');
			},
			success: function (resp) {
				hideLoader('.wr-system-mess-list');
				$('.popup-header-nav-top .inbox-content').html(resp.block);
				$('.checkbox-17-blue').iCheck({checkboxClass: 'wr-checkbox-17-blue', increaseArea: '20%', });

				updateCountersNotifications(resp);
			}
		});
	}
	//end load notifications

	// read notification
	var no_read_notification = function(obj){
		systemMessages('You did not check any notification(s).', 'message-warning');
	}

	var read_notification = function(obj){
		var $this = $(obj);
		var notificationList = [];
		var nrNewReaded = 0;
		var type = $('.ico-wr li.active').find('a').attr('href');

		$('.system-mess-list input[type=checkbox]').each(function () {
			var $inputCheck = $(this);
			if ($inputCheck.prop('checked')) {
				if(!$inputCheck.closest('li').hasClass('system-mess-list__item--seen')){
					nrNewReaded++;
					notificationList.push($inputCheck.val());
				}
			}
		});

		var nrNotificationList = notificationList.length;

		if (nrNotificationList != 0) {
			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>systmess/ajax_systmess_operation/notification_readed',
				data: {messages: notificationList},
				beforeSend: function(){ showLoader('.wr-system-mess-list'); },
				dataType: 'json',
				success: function(resp){
					systemMessages( resp.message, 'message-' + resp.mess_type );
					hideLoader('.wr-system-mess-list');

					if(resp.mess_type == 'success'){
						notificationList = new Array();
						loadNotificationList(type);

						var $allSpanLi = $('.ico-wr .i-all').find('span');
						var allNr = parseInt($allSpanLi.text());
						$allSpanLi.text(allNr - nrNewReaded);
					}
				}
			});
		}else{
			systemMessages('You did not check any notification(s).', 'message-warning');
		}
	}
	//end read notifications

	// remove notification
	var no_remove_notification = function(obj){
		systemMessages('You did not check any notification(s).', 'message-warning');
	}

	var empty_trash_notifications = function(obj){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>systmess/ajax_systmess_operation/delete_all_from_trash',
			data: {},
			beforeSend: function(){ showLoader('.my-order-right-b2'); },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );
				hideLoader('.my-order-right-b2');
				loadNotificationList();
			}
		});
	}

	var remove_notification = function(obj){
		var $this = $(obj);
		var notificationList = [];
		var removeList = { 'notice': 0, 'warning': 0 };
		var nrNewDeleted = 0;
		var type = $('.ico-wr li.active').find('a').attr('href');

		$('.system-mess-list input[type=checkbox]').each(function () {
			var $inputCheck = $(this);
			if ($inputCheck.prop('checked')) {
				if(!$inputCheck.closest('li').hasClass('system-mess-list__item--seen'))
					nrNewDeleted++;

				notificationList.push($inputCheck.val());

				if($inputCheck.data('type') == 'notice')
					removeList.notice++;
				else
					removeList.warning++;
			}
		});

		var nrNotificationList = notificationList.length;

		if (nrNotificationList != 0) {
			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>systmess/ajax_systmess_operation/notification_deleted',
				data: {messages: notificationList},
				beforeSend: function(){ showLoader('.my-order-right-b2'); },
				dataType: 'json',
				success: function(resp){
					systemMessages( resp.message, 'message-' + resp.mess_type );
					hideLoader('.my-order-right-b2');

					if(resp.mess_type == 'success'){
						notificationList = new Array();
						loadNotificationList(type);

						//counters
						if ($('.ico-wr .i-deleted').length == 0)
							$('.ico-wr').prepend('<li class="i-deleted"><a href="deleted"><i></i> Trash (<span>0</span>)</a></li>');
						var $trashSpanLi = $('.ico-wr .i-deleted').find('span');
						var trashNr = parseInt($trashSpanLi.text());

						if (type != 'deleted') {
							$trashSpanLi.text(trashNr + nrNotificationList);
						}

						if (type == 'all') {
							var $noticeSpanLi = $('.ico-wr .i-notice').find('span');
							$noticeSpanLi.text(parseInt($noticeSpanLi.text()) - removeList.notice);
							// console.log($noticeSpanLi.text());
							var $warningSpanLi = $('.ico-wr .i-warning').find('span');
							$warningSpanLi.text(parseInt($warningSpanLi.text()) - removeList.warning);

							var $allSpanLi = $('.ico-wr .i-all').find('span');
							var allNr = parseInt($allSpanLi.text());
							$allSpanLi.text(allNr - nrNewDeleted);
						} else if (type != 'deleted') {
							//new counter
							if (nrNewDeleted > 0) {
								var $allSpanLi = $('.ico-wr .i-all').find('span');
								var allNr = parseInt($allSpanLi.text());
								$allSpanLi.text(allNr - nrNewDeleted);
							}

							var $currentSpanLi = $('.ico-wr .i-' + type).find('span');
							var currentNr = parseInt($currentSpanLi.text());

							$currentSpanLi.text(currentNr - nrNotificationList);
						} else {
							$trashSpanLi.text(trashNr - nrNotificationList);
						}
					}
				}
			});
		}else{
			systemMessages('You did not check any notification(s).', 'message-warning');
		}
	}
	//end remove notification

	function checkSendNotification(check){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>systmess/ajax_systmess_operation/send_notify',
			data: { check : check},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					var $checkSendNotification = $('#check-distribution-notification');
					if($checkSendNotification.length){
						if(check)
							$checkSendNotification.find('.checkbox-17-blue').iCheck('check');
						else
							$checkSendNotification.find('.checkbox-17-blue').iCheck('uncheck');
					}
				}
			}
		});
	}

//END all notifications

	// load saved items
	function laodSavedList(type, page) {
		$.ajax({
			url: type + '/ajax_get_saved',
			type: 'POST',
			data: {page: page},
			dataType: 'JSON',
			beforeSend: function () {
				showLoader('.header-save-items__content');
			},
			success: function (resp) {
				if (resp.mess_type == 'success') {
					if (resp.counter != undefined)
						$('.header-save-items__nav li[data-type=' + type + ']').find('span').text(resp.counter);

					$(".header-save-items__content").html(resp.message);
				} else {
					systemMessages(resp.message, 'message-' + resp.mess_type);
				}
			}
		})
	}
	//end load saved items

	//change saved counter
	function changeLikeCounter(type) {
		var $spanCounter = $('.header-save-items__nav li[data-type=' + type + ']').find('span');
		var counter = parseInt($spanCounter.text());
		$spanCounter.text(--counter);
	}
	//end change saved counter

	//remove contact item
	var remove_header_contact = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/ajax_contact_operations/remove/';?>' + ($this.data('id')),
			type: 'POST',
			dataType: 'JSON',
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').fadeOut('normal', function(){
						$(this).remove();
					});
					laodSavedList('contact', 1);
					changeLikeCounter('contact');
				}
			}
		});
	}
	//end remove contact item

	//saved items navigations
	$('body').on('click', ".header-save-items__nav li", function (e) {
		e.preventDefault();
		var $this = $(this);
		$this.siblings().removeClass('active').end()
				.addClass('active');
		laodSavedList($this.data('type'), 1);
	})
	//end saved items navigations

	//remove saved sellers
	var remove_header_company = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'directory/ajax_company_operations/remove_company_saved';?>',
			type: 'POST',
			dataType: 'JSON',
			data: {company: $this.data('company')},
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').fadeOut('normal', function(){
						$(this).remove();
					});
					laodSavedList('directory', 1);
					changeLikeCounter('directory');
				}
			}
		});
	}
	//end remove saved sellers

	//remove saved shippers
	var remove_header_shipper = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'shipper/ajax_shipper_operation/remove_shipper_saved';?>',
			type: 'POST',
			dataType: 'JSON',
			data: {company: $this.data('shipper')},
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').fadeOut('normal', function(){
						$(this).remove();
					});
					laodSavedList('shippers', 1);
					changeLikeCounter('shippers');
				}
			}
		});
	}
	//end remove saved shippers

	//remove saved b2b partners
	var remove_header_b2b_partners = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'b2b/ajax_b2b_operation/delete_partner';?>',
			type: 'POST',
			dataType: 'JSON',
			data: {partner: $this.data('partner'), company: $this.data('company')},
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').remove();
					laodSavedList('b2b', 1);
					changeLikeCounter('b2b');
				}
			}
		});
	}
	//end remove b2b partners

	//remove saved product
	var remove_header_product = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'items/ajax_saveproduct_operations/remove_product_saved';?>',
			type: 'POST',
			dataType: 'JSON',
			data: {product: $this.data('product')},
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').remove();
					laodSavedList('items', 1);
					changeLikeCounter('items');
				}
			}
		});
	}
	//end remove saved product

	//remove saved search
	var remove_header_search = function (opener) {
		var $this = $(opener);
		$.ajax({
			url: '<?php echo __CURRENT_SUB_DOMAIN_URL . 'save_search/ajax_savesearch_operations/remove_search_saved';?>',
			type: 'POST',
			dataType: 'JSON',
			data: {search: $this.data('search')},
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
				if (resp.mess_type == 'success') {
					$this.closest('li').remove();
					laodSavedList('save_search', 1);
					changeLikeCounter('save_search');
				}
			}
		});
	}
	//end remove saved search

	<?php if (have_right('buy_item')) { ?>
		//remove saved basket item
		var removeBasketItemHeader = function (obj) {
			var $this = $(obj);
			var item = $this.data('item');

			$.ajax({
				url: 'basket/ajax_basket_operation/delete_one',
				type: 'POST',
				data: {id: item},
				dataType: 'json',
				success: function (resp) {
					systemMessages(resp.message, 'message-' + resp.mess_type);

					if (resp.mess_type == 'success') {

						var parentLi = $this.closest("li");
						var parentUl = parentLi.closest("ul");

						parentLi.fadeOut(function(){
							$(this).remove();
							calculateBasketHeader(parentUl, item);
						});

						var BasketTotalNr = parseInt($('.btnBasket').find('strong').text());
						if (BasketTotalNr > 0)
							$('.btnBasket').find('strong').text(BasketTotalNr - 1);
					}
				}
			});
		}
		//end remove saved basket item

		//calculate basket
		function calculateBasketHeader(parentUl, id_item) {
			var parentDiv = parentUl.parent('div');
			var id_user = parentDiv.attr('id').split('-');

			if (!parentUl.find('li').length) {
				parentDiv.remove();

				if (!$('.show-basket-b > div').length) {
					$('.show-basket-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> No items in the basket.</div>');
				}

				if( $('.basket-users-list').length){
					$('.basket-users-list li#company-' + id_user[1]).remove();
					$('#user-basket-b #basket-' + id_user[1]).remove();
				}
			} else {
				var count = parentUl.children('li').length;

				//title item group
				parentUl.prev('.item-user-basket-title-b').find('.nr-val').text(count);

				//change basket my
				if ($('.basket-users-list li#company-' + id_user[1]).length) {
					//change user list count
					$('.basket-users-list li#company-' + id_user[1]).find('.nr-val').html(count);
					//change item list
					var $basketItemChange = $('#user-basket-b #basket-' + id_user[1]);
					$basketItemChange.find('.item-user-basket-title-b .nr-val').html(count);
					$basketItemChange.find('#item-' + id_item).remove();
				}
			}

			if($('.basket-users-list').length){
				$('.basket-users-list li').removeClass('active');
				$('.item-user-basket-b').show().end()
						.removeClass('active').end()
						.children('.item-user-basket-title-b').show().end()
						.children('.item-user-basket-total-b').show();
				if ($('.basket-users-list li').length <= 0) {
					$('.basket-users-list, #user-basket-b, .show-basket-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> No items in the basket.</div>');
				}

				if (apiUserBasket !== undefined) {
					apiUserBasket.reinitialise();
				}
			}
		}
		//end calculate basket
	<?php } ?>

    function navHeaderMenu(navHeaderMenu) {
        $.each(navHeaderMenu, function (index, item) {
            $('.dashboard-nav-b .col'+item.col+'-cell'+item.cell)
                .html('<a href="'+item.link+'"><div class="ico-b '+item.icon_color+'"><i class="ep-icon ep-icon_'+item.icon+'"></i></div><span>'+item.title+'</span></a>');
        });
    }
</script>

<?php $cookie = tmvc::instance()->controller->cookies; ?>
<a class="header-nav-top__link pull-right" <?php echo addQaUniqueIdentifier("admin-users_header-nav-top_logout_btn")?> href="<?php echo __SITE_URL ?>authenticate/logout<?php if(isset($logout_page)) echo '/'.$logout_page;?>"><i class="ep-icon ep-icon_logout"></i> <span><?php echo translate('header_navigation_link_logout');?></span></a>

<a class="header-nav-top__link pull-right btn-new-notifications call-function <?php if ($count_notifications['count_new'] > 0) { ?>bg-red<?php } ?>" data-callback="notificationBlock" href="#" title="<?php echo translate('header_navigation_link_notifications_title');?>">
    <i class="ep-icon ep-icon_bell"></i> <strong class="fs-14"><?php echo $count_notifications['count_new']; ?></strong> / <span><?php echo $count_notifications['count_all']; ?></span>
    <div class="i-arrow"></div>
</a>

<a class="header-nav-top__link pull-right btnDashboard" href="#" title="<?php echo translate('header_navigation_link_dashboard_menu_title');?>">
    <img class="h-20" src="<?php echo $userImageUrl ?>" alt="<?php echo $session->fname; ?>" />
    <i class="ep-icon ep-icon_menu"></i>
    <div class="i-arrow"></div>
</a>

<div class="popup-header-nav-top" style="display:none;">
	<div class="inbox-content"></div>

	<div class="wr-dashboard-nav-b"></div>

	<div class="new-messages-nav-b"></div>

	<span class="btn-close"><i class="ep-icon ep-icon_remove"></i></span>
</div>
