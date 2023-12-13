<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-countdown-2-2-0/jquery.countdown.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<?php views()->display('new/file_upload_scripts'); ?>

<script type="text/javascript">
var itemDetailApi;
var $itemDetail;
var itemDetailTimeout;

var centerListApi;
var $centerList;
var centerListTimeout;

var currentAction = 'list_orders';

var calcHeightClasses = [{'minus':95, 'name': 'itemDetail', 'width': true}];

<?php if(isset($id_order)){?>
	var order_selected = <?php echo $id_order;?>;
<?php }?>

$(document).ready(function() {
	$centerList = $('#order-users-list');
    setTimeout(function() {
        centerListApi = $centerList.jScrollPane().data('jsp');
    }, 100)

	// SIDEBAR NAVIGATION
	$('.dashboard-sidebar-sub').on('click', '.dashboard-sidebar-sub__item', function(e) {
		e.preventDefault();

		var $thisBtn = $(this);
		current_status = $thisBtn.data('status');
		current_page = 1;
		currentAction = 'list_orders';
		var selectText = $thisBtn.find('.dashboard-sidebar-sub__text > span').text();

		$('.dashboard-sidebar-sub__item.active').removeClass('active');

		$thisBtn.addClass('active');

		resetSearchForm();
		loadOrderList();

		$('#filter-selected-ttl').html(selectText);
		updateStatusesCounters();
	});
	// SIDEBAR NAVIGATION END

	$('#order-users-list').on('click', '.order-users-list__item', function(e) {
		e.preventDefault();

		var $this = $(this);
		var order = $this.data('order');

		$this.addClass('active').siblings().removeClass('active');

		showOrder(order);
	});

	/* DASHBOARD PAGINATION */

	dashboardPagination();

	$('body').on('click', '.order-list-pag__number-prev', function(e){
		if(current_page > 1){
			current_page = current_page - 1;
		}
		if(current_page <= 1){
			current_page = 1;
			$(this).prop('disabled', true);
		}
		if(current_page < total_pages)
			$('.order-list-pag__number-next').prop('disabled', false);

		if(currentAction == 'list_orders'){
			loadOrderList();
		} else{
			search_orders();
		}

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});

	$('body').on('click', '.order-list-pag__number-next', function(e){
		if(current_page < total_pages){
			current_page = current_page + 1;
		}
		if(current_page >= total_pages){
			current_page = total_pages;
			$(this).prop('disabled', true);
		}
		if(current_page > 1)
			$('.order-list-pag__number-prev').prop('disabled', false);

		if(currentAction == 'list_orders'){
			loadOrderList();
		} else{
			search_orders();
		}

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});

	$('body').on('change', '.order-list-pag__number-list', function(e){
		var $this = $(this);
		current_page = $this.val();
		if(current_page >= total_pages){
			current_page = total_pages;
			$('.order-list-pag__number-next').prop('disabled', true);
			$('.order-list-pag__number-prev').prop('disabled', false);
		} else if(current_page <= 1){
			current_page = 1;
			$('.order-list-pag__number-prev').prop('disabled', true);
			$('.order-list-pag__number-next').prop('disabled', false);
		} else{
			$('.order-list-pag__number-prev').prop('disabled', false);
			$('.order-list-pag__number-next').prop('disabled', false);
		}

		if(currentAction == 'list_orders'){
			loadOrderList();
		} else{
			search_orders();
		}

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});

	/* DASHBOARD PAGINATION END */

	<?php if(isset($id_order)){?>
		showOrder(<?php echo $id_order;?>);
		$('#filter-selected-ttl').html('Order number');
	<?php }?>
});

