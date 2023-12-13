<script type="text/javascript">
var itemDetailApi;
var $itemDetail;
var itemDetailTimeout;

var widthChanged = false;
var heightChanged = false;

var currentAction = 'list_followers';

var calcHeightClasses = [{'minus':0, 'name': 'itemDetail', 'width': true}];

$(document).ready(function() {
	$itemDetail = $('.ppersonal-followers-dashboard');
	itemDetailApi = $itemDetail.jScrollPane().data('jsp');

	// SIDEBAR NAVIGATION
	$('.dashboard-sidebar-sub').on('click', '.dashboard-sidebar-sub__item', function(e) {
		e.preventDefault();

		var $thisBtn = $(this);
		current_status = $thisBtn.data('status');
		current_page = 1;
		currentAction = 'list_followers';
		var selectText = $thisBtn.find('.dashboard-sidebar-sub__text > span').text();

		$thisBtn.addClass('active').siblings().removeClass('active');
		callResetSearchForm();
		loadFollowersList();

		$('#filter-selected-ttl').html(selectText);
		updateStatusesCounters();
	});
	// SIDEBAR NAVIGATION END

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

		if(currentAction == "list_followers")
			loadFollowersList();
		else
			search_followers();

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

		if(currentAction == "list_followers")
			loadFollowersList();
		else
			search_followers();

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

		if(currentAction == "list_followers")
			loadFollowersList();
		else
			search_followers();

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */
});

jQuery(window).on('resizestop', function () {
	if($(this).width() != widthBrowser){
		widthBrowser = $(this).width();
		widthChanged = true;

		calcWidthBlockSimple(calcHeightClasses, widthChanged);
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
var per_page = <?php echo (int)$followers_per_page;?>;
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
	if(current_page == 1){
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

// LOAD followers LIST
function loadFollowersList(){
	$.ajax({
		type: 'POST',
		url: "followers/ajax_followers_info",
		data: { type: current_status + '_list', page: current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-right', 'Loading...');
		},
		success: function(resp){
			hideLoader('#columns-content-right');

			$('.ppersonal-followers').html('<li><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select followers.</div></li>');

			if(resp.mess_type == 'success'){
				total = resp.total_followers_by_status;
				dashboardPagination();
				$('.ppersonal-followers').html(resp.followers_list);
				itemDetailApi.reinitialise();
			}else if(resp.mess_type == 'info'){
				total = resp.total_followers_by_status;
				dashboardPagination();
				$('.ppersonal-followers').html('<li class="w-100pr p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> '+resp.message+'</div></li>');
				itemDetailApi.reinitialise();
			}else{
				systemMessages( resp.message, resp.mess_type );
			}
		}
	});
}
// END LOAD followers LIST

// SEARCH FORM
function search_followers(){
	var $thisForm = $('#search_followers_form');
	var $searchInput = $thisForm.find('input[name=keywords]');
	var searchKeywords = $searchInput.val();
	$('.dashboard-sidebar-sub__item').removeClass('active');

	var search_filter = $thisForm.find('select[name=search_filter]').val();

	$.ajax({
		url: '<?php echo __SITE_URL; ?>followers/ajax_followers_info',
		type: 'POST',
		data:  {type:"search_followers", keywords:searchKeywords, search_filter:search_filter, page:current_page},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('#columns-content-center', 'Searching...');
			$thisForm.find('button[type=reset]').show();
		},
		success: function(resp){
			hideLoader('#columns-content-center');
			$('.btn-filter').addClass('btn-filter--active');
			$('#filter-selected-ttl').html('Search result');

			if(resp.mess_type == 'success'){
				total = resp.total_followers_by_status;
				current_status = resp.status;
				dashboardPagination();
				$('.ppersonal-followers').html(resp.followers_list);
				itemDetailApi.reinitialise();
			}else if(resp.mess_type == 'info'){
				total = 0;
				dashboardPagination();
				$('.ppersonal-followers').html('<li class="w-100pr p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> '+resp.message+'</div></li>');
				itemDetailApi.reinitialise();
			} else{
				systemMessages(resp.message, resp.mess_type );
			}
		}
	});
	return false;
}

var search_follower = function(){
	currentAction = 'search_followers';
	current_page = 1;
	search_followers();
}

var resetSearchForm = function($this){
	callResetSearchForm();

	current_status = "followers";
	current_page = 1;
	var $statusBtn = $('.dashboard-sidebar-tree__subtree').find('a[data-status='+current_status+']');
	$statusBtn.closest('li').addClass('active').siblings().removeClass('active');

	var selectText = $statusBtn.find('.dashboard-sidebar-sub__text > span').text();
	$('#filter-selected-ttl').html(selectText);

	loadFollowersList();
};

function callResetSearchForm(){
	currentAction = 'list_followers';
	var $followers_form = $('#search_followers_form');
	var search_filter = $followers_form.find('select[name=search_filter]').val();

	$('.btn-filter').removeClass('btn-filter--active');

	$followers_form.find('select[name=search_filter]').prop('selectedIndex',0);
	$followers_form.find('input[name=keywords]').val('');
	$followers_form.find('button[type=reset]').hide();
}
// END SEARCH FORM

// UPDATE STATUSES COUNTERS
function updateStatusesCounters(){
	$.ajax({
		type: 'POST',
		url: 'followers/ajax_followers_operation/update_sidebar_counters',
		dataType: 'JSON',
		success: function(resp){
			$('.dashboard-sidebar-sub__counter').text('0');

			$.each(resp.counters, function(key, val){
				$('#counter-'+key).html(val.counter);
			});
		}
	});
}
// END UPDATE STATUSES COUNTERS

</script>

<div id="filter-panel" class="display-n">
	<?php tmvc::instance()->controller->view->display('new/followers/filter_panel_view'); ?>
</div>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Followers</h1>

        <div class="dashboard-line__actions">
			<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/folowers_doc?user_type=<?php echo strtolower(user_group_type());?>" title="View followers / following documentation" data-title="View followers / following documentation" target="_blank">User guide</a> -->

			<a class="btn btn-dark btn-filter fancybox" href="#filter-panel" data-mw="320" data-title="Filter panel">
				<i class="ep-icon ep-icon_filter"></i> Filter
			</a>

			<a id="columns-content-left-btn" class="btn btn-primary fancybox" href="#dashboard-statuses" data-mw="320" data-mnh="100%" data-title="Statuses">
                Statuses
            </a>
		</div>
	</div>

	<div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('followers_my_description'); ?></span>
	</div>

	<div class="columns-content">
		<div id="columns-content-left" class="columns-content__one dn-m w-30pr-lg columns-content__one--250">
			<div class="columns-content__ttl">All Followers</div>

			<?php tmvc::instance()->controller->view->display('new/followers/sidebar_view'); ?>
		</div><!-- sidebar -->

		<div id="columns-content-right" class="columns-content__one">
			<div class="columns-content__ttl">Followers</div>

			<div class="ppersonal-followers-dashboard">
				<ul class="ppersonal-followers ppersonal-followers--3">
					<?php $data_followers = array('type' => 'followers') ?>
					<?php tmvc::instance()->controller->view->display('new/followers/follower_item_view', $data_followers); ?>
				</ul>
			</div>

			<?php tmvc::instance()->controller->view->display('new/followers/pagination_view'); ?>
		</div><!-- right block -->
	</div><!-- my-order-main-content -->
</div>
