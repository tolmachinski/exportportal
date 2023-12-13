<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-countdown-2-2-0/jquery.countdown.js');?>"></script>

<script type="text/javascript">
var itemDetailApi;
var $itemDetail;
var itemDetailTimeout;

var centerListApi;
var $centerList;
var centerListTimeout;

var $mainContent;
var currentAction = "<?php if(empty($id_bill) || empty($id_item) || $status_select == 'all') echo 'list_bills'; else echo 'search_bills';?>";

var widthChanged = false;
var heightChanged = false;

var calcHeightClasses = [{'minus':95, 'name': 'itemDetail', 'width': true}];

$(document).ready(function(){
	$centerList = $('#bills-users-list');
	centerListApi = $centerList.jScrollPane().data('jsp');

	// SIDEBAR NAVIGATION
	$('.dashboard-sidebar-sub').on('click', '.dashboard-sidebar-sub__item', function(e) {
		e.preventDefault();

		var $thisBtn = $(this);
		current_status = $thisBtn.data('status');
		current_type = $thisBtn.data('type');
		current_page = 1;
		currentAction = 'list_bills';
		var selectText = $thisBtn.find('.dashboard-sidebar-sub__text > span').text();

		$thisBtn.addClass('active').siblings().removeClass('active');
		$thisBtn.closest('.dashboard-statuses__item').siblings().find('.dashboard-sidebar-sub__item').removeClass('active');
		callResetSearchForm();
		loadBillsList();

		$('#filter-selected-ttl').html(selectText);
		updateStatusesCounters();
	});
	// SIDEBAR NAVIGATION END

	$('#bills-users-list').on('click', '.order-users-list__item', function(e) {
		e.preventDefault();

		var $this = $(this);
		var bill = $this.data('bill');

		$this.addClass('active').siblings().removeClass('active');

		loadBillInfo(bill);
    });

    $(globalThis).on('billing:send-cancellation-request', function (e, bill) {
        loadBillInfo(bill);
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

		if(currentAction == "list_bills")
			loadBillsList();
		else
			searchBillsList();

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

		if(currentAction == "list_bills")
			loadBillsList();
		else
			searchBillsList();

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

		if(currentAction == "list_bills")
			loadBillsList();
		else
			searchBillsList();

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */

	$('#search_bills_form input[name=keywords]').on('change', function(){
		var $thisInput = $(this);
		var search_filter = $('#search_bills_form').find('input[name=status]').val();

		if(search_filter == 'bill_number'){
			var number = toOrderNumber($thisInput.val());
			if(number)
				$thisInput.val(number);
			else{
				systemMessages('<?php echo translate('systmess_bill_number_incorect_message', null, true); ?>', 'error' );
				$thisInput.val('');
				return false;
			}
		}
	});

	$('#search_bills_form select[name=type]').on('change', function(){
		var $this = $(this);

		var status = $this.find('option:selected').closest('optgroup').data('status');
		$('#search_bills_form').find('input[name=status]').val(status);

		var $thisInput = $('#search_bills_form input[name=keywords]');

		if(status == 'by_number'){
			var number = toOrderNumber($thisInput.val());
			if(number)
				$thisInput.val(number);
			else{
				$thisInput.val('');
				return false;
			}
		}
	});

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
// 3. current PAGE => DEFAULT WE ARE ON FIRST PAGE
// 4. TOTAL NUMBER OF PAGES => DEFAULT SHULD BE 1 PAGE
// 5. FILTERS VARS (HERE WE ARE USING STATUS FILTERING)
var total = <?php echo (int)$status_select_count;?>;
var per_page = <?php echo (int)$bills_per_page;?>;
var current_page = 1;
var total_pages = 1;
var current_status = "<?php echo $active_status;?>";
var current_type = "<?php echo $active_type;?>";
var search_keywords = "";

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

	// IF current PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page == 1){
		$('.order-list-pag__number-prev').prop('disabled', true);
	}

	// IF current PAGE IS MORE THAN 1 => ENABLE PREV BUTTON
	if(current_page > 1){
		$('.order-list-pag__number-prev').prop('disabled', false);
	}

	// IF current PAGE IS LESS THAN "total_pages" => ENABLE NEXT BUTTON
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

function searchBillsList(){
	var $thisForm = $('#search_bills_form');
	var $searchInput = $thisForm.find('input[name=keywords]');
	var searchKeywords = $searchInput.val();
	$('.dashboard-sidebar-sub__item').removeClass('active');

	var search_filter = $thisForm.find('input[name=status]').val();
	var search_type = $thisForm.find('select[name=type]').val();

	if(search_filter == 'by_number'){
		var number = toOrderNumber(searchKeywords);
		if(number)
			$searchInput.val(number);
		else{
			systemMessages('<?php echo translate('systmess_bill_number_incorect_message', null, true); ?>', 'error' );
			$searchInput.val('');
			return false;
		}
	}

	$.ajax({
		url: '<?php echo __SITE_URL; ?>billing/ajax_bill_operations/search_bills',
		type: 'POST',
		data:  {status:search_filter, type:search_type, keywords:searchKeywords, page:current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-center', 'Searching...');
			$thisForm.find('button[type=reset]').show();
		},
		success: function(resp){
			hideLoader('#columns-content-center');

			$('.btn-filter').addClass('btn-filter--active');
			$('#filter-selected-ttl').html('Search result');
			$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select the bill.</div>');

			if(resp.mess_type == 'success'){
				total = resp.total_bills_by_status;
				current_status = resp.status;
				current_type = resp.type;
				search_keywords = searchKeywords;
				dashboardPagination();
				$('#bills-users-list ul').html(resp.bills);
				centerListApi.reinitialise();

			}else if(resp.mess_type == 'info'){
				total = 0;
				search_keywords = '';
				dashboardPagination();
				$('#bills-users-list ul').html('<li class="w-100pr p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> '+resp.message+'</div></li>');
				centerListApi.reinitialise();
			} else{
				systemMessages(resp.message, resp.mess_type );
			}
		}
	});
	return false;
}

var search_bill = function(){
	currentAction = 'search_bill';
	current_page = 1;
	searchBillsList();
}

function resetSearchForm(){
	callResetSearchForm();

	current_status = "init";
	current_page = 1;
	var $statusBtn = $('.dashboard-sidebar-tree__subtree').find('a[data-status='+current_status+']');
	$statusBtn.closest('li').addClass('active').siblings().removeClass('active');
	current_type = $statusBtn.data('type');

	var selectText = $statusBtn.find('.dashboard-sidebar-sub__text > span').text();
	$('#filter-selected-ttl').html(selectText);

    loadAllBills();
};

function callResetSearchForm(){
	currentAction = 'list_bills';
	var $bills_form = $('#search_bills_form');
	var search_filter = $bills_form.find('select[name=type]').val();

	if(search_filter == 'by_number'){
		var new_url = '<?php echo __SITE_URL.'billing/my'?>';
		if(window.history.pushState){
			history.pushState({}, 'Export Portal Bills &raquo; International export & import, b2b and trading in world', new_url);
		} else{
			window.location.href = new_url;
		}
	}

	$('.btn-filter').removeClass('btn-filter--active');
	$bills_form.find('select[name=type]').prop('selectedIndex',0);
	$bills_form.find('input[name=keywords]').val('');
	$bills_form.find('input[name=status]').val('');
	$bills_form.find('button[type=reset]').hide();
}

// UPDATE STATUSES COUNTERS
function updateStatusesCounters(){
	$.ajax({
		type: 'POST',
		url: 'billing/ajax_update_sidebar_counters',
		dataType: 'JSON',
		success: function(resp){
			$('.dashboard-sidebar-sub__counter').text('0');

			$.each(resp.counters, function(key, val){
				$('#counter-'+key).html(val);
			});
		}
	});
}
// END UPDATE STATUSES COUNTERS

var loadAllBills = function(){
	current_status = 'all';
	current_type = 'all';
	current_page = 1;
	currentAction = 'list_bills';

	$('.dashboard-sidebar-sub__item').removeClass('active');
	callResetSearchForm();
	loadBillsList();

	$('#filter-selected-ttl').html('All bills');
	updateStatusesCounters();
};

function loadBillsList(){
	$.ajax({
		type: "POST",
		context: $(this),
		url: "billing/ajax_bill_operations/my_bills",
		data: { status: current_status, type:current_type, page: current_page },
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-center');
		},
		success: function(resp){
			hideLoader('#columns-content-center');
			$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Bill.</div>');

			if(resp.mess_type == 'success'){
				total = resp.total_bills_by_status;
				dashboardPagination();
				$('#bills-users-list .order-users-list-ul').html(resp.bills);
				centerListApi.reinitialise();
			}
		}
	});
}

function create_extend_request_callback(resp){
	loadBillInfo(resp.bill);
}

function cancel_extend_callback(resp){
	loadBillInfo(resp.bill);
}

function payment_callback(data){
	if((data.mess_type === 'success')){
		current_page = 1;
		if(currentAction === 'search_bills'){
			searchBillsList();
		} else{
			loadBillsList();
		}
		updateStatusesCounters();
		closeFancyBox();
	}
}

function loadBillInfo(bill){
	var modal = false;

	if(window.innerWidth < 992){
		modal = true;
	}

	$.ajax({
		type: "POST",
		context: $(this),
		url: "billing/ajax_bill_operations/my_bill_info",
		data: { status: current_status, type:current_type, bill:bill },
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-right');
		},
		success: function(resp){
			hideLoader('#columns-content-right');

			if(resp.mess_type == 'success'){
				if(modal){
					billInformationFancybox(resp.bill);
					$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select bill.</div>');
				}else{
					$('.wr-orders-detail-b').html(resp.bill);
					$('#order-detail-b').find('.order-detail-table').removeClass('order-detail-table');
					$itemDetail = $('.order-detail__scroll');
					itemDetailApi = $itemDetail.jScrollPane().data('jsp');
				}

				if(resp.show_timeline == true){
					var selectedDate = new Date().valueOf() + resp.expire;

					$('.order-detail__status-timer').countdown(selectedDate.toString(), {defer: true})
					.on('update.countdown', function(event) {
						var format_clock = '<div class="txt-green">%D days %H hours %M min</div>';
						if(resp.expire < 7200000){
							format_clock = '<div class="txt-red">%D days %H hours %M min</div>';
						}
						$(this).html(event.strftime(format_clock));
					}).on('finish.countdown', function(event) {
						$(this).html('<div class="txt-red">The payment time has expired!</div>');
					}).countdown('start');
				}

				$('.order-popover').popover({
					container: 'body',
					trigger: 'hover'
				});
			}else{
				systemMessages( resp.message, resp.mess_type );
			}
		}
	});
}

var billInformationFancybox = function(html){
	$.fancybox.open({
		title: 'Bill information',
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
		onUpdate : function() {	}
	});
}

<?php if (!empty($id_bill)) {?>
	$(function() {
    	loadBillInfo(parseInt("<?php echo $id_bill;?>", 10));
		$('.btn-filter').addClass('btn-filter--active');
	});
<?php }?>
</script>

<div id="filter-panel" class="display-n">
	<?php views()->display('new/user/bills/filter_panel_view'); ?>
</div>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Billing</h1>

        <div class="dashboard-line__actions">
			<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/billing_doc?user_type=<?php echo strtolower(user_group_type());?>" title="View bills documentation" data-title="View bills documentation" target="_blank">User guide</a> -->

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
		<span><?php echo translate('billing_my_description'); ?></span>
	</div>

	<div class="columns-content">
		<div id="columns-content-left" class="columns-content__one dn-m w-40pr-lg columns-content__one--250">
			<div class="columns-content__ttl">Statuses</div>

			<?php tmvc::instance()->controller->view->display('new/user/bills/sidebar_view'); ?>
		</div><!-- sidebar -->

		<div id="columns-content-center" class="columns-content__one w-60pr-lg w-100pr-m columns-content__one--350">
			<div id="filter-selected-ttl" class="columns-content__ttl">All bills</div>

			<div id="bills-users-list" class="order-users-list jscroll-init" >
                <ul class="order-users-list-ul clearfix">
                	<?php tmvc::instance()->controller->view->display('new/user/bills/bills_list_view'); ?>
                </ul>
            </div><!-- bills-users-list -->

			<?php tmvc::instance()->controller->view->display('new/user/bills/pagination_view'); ?>
		</div><!-- center block -->

		<div id="columns-content-right" class="columns-content__one dn-lg">
			<div class="columns-content__ttl">Bill information</div>

			<div class="wr-orders-detail-b">
				<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select the bill.</div>
			</div>
		</div><!-- right block -->
	</div>
</div>
