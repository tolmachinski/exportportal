<script type="text/javascript">
var requestListApi;
var $requestList;
var requestListTimeout;

var companyListApi;
var $companyList;
var companyListTimeout;

var responseListApi;
var $responseList;
var responseListTimeout;

var calcHeightClasses = [
	{'minus':60, 'name': 'requestList'},
	{'minus':30, 'name': 'companyList'},
	{'minus':60, 'name': 'responseList', 'width': true}];

$(document).ready(function() {
	$requestList = $('#seller-request-list');
	requestListApi = $requestList.jScrollPane().data('jsp');

	$companyList = $('#seller-company-list');
	companyListApi = $companyList.jScrollPane().data('jsp');

	$responseList = $('#seller-response-list');
	responseListApi = $responseList.jScrollPane().data('jsp');

	/* DASHBOARD PAGINATION */
	dashboardPaginationResponse();

	$('.response-list-pag').on('click', '.order-list-pag__number-prev', function(e){
		if(current_page_response > 1){
			current_page_response = current_page_response - 1;
		}
		if(current_page_response <= 1){
			current_page_response = 1;
			$(this).prop('disabled',true);
		}
		if(current_page_response < total_pages_response)
			$('.response-list-pag .order-list-pag__number-next').prop('disabled',false);

		getRequestResponse();

		$('.response-list-pag .order-list-pag__number-list option[value="'+current_page_response+'"]').prop('selected', true);
	});

	$('.response-list-pag').on('click', '.order-list-pag__number-next', function(e){
		if(current_page_response < total_pages_response){
			current_page_response = current_page_response + 1;
		}
		if(current_page_response >= total_pages_response){
			current_page_response = total_pages_response;
			$(this).prop('disabled',true);
		}
		if(current_page_response > 1)
			$('.response-list-pag .order-list-pag__number-prev').prop('disabled',false);

		getRequestResponse();

		$('.response-list-pag .order-list-pag__number-list option[value="'+current_page_response+'"]').prop('selected', true);
	});

	$('.response-list-pag').on('change', '.order-list-pag__number-list', function(e){
		var $this = $(this);
		current_page_response = $this.val();
		if(current_page_response >= total_pages_response){
			current_page_response = total_pages_response;
			$('.response-list-pag .order-list-pag__number-next').prop('disabled',true);
			$('.response-list-pag .order-list-pag__number-prev').prop('disabled',false);
		} else if(current_page_response <= 1){
			current_page_response = 1;
			$('.response-list-pag .order-list-pag__number-prev').prop('disabled',true);
			$('.response-list-pag .order-list-pag__number-next').prop('disabled',false);
		} else{
			$('.response-list-pag .order-list-pag__number-prev').prop('disabled',false);
			$('.response-list-pag .order-list-pag__number-next').prop('disabled',false);
		}

		getRequestResponse();

		$('.response-list-pag .order-list-pag__number-list option[value="'+current_page_response+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */

	/* DASHBOARD PAGINATION */
	dashboardPagination();

	$('.request-list-pag').on('click', '.order-list-pag__number-prev', function(e){
		if(current_page > 1){
			current_page = current_page - 1;
		}
		if(current_page <= 1){
			current_page = 1;
			$(this).prop('disabled',true);
		}
		if(current_page < total_pages)
			$('.order-list-pag__number-next').prop('disabled',false);

		getRequests();

		$('.request-list-pag .order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});

	$('.request-list-pag').on('click', '.order-list-pag__number-next', function(e){
		if(current_page < total_pages){
			current_page = current_page + 1;
		}
		if(current_page >= total_pages){
			current_page = total_pages;
			$(this).prop('disabled',true);
		}
		if(current_page > 1)
			$('.request-list-pag .order-list-pag__number-prev').prop('disabled',false);

		getRequests();

		$('.request-list-pag .order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});

	$('.request-list-pag').on('change', '.order-list-pag__number-list', function(e){
		var $this = $(this);
		current_page = $this.val();
		if(current_page >= total_pages){
			current_page = total_pages;
			$('.request-list-pag .order-list-pag__number-next').prop('disabled',true);
			$('.request-list-pag .order-list-pag__number-prev').prop('disabled',false);
		} else if(current_page <= 1){
			current_page = 1;
			$('.request-list-pag .order-list-pag__number-prev').prop('disabled',true);
			$('.request-list-pag .order-list-pag__number-next').prop('disabled',false);
		} else{
			$('.request-list-pag .order-list-pag__number-prev').prop('disabled',false);
			$('.request-list-pag .order-list-pag__number-next').prop('disabled',false);
		}

		getRequests();

		$('.request-list-pag .order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */

	$('body').on('change', ".search-my-order__status", function(){
		var $thisSelect = $(this);
		var statusSelect = $thisSelect.val();
		var ico;

		switch(statusSelect) {
			case 'new':
				ico = 'new-stroke';
			break;
			case 'approved':
				ico = 'ok-circle';
			break;
			case 'declined':
				ico = 'remove-circle';
			break;
		}

		if(statusSelect != ''){
			$('.filter-response').html('<i class="txt-gray ep-icon ep-icon_'+ico+'"></i> '+statusSelect);
			$('.btn-filter').addClass('btn-filter--active');
		}else{
			$('.filter-response').html('All');
			$('.btn-filter').removeClass('btn-filter--active');
		}

		current_page_response = 1;
		current_response_status = statusSelect;
		getRequestResponse();
	});
});

jQuery(window).on('resizestop', function () {
	if($(this).width() != widthBrowser){
		widthBrowser = $(this).width();
		widthChanged = true;

		calcWidthBlocks(calcHeightClasses, widthChanged);
		calcWidthBlocks([{'name': 'centerList', 'width': true},{'name': 'leftList', 'width': true}], widthChanged);
		widthChanged = false;

		if($(this).width() > 660){
			$('#dashboard-statuses').show();
		}
	}
});

/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES */
// WE NEED A DEFAULT VARIABLES FOR PAGINATION (GLOBAL FOR THIS PAGE)
// 1. COUNTE ITEMS(ORDERS, INQUIRY, PO, ETC.)
// 2. NUMBER OF ITEMS PER PAGE(USED ONLY ON PAGE TO RENDER PAGES NUMBERS)
// 3. CURRENT PAGE => DEFAULT WE ARE ON FIRST PAGE
// 4. TOTAL NUMBER OF PAGES => DEFAULT SHULD BE 1 PAGE
// 5. FILTERS VARS (HERE WE ARE USING STATUS FILTERING)
var total_response = 0;
var per_page_response = <?php echo (int)$request_per_page;?>;
var current_page_response = 1;
var total_pages_response = 1;
var current_response_status = "";

function dashboardPaginationResponse(){
	// CALCULATE TOTAL NUMBER OF PAGES
	total_pages_response = Math.ceil(total_response/per_page_response);

	// IF THERE ARE 0 RESULTS  => WE ARE ON FIRST PAGE
	if(total_pages_response == 0)
		total_pages_response = 1;

	// IF TOTAL NUMBER OF PAGES IS NOT MORE THAN 1 => DISABLE PREV/NEXT BUTTONS
	if(total_pages_response == 1){
		$('.response-list-pag .order-list-pag__number-next').prop('disabled',true);
		$('.response-list-pag .order-list-pag__number-prev').prop('disabled',true);
	}

	// IF CURRENT PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page_response == 1){
		$('.response-list-pag .order-list-pag__number-prev').prop('disabled',true);
	}

	// IF CURRENT PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page_response > 1){
		$('.response-list-pag .order-list-pag__number-prev').prop('disabled',false);
	}

	// IF CURRENT PAGE IS LESS THAN "total_pages_response" => ENABLE NEXT BUTTON
	if(current_page_response < total_pages_response){
		$('.response-list-pag .order-list-pag__number-next').prop('disabled',false);
	}

	// RENDER OPTIONS FOR SELECT WITH PAGE NUMBERS
	// BY DEFAULT WE HAVE AT LEAST ONE PAGE
	var p_options = '<option value="1">1</option>';
	if(total_pages_response >= 2){
		for(i=2; i<=total_pages_response; i++){
			var selected = '';
			if(i == current_page_response)
				selected = 'selected="selected"';
			p_options = p_options.concat('<option value="'+i+'" '+selected+'>'+i+'</option>')
		}
	}

	// UPDATE DASHBOARD PAGINATION ELEMENTS
	// 1. TOTAL COUNTER
	// 2. PAGES (OPTIONS IN SELECT ELEMENT)
	// 3. TOTAL NUMBER OF PAGES
	$('#total_response_count_by_status').html(total_response);
	$('.response-list-pag .order-list-pag__number-list').html(p_options);
	$('.response-list-pag .order-list-pag__number-total').html(total_pages_response);
}
/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES END */

/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES */
// WE NEED A DEFAULT VARIABLES FOR PAGINATION (GLOBAL FOR THIS PAGE)
// 1. COUNTE ITEMS(ORDERS, INQUIRY, PO, ETC.)
// 2. NUMBER OF ITEMS PER PAGE(USED ONLY ON PAGE TO RENDER PAGES NUMBERS)
// 3. CURRENT PAGE => DEFAULT WE ARE ON FIRST PAGE
// 4. TOTAL NUMBER OF PAGES => DEFAULT SHULD BE 1 PAGE
// 5. FILTERS VARS (HERE WE ARE USING STATUS FILTERING)
var total = 0;
var per_page = <?php echo (int)$request_per_page;?>;
var current_page = 1;
var total_pages = 1;
var current_request;
var current_company;

function dashboardPagination(){
	// CALCULATE TOTAL NUMBER OF PAGES
	total_pages = Math.ceil(total/per_page);

	// IF THERE ARE 0 RESULTS  => WE ARE ON FIRST PAGE
	if(total_pages == 0)
		total_pages = 1;

	// IF TOTAL NUMBER OF PAGES IS NOT MORE THAN 1 => DISABLE PREV/NEXT BUTTONS
	if(total_pages == 1){
		$('.request-list-pag .order-list-pag__number-next').prop('disabled',true);
		$('.request-list-pag .order-list-pag__number-prev').prop('disabled',true);
	}

	// IF CURRENT PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page == 1){
		$('.request-list-pag .order-list-pag__number-prev').prop('disabled',true);
	}

	// IF CURRENT PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page > 1){
		$('.request-list-pag .order-list-pag__number-prev').prop('disabled',false);
	}

	// IF CURRENT PAGE IS LESS THAN "total_pages" => ENABLE NEXT BUTTON
	if(current_page < total_pages){
		$('.request-list-pag .order-list-pag__number-next').prop('disabled',false);
	}

	// RENDER OPTIONS FOR SELECT WITH PAGE NUMBERS
	// BY DEFAULT WE HAVE AT LEAST ONE PAGE
	var p_options = '<option value="1">1</option>';
	if(total_pages >= 2){
		for(i=2; i<=total_pages; i++){
			var selected = '';
			if(i == current_page)
				selected = 'selected="selected"';
			p_options = p_options.concat('<option value="'+i+'" '+selected+'>'+i+'</option>')
		}
	}

	// UPDATE DASHBOARD PAGINATION ELEMENTS
	// 1. TOTAL COUNTER
	// 2. PAGES (OPTIONS IN SELECT ELEMENT)
	// 3. TOTAL NUMBER OF PAGES
	$('#total_orders_count_by_status').html(total);
	$('.request-list-pag .order-list-pag__number-list').html(p_options);
	$('.request-list-pag .order-list-pag__number-total').html(total_pages);
}
/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES END */

var remove_request = function($this){
	var request = $this.data('request');
	var $parentLi = $('#seller-request-list .order-users-list__item[data-request="' + request + '"]');
	var $parentUl = $parentLi.closest('.order-users-list-ul');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/delete_request',
		data: { request : request },
		beforeSend: function(){ showLoader('#seller-request-list'); },
		dataType: 'json',
		success: function(data){
			hideLoader('#seller-request-list');
			systemMessages( data.message, data.mess_type );

			if(data.mess_type == 'success'){
				current_page_response = 1;
				current_request = '';
				resetSearchForm();

				$parentLi.fadeOut('slow', function(){
					$(this).remove();

					if(!$parentUl.find('li').length)
						$('#seller-request-list .order-users-list-ul').html('<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> You do not have any requests.</div></li>');

					requestListApi.reinitialise();

					$('#seller-response-list .order-users-list-ul').html('<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Please select response first.</div></li>');
					responseListApi.reinitialise();
				});
			}
		}
	});
}

var activate_request = function($this){
	var request = $this.data('request');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/change_request_status',
		data: { request : request },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
			    if (resp.remove_status !== "") {
                    $this.find('span[data-request-status="true"]').html(resp.remove_status);
                }
				$this.find('.ep-icon').removeClass('ep-icon_'+resp.status).addClass('ep-icon_'+resp.remove_status);
			}
		}
	});
}

var aprove_partnership = function($this){
	var partner = $this.data('partner');
	var company = $this.data('company');
	var response = $this.data('response');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/set_partnership',
		data: { partner: partner, company:company },
		beforeSend: function(){ showLoader('#columns-content-right'); },
		dataType: 'json',
		success: function(resp){
			hideLoader('#columns-content-right');
			systemMessages( resp.message, resp.mess_type );
			if(resp.mess_type == 'success'){
				$this.closest('.order-users-list__item')
					.find('.ep-icon_new-stroke').toggleClass('ep-icon_new-stroke ep-icon_ok-circle');

				$this.closest('.dropdown-menu').find('.btn-decline').remove();

				$this.replaceWith('<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this response?" data-callback="remove_partnership" data-response="'+response+'" href="#" title="Remove partnership"><i class="ep-icon ep-icon_trash-stroke"></i> <span class="txt">Remove</span></a>');
			}
		}
	});
}

