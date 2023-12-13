$(function(){
	$('.js-icon-circle-notification').find('.epuser-line__circle-sign').removeClass('pulse-shadow-animation');
});

jQuery(window).on('resizestop', function () {
	$('.js-popover-mep').popover('hide')
});

function updateNotificationsCounter(countNew, countImportant) {
    const allNotificationsNode = $("#js-popover-nav-count-new");
    const importantNotificationsNode = $("#js-popover-nav-count-important");
    allNotificationsNode.text(countNew);
    importantNotificationsNode.text(countImportant);
};

function btnNotificationList($this) {
	var status = $this.data('status');

	// $this.addClass('active').siblings().removeClass('active');

	loadNotificationList2(status);
}

function filterNotificationList($this) {
	if($this.hasClass('active')){
		return false;
	}

	var status = $this.data('status');
	var type = $this.data('type')||false;

	$this.addClass('active').siblings().removeClass('active');

	loadNotificationList2(status, 1, type);
}

//load notifications
function loadNotificationList2(status, page, type) {
	if (page === undefined)
		page = 1;

		type = type||false;

	var dataSend = {status: status, page: page};

	if(type != false){
		dataSend.type = type;
	}

	$.ajax({
		type: 'POST',
		url: __current_sub_domain_url + "systmess/ajax_systmess_operation/show_notification_block2",
		data: dataSend,
		dataType: "JSON",
		beforeSend: function () {
			showLoader('#js-epuser-notifications2 .js-epuser-subline-list2', 'Loading...');
		},
		success: function (resp) {
			hideLoader('#js-epuser-notifications2 .js-epuser-subline-list2');
			$('#js-epuser-notifications2').html(resp.block);

			if(status == 'deleted'){
				$('#js-epuser-notifications2').find('.read-notification').hide();
			}else{
				$('#js-epuser-notifications2').find('.read-notification').show();
			}

			$.fancybox.update();

            var notifications = resp.count_notifications;
            updateNotificationsCounter(notifications.count_new, notifications.count_warning);
		}
	});
}
//end load notifications

//end notifications detail
var showNotificationDetail = function(obj){
	var $this = $(obj).closest('.epuser-subline-list2__ttl');

	var notify = $this.data('notify');
	var type = $this.find('input[type="checkbox"]').data('type');
	var liParent = $this.closest('.js-epuser-subline-list2__item');
	// console.log(type);

	var active = $('#js-epuser-notifications2 .epuser-subline-nav2 .link.active').data('status');

	if (!liParent.hasClass('js-epuser-subline-list2__item--seen') && (active != 'deleted')) {
		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url +'systmess/ajax_systmess_operation/notification_seen',
			data: {message: notify},
			dataType: 'json',
			success: function (resp) {
				liParent.addClass('js-epuser-subline-list2__item--seen');
				var $newAll = $('#js-epuser-notifications2 .epuser-subline-nav2 .link.active .count');
				var newCount = parseInt($newAll.text()) - 1;
				$newAll.text(newCount);

				var $newByType = $('#js-epuser-notifications2 .epuser-subline-filter .link[data-type="' + type + '"] .count');
				$newByType.text(parseInt($newByType.text()) - 1)

				if(newCount == 0){
					$('#js-epuser-notifications2 .epuser-subline-nav2 .link.active').addClass('disabled');
				}

                var notifications = resp.count_notifications;
                updateNotificationsCounter(notifications.count_new, notifications.count_warning);
			}
		});
	}

	liParent.find('.epuser-subline-list2__desc').slideToggle('slow');
}
//end notifications detail

// read notification
var no_read_notification2 = function(obj){
	systemMessages('You do not have any unread notifications.', 'warning');
}

