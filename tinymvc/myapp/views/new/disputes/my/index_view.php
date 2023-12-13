<?php views()->display('new/filter_panel_main_view', array('filter_panel' => 'new/disputes/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Disputes</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo getUrlForGroup("user_guide/popup_forms/show_doc/order_dispute_doc?user_type=seller"); ?>" title="Order disputes" data-title="Order disputes" target="_blank">User guide</a> -->
            <a class="btn btn-dark btn-filter fancybox" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b mt-15">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span>
            This module allows any buyer to express his/her discontent with the order in general or with the definite item in the order.<br>
            In case the buyer proves he/she is right, he/she can request the replacement of the items, as well as partial or full monetary compensation.
        </span>
    </div>

    <table class="main-data-table" id="dtDisputes">
        <thead>
            <tr>
				<th class="dt_details">Dispute</th>
				<th class="dt_users">Participants</th>
				<th class="dt_money_back">Refund</th>
				<th class="dt_created">Created</th>
				<th class="dt_updated">Updated</th>
				<th class="dt_status">Status</th>
				<th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php views()->display('new/file_upload_scripts'); ?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js'); ?>"></script>
<script src="<?php echo fileModificationTime('public/plug/js/dt.scripts.js');?>"></script>
<script>
    $(function() {
        var beforeSetFilters = function (caller) {
            if (typeof filterPreMutators !== 'undefined') {
                filterPreMutators.handle({ name: caller.prop("name"), node: caller });
            }
        };
        var onSetFilters = function(caller, filter) {
            if (typeof filterMutators !== 'undefined') {
                filterMutators.handle(filter);
            }
        };
        var onDeleteFilters = function(filter) {
            if (typeof filterCleaners !== 'undefined') {
                filterCleaners.handle(filter);
            }
        };
        var onDatepickerShow = function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var resolveDispute = function(caller) {
            var button = $(caller);
            var dispute = button.data('dispute') || null;
            var url = __group_site_url + 'dispute/ajax_operation/resolve';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                dtDisputes.fnDraw(false);
            }

            if(null !== dispute) {
                $.post(url, { dispute: dispute }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var onSendRequest = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type === 'error') {
                    systemMessages(response.message, 'message-' + response.mess_type);
                }

                callback(
                    $.extend({ aaData: [], iTotalRecords: 0, iTotalDisplayRecrds: 0, }, response),
                    textStatus,
                    jqXHR
                );
            };

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError);
        };
        var onCancelDispute = function() {
            dtDisputes.fnDraw(false);
        };
        var onEditDispute = function() {
            dtDisputes.fnDraw(false);
        };
        var onDatagridDraw = function() {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
        };

        var myFilters;
        var dtDisputes;
        var datepickers = $(".datepicker-init");
        var filterPreMutators = new DTFilters.Bindings(
            [
                { name: 'dispute', op: [{ name: 'maskNumber' }]},
                { name: 'order',   op: [{ name: 'maskNumber' }]},
            ],
            {
                'maskNumber': function() {
                    var value = this.node.val() || null;
                    var number = null !== value ? toOrderNumber(value) : null;
                    if(number){
                        this.node.val(number);
                    } else{
                        this.node.val('');

                        return false;
                    }
                }
            }
        );
        var filterMutators = new DTFilters.Bindings(
            [
                { name: 'created_from', source: '#filter-created-from', op: [{ name: 'minDate', target: '#filter-created-to'   }]},
                { name: 'created_to',   source: '#filter-created-to',   op: [{ name: 'maxDate', target: '#filter-created-from' }]},
                { name: 'updated_from', source: '#filter-updated-from', op: [{ name: 'minDate', target: '#filter-updated-to'   }]},
                { name: 'updated_to',   source: '#filter-updated-to',   op: [{ name: 'maxDate', target: '#filter-updated-from' }]},
            ],
            {
                minDate: function(source, target) { target.datepicker("option", "minDate", source.datepicker("getDate")); },
                maxDate: function(source, target) { target.datepicker("option", "maxDate", source.datepicker("getDate")); }
            }
        );
        var filterCleaners = new DTFilters.Bindings(
            [
                { name: 'created_from', op: [{ name: 'minDate', target: '#filter-created-to'    }]},
                { name: 'created_to',   op: [{ name: 'maxDate', target: '#filter-created-from'  }]},
                { name: 'updated_from', op: [{ name: 'minDate', target: '#filter-updated-to'    }]},
                { name: 'updated_to',   op: [{ name: 'maxDate', target: '#filter-updated-from'  }]},
                { name: 'dispute',      op: [{ name: 'clearUrl' }]},
                { name: 'status',       op: [{ name: 'clearUrl' }]},
                { name: 'order',        op: [{ name: 'clearUrl' }]},
            ],
            {
                minDate: function(source, target) { target.datepicker("option", "minDate", null); },
                maxDate: function(source, target) { target.datepicker("option", "maxDate", null); },
                clearUrl: function() {
                    DTFilters.Util.rewriteHystory(
                        __group_site_url +  DTFilters.Util.dropPathParams(location.pathname, 'status' !== this.name ? this.name + '_number' : this.name),
                        this
                    );
                },
            }
        );
        var datepickerOptions = {
            beforeShow: onDatepickerShow
        };
        var disputeOptions = {
            sDom: '<"top"i>rt<"bottom"lp><"clear">',
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: __group_site_url + "dispute/ajax_my_disputes_dt",
            sServerMethod: "POST",
            aoColumnDefs: [
				{ sClass: "",                aTargets: ["dt_details"],    mData: "dt_details",    bSortable: true },
				{ sClass: "mw-200",          aTargets: ["dt_users"],      mData: "dt_users",      bSortable: false },
				{ sClass: "w-120 dn-xl",     aTargets: ["dt_money_back"], mData: "dt_money_back", bSortable: true },
				{ sClass: "w-120 vam dn-xl", aTargets: ["dt_created"],    mData: "dt_created",    bSortable: true },
				{ sClass: "w-120 vam dn-xl", aTargets: ["dt_updated"],    mData: "dt_updated",    bSortable: true },
				{ sClass: "w-90 tac vam",    aTargets: ["dt_status"],     mData: "dt_status",     bSortable: true },
				{ sClass: "w-50 tac vam",    aTargets: ["dt_actions"],    mData: "dt_actions",    bSortable: false }
            ],
            sorting : [[3, 'desc']],
            sPaginationType: "full_numbers",
            language: {
                paginate: {
                    first: "<i class='ep-icon ep-icon_arrow-left'></i>",
                    previous: "<i class='ep-icon ep-icon_arrows-left'></i>",
                    next: "<i class='ep-icon ep-icon_arrows-right'></i>",
                    last: "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            fnServerData: onSendRequest,
            fnDrawCallback: onDatagridDraw,
        };

        myFilters = initDtFilter();
        dataT = dtDisputes = $('#dtDisputes').dataTable(disputeOptions);
        datepickers.datepicker(datepickerOptions);

        dataTableScrollPage(dataT);
        mix(window, {
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            beforeSetFilters: beforeSetFilters,
            resolveDispute: resolveDispute,
            callbackEditDispute: onEditDispute,
            callbackCancelDispute: onCancelDispute
        });
    });
</script>