var decline_partnership = function($this){
	var partner = $this.data('partner');
	var company = $this.data('company');
	var response = $this.data('response');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/decline_partnership',
		data: { partner: partner,company:company,response:response },
		beforeSend: function(){ showLoader('#columns-content-right'); },
		dataType: 'json',
		success: function(resp){
			hideLoader('#columns-content-right');
			systemMessages( resp.message, resp.mess_type );
			if(resp.mess_type == 'success'){
				$this.closest('.order-users-list__item')
					.find('.ep-icon_new-stroke').toggleClass('ep-icon_new-stroke ep-icon_remove-circle').addClass('fs-14');

				$this.closest('.dropdown-menu').find('.btn-aprove').remove();
				$this.replaceWith('<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this response?" data-callback="remove_partnership" data-response="'+response+'" href="#" title="Remove partnership"><i class="ep-icon ep-icon_trash-stroke"></i> <span class="txt">Remove</span></a>');
			}
		}
	});
}

var remove_partnership = function($this){
	var response = $this.data('response');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/remove_response',
		data: { response: response },
		beforeSend: function(){ showLoader('#columns-content-right'); },
		dataType: 'json',
		success: function(resp){
			hideLoader('#columns-content-right');
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				var $partnerLi = $this.closest('.order-users-list__item');
				var $partnerUl = $partnerLi.closest('#seller-response-list .order-users-list-ul');

				$partnerLi.fadeOut('slow', function(){
					$(this).remove();

					if(!$partnerUl.find('.order-users-list__item').length)
						$partnerUl.html('<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Please select request.</div></li>');

					responseListApi.reinitialise();
				});
			}
		}
	});
}

