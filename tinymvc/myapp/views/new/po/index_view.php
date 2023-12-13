
<script type="text/javascript">
var itemDetailApi;
var $itemDetail;
var itemDetailTimeout;

var centerListApi;
var $centerList;
var centerListTimeout;

var $mainContent;
var currentAction = '<?php if(isset($statuses[$status_select]) || $status_select == 'all'){?>list_po<?php } else{?>search_po<?php }?>';

var widthChanged = false;
var heightChanged = false;

var calcHeightClasses = [{'minus':95, 'name': 'itemDetail', 'width': true}];

$(document).ready(function(){
	$centerList = $('#po-users-list');
	centerListApi = $centerList.jScrollPane().data('jsp');

	// NAV SIDEBAR
	$('.dashboard-sidebar-sub').on('click', '.dashboard-sidebar-sub__item', function(e) {
		e.preventDefault();

		var $thisBtn = $(this);
		current_status = $thisBtn.data('status');
		current_page = 1;
		currentAction = 'list_po';
		var selectText = $thisBtn.find('.dashboard-sidebar-sub__text > span').text();

		$thisBtn.addClass('active').siblings().removeClass('active');
		loadPoList();

		$('#filter-selected-ttl').html(selectText);
		updateStatusesCounters();
	});
	// NAV SIDEBAR END

	$('#po-users-list').on('click', '.order-users-list__item', function(e) {
		e.preventDefault();

		var $this = $(this);
		var po = $this.data('po');

		$this.addClass('active').siblings().removeClass('active');

		showPo(po);
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

		if(currentAction == "list_po")
			loadPoList();
		else
			searchPoList();

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

		if(currentAction == "list_po")
			loadPoList();
		else
			searchPoList();

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

		if(currentAction == "list_po")
			loadPoList();
		else
			searchPoList();

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */

	$('#search_po_form input[name=keywords]').on('change', function(){
		var $thisInput = $(this);
		var search_filter = $('#search_po_form').find('select[name=search_filter]').val();
		if(search_filter == 'po_number'){
			var number = toOrderNumber($thisInput.val());
			if(number)
				$thisInput.val(number);
			else{
				systemMessages('Error: Incorrect Producing Requests number.', 'error' );
				$thisInput.val('');
				return false;
			}
		}
	});

	$('#search_po_form select[name=search_filter]').on('change', function(){
		var $this = $(this);
		var $thisInput = $('#search_po_form input[name=keywords]');
		var search_filter = $this.val();
		if(search_filter == 'po_number'){
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
var per_page = <?php echo (int)$po_per_page;?>;
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

// SEARCH FORM
function searchPoList(){
	var $thisForm = $('#search_po_form');
	var fdata = $thisForm.serialize();
	var $searchInput = $thisForm.find('input[name=keywords]');
	var searchKeywords = $searchInput.val();
	$('.dashboard-sidebar-sub__item').removeClass('active');

	var search_filter = $thisForm.find('select[name=search_filter]').val();
	if(search_filter == 'po_number'){
		var number = toOrderNumber(searchKeywords);
		if(number)
			$searchInput.val(number);
		else{
			systemMessages('Error: Incorrect Producing Requests number.', 'error' );
			$searchInput.val('');
			return false;
		}
	}

	$.ajax({
		url: '<?php echo __SITE_URL; ?>po/ajax_po_info',
		type: 'POST',
		data:  {type:"search_po", keywords:searchKeywords, search_filter:search_filter, page:current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-center', 'Searching...');
			$thisForm.find('button[type=reset]').show();
		},
		success: function(resp){
			hideLoader('#columns-content-center');

			$('.btn-filter').addClass('btn-filter--active');
			$('.filter-selected-b').html('Search result');
			$('#order-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Requests.</div>');

			if(resp.mess_type == 'success'){
				total = resp.total_po_by_status;
				current_status = resp.status;
				dashboardPagination();
				$('#po-users-list ul').html(resp.po_list);
				centerListApi.reinitialise();

			}else if(resp.mess_type == 'info'){
				total = 0;
				dashboardPagination();
				$('#po-users-list ul').html('<li class="w-100pr p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> '+resp.message+'</div></li>');
				centerListApi.reinitialise();
			} else{
				systemMessages(resp.message, resp.mess_type );
			}
		}
	});
	return false;
}

var search_po = function(){
	currentAction = 'search_po';
	current_page = 1;
	searchPoList();
}

// SEARCH FORM
function resetSearchForm(){
	callResetSearchForm();

	current_status = "initiated";
	current_page = 1;
	var $statusBtn = $('.dashboard-sidebar-sub__item[data-status='+current_status+']');
	$statusBtn.addClass('active').siblings().removeClass('active');

	var selectText = $statusBtn.find('.dashboard-sidebar-sub__text > span').text();
	$('#filter-selected-ttl').html(selectText);

	loadPoList();
};
// END SEARCH FORM

var callResetSearchForm = function($this){
	currentAction = 'list_po';
	var $po_form = $('#search_po_form');
	var search_filter = $po_form.find('select[name=search_filter]').val();

	if(search_filter == 'po_number'){
		var new_url = '<?php echo __SITE_URL.'po/my'?>';
		if(window.history.pushState){
			history.pushState({}, 'Export Portal Producing Requests &raquo; International export & import, b2b and trading in world', new_url);
		} else {
			window.location.href = new_url;
		}
	}

	$('.btn-filter').removeClass('btn-filter--active');

	$po_form.find('select[name=search_filter]').prop('selectedIndex',0);
	$po_form.find('input[name=keywords]').val('');
	$po_form.find('button[type=reset]').hide();
}

// UPDATE STATUSES COUNTERS
function updateStatusesCounters(){
	$.ajax({
		type: 'POST',
		url: 'po/ajax_po_info',
		data: {type : 'update_sidebar_counters'},
		dataType: 'JSON',
		success: function(resp){
			if(resp.mess_type == 'success'){
				$('.dashboard-sidebar-sub__counter').text('0');

				$.each(resp.counters, function(key, val){
					$('#counter-'+key).html(val.counter);
				});
			}
		}
	});
}
// END UPDATE STATUSES COUNTERS

function showPo(idPo){
	var modal = false;

	if(window.innerWidth < 992){
		modal = true;
	}

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>po/ajax_po_info',
		data: { po: idPo, type: 'po'},
		beforeSend: function(){
			showLoader('#columns-content-right', 'Loading...');
		},
		dataType: 'json',
		success: function(resp){
			hideLoader('#columns-content-right');

			if(resp.mess_type == 'success'){

				if(modal){
					poInformationFancybox(resp.content);
					$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Requests.</div>');
				}else{
					$('.wr-orders-detail-b').html(resp.content);
					$('#order-detail-b').find('.order-detail-table').removeClass('order-detail-table');
					$itemDetail = $('.order-detail__scroll');
					itemDetailApi = $itemDetail.jScrollPane().data('jsp');
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

function loadPoList(){
	if($('#search_po_form input[type=text]').val() != ""){
        searchPoList();
        return false;
    }

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>po/ajax_po_info',
		data: { status: current_status, type: 'po_list', page: current_page},
		beforeSend: function(){
			showLoader('#columns-content-center', 'Loading...');
		},
		dataType: 'json',
		success: function(resp){
			hideLoader('#columns-content-center');

			$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Requests.</div>');

			if(resp.mess_type == 'success'){
				total = resp.total_po_by_status;
				dashboardPagination();
				$('#po-users-list .order-users-list-ul').html(resp.po_list);
				centerListApi.reinitialise();
			}else if(resp.mess_type == 'info'){
				total = resp.total_po_by_status;
				dashboardPagination();
				$('#po-users-list .order-users-list-ul').html('<li class="w-100pr p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> '+resp.message+'</div></li>');
				centerListApi.reinitialise();
			} else{
				systemMessages( resp.message, resp.mess_type );
			}
		}
	});
}

var update_status_counter_active = function(title, current_status){
	$('#filter-selected-ttl').html(title);
	updateStatusesCounters();
	$('.dashboard-sidebar-sub__item[data-status='+current_status+']').addClass('active').siblings().removeClass('active');
	callResetSearchForm();
}

function manage_prototype_callback(data){
	loadPoList();
	showPo(data.id_request);
}

var activate_prototype = function(btn){
	var $this = $(btn);
	var prototype = $this.data('prototype');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>prototype/ajax_prototype_operation/activate_prototype',
		data: { prototype: prototype},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				current_status = 'po_processing';
				current_page = 1;
				update_status_counter_active('In process', current_status);

				loadPoList();
				showPo(resp.id_request);
			}
		}
	});
}

var change_status = function(obj){
	var $this = $(obj);
	var action = $this.data('action');
	var po = $this.data('po');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>po/ajax_po_operation/'+action,
		data: { po: po },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){

				current_status = resp.new_status;
				current_page = 1;
				update_status_counter_active('', current_status);

				loadPoList();
				$('.wr-orders-detail-b').append('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Requests.</div>');

			}
		}
	});
}

var remove_po = function(obj){
	var $this = $(obj);
	var po = $this.data('po');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>po/ajax_po_operation/remove_po',
		data: { po: po},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				updateStatusesCounters();
				$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Requests.</div>');
				$('#po-users-list li[data-po="'+po+'"]').remove();


				if(!$('#po-users-list li').length){
					$('#po-users-list ul').html('<li><div class="info-alert-b absolute-b pos-t0 pos-l0 pos-r0"><i class="ep-icon ep-icon_info-stroke"></i> 0 po found by this search.</div></li>');
					$('#po-users-list').jScrollPane();
				}
			}
		}
	});
}

