<script type="text/javascript">
    function toggle_visible($btn) {
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>cr_events/ajax_toggle_visible',
            data: {
                id: $btn.data('id'),
                visible_value: (parseInt($btn.data('visible')) + 1) % 2
            },
            dataType: 'json',
            success: function (data) {
                if (data.mess_type === 'success') {
                    crEvents.fnDraw(false);
                }
            }
        });
    }

    function remove_event($btn) {
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>cr_events/ajax_remove_event',
            data: {
                id: $btn.data('id')
            },
            dataType: 'json',
            success: function (data) {
                if (data.mess_type === 'success') {
                    crEvents.fnDraw(false);
                }
            }
        });
    }

    function on_users_selected(data) {
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>cr_users/ajax_operations/assign_users',
            data: {
                id_list: data.dataIds,
                id_item: data.idItem,
                type: data.type
            },
            dataType: 'json',
            success: function (data) {
                if (data.mess_type === 'success') {
                    crEvents.fnDraw(false);
                    closeFancyBox();
                }

                systemMessages(data.message, 'message-' + data.mess_type);
            }
        });
    }

    function approve_event($btn) {
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>cr_events/ajax_approve_event',
            data: {
                id: $btn.data('id')
            },
            dataType: 'json',
            success: function (data) {
                if (data.mess_type === 'success') {
                    crEvents.fnDraw(false);
                }
            }
        });
    }

    $(function () {
        window.crEvents = $('#cr-events').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>cr_events/ajax_events_administration",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-30 tac", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": true},
                {"sClass": "w-250", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false},
                {"sClass": "w-250", "aTargets": ['dt_location'], "mData": "dt_location", "bSortable": false},
                {"sClass": "tac w-120", "aTargets": ['dt_date_start'], "mData": "dt_date_start", "bSortable": true},
                {"sClass": "tac w-120", "aTargets": ['dt_date_end'], "mData": "dt_date_end", "bSortable": true},
                {"sClass": "tac w-160", "aTargets": ['dt_count_ambassadors'], "mData": "dt_count_ambassadors", "bSortable": true},
                {"sClass": "tac w-160", "aTargets": ['dt_count_users'], "mData": "dt_count_users", "bSortable": true},
                {"sClass": "tac w-100", "aTargets": ['dt_visible'], "mData": "dt_visible", "bSortable": true},
                {"sClass": "tac w-100", "aTargets": ['dt_status'], "mData": "dt_status", "bSortable": true},
                {"sClass": "tac w-50", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sorting": [[0, "desc"]],
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function (oSettings) {

            },
            "fnServerData": function (sSource, aoData, fnCallback) {
                if (!window.crEventsFilter) {
                    window.crEventsFilter = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function () {
                            crEvents.fnDraw();
                        },
                        onSet: function(callerObj, filterObj) {
                            if (filterObj.name === 'approved_type') {
                                $('.menu-level3 a[data-value="' + filterObj.value + '"]')
                                    .parent('li')
                                    .addClass('active')
                                    .siblings()
                                    .removeClass('active');
                            }
                        },
                        onDelete: function(filterObj) {
                            if (filterObj.name === 'approved_type') {
                                $('a[data-value="' + filterObj.default + '"]')
                                    .parent('li')
                                    .addClass('active')
                                    .siblings()
                                    .removeClass('active');
                            }
                        }
                    });
                }

                aoData = aoData.concat(crEventsFilter.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type === 'error') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        if (data.mess_type === 'info') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            }
        });
    });
</script>


<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>CR Events</span>
            <a class="btn btn-primary btn-xs pull-right fancyboxValidateModalDT fancybox.ajax" data-table="crEvents" href="<?php echo __SITE_URL;?>cr_events/popup_forms/add_event" title="Add new event" data-title="Add new event">
                <i class="ep-icon ep-icon_plus"></i>
                <span>Add new event</span>
            </a>
        </div>

        <?php tmvc::instance()->controller->view->display('admin/cr/events/filter_panel_view'); ?>

        <div class="wr-filter-list clearfix mt-10"></div>

        <ul class="menu-level3 mb-10 clearfix">
            <li class="active">
                <a class="dt_filter" data-title="Approved type" data-name="approved_type" data-value="" data-value-text="All">All</a>
            </li>
            <li>
                <a class="dt_filter" data-name="approved_type" data-value="mine" data-value-text="Assigned to me">Events approved by me</a>
            </li>
        </ul>

        <table class="data table-striped table-bordered w-100pr" id="cr-events" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th class="dt_id">#</th>
                <th class="dt_name">Name</th>
                <th class="dt_location">Location</th>
                <th class="dt_date_start">Start date</th>
                <th class="dt_date_end">End date</th>
                <th class="dt_count_ambassadors">Count ambassadors</th>
                <th class="dt_count_users">Count users</th>
                <th class="dt_visible">Visible</th>
                <th class="dt_status">Status</th>
                <th class="dt_actions">Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