function getRequests(){
	$('#request-actions').html('');
	$('#request-country').html('');
	$('#seller-response-list .order-users-list-ul').html('<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Please select request first.</div></li>');
	responseListApi.reinitialise();
	resetSearchForm();

	var modal = false;

	if(window.innerWidth < 661){
		modal = true;
	}

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>b2b/ajax_b2b_operation/get_requests',
		data: {company: current_company, page: current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#seller-request-list');
		},
		success: function(resp){
			hideLoader('#seller-request-list');

			if(resp.mess_type == 'success' || resp.mess_type == 'info'){
				total = resp.total_requests_by_company;
				dashboardPagination();

				var html = '<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> 0 requests found by this search.</div></li>';
				if(resp.mess_type == 'success'){
					html = resp.requests;
				}
				$('#seller-request-list ul').html(html);
				requestListApi.reinitialise();

				if(modal){
					requestsInformationFancybox();
				}
			} else {
				systemMessages( resp.message, resp.mess_type );
			}


		}
	});
}

function getRequestResponse(){
	if(current_request != '' && current_request != undefined){
		var modal = false;

		if(window.innerWidth < 992){
			modal = true;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>b2b/ajax_b2b_operation/get_request_response',
			data: {request: current_request, page: current_page_response, status: current_response_status},
			dataType: 'JSON',
			beforeSend: function(){
				showLoader('#seller-response-list');
			},
			success: function(resp){
				hideLoader('#seller-response-list');

				if(resp.mess_type == 'success' || resp.mess_type == 'info'){

					if(modal){
						responseInformationFancybox(resp.responses);
						$('#seller-response-list .order-users-list-ul').html(resp.responses);
					}else{
						total_response = resp.total_response_by_status;
						dashboardPaginationResponse();

						var html = '<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> 0 response found by this search.</div></li>';

						if(resp.mess_type == 'success'){
							html = resp.responses;
						}

						$('#seller-response-list .order-users-list-ul').html(html);

						responseListApi.reinitialise();
					}

				} else{
					systemMessages( resp.message, resp.mess_type );
				}
			}
		});
	}else{
		systemMessages( 'Select request', 'info' );
	}
}

