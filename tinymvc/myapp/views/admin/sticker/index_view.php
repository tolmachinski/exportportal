<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js"></script>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/freewall-1-0-4/freewall.js"></script>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/resizestop-master/jquery.resizestop.min.js"></script>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/jquery-jscrollpane-2-0-20/jquery.jscrollpane.min.js"></script>

<script type="text/javascript">
	var itemDetailApi;
	var $itemDetail;
	var itemDetailTimeout;

    var $mainContent;

	var wall = new freewall("#freewall");

	var widthChanged = false;
	var heightChanged = false;

	var calcHeightClasses = [{'minus':30, 'name': 'itemDetail', 'width': true}];

$(document).ready(function() {
	$mainContent = $('.my-order-main-content');
	$itemDetail = $('.wr-sticker-list');
	itemDetailApi = $itemDetail.jScrollPane().data('jsp');
	calcHeightDashboard(calcHeightClasses);

	wall = new freewall("#freewall");
    wall.reset({
		selector: '.sticker-list__item',
		cellW: 345,
		cellH: 'auto',
		animate: true,
		gutterX: 10,
		gutterY: 10,
	});
	wall.fitWidth();

	// NAV SIDEBAR
	$('.dashboard-sidebar-tree__subtree').on('click', 'li a', function(e) {
		var $thisBtn = $(this);
		current_status = $thisBtn.data('status');
		current_page = 1;
		search_keywords = '';
		resetSearchForm();

		$(this).closest('li').addClass('active').siblings().removeClass('active');
		loadStickers();
		updateStatusesCounters();
		e.preventDefault();
	});
	// NAV SIDEBAR END

	$('select[name="sort"]').change(function(){
		current_page = 1;
		current_sort = $(this).val();
		loadStickers();
	});

	$('.sticker-search-form').on('click', 'button[type=reset]', function(e){
		var $thisBtn = $(this);
		resetSearchForm();
		current_status = "new";
		current_page = 1;
		$('.dashboard-sidebar-tree__subtree').find('a[data-status='+current_status+']')
			.parent('li').addClass('active').siblings().removeClass('active');
		loadStickers();
	});

	$('body').on('submit', '.sticker-search-form', function(e){
		var $thisForm = $(this);
		search_keywords= $thisForm.find('input[type=text]').val();
		if(search_keywords != ""){
			$('.dashboard-sidebar-tree__subtree li').removeClass('active');
			current_status = $thisForm.find('select[name=status]').val();
			current_page = 1;
			$thisForm.find('button[type=reset]').show();

			searchStickerList();
		}else{
			systemMessages('Error: Search keywords is required.', 'message-error');
		}
		e.preventDefault();
	});

	$('body').on('click', '.btn-all-sticker', function(e){
		e.preventDefault();
		current_page = 1;
		current_status = "";
		resetSearchForm();
		$('.dashboard-sidebar-tree__subtree li').removeClass('active');
		loadStickers();
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

		if(search_keywords == "")
			loadStickers();
		else
			searchStickerList();

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

		if(search_keywords == "")
			loadStickers();
		else
			searchStickerList();

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

		if(search_keywords == "")
			loadStickers();
		else
			searchStickerList();

		$('.order-list-pag__number-list option[value="'+current_page+'"]').prop('selected', true);
	});
	/* DASHBOARD PAGINATION END */
});

	jQuery(window).on('resizestop', function () {
		if($(this).width() != widthBrowser){
			widthBrowser = $(this).width();
			widthChanged = true;
		}

		if($(this).height() != heightBrowser){
			heightBrowser = $(this).height();
			heightChanged = true;
		}

		calcHeightDashboard(calcHeightClasses, widthChanged, heightChanged);
		wall.refresh();
		widthChanged = heightChanged = false;
	});

	/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES */
	// WE NEED A DEFAULT VARIABLES FOR PAGINATION (GLOBAL FOR THIS PAGE)
	// 1. COUNTE ITEMS(ORDERS, INQUIRY, PO, ETC.)
	// 2. NUMBER OF ITEMS PER PAGE(USED ONLY ON PAGE TO RENDER PAGES NUMBERS)
	// 3. current PAGE => DEFAULT WE ARE ON FIRST PAGE
	// 4. TOTAL NUMBER OF PAGES => DEFAULT SHULD BE 1 PAGE
	// 5. FILTERS VARS (HERE WE ARE USING STATUS FILTERING)
	var total = <?php echo (int)$status_select_count;?>;
	var per_page = <?php echo (int)$stickers_per_page;?>;
	var current_page = 1;
	var total_pages = 1;
	var current_status = "<?php echo $status_select;?>";
	var search_keywords = "";
	var current_sort = $('select[name=sort]').val();

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
		$('#total_stickers_count_by_status').html(total);
		$('.order-list-pag__number-list').html(p_options);
		$('.order-list-pag__number-total').html(total_pages);
	}

	/* DASHBOARD PAGINATION FUNCTIONS/VARIABLES END */

	function simbol(val, number){
		var value = val;
		if (value == ''){
			$('#max-length').html(number + ' characters left');
		} else {
			if (value.length > number) {
				afterText = value.slice(number);
				text = value.slice(0,number);
				$("#text-message").val(text);
				$('#max-length').html(0 + ' characters left');
				return false;
			} // end if > 10 symbols
			else {
				var main = value.length * 100;
				var value2 = (main / number);
				var count = number - value.length;
				$('#max-length').html(count + ' characters left');
			};
		}; // end else
	}

	// UPDATE STATUSES COUNTERS
	function updateStatusesCounters(){
		$.ajax({
			type: 'POST',
			url: 'sticker/ajax_sticker_operation/update_sidebar_counters',
			dataType: 'JSON',
			success: function(resp){
				$('.dashboard-sidebar-tree__subtree span.counter-b').html('0');
				$.each(resp.counters, function(key, val){
					$('#counter-'+key).html(val.counter);
				});
			}
		});
	}
	// END UPDATE STATUSES COUNTERS

	function loadStickers(){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>sticker/ajax_sticker_operation/load_stickers',
			data: { status: current_status, sort: current_sort, page: current_page},
			beforeSend: function(){ showLoader('.my-order-right-b2'); },
			dataType: 'json',
			success: function(resp){
				hideLoader('.my-order-right-b2');

				if(resp.mess_type == 'success'){
					total = resp.total_stickers_by_status;
					dashboardPagination();
					updateStatusesCounters();
					$('.sticker-list').html(resp.content);

					wall.refresh();
					itemDetailApi.reinitialise();
				}
			}
		});
	}

	change_status = function(obj){
		var $this = $(obj);//alert($this.data('column'));
		var sticker = $this.closest('li').data('sticker');
		var status = $this.data('status');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>sticker/ajax_sticker_operation/change_status',
			data: { sticker : sticker, status: status},
			beforeSend: function(){ showLoader('.my-order-right-b2'); },
			dataType: 'json',
			success: function(data){
				hideLoader('.my-order-right-b2');
				systemMessages( data.message, 'message-' + data.mess_type );
				if(data.mess_type == 'success'){
					search_keywords = '';
					resetSearchForm();
					current_page = 1;
					current_status = status;
					$('.dashboard-sidebar-tree__subtree').find('a[data-status='+status+']')
						.parent('li').addClass('active').siblings().removeClass('active');
					loadStickers();
				}
			}
		});
	}

	function resetSearchForm(){
		search_keywords = "";
		$('.sticker-search-form')[0].reset();
		$('.sticker-search-form button[type=reset]').hide();
	}

	function searchStickerList(){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>sticker/ajax_sticker_operation/search_stickers',
			data: {keywords : search_keywords, status: current_status, sort: current_sort, page: current_page},
			beforeSend: function(){ showLoader('.my-order-right-b2'); },
			dataType: 'json',
			success: function(resp){
				hideLoader('.my-order-right-b2');

				if(resp.mess_type == 'success'){
					total = resp.total_stickers_by_status;
					dashboardPagination();
					updateStatusesCounters()
					$('.sticker-list').html(resp.content);

					wall.refresh();
					itemDetailApi.reinitialise();
				}else{
					total = 0;
					dashboardPagination();
					$('.sticker-list').html('<li class="w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> 0 stickers found by this search.</div></li>');

					wall.refresh();
					itemDetailApi.reinitialise();
					systemMessages( resp.message, 'message-' + resp.mess_type );
				}
			}
		});
	}