var read_notification2 = function(obj){
	var $this = $(obj);
	var notificationList = [];
    var $filterActive = $('#js-epuser-notifications2 .epuser-subline-filter .link.active');
	var status = $filterActive.data('status');
	var type = $filterActive.data('type');
	var total = 0;
	var infoTotal = 0;
	var importantTotal = 0;

	if(status == 'deleted'){
		systemMessages('You did not mark as read this notification(s).', 'warning');
		return;
	}

	$('#js-epuser-notifications2 .js-epuser-subline-list2 input[type=checkbox]').each(function () {
        var $inputCheck = $(this);
		if ($inputCheck.prop('checked')) {
			var $parentLi = $inputCheck.closest('.js-epuser-subline-list2__item');
			if(!$parentLi.hasClass('js-epuser-subline-list2__item--seen')){
				total++;
				if($inputCheck.data('type') == 'notice'){
					infoTotal++;
				}else if($inputCheck.data('type') == 'warning'){
					importantTotal++;
				}
				$parentLi.addClass('js-epuser-subline-list2__item--seen');
				notificationList.push($inputCheck.val());
			}
		}
    });

    var nrNotificationList = notificationList.length;

	if (nrNotificationList != 0) {
		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'systmess/ajax_systmess_operation/notification_readed',
			data: {messages: notificationList},
			beforeSend: function(){ showLoader('#js-epuser-notifications2 .js-epuser-subline-list2'); },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );
				hideLoader('#js-epuser-notifications2 .js-epuser-subline-list2');

				if(resp.mess_type == 'success'){
					//loadNotificationList2(status, 1, type);

					var $newAll = $('#js-epuser-notifications2 .epuser-subline-nav2 .link.active .count');
					var newCount = parseInt($newAll.text()) - total;
					$newAll.text(newCount);

					var $subline = $('#js-epuser-notifications2 .epuser-subline-filter')
					var $newByNotice = $subline.find('.link[data-type="notice"] .count');
					$newByNotice.text(parseInt($newByNotice.text()) - infoTotal);
					var $newByImportant = $subline.find('.link[data-type="warning"] .count');
					$newByImportant.text(parseInt($newByImportant.text()) - importantTotal);

					if(newCount == 0){
						$('#js-epuser-notifications2 .epuser-subline-nav2 .link.active').addClass('disabled');
					}

                    var notifications = resp.count_notifications;
                    updateNotificationsCounter(notifications.count_new, notifications.count_warning);
				}
			}
		});
	}else{
		systemMessages('You have not selected any unread notification(s).', 'warning');
	}
}
//end read notifications

// remove notification
var no_remove_notification2 = function(obj){
	systemMessages('You did not check any notification(s).', 'warning');
}

var empty_trash_notification2 = function(obj){
	$.ajax({
		type: 'POST',
		url: __current_sub_domain_url + "systmess/ajax_systmess_operation/delete_all_from_trash",
		data: {},
		beforeSend: function(){ showLoader('#js-epuser-notifications2 .js-epuser-subline-list2'); },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );
			hideLoader('#js-epuser-notifications2 .js-epuser-subline-list2');
			if(resp.mess_type == 'success'){
				loadNotificationList2('all', 1, 'all');
			}
		}
	});
}

var remove_notification2 = function(obj){
	var $this = $(obj);
	var notificationList = [];
	var $filterActive = $('#js-epuser-notifications2 .epuser-subline-filter .link.active');
	var pageActive = parseInt($('#js-epuser-notifications2 .epuser-pagination .active').text());
	var status = $filterActive.data('status');
	var type = $filterActive.data('type');

	$('#js-epuser-notifications2 .js-epuser-subline-list2 input[type=checkbox]').each(function () {
		var $inputCheck = $(this);
		if ($inputCheck.prop('checked')) {
			notificationList.push($inputCheck.val());
		}
	});

	var nrNotificationList = notificationList.length;

	if (nrNotificationList != 0) {
		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'systmess/ajax_systmess_operation/notification_deleted',
			data: {messages: notificationList},
			beforeSend: function(){ showLoader('#js-epuser-notifications2 .js-epuser-subline-list2'); },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );
				hideLoader('#js-epuser-notifications2 .js-epuser-subline-list2');

				if(resp.mess_type == 'success'){
					loadNotificationList2(status, pageActive, type);
				}
			}
		});
	}else{
		systemMessages('You did not check any notification(s).', 'warning');
	}
}
//end remove notification