function resetSearchForm(){
	total_response = 0;
	dashboardPaginationResponse();
	current_response_status = "";
	$('.search-my-order__status').prop('selectedIndex',0);
}

var callGetRequests = function($thisBtn){
	$thisBtn.addClass('active').siblings().removeClass('active');
	$('.filter-request').html('<a href="' + $thisBtn.data('link') + '" target="_blank">' + $thisBtn.find('.order-users-list__number').text() + '</a>');
	current_company = $thisBtn.data('company');
	current_request = '';

	resetSearchForm();
	getRequests();
};

var callGetResponse = function($thisBtn){
	$thisBtn.addClass('active').siblings().removeClass('active');
	current_request = $thisBtn.data('request');
	$('.filter-response').html('All');

	var $actions = $thisBtn.find('.order-users-list__actions').html();
	$('#request-actions').html($actions);

	$('#request-country').html('<a href="' + $thisBtn.data('link') + '" target="_blank">'+$thisBtn.find('.order-users-list__number').html()+'</a>');
	resetSearchForm();
	getRequestResponse();
};
// end load response list

// view full message
var messageMore = function($thisBtn){
	$thisBtn.toggleClass('ep-icon_arrow-down ep-icon_arrow-up')
		.parent().toggleClass('active');

	responseListApi.reinitialise();
};
// end view full message

