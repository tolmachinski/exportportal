<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/js/calc.js"></script>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/select2-4-0-3/js/select2.min.js"></script>

<script>
	var dtItemsList;
	var itemsFilters;
	var selectState = 0;
	var $selectCity;

	var unblockResource = function (caller) {
		var button = $(caller);
		var url = button.data('url') || null;
		var onRequestSuccess = function(resposne) {
			systemMessages(resposne.message, resposne.mess_type);
			if(resposne.mess_type === 'success') {
				dtItemsList.fnDraw(false);
			}
		}

		if(null !== url) {
			$.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
		}
	};

	var onResourceBlock = function() {
		dtItemsList.fnDraw(false);
	};
	var onPickOfTheMonthEnabled = function() {
		dtItemsList.fnDraw(false);
	};

	var change_visibility = function(opener){
		var $this = $(opener);
		var item = $this.data("item");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "items/ajax_item_operation/change_visibility",
			data: { item: item },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtItemsList.fnDraw(false);
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var change_locked = function(opener){
		var $this = $(opener);
		var item = $this.data("item");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "items/ajax_item_operation/change_visibility",
			data: { item: item },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtItemsList.fnDraw(false);
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}
	// free_refeature_item
	var free_feature_item = function(opener){
		var $this = $(opener);
		var item = $this.data("item");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "items/ajax_item_operation/free_feature_item",
			data: { item: item },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtItemsList.fnDraw(false);
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
    }

    // free_extend_refeature_item
	var free_extend_feature_item = function(opener){
		var $this = $(opener);
		var item = $this.data("item");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "items/ajax_item_operation/free_extend_feature_item",
			data: { item: item },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtItemsList.fnDraw(false);
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var change_partnership = function(opener){
		var $this = $(opener);
		var item = $this.data("item");
		var changeTo = $this.data("change-to");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "items/ajax_item_operation/change_partnership",
			data: {
				id_item: item,
				change_to: changeTo
			},
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtItemsList.fnDraw(false);
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
    }

    var removeSingleUrlParameter = function(parameterName) {
        var string = window.location.search;
        search_array = string.split('&');

        $(search_array).each(function(index, section) {
            if (section.indexOf(parameterName) !== -1) {
                search_array.splice(index, 1);
            }
        });

        return window.location.pathname + '?' + search_array.join('&');
    }

    var filterRemovalAction = function(filterName) {
        switch ( filterName ) {
            case 'id_item':
                window.history.replaceState(null, null, removeSingleUrlParameter('id_item'));
                break;
            case 'expire':
                window.history.replaceState(null, null, removeSingleUrlParameter('expire'));
                break;
            case 'seller':
                window.history.replaceState(null, null, removeSingleUrlParameter('seller'));
                break;
            case 'parent':
                $('.subcategories').remove();
                break;
        }
    }

	// var actualizeItemThumbs = function(obj){
	// 	var $this = $(obj);
	// 	var item = $this.data('item');

	// 	$.ajax({
	// 		type: 'POST',
	// 		url: '<?php echo __SITE_URL?>items/ajax_item_operation/actualize_item_thumbs',
	// 		data: { item : item},
	// 		dataType: 'json',
	// 		beforeSend: function(){  showLoader('.dataTables_wrapper');},
	// 		success: function(resp){
	// 			systemMessages( resp.message, 'message-' + resp.mess_type );

	// 			if(resp.mess_type == 'success'){
	// 				dtItemsList.fnDraw(false);
	// 			}

	// 			hideLoader('.dataTables_wrapper');
	// 		}
	// 	});
	// }

	// var actualizeItemsThumbs = function(obj){
	// 	var $this = $(obj);

	// 	$.ajax({
	// 		type: 'POST',
	// 		url: '<?php echo __SITE_URL?>items/ajax_item_operation/actualize_items_thumbs',
	// 		data: {},
	// 		dataType: 'json',
	// 		beforeSend: function(){
	// 			showLoader('.dataTables_wrapper');
	// 		},
	// 		success: function(resp){
	// 			systemMessages( resp.message, 'message-' + resp.mess_type );

	// 			if(resp.mess_type == 'success'){
	// 				dtItemsList.fnDraw(false);
	// 			}

	// 			hideLoader('.dataTables_wrapper');
	// 		}
	// 	});
	// }

$(document).ready(function(){
	dtItemsList = $('#dtItemsList').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>items/ajax_administration_items",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{"sClass": "", "aTargets": ['dt_item'], "mData": "dt_item" , 'bSortable': false},
			{"sClass": "w-80", "aTargets": ['dt_translation'], "mData": "dt_translation" , 'bSortable': false},
			{"sClass": "w-150", "aTargets": ['dt_seller'], "mData": "dt_seller"},
			{"sClass": "w-200", "aTargets": ['dt_address'], "mData": "dt_address" , 'bSortable': false},
			{"sClass": "w-120", "aTargets": ['dt_price_qty'], "mData": "dt_price_qty" , 'bSortable': false},
			{"sClass": "w-50 tac", "aTargets": ['dt_create_date'], "mData": "dt_create_date"},
			{"sClass": "w-50 tac", "aTargets": ['dt_update_date'], "mData": "dt_update_date"},
			{"sClass": "w-100", "aTargets": ['dt_statistics'], "mData": "dt_statistics", 'bSortable': false},
			// {"sClass": "w-70", "aTargets": ['dt_thumbs'], "mData": "dt_thumbs", 'bSortable': false},
			{"sClass": "w-70 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
		],
		"sorting" : [[4,'desc']],
		"sPaginationType": "full_numbers",
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!itemsFilters){
				itemsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':true,
					callBack: function(){
						dtItemsList.fnDraw();
					},
					onDelete: function(filter){
                        filterRemovalAction(filter.name);
					}
				});
			}

			aoData = aoData.concat(itemsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);
				}
			});
		},
		"fnDrawCallback": function(oSettings) {
			$('.rating-bootstrap').rating();
		}
	});

	$('body').on('change', 'select.categ1', function(){
		var select = this;
		var cat = select.value;
		var sClass = select.className;
		var control = select.id; //alert(cat + '-- '+ control);
		var level = $(select).attr('level');
		var text = $(select).find('option:selected').text();

		$('td.select_category div.subcategories').each(function (){
			thislevel = $(this).attr('level');
			if(thislevel > level) $(this).remove();
		});

		if(cat != 0){
			if(cat != control){
				$.ajax({
					type: 'POST',
					url: '/categories/getcategories',
					dataType: 'JSON',
					data: { op : 'select', cat: cat, level : level, cl : sClass, not_filter: 1},
					beforeSend: function(){ showLoader('.full_block'); },
					success: function(json){
						if(json.mess_type == 'success'){
							$('.select_category').append(json.content);
							$('select.categ1').css('color', 'black');
							$(select).css('color', 'red');
						}else{
							systemMessages(json.message,  'message-' + json.mess_type);
						}
						hideLoader('.full_block');
					},
					error: function(){alert('ERROR')}
				});

                $("#test-select-category option").val(select.value).html(text).trigger("change");
			}else{
				$('select.categ1').css('color', 'black');
				$('select.categ1[level='+(level-1)+']').css('color', 'red');
                var prevSelect = $('select.categ1[level='+(level-1)+']').find('option:selected');
                $("#test-select-category option").val(prevSelect.val()).html(prevSelect.text()).trigger("change");
			}
		} else{
			$('.subcategories').remove();
            $("#test-select-category option").val(0).html("All").trigger("change");
		}

	});

	$('body').on('click', '.collapse-item_name', function(){
		var $thisBtn = $(this);

		$thisBtn.next('.collapse-item_block').slideToggle();
		$thisBtn.children('i').toggleClass('ep-icon_plus ep-icon_minus');

	});

	idStartItemNew = <?php echo $last_items_id;?>;
	startCheckAdminNewItems('items/ajax_item_operation/check_new', idStartItemNew);

	window.moderateResource = function (caller) {
		var button = $(caller);
		var url = button.data('url') || null;
		var type = button.data('type') || null;
		var resource_id = button.data('resource');

		var onRequestSuccess = function(resposne) {
			systemMessages(resposne.message, resposne.mess_type);
			if(type !== null && type == 'items'){
				$.post(__site_url + 'items/ajax_item_operation/prepare_item', { id: resource_id }, null, 'json').done(function(resp) {
					systemMessages(resp.message, resp.mess_type);
				});
			}
			if(resposne.mess_type === 'success') {
				dtItemsList.fnDraw(false);
			}
		}

		if(null !== url) {
			$.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
		}
	};
});