jQuery(window).on('resizestop', function () {
	if($(this).width() != widthBrowser){
		widthBrowser = $(this).width();
		widthChanged = true;

		calcWidthBlockSimple(calcHeightClasses, widthChanged);
		calcWidthBlockSimple([{'name': 'centerList', 'width': true}], widthChanged);
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
var total = <?php echo (int)$status_select_count;?>;
var per_page = <?php echo (int)$orders_per_page;?>;
var current_page = 1;
var total_pages = 1;
var current_status = "<?php echo $status_select;?>";

function dashboardPagination(){
	// CALCULATE TOTAL NUMBER OF PAGES
	total_pages = Math.ceil(total/per_page);

	// IF THERE ARE 0 RESULTS  => WE ARE ON FIRST PAGE
	if(total_pages == 0)
		total_pages = 1;

	// IF TOTAL NUMBER OF PAGES IS NOT MORE THAN 1 => DISABLE PREV/NEXT BUTTONS
	if(total_pages == 1){
		$('.order-list-pag__number-next').prop('disabled', true);
		$('.order-list-pag__number-prev').prop('disabled', true);
	}

	// IF CURRENT PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page > 1){
		$('.order-list-pag__number-prev').prop('disabled', false);
	}

	// IF CURRENT PAGE IS LESS THAN "total_pages" => ENABLE NEXT BUTTON
	if(current_page < total_pages){
		$('.order-list-pag__number-next').prop('disabled', false);
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
	$('.order-list-pag__number-list').html(p_options);
	$('.order-list-pag__number-total').html(total_pages);
}
/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES END */

var loadAllOrders = function(){
	current_status = 'all';
	current_page = 1;
	currentAction = 'list_orders';

	$('.dashboard-sidebar-sub__item.active').removeClass('active');

	resetSearchForm();
	loadOrderList();

	$('#filter-selected-ttl').html('All orders');
	updateStatusesCounters();
};

var loadOrderList = function(showOrder){

	if(showOrder == undefined)
		showOrder = false;

	var params = { status: current_status, type: 'order_list', page:current_page};

	if(current_status == 'order_number'){
		params.order = order_selected;
	}

	$.ajax({
		type: 'POST',
		url: '<?php echo getUrlForGroup('order/ajax_order_info');?>',
		data: params,
		dataType: 'JSON',
		beforeSend: function(){ showLoader('#columns-content-center'); },
		success: function(resp){
			hideLoader('#columns-content-center');

			if(resp.mess_type == 'success'){
				total = resp.total_orders_by_status;
				dashboardPagination();
				$('#order-users-list .order-users-list-ul').html(resp.orders_list);
				$('#order-users-list').jScrollPane();

				if(!showOrder){
					$('#order-detail-b').html('<div class="info-alert-b no-selected"><i class="ep-icon ep-icon_info-stroke"></i> Please select an order.</div>');
				}
			} else{
				systemMessages( resp.message, resp.mess_type );
			}
		}
	});
}

var showOrder = function(idOrder){
	var modal = false;

	if(window.innerWidth < 992){
		modal = true;
	}

	$.ajax({
		type: 'POST',
		url: '<?php echo getUrlForGroup('order/ajax_order_info')?>',
		data: { order: idOrder, type: 'order'},
		dataType: 'JSON',
		beforeSend: function(){ showLoader('#columns-content-right'); },
		success: function(resp){
			hideLoader('#columns-content-right');

			if(resp.mess_type == 'success'){

				if(modal){
					// $('#order-detail-b').html(resp.order_info);
					$('#order-detail-b').html('<div class="info-alert-b no-selected"><i class="ep-icon ep-icon_info-stroke"></i><span>Please select an order.</span></div>');
					orderInformationFancybox(resp.order_info);
				}else{
					$('#order-detail-b').html(resp.order_info);
					$('#order-detail-b').find('.order-detail-table').removeClass('order-detail-table');
					$itemDetail = $('#order-detail-'+idOrder+' .order-detail__scroll');
					itemDetailApi = $itemDetail.jScrollPane().data('jsp');
				}

				$('.rating-bootstrap').rating();

				if(resp.show_timeline == true){
					var selectedDate = new Date().valueOf() + resp.expire;
					$('.order-detail__status-timer').countdown(selectedDate.toString(), {defer: true})
					.on('update.countdown', function(event) {
						var format_clock = '%D days %H:%M';
						if(resp.expire < 86400000){
							format_clock = '%H:%M';
						}
						$(this).html(event.strftime(format_clock));
					}).on('finish.countdown', function(event) {
						$(this).html('<div class="txt-red">The time for this status has expired!</div>');
					}).countdown('start');
				}

				$('.order-popover').popover({
					container: 'body',
					trigger: 'hover'
				});
			} else{
				$('#order-detail-b').html('<div class="info-alert-b no-selected"><i class="ep-icon ep-icon_info-stroke"></i><span>Please select an order.</span></div>');
			}
		}
	});
}

var orderInformationFancybox = function(html){

    $.fancybox.open({
		title: 'Order information',
        content: html
    },{
        width		: fancyW,
        height		: 'auto',
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
		beforeShow : function() {
			centerListApi.reinitialise();
		},
		beforeLoad : function() {
			this.width = fancyW;
			this.padding = [fancyP,fancyP,fancyP,fancyP];
		},
		onUpdate : function() {
			//myRepositionFancybox();
		}
    });
}

// UPDATE STATUSES COUNTERS
function updateStatusesCounters(){
	$.ajax({
		type: 'POST',
		url: '<?php echo getUrlForGroup('order/ajax_update_sidebar_counters')?>',
		dataType: 'JSON',
		success: function(resp){
			$.each(resp.counters, function(key, val){
				$('#counter-'+key).html(val);
			});
		}
	});
}

function cancel_request_callback(resp){
	showOrder(resp.order);
}

function create_extend_request_callback(resp){
	showOrder(resp.order);
}

function cancel_extend_callback(resp){
	showOrder(resp.order);
}

function addFeedbackCallback(resp){
	showOrder(resp.order);
}

function editFeedbackCallback(resp){
	return true;
}

function addReplyFeedbackCallback(resp){
	return true;
}

function editReplyFeedbackCallback(resp){
	return true;
}

function edit_document_callback(){
	$("#go_to_doc_list").trigger("click");
}

function upload_signed_doc_callback(){
	$("#go_to_doc_list").trigger("click");
}

<?php if(have_right('manage_shipper_orders')){?>

var confirm_delivery = function(opener){
	var $this = $(opener);
	var order = $this.data('order');

	$.ajax({
		type: 'POST',
		url: '<?php echo getUrlForGroup('order/ajax_order_operations/shipper_confirm_delivery');?>',
		data: { order : order},
		dataType: 'JSON',
		success: function(resp){
			if(resp.mess_type == 'success'){
				$this.remove();
				showOrder(order);
			}
			systemMessages( resp.message, resp.mess_type );
		}
	});
}

<?php }?>

<?php if(have_right('sell_item')){?>

	// START PACKAGING ACTION
	var start_packaging = function(opener){
        var $this = $(opener);
		var order = $this.data('order');

        $.ajax({
            type: 'POST',
            url: '<?php echo getUrlForGroup('order/ajax_order_operations/start_packaging');?>',
            data: { order : order},
            dataType: 'JSON',
            success: function(resp){
                if(resp.mess_type == 'success'){
                    $this.remove();
                    current_status = 'preparing_for_shipping';
                    current_page = 1;
					loadOrderList(true);
					showOrder(order);
					update_status_counter_active('Preparing for shipping', current_status);
				 }
                systemMessages( resp.message, resp.mess_type );
            }
        });
	}

	function addReviewReplyCallback(resp){

	}

	function editReviewReplyCallback(resp){

	}
<?php }?>

<?php if(have_right('buy_item')){?>

	function assign_shipper_callback(resp){
		if(resp.mess_type == 'success'){
			current_status = resp.order_status_alias;
			loadOrderList(true);
			showOrder(resp.order);
			update_status_counter_active(resp.order_status_name, current_status);
		}
	}

	function payment_callback(data){
		if(data.mess_type == 'success'){
            current_status = 'payment_processing';
			loadOrderList(true);
			showOrder(data.order);
			update_status_counter_active('Payment processing', current_status);
            closeFancyBox();
		}
	}

	// CONFIRM THE DELIVERY ACTION
    var confirm_shipping_complete = function(opener){
        var $this = $(opener);
        var order = $this.data('order');
        $.ajax({
            type: 'POST',
            url: '<?php echo getUrlForGroup('order/ajax_order_operations/confirm_shipping_complete');?>',
            data: { order : order},
            dataType: 'JSON',
            success: function(resp){
                if(resp.mess_type == 'success'){
                    $this.remove();
                    current_status = 'shipping_completed';
                    current_page = 1;
					loadOrderList(true);
					showOrder(order);
					update_status_counter_active('Shipping completed', current_status);
                }
                systemMessages( resp.message, resp.mess_type );
            }
        });
	}

	// CONFIRM THE DELIVERY ACTION
    var confirm_order_completed = function(opener){
        var $this = $(opener);
        var order = $this.data('order');
        $.ajax({
            type: 'POST',
            url: '<?php echo getUrlForGroup('order/ajax_order_operations/confirm_order_completed');?>',
            data: { order : order},
            dataType: 'JSON',
            success: function(resp){
                if(resp.mess_type == 'success'){
                    $this.remove();
                    current_status = 'order_completed';
                    current_page = 1;
					loadOrderList(true);
					showOrder(order);
					update_status_counter_active('Order completed', current_status);
                    $('.dashboard-statuses__item#finished').addClass('active').siblings().removeClass('active');
				}
                systemMessages( resp.message, resp.mess_type );
            }
        });
    }

	function addReviewCallback(resp){
		showOrder(resp.order);
	}

	function editReviewCallback(resp){
		return true;
	}
<?php }?>

function deleteReviewCallback(resp){
	showOrder(resp.order);
}

var update_status_counter_active = function(title, current_status){
	$('#filter-selected-ttl').html(title);
	updateStatusesCounters();
	$('.dashboard-sidebar-sub__item.active').removeClass('active');
	$('.dashboard-sidebar-sub__item[data-status='+current_status+']').addClass('active');
}

var orderFancyboxStatusVideo = function($this){
	var link = $this.attr('href');
	var title = $this.data('title');
	BootstrapDialog.closeAll();

	$.fancybox.open({
		title: title,
		href: link
	},{
		width		: '100%',
		height		: 'auto',
		maxWidth	: 1920,
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
		beforeShow : function() {
			centerListApi.reinitialise();
		},
		beforeLoad : function() {
			this.width = fancyW;
			this.padding = [fancyP,fancyP,fancyP,fancyP];
		}
	});
}

// SEARCH FORM
var resetSearchForm = function($this){
	currentAction = 'list_orders';
	var $searchForm = $('#search_order_form');
	var search_filter = $searchForm.find('select[name=search_filter]').val();

	if(search_filter == 'order_number'){
		var new_url = '<?php echo getUrlForGroup('order/my');?>';
		if(window.history.pushState){
			history.pushState({}, 'Export Portal Orders &raquo; International export & import, b2b and trading in world', new_url);
		} else{
			window.location.href = new_url;
		}
	}

	$('.btn-filter').removeClass('btn-filter--active');

	$searchForm.find('select[name=search_filter]').prop('selectedIndex',0);
	$searchForm.find('input[name=keywords]').val('');
	$searchForm.find('button[name=reset]').hide();
}

var search_order = function(){
	currentAction = 'search_order';
	current_page = 1;
	search_orders();
}

function search_orders(){
	var $thisForm = $('#search_order_form');
	var $searchInput = $thisForm.find('input[name=keywords]');
	var searchKeywords = $searchInput.val();
	$('.dashboard-sidebar-sub__item.active').removeClass('active');

	var search_filter = $thisForm.find('select[name=search_filter]').val();
	if(search_filter == 'order_number'){
		var number = toOrderNumber(searchKeywords);
		if(number)
			$searchInput.val(number);
		else{
			systemMessages('Error: Incorrect order number.', 'error' );
			$searchInput.val('');
			return false;
		}
	}

	$.ajax({
		url: '<?php echo getUrlForGroup('order/ajax_order_info'); ?>',
		type: 'POST',
		data:  {type:"search_orders", keywords:searchKeywords, search_filter:search_filter, page:current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-center', 'Searching...');
			$thisForm.find('button[type=reset]').show();
		},
		success: function(resp){
			hideLoader('#columns-content-center');
			$('.btn-filter').addClass('btn-filter--active');

			$('#filter-selected-ttl').html('Search result');
			$('#order-detail-b').html('<div class="info-alert-b no-selected"><i class="ep-icon ep-icon_info-stroke"></i> Please select an order.</div>');

			if(resp.mess_type == 'success'){
				total = resp.total_orders_by_status;
				current_status = resp.status;
				dashboardPagination();
				$('#order-users-list ul').html(resp.orders_list);
				centerListApi.reinitialise();
			}else if(resp.mess_type == 'info'){
				total = 0;
				$('#order-users-list .order-users-list-ul').html(resp.orders_list);
				$('#order-users-list').jScrollPane();
			} else{
				systemMessages(resp.message, resp.mess_type );
			}
		}
	});
	return false;
}

<?php if(have_right_or('moderate_content,write_reviews')){?>
	var delete_review = function(obj){
		var $this = $(obj);
		var review = $this.data('review');

		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('reviews/ajax_review_operation/delete');?>',
			data: { checked_reviews : review},
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					closeFancyBox();
					deleteReviewCallback(resp);
				}
			}
		});
	}