var responseInformationFancybox = function(html){
	$.fancybox.open({
		title: '&ensp;',
		href: '#columns-content-right'
	},{
		width		: fancyW,
		height		: '100%',
		maxWidth	: 700,
		autoSize	: false,
		loop : false,
		helpers : {
			title: {
				type: 'inside',
				position: 'top'
			},
			overlay: {
				locked: true
			}
		},
		modal: true,
		closeBtn : true,
		padding : fancyP,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		lang : __site_lang,
		i18n : translate_js_one({plug:'fancybox'}),
		beforeLoad : function() {
			this.width = fancyW;
			this.padding = [fancyP,fancyP,fancyP,fancyP];
		},
		beforeShow : function() {
			$('#columns-content-right').addClass('columns-content__one--popup');
			setTimeout(function() {
				$('#seller-response-list').addClass('h-auto');
			}, 1);
			responseListApi.destroy();
		},
		afterClose : function() {
			$('#columns-content-right').attr({'style': ''});
			$('#columns-content-right').removeClass('columns-content__one--popup');
			$('#seller-response-list').removeClass('h-auto');

			$responseList = $('#seller-response-list');
			responseListApi = $responseList.jScrollPane().data('jsp');
		}
	});
}

var requestsInformationFancybox = function(html){
	$.fancybox.open({
		title: '&ensp;',
		href: '#columns-content-center'
	},{
		width		: fancyW,
		height		: '100%',
		maxWidth	: 700,
		autoSize	: false,
		loop : false,
		helpers : {
			title: {
				type: 'inside',
				position: 'top'
			},
			overlay: {
				locked: true
			}
		},
		modal: true,
		closeBtn : true,
		padding : fancyP,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		lang : __site_lang,
		i18n : translate_js_one({plug:'fancybox'}),
		beforeLoad : function() {
			this.width = fancyW;
			this.padding = [fancyP,fancyP,fancyP,fancyP];
		},
		beforeShow : function() {
			$('#columns-content-center').addClass('columns-content__one--popup');
			setTimeout(function(){
				$('#seller-request-list').addClass('h-auto');
			}, 1);

			requestListApi.destroy();
		},
		afterClose : function() {
			$('#columns-content-center').attr({'style': ''});
			$('#columns-content-center').removeClass('columns-content__one--popup');
			$('#seller-request-list').removeClass('h-auto');

			$requestList = $('#seller-request-list');
			requestListApi = $requestList.jScrollPane().data('jsp');
		}
	});
}
</script>

<div id="filter-panel" class="display-n">
	<?php tmvc::instance()->controller->view->display('new/b2b/request/filter_panel_view'); ?>