</script>

<div class="container-fluid content-dashboard">
	<div class="my-orders-header">
		<div class="line-b clearfix">
			<div class="col-xs-1"> <p class="den">Stickers</p> </div>
			<div class="col-xs-11">
				<form class="pull-right sticker-search-form" method="post">
					<div class="search-my-order__ttl pull-left">Search</div>
					<div class="search-my-order pull-right">
						<input type="text" name="keywords" placeholder="search for sticker"/>
						<button type="submit"><i class="ep-icon ep-icon_magnifier"></i></button>
						<button type="reset"><i class="ep-icon ep-icon_remove"></i></button>
					</div>
					<select class="search-my-order__status pull-right" name="status" >
						<option value="" selected>All statuses</option>
						<option value="new">New</option>
						<option value="read">Read</option>
						<option value="important">Important</option>
						<option value="personal">Personal</option>
						<option value="archived">Archived</option>
						<option value="trash">Trash</option>
					</select>
				</form>

				<a class="btn btn-primary mt-20 mr-10 pull-left fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>sticker/popup_forms/create_sticker_form" data-title="Create sticker">Create new sticker</a>
				<div class="search-my-order__ttl pull-left">Filter by:</div>
				<select class="search-my-order__status pull-left" name="sort" >
					<option value="" selected>Select sort</option>
					<option value="create_date-asc">Create date &#9650;</option>
					<option value="create_date-desc">Create date &#9660;</option>
					<option value="update_date-asc">Update date &#9650;</option>
					<option value="update_date-desc">Update date &#9660;</option>
				</select>
			</div>
		</div>
	</div>

	<div class="my-order-main-content">
		<div class="my-orders-sidebar relative-b">
			<div class="ttl-b"><i class="ep-icon ep-icon_sticker"></i> <a class="btn-all-sticker" href="#"> All stickers</a></div>

			<div class="wr-dashboard-sidebar-tree__subtree h-472">
			<ul class="dashboard-sidebar-tree__subtree display-b_i clearfix w-245">
				<li class="<?php echo equals($status_select,'new','active');?>">
					<a href="#" data-status="new">
						<span class="icon-b"><i class="ep-icon ep-icon_new txt-green"></i></span>
						<span class="text-b">New</span>
						<span class="counter-b" id="counter-new"><?php echo (int)$statuses['new']['counter']; ?></span>
					</a>
				</li>
				<li class="<?php echo equals($status_select,'read','active');?>">
					<a href="#" data-status="read">
						<span class="icon-b"><i class="ep-icon ep-icon_circle txt-blue"></i></span>
						<span class="text-b">Read</span>
						<span class="counter-b" id="counter-read"><?php echo (int)$statuses['read']['counter']; ?></span>
					</a>
				</li>
				<li class="<?php echo equals($status_select,'important','active');?>">
					<a href="#" data-status="important">
						<span class="icon-b"><i class="ep-icon ep-icon_circle txt-red"></i></span>
						<span class="text-b">Important</span>
						<span class="counter-b" id="counter-important"><?php echo (int)$statuses['important']['counter']; ?></span>
					</a>
				</li>
				<li class="<?php echo equals($status_select,'personal','active');?>">
					<a href="#" data-status="personal">
						<span class="icon-b"><i class="ep-icon ep-icon_circle txt-green"></i></span>
						<span class="text-b">Personal</span>
						<span class="counter-b" id="counter-personal"><?php echo (int)$statuses['personal']['counter']; ?></span>
					</a>
				</li>
				<li class="<?php echo equals($status_select,'archived','active');?>">
					<a href="#" data-status="archived">
						<span class="icon-b"><i class="ep-icon ep-icon_archive txt-gray"></i></span>
						<span class="text-b">Archived</span>
						<span class="counter-b" id="counter-archived"><?php echo (int)$statuses['archived']['counter']; ?></span>
					</a>
				</li>
				<li class="<?php echo equals($status_select,'trash','active');?>">
					<a href="#" data-status="trash">
						<span class="icon-b"><i class="ep-icon ep-icon_trash txt-red"></i></span>
						<span class="text-b">Trash</span>
						<span class="counter-b" id="counter-trash"><?php echo (int)$statuses['trash']['counter']; ?></span>
					</a>
				</li>
			</ul>
			</div>
		</div><!-- sidebar -->

		<div class="my-order-right-b2">
			<div class="wr-sticker-list">
				<ul id="freewall" class="sticker-list">
					<?php tmvc::instance()->controller->view->display('admin/sticker/sticker_item_view'); ?>
				</ul>
			</div>
			<div class="order-list-pag">
				<div class="order-list-pag__total">
					<span id="total_stickers_count_by_status" title="Total stickers"><?php echo (int)$status_select_count;?></span>
				</div>
				<div class="order-list-pag__number">
					<button class="order-list-pag__number-prev btn btn-primary btn-xs"><i class="ep-icon ep-icon_arrows-left lh-18"></i></button>
					Page
					<select class="order-list-pag__number-list tac">
						<option>1</option>
					</select>
					of <span class="order-list-pag__number-total">1</span>
					<button class="order-list-pag__number-next btn btn-primary btn-xs"><i class="ep-icon ep-icon_arrows-right lh-18"></i></button>
				</div>
			</div><!-- sticker-list-pag -->
		</div><!-- right block -->
	</div><!-- my-order-main-content -->
</div>