var loadAllPO = function(){
	current_status = 'all';
	current_page = 1;
	currentAction = 'list_po';

	$('.dashboard-sidebar-sub__item').removeClass('active');
	callResetSearchForm();
	loadPoList();

	$('#filter-selected-ttl').html('All Producing Requests');
	updateStatusesCounters();
};

var poInformationFancybox = function(html){
	$.fancybox.open({
		title: 'Producing Requests information',
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

</script>

<div id="filter-panel" class="display-n">
	<?php tmvc::instance()->controller->view->display('new/po/filter_panel_view'); ?>
</div>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Producing Requests</h1>

        <div class="dashboard-line__actions">
			<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/manage_po_doc?user_type=<?php echo strtolower(user_group_type());?>" title="View Producing Requests documentation" data-title="View Producing Requests documentation" target="_blank">User guide</a> -->

			<a class="btn btn-dark fancybox btn-filter <?php echo (!empty($id_po))?'btn-filter--active':'';?>" href="#filter-panel" data-mw="320" data-title="Filter panel">
				<i class="ep-icon ep-icon_filter"></i> Filter
			</a>

			<a id="columns-content-left-btn" class="btn btn-primary fancybox" href="#dashboard-statuses" data-mw="320" data-mnh="100%" data-title="Statuses">
                Statuses
            </a>
		</div>
	</div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('po_my_description'); ?></span>
	</div>

	<div class="columns-content">
		<div id="columns-content-left" class="columns-content__one dn-m w-40pr-lg columns-content__one--250">
			<div class="columns-content__ttl">Statuses</div>

			<?php tmvc::instance()->controller->view->display('new/po/sidebar_view'); ?>
		</div><!-- sidebar -->

		<div id="columns-content-center" class="columns-content__one w-60pr-lg w-100pr-m columns-content__one--350">
			<div id="filter-selected-ttl" class="columns-content__ttl">All Producing Requests</div>

			<div id="po-users-list" class="order-users-list jscroll-init" >
                <ul class="order-users-list-ul clearfix">
                	<?php tmvc::instance()->controller->view->display('new/po/po_list_view'); ?>
                </ul>
            </div><!-- po-users-list -->

			<?php tmvc::instance()->controller->view->display('new/po/pagination_view'); ?>
		</div>

		<div id="columns-content-right" class="columns-content__one dn-lg">
			<div class="columns-content__ttl">Producing Request details</div>

			<div class="wr-orders-detail-b">
				<div class="info-alert-b no-selected"><i class="ep-icon ep-icon_info-stroke"></i> Select Producing Request.</div>
			</div>
		</div><!-- right block -->
	</div>
</div>