</div>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Requests</h1>

        <div class="dashboard-line__actions">
			<a class="btn btn-light" href="<?php echo __SITE_URL;?>b2b/reg" title="Add request" target="_blank">Add request</a>
			<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/96" title="View B2B Requests documentation" data-title="View B2B Requests documentation" target="_blank">User guide</a> -->

			<a class="btn btn-dark fancybox btn-filter" href="#filter-panel" data-mw="320" data-title="Search">
				<i class="ep-icon ep-icon_filter"></i> Filter
			</a>
		</div>
	</div>

	<div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('b2b_my_requests'); ?></span>
	</div>

	<div class="columns-content">

		<div id="columns-content-left" class="columns-content__one w-40pr-lg w-xl-300 w-100pr-m columns-content__one--300">
			<div class="columns-content__ttl">All companies</div>

			<div id="seller-company-list" class="order-users-list jscroll-init">
				<ul class="order-users-list-ul order-users-list-ul--h-50">
					<?php foreach($companies as $company){?>
					<li class="order-users-list__item flex-card call-function" data-callback="callGetRequests" data-company="<?php echo $company['id_company'];?>" data-link="<?php echo getCompanyURL($company);?>">
						<div class="order-users-list__img image-card-center flex-card__fixed">
							<span class="link">
								<img
									class="image"
									src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>"
									alt="<?php echo $company['name_company'];?>"/>
							</span>
						</div>
						<div class="order-users-list__detail flex-card__float">
							<div class="order-users-list__number">
								<?php echo $company['name_company'];?>
							</div>
							<div class="order-users-list__company">
								<img
                                    width="24"
                                    height="24"
                                    src="<?php echo getCountryFlag($countries[$company['id_country']]['country']);?>"
                                    alt="<?php echo $countries[$company['id_country']]['country'];?>"
                                />
								<span class="link"><?php echo $countries[$company['id_country']]['country'];?></span>
							</div>
						</div>
					</li>
					<?php }?>
				</ul>
			</div>
		</div>

		<div id="columns-content-center" class="columns-content__one w-60pr-lg dn-m columns-content__one--300">
			<div class="columns-content__ttl">
				<span>Requests from: </span>
				<span class="columns-content__ttl-name filter-request txt-normal"></span>
			</div>

			<div id="seller-request-list" class="order-users-list">
				<ul class="order-users-list-ul">
					<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Please select company first.</div></li>
				</ul>
			</div><!-- order-users-list -->

			<div class="order-list-pag request-list-pag">
				<div class="order-list-pag__total">
					<span class="order-list-pag__txt">Total</span>
					<span id="total_orders_count_by_status">0</span>
				</div>
				<div class="order-list-pag__number">
					<button class="order-list-pag__number-prev btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-left"></i></button>
					<span class="order-list-pag__number-text">Page</span>
					<select class="order-list-pag__number-list">
						<option>1</option>
					</select>

					<span class="order-list-pag__number-text">
						of <span class="order-list-pag__number-total">1</span>
					</span>
					<button class="order-list-pag__number-next btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-right"></i></button>
				</div>
			</div><!-- order-list-pag -->
		</div>

		<div id="columns-content-right" class="columns-content__one dn-lg">
			<div class="columns-content__ttl">
				<span>
					<span id="request-country" class="txt-normal pr-5 mw-250 text-nowrap display-ib"></span>
					Status:
					<span class="filter-response tt-capitalize txt-normal"></span>
				</span>

				<div id="request-actions"></div>
			</div>

			<div id="seller-response-list" class="order-users-list">
				<ul class="order-users-list-ul">
					<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Please select request first.</div></li>
				</ul>
			</div>

			<div class="order-list-pag response-list-pag">
				<div class="order-list-pag__total">
					<span class="order-list-pag__txt">Total</span>
					<span id="total_response_count_by_status">0</span>
				</div>
				<div class="order-list-pag__number">
					<button class="order-list-pag__number-prev btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-left"></i></button>
					<span class="order-list-pag__number-text">Page</span>
					<select class="order-list-pag__number-list">
						<option>1</option>
					</select>

					<span class="order-list-pag__number-text">
						of <span class="order-list-pag__number-total">1</span>
					</span>
					<button class="order-list-pag__number-next btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-right"></i></button>
				</div>
			</div><!-- order-list-pag -->
		</div>

	</div>
</div>
