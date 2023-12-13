<script>
var dtFeaturedItemsList;
var itemsFilters;

$(document).ready(function(){
	dtFeaturedItemsList = $('#dtFeaturedItemsList').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>items/dt_ajax_administration_featured_items",
        "sServerMethod": "POST",
        "sorting": [[7, "desc"]],
		"aoColumnDefs": [
            {"sClass": "w-100", "aTargets": ['dt_image'], "mData": "dt_image" , 'bSortable': false},
            {"sClass": "mnw-190 vam", "aTargets": ['dt_item'], "mData": "dt_item" , 'bSortable': false},
            {"sClass": "mnw-150", "aTargets": ['dt_seller'], "mData": "dt_seller" , 'bSortable': false},
            {"sClass": "mnw-100 vam tal", "aTargets": ['dt_address'], "mData": "dt_address" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_fstatus'], "mData": "dt_fstatus" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_paid'], "mData": "dt_paid" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_price'], "mData": "dt_price" , 'bSortable': false},
            {"sClass": "mnw-80 vam tac", "aTargets": ['dt_update_date'], "mData": "dt_update_date"},
            {"sClass": "mnw-80 vam tac", "aTargets": ['dt_expire_date'], "mData": "dt_expire_date"},
            {"sClass": "w-50 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
		],
		"sPaginationType": "full_numbers",
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            if(!itemsFilters){
                itemsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug':true,
                    callBack: function(){
                        dtFeaturedItemsList.fnDraw();
                    },
                    onSet: function(callerObj, filterObj){
                        if(filterObj.name == 'itf_status'){
                            $('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li').addClass('active').siblings().removeClass('active');
                        }
                        if(filterObj.name == 'start_from'){
                            $(".start_to").datepicker("option","minDate", $(".start_from").datepicker("getDate"));
                        }
                        if(filterObj.name == 'start_to'){
                            $(".start_from").datepicker("option","maxDate", $(".start_to").datepicker("getDate"));
                        }
                        if(filterObj.name == 'end_from'){
                            $(".end_to").datepicker("option","minDate", $(".end_from").datepicker("getDate"));
                        }
                        if(filterObj.name == 'end_to'){
                            $(".end_from").datepicker("option","maxDate", $(".end_to").datepicker("getDate"));
                        }
                    },
                    onDelete: function(filter){
                        if(filter.name == 'parent'){
                            $('.subcategories').remove();
                        }
                        if(filter.name == 'itf_status'){
                            $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
                            $li.siblings('li').removeClass('active').end()
                               .addClass('active');
                        }
                    },
                    onReset: function(){
                        $('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
                            minDate: null,
                            maxDate: null
                        });
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

	idStartItemNew = <?php echo $last_featured_items_id;?>;
	startCheckAdminNewItems('items/ajax_item_operation/check_new_featured_items', idStartItemNew);
});

var free_extend_feature_item = function(opener){
    var $this = $(opener);
    var item = $this.data("item");
    $.ajax({
        type: "POST",
        context: $(this),
        url: "<?php echo __SITE_URL ?>items/ajax_item_operation/free_extend_feature_item",
        data: { item: item },
        dataType: 'JSON',
        success: function(resp){
            if(resp.mess_type == 'success'){
                dtFeaturedItemsList.fnDraw(false);
            }
            systemMessages( resp.message, 'message-' + resp.mess_type );
        }
    });
}

var stop_auto_extend_item = function(opener){
    var $this = $(opener);
    var item = $this.data("item");
    $.ajax({
        type: "POST",
        context: $(this),
        url: "<?php echo __SITE_URL . 'items/ajax_item_operation/stop_auto_extend_feature_items'?>",
        data: { id_featured: item},
        dataType: 'JSON',
        success: function(resp){
            if(resp.mess_type == 'success'){
                dtFeaturedItemsList.fnDraw(false);
            }
            systemMessages( resp.message, 'message-' + resp.mess_type );
        }
    });
}

var un_feature_item = function(opener){
    var $this = $(opener);
    var item = $this.data("item");
    $.ajax({
        type: "POST",
        context: $(this),
        url: "<?php echo __SITE_URL . 'items/ajax_item_operation/un_feature_item'?>",
        data: { id_featured: item},
        dataType: 'JSON',
        success: function(resp){
            if(resp.mess_type == 'success'){
                dtFeaturedItemsList.fnDraw(false);
            }
            systemMessages( resp.message, 'message-' + resp.mess_type );
        }
    });
}
</script>

<div class="row">
    <div class="col-xs-12">
		<h3 class="titlehdr">
			<span>Featured items list</span>
		</h3>
		<?php views()->display('admin/item/featured/filter_view'); ?>
		<ul class="menu-level3 mb-10 clearfix">
            <li class="active"><a class="dt_filter" data-name="itf_status" data-title="Featured status" data-value="" data-value-text="All">All</a></li>
            <?php foreach ($statuses as $key => $status) {?>
                <li><a class="dt_filter" data-name="itf_status" data-value="<?php echo $key;?>" data-value-text="<?php echo $status;?>"><?php echo $status;?></a></li>
            <?php }?>
		</ul>
		<div class="wr-filter-list clearfix mt-10"></div>

        <table  class="data table-striped table-bordered w-100pr" id="dtFeaturedItemsList">
            <thead>
                <tr>
                    <th class="dt_image"></th>
                    <th class="dt_item">Item</th>
                    <th class="dt_seller">Seller info</th>
                    <th class="dt_address">Country/State/City</th>
                    <th class="dt_fstatus">Status</th>
                    <th class="dt_paid">Payment</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_update_date">Start</th>
                    <th class="dt_expire_date">Expired</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall"></tbody>
        </table>
    </div>
</div>
