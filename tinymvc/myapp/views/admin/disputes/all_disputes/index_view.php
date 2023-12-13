<script src="<?php echo __FILES_URL . 'public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js';?>"></script>

<script type="text/javascript">
    var disputesFilters;
    var dtDispute;

    $(document).ready(function(){
        dtDispute = $('#dtDispute').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "bSortCellsTop": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'dispute/ajax_admin_all_disputes_dt';?>",
            "sServerMethod": "POST",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            "aoColumnDefs": [
                { "sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id"},
                { "sClass": "", "aTargets": ["dt_dispute"], "mData": "dt_dispute", "bSortable": false},
                { "sClass": "w-200", "aTargets": ["dt_buyer"], "mData": "dt_buyer", "bSortable": false},
                { "sClass": "w-200", "aTargets": ["dt_seller"], "mData": "dt_seller" , "bSortable": false},
                { "sClass": "w-200", "aTargets": ["dt_shipper"], "mData": "dt_shipper" , "bSortable": false},
                { "sClass": "w-180", "aTargets": ["dt_money_back"], "mData": "dt_money_back" , "bSortable": false},
                { "sClass": "w-120 vam tac", "aTargets": ["dt_date_time"], "mData": "dt_date_time"},
                { "sClass": "w-120 vam tac", "aTargets": ["dt_date_time_changed"], "mData": "dt_date_time_changed"},
                { "sClass": "w-90 tac vam", "aTargets": ["dt_status"], "mData": "dt_status"},
                { "sClass": "tac vam", "aTargets": ["dt_photos"], "mData": "dt_photos" , "bSortable": false },
                { "sClass": "tac vam", "aTargets": ["dt_reason"], "mData": "dt_reason", "bSortable": false },
                { "sClass": "vam", "aTargets": ["dt_comment"], "mData": "dt_comment", "bSortable": false },
                { "sClass": "tac vam w-50", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false }
            ],
            "sorting" : [[0,'desc']],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!disputesFilters){
                    disputesFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtDispute.fnDraw(); },
                        onSet: function(callerObj, filterObj){
                            if(filterObj.name == 'status'){
                                $('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li')
                                    .addClass('active').siblings().removeClass('active');

                            }
                        },
                        onDelete: function(filter){
                            if(filter.name == 'status'){
                                $('.menu-level3 a[data-value="' + filter.value + '"]').parent('li')
                                    .addClass('active').siblings().removeClass('active');

                            }
                        }
                    });
                }

                aoData = aoData.concat(disputesFilters.getDTFilter());
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
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function( oSettings ) {

            }
        });

        $('body').on('click', 'a[rel=user_details]', function() {
            var $aTd = $(this);
            var nTr = $aTd.parents('tr')[0];
            if (dtDispute.fnIsOpen(nTr)){
                dtDispute.fnClose(nTr);
            }else{
                dtDispute.fnOpen(nTr, fnFormatDetails(nTr), 'details');
            }

            $aTd.toggleClass('ep-icon_plus ep-icon_minus');
        });

        function fnFormatDetails(nTr){
            var aData = dtDispute.fnGetData(nTr);
            var sOut = '<div class="dt-details"><table class="dt-details__table">';
                sOut += '<tr><td class="w-100">Comment</td><td>' + aData['dt_comment']+ '</td></tr>';

                if(aData['dt_photos'].buyer.photos.length > 0 || aData['dt_photos'].seller.photos.length > 0 || aData['dt_photos'].shipper.photos.length  > 0 || aData['dt_photos'].ep_manager.photos.length  > 0){
                    sOut += '<tr><td>Photos </td><td>';
                    if(aData['dt_photos'].buyer.photos.length > 0){
                        sOut += 'Buyer: ' + aData['dt_photos'].buyer.user_name + '<br/>';
                        sOut += aData['dt_photos'].buyer.photos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_photos'].seller.photos.length > 0){
                        sOut += 'Seller: ' + aData['dt_photos'].seller.user_name + '<br/>';
                        sOut += aData['dt_photos'].seller.photos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_photos'].shipper.photos.length > 0){
                        sOut += 'Freight Forwarder: ' + aData['dt_photos'].shipper.user_name + '<br/>';
                        sOut += aData['dt_photos'].shipper.photos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_photos'].ep_manager.photos.length > 0){
                        sOut += 'EP manager: ' + aData['dt_photos'].ep_manager.user_name + '<br/>';
                        sOut += aData['dt_photos'].ep_manager.photos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }

                    sOut += '</td></tr>';
                }

                if(aData['dt_videos'].buyer.videos.length > 0 || aData['dt_videos'].seller.videos.length > 0 || aData['dt_videos'].shipper.videos.length > 0 || aData['dt_videos'].ep_manager.videos.length > 0){
                    sOut += '<tr><td>Videos </td><td>';
                    if(aData['dt_videos'].buyer.videos.length > 0){
                        sOut += 'Buyer: ' + aData['dt_videos'].buyer.user_name + '<br/>';
                        sOut += aData['dt_videos'].buyer.videos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_videos'].seller.videos.length > 0){
                        sOut += 'Seller: ' + aData['dt_videos'].seller.user_name + '<br/>';
                        sOut += aData['dt_videos'].seller.videos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_videos'].shipper.videos.length > 0){
                        sOut += 'Freight Forwarder: ' + aData['dt_videos'].shipper.user_name + '<br/>';
                        sOut += aData['dt_videos'].shipper.videos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    if(aData['dt_videos'].ep_manager.videos.length > 0){
                        sOut += 'EP manager: ' + aData['dt_videos'].ep_manager.user_name + '<br/>';
                        sOut += aData['dt_videos'].ep_manager.videos.join('');
                        sOut += '<div class="clearfix"></div><hr class="mt-5 mb-5"/>';
                    }
                    sOut += '</td></tr>';
                }

                if(aData['dt_reason'] != ''){
                    sOut += '<tr><td>Reason</td><td>' + aData['dt_reason'] +'</td></tr>';
                }

                sOut += '</table> </div>';
            return sOut;
        }
    });
</script>
<div class="row">
    <div class="col-xs-12">
		<?php views('admin/disputes/all_disputes/filter_panel_view');?>
		<div class="titlehdr h-30"><span>All Disputes</span></div>
		<div class="wr-filter-list clearfix mt-10"></div>

		<ul class="menu-level3 mb-10 clearfix">
			<li >
				<a class="dt_filter" data-title="Status" data-name="status" data-value="" data-value-text="">All</a>
			</li>
			<?php foreach($statuses as $status_key => $status){?>
				<li class="<?php echo empty($cur_order) ? active($status_key, 'init') : '';?>">
					<a class="dt_filter" data-title="Status" data-name="status" data-value="<?php echo $status_key;?>" data-value-text="<?php echo $status['title'];?>"><?php echo $status['title'];?></a>
				</li>
			<?php }?>
		</ul>
		<table class="data table-bordered table-striped w-100pr " id="dtDispute">
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="tac dt_dispute tal">Dispute</th>
                    <th class="dt_money_back tal">Refund</th>
                    <th class="tac dt_buyer tal">Buyer</th>
                    <th class="tac dt_seller tal">Seller</th>
                    <th class="tac dt_shipper tal">Freight Forwarder</th>
                    <th class="tac dt_date_time">Created</th>
                    <th class="tac dt_date_time_changed">Update</th>
                    <th class="dt_status">Status</th>
                    <th class="tac dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall">
            </tbody>
        </table>
    </div>
</div>