<?php }?>
</script>

<div id="filter-panel" class="display-n">
	<?php tmvc::instance()->controller->view->display('new/order/filter_panel_view'); ?>
</div>

<div class="container-center dashboard-container">

	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My orders</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php //echo getUrlForGroup('user_guide/popup_forms/show_doc/order_doc?user_type='.strtolower(user_group_type()));?>" title="View order documentation" data-title="View order documentation" target="_blank">User guide</a> -->

            <a class="btn btn-light pl-20 pr-20 w-100pr-m js-customs-calculator"
               href="https://customsdutyfree.com/duty-calculator"
               title="Customs calculator"
               data-title="Customs calculator"
               rel="nofollow"
               target="_blank">
               <i class="ep-icon ep-icon_customs-calculator pr-10"></i>Customs calculator
            </a>

			<a class="btn btn-dark fancybox btn-filter" href="#filter-panel" data-mw="320" data-title="Filter panel">
				<i class="ep-icon ep-icon_filter"></i> Filter
			</a>

			<a id="columns-content-left-btn" class="btn btn-primary fancybox" href="#dashboard-statuses" data-mw="320" data-mnh="100%" data-title="Statuses">
                Statuses
            </a>
		</div>
	</div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('order_my_description'); ?></span>
	</div>

    <div class="columns-content">

		<div id="columns-content-left" class="columns-content__one dn-m w-40pr-lg columns-content__one--250">
			<div class="columns-content__ttl">Statuses</div>

			<?php tmvc::instance()->controller->view->display('new/order/sidebar_view'); ?>
		</div><!-- sidebar -->

		<div id="columns-content-center" class="columns-content__one w-60pr-lg w-100pr-m columns-content__one--350">
			<div id="filter-selected-ttl" class="columns-content__ttl">All orders</div>

            <div id="order-users-list" class="order-users-list jscroll-init" >
                <ul class="order-users-list-ul clearfix">
					<?php tmvc::instance()->controller->view->display('new/order/order_list_view'); ?>
                </ul>
            </div><!-- order-users-list -->

            <?php tmvc::instance()->controller->view->display('new/order/order_pagination_view'); ?>
        </div><!-- center block -->

        <div id="columns-content-right" class="columns-content__one dn-lg">
			<div class="columns-content__ttl">
				<span>Order information</span>
			</div>

            <!-- Order detail -->
            <div id="order-detail-b" class="wr-orders-detail">
                <div class="info-alert-b no-selected">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>Please select an order.</span>
                </div>
            </div>
		</div><!-- right block -->

	</div><!-- columns-content -->

</div>
