
<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/items_questions/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Questions on items</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/items_questions_doc?user_type=<?php echo strtolower(user_group_type());?>"
                title="View item questions documentation"
                data-title="View item questions documentation"
                target="_blank">
                User guide
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter" <?php echo addQaUniqueIdentifier("global__dashboard_filter-btn")?> href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('items_questions_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dtQuestionsList">
        <thead>
            <tr>
                <th class="item_dt">Item</th>
                <th class="question_dt">Question</th>
                <th class="created_dt">Created</th>
                <th class="replied_at">Replied</th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="application/javascript">
    var myFilters;
    var dtQuestionsList;
    var onSetFilters = function(callerObj, filterObj) {
        if(filterObj.name === 'create_from'){
            $("#create_to").datepicker("option", "minDate", $("#create_from").datepicker("getDate"));
        }
        if(filterObj.name === 'create_to'){
            $("#create_from").datepicker("option","maxDate", $("#create_to").datepicker("getDate"));
        }
        if(filterObj.name === 'reply_from'){
            $("#reply_to").datepicker("option", "minDate", $("#reply_from").datepicker("getDate"));
        }
        if(filterObj.name === 'reply_to'){
            $("#reply_from").datepicker("option","maxDate", $("#reply_to").datepicker("getDate"));
        }
    };
    var onDeleteFilters = function(filterObj) {
        if(filterObj.name === 'create_from'){
            $("#create_to").datepicker("option", "minDate", null);
        }
        if(filterObj.name === 'create_to'){
            $("#create_from").datepicker("option","maxDate", null);
        }
        if(filterObj.name === 'reply_from'){
            $("#reply_to").datepicker("option", "minDate", null);
        }
        if(filterObj.name === 'reply_to'){
            $("#reply_from").datepicker("option","maxDate", null);
        }
    };
    var addQuestionReplyCallback = function(response) {
		dtQuestionsList.fnDraw(false);
	};
	var editQuestionReplyCallback = function(response) {
		dtQuestionsList.fnDraw(false);
	};
	var editQuestionCallback = function(response) {
		dtQuestionsList.fnDraw(false);
    };
    var deleteQuestionCallback = function(response) {
        closeFancyBox();
		dtQuestionsList.fnDraw(false);
    };
	var deleteQuestion = function(callerObj){
		var button = $(callerObj);
        var url = button.data('href') || null;
        var question = button.data('question') || null;
        var onRequestSuccess = function(response) {
            systemMessages(response.message, response.mess_type );
            if(response.mess_type == 'success'){
                window['deleteQuestionCallback'] && deleteQuestionCallback(response);
            }
        };
        if(null === url || null === question) {
            return;
        }

        $.post(url, { question: question }, null, 'json').done(onRequestSuccess).fail(onRequestError);
	};

    $(function() {
        var onDatagridServerResponse = function(sSource, aoData, fnCallback) {
            if(!myFilters){
                myFilters = initDtFilter();
            }

            $.post(sSource, aoData.concat(myFilters.getDTFilter()), null, 'json').done(function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
                }

                fnCallback(response, textStatus, jqXHR);
            });
        };
        var onDatagridDraw = function(oSettings) {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
            $('.rating-bootstrap').rating();
        };

        dataT = dtQuestionsList = $('#dtQuestionsList').dataTable({
            sDom: '<"top"i>rt<"bottom"lp><"clear">',
            language: {
                url: location.origin + '/public/plug/jquery-datatables-1-10-12/i18n/' + __site_lang + '.json'
            },
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: '<?php echo $questionsDtUrl;?>',
            aoColumnDefs: [
                { sClass: "w-425",        aTargets: ['item_dt'],     mData: "item",       bSortable: true  },
                { sClass: "dn-xl",        aTargets: ['question_dt'], mData: "question",   bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],  mData: "created_at", bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['replied_at'],  mData: "replied_at", bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],  mData: "actions",    bSortable: false }
            ],
            sPaginationType: "full_numbers",
            language: {
                paginate: {
                    previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                    first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                    next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                    last: '<i class="ep-icon ep-icon_arrow-right"></i>'
                }
            },
            sorting: [[2, 'desc']],
            fnServerData: onDatagridServerResponse,
            fnDrawCallback: onDatagridDraw,
        });
        dataTableScrollPage(dataT);

        $(".datepicker-init").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });
    });
</script>
