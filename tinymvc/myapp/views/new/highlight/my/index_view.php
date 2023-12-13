<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-countdown-2-2-0/jquery.countdown.js');?>"></script>

<script>
    var dtItemsHighlight;
    var itemsFilters;
    var dataT;
    filters_has_datepicker = true;

    beforeSetFilters = function(callerObj){
        if (callerObj.prop("name") == 'highlight_number') {
            var number = toOrderNumber(callerObj.val());
            if (number) {
                callerObj.val(number);
            }
            else {
                systemMessages('Error: Incorrect highlight number.', 'error');
                callerObj.val('');
                return false;
            }
        }
        if (callerObj.prop("name") == 'id_item') {
            var number = toOrderNumber(callerObj.val());
            if (number) {
                callerObj.val(number);
            }
            else {
                systemMessages('Error: Incorrect item number.', 'error');
                callerObj.val('');
                return false;
            }
        }
    }

    setDateFilters = function(callerObj, filter){
        if (filter.name == 'parent') {
            $('.subcategories').remove();
        }

        if (filter.name == 'highlight_number' || filter.name == 'id_item') {
            var new_url = '<?php echo __SITE_URL . 'highlight/my'?>';
            if (window.history.pushState) {
                history.pushState({}, 'Export Portal Highlight_number &raquo; International export & import, b2b and trading in world', new_url);
            } else {
                window.location.href = new_url;
            }
        }

        if(filter.name === 'create_date_from'){
            $("#start_to").datepicker("option", "minDate", $("#start_from").datepicker("getDate"));
        }
        if(filter.name === 'create_date_to'){
            $("#start_from").datepicker("option","maxDate", $("#start_to").datepicker("getDate"));
        }
        if(filter.name === 'start_last_update'){
            $("#update_to").datepicker("option", "minDate", $("#update_from").datepicker("getDate"));
        }
        if(filter.name === 'finish_last_update'){
            $("#update_from").datepicker("option","maxDate", $("#update_to").datepicker("getDate"));
        }
        if(filter.name === 'start_expire'){
            $("#end_to").datepicker("option", "minDate", $("#end_from").datepicker("getDate"));
        }
        if(filter.name === 'finish_expire'){
            $("#end_from").datepicker("option","maxDate", $("#end_to").datepicker("getDate"));
        }
    }

    onDeleteFilters = function(filter){
        if(filter.name == 'parent'){
            $('.subcategories').remove();
        }

        if(filter.name == 'featured_number' || filter.name == 'id_item' || filter.name == 'expire_days' || filter.name == 'status'){
            var new_url = '<?php echo __SITE_URL.'featured/my'?>';
            if(window.history.pushState){
                history.pushState({}, 'Export Portal Highlight_number &raquo; International export & import, b2b and trading in world', new_url);
            } else{
                window.location.href = new_url;
            }
        }

        if(filter.name === 'create_date_from'){
            $("#start_to").datepicker("option", "minDate", null);
        }
        if(filter.name === 'create_date_to'){
            $("#start_from").datepicker("option","maxDate", null);
        }
        if(filter.name === 'start_last_update'){
            $("#update_to").datepicker("option", "minDate", null);
        }
        if(filter.name === 'finish_last_update'){
            $("#update_from").datepicker("option","maxDate", null);
        }
        if(filter.name === 'start_expire'){
            $("#end_to").datepicker("option", "minDate", null);
        }
        if(filter.name === 'finish_expire'){
            $("#end_from").datepicker("option","maxDate", null);
        }
    }

    $(document).ready(function(){
        dataT = dtItemsHighlight = $('#dtItemsHighlight').dataTable({
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>highlight/ajax_highlight_my_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-90 tac vam dn-xl", "aTargets": ['dt_highlight_number'], "mData": "dt_highlight_number", 'bSortable': false},
                {"sClass": "", "aTargets": ['dt_item'], "mData": "dt_item", 'bSortable': false},
                {"sClass": "w-100 vam", "aTargets": ['dt_end_date'], "mData": "dt_end_date"},
                {"sClass": "w-100 vam dn-xl", "aTargets": ['dt_update_date'], "mData": "dt_update_date"},
                {"sClass": "w-100 vam dn-xl", "aTargets": ['dt_create_date'], "mData": "dt_create_date"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_status'], "mData": "dt_status", 'bSortable': false},
                {"sClass": "w-70 tac vam dn-lg", "aTargets": ['dt_price'], "mData": "dt_price"},
                {"sClass": "w-50 tac vam dn-lg", "aTargets": ['dt_paid'], "mData": "dt_paid", 'bSortable': false},
                {"sClass": "w-40 tac vam dt-actions", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
            ],
            "sorting": [[3, 'desc']],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function (sSource, aoData, fnCallback) {
                if(!itemsFilters){
                    //view template initDtFilter in scripts_new
                    itemsFilters = initDtFilter();
                }

                aoData = aoData.concat(itemsFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "fnDrawCallback": function (oSettings) {
                hideDTbottom(this);
                mobileDataTable($('.main-data-table'));

                $.each($('span.countdown-dt'), function () {
                    var expire = $(this).data('expire');
                    var selectedDate = new Date().valueOf() + expire;
                    $(this).countdown(selectedDate.toString(), function (event) {
                        var format_clock = '<div class="txt-green">%D days %H hours %M min %S sec</div>';
                        if (expire < 7200000) {
                            format_clock = '<div class="txt-red">%D days %H hours %M min %S sec</div>';
                        }
                        $(this).html(event.strftime(format_clock));
                    }).on('finish.countdown', function (event) {
                        $(this).html('<div class="txt-red">Expired!</div>');
                    });
                });
            }
        });
        dataTableScrollPage(dataT);

        $(".datepicker-init").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });
    });


    var rehighlight_item = function(opener){
        var $this = $(opener);
        var high_item = $this.data('item');
        var url = "highlight/ajax_highlight_operation/rehighlight_item/" + high_item;
        $.ajax({
            type: "POST",
            url: url,
            dataType: "JSON",
            success: function(resp) {
                if (resp.mess_type == 'success'){
                    dtItemsHighlight.fnDraw();
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    function cancelHighlightItem(button) {
        return postRequest('<?php echo __SITE_URL . 'highlight/ajax_highlight_operation/cancel';?>', {item: $(button).data('item')})
            .then(function (data) {
                var messageType = data.mess_type;
                var message = data.message;
                systemMessages(message, `message-${messageType}`);
                if ('success' == messageType) {
                    dtItemsHighlight.fnDraw();
                }
            })
            .catch(onRequestError)
    }
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/highlight/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My Items Highlight</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/33" title="View Highlight item documentation" data-title="View Highlight item documentation" target="_blank">User guide</a> -->
            <a class="btn btn-dark fancybox btn-filter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span><?php echo translate('highlight_description'); ?></span>
    </div>

    <table class="main-data-table" id="dtItemsHighlight">
        <thead>
        <tr>
            <th class="dt_highlight_number">Highlight #</th>
            <th class="dt_item">Item</th>
            <th class="dt_end_date">Expiry</th>
            <th class="dt_create_date">Created</th>
            <th class="dt_update_date">Update</th>
            <th class="dt_status">Status</th>
            <th class="dt_price">Price</th>
            <th class="dt_paid">Paid</th>
            <th class="dt_actions"></th>
        </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>