var exportItems = function () {
    activeFilters = itemsFilters.getDTFilter();
    getParams = {};

    for (const [key, filter] of Object.entries(activeFilters)) {
        getParams = Object.assign(getParams, {[filter.name]: filter.value});
    }

    document.getElementById("export_items").setAttribute('src', __site_url + 'items/export_items?' + new URLSearchParams(getParams).toString());
}

function validateReInit(){
    $(".validateModal").validationEngine('detach');
    $(".validateModal").validationEngine('attach', {
        promptPosition : "topLeft:0",
        //promptPosition : "centerRight",
        autoPositionUpdate : true,
        onValidationComplete: function(form, status){
            if(status){
                modalFormCallBack(form, dtItemsList);
            }
        }
    });
}
</script>
<div class="row">
	<div class="col-xs-12">
        <iframe src="" id="export_items" class="display-n"></iframe>
		<h3 class="titlehdr">
			<span>Products list</span>
			<a class="ep-icon ep-icon_items fancybox fancybox.ajax pull-right ml-10" href="<?php echo __SITE_URL?>items/popup_forms/thumbs_actualize_log" data-title="Actualize thumbs logs"></a>
			<a class="ep-icon ep-icon_file-text pull-right ml-10 call-function" title="Export items" data-callback="exportItems"></a>
		</h3>
		<?php views()->display('admin/item/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table  class="data table-bordered table-striped w-100pr" id="dtItemsList">
			<thead>
				<tr>
					<th class="dt_item">Item</th>
					<th class="dt_translation">Translation</th>
					<th class="dt_seller">Seller info</th>
					<th class="dt_address">Country/State/City</th>
					<th class="dt_price_qty">Price/Quantity</th>
					<th class="dt_create_date">Created</th>
					<th class="dt_update_date">Update</th>
					<th class="dt_statistics">Statistics</th>
					<!-- <th class="dt_thumbs">Thumbs</th> -->
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
	</div>
</div>
