
<?php views()->display('new/filter_panel_main_view', array('filter_panel' => 'new/items_comments/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My comments</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/items_comments_doc?user_type=<?php echo strtolower(user_group_type());?>"
                title="View item comments documentation"
                data-title="View item comments documentation"
                target="_blank">
                User guide
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('items_comments_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dtCommentsList">
        <thead>
            <tr>
                <th class="item_dt">Item</th>
                <?php if (have_right('manage_seller_item_comments')) {?>
                    <th class="author_dt">Author</th>
                <?php }?>
                <th class="comment_dt">Comment</th>
                <th class="created_dt">Created</th>
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
    var dtCommentsList;

    var beforeSetFilters = function(callerObj) {
        if(callerObj.prop("name") == 'id_item'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else {
                systemMessages('Incorrect item number.', 'error' );
                callerObj.val('');

                return false;
            }
        }
    };

    var onSetFilters = function(callerObj, filterObj) {
        if(filterObj.name === 'create_from'){
            $("#crate_to").datepicker("option", "minDate", $("#create_from").datepicker("getDate"));
        }
        if(filterObj.name === 'crate_to'){
            $("#create_from").datepicker("option","maxDate", $("#crate_to").datepicker("getDate"));
        }
    };
    var onDeleteFilters = function(filterObj) {
        if(filterObj.name === 'create_from'){
            $("#crate_to").datepicker("option", "minDate", null);
        }
        if(filterObj.name === 'crate_to'){
            $("#create_from").datepicker("option","maxDate", null);
        }
    };
	var addCommentCallback = function(response) {
		dtCommentsList.fnDraw(false);
	};
    var addCommentReplyCallback = function(response) {
        dtCommentsList.fnDraw(false);
    };
	var editCommentCallback = function(response) {
		dtCommentsList.fnDraw(false);
	};
	var editCommentReplyCallback = function(response) {
		dtCommentsList.fnDraw(false);
	};
    var deleteCommentCallback = function(response, button) {
        systemMessages(response.message, response.mess_type );
        if(response.mess_type == 'success'){
            if($.fancybox.isOpened) {
                button.closest('.product-comments__item').fadeOut('normal', function(){
                    $(this).remove();
                    $.fancybox.update();
                });
            }
		    dtCommentsList.fnDraw(false);
        }
    };
	/* var deleteComment = function(caller){
		var button = $(caller);
        var url = button.data('href') || (__site_url  + '/items_comments/ajax_comment_operation/delete_comment');
        var item = button.data('item') || null;
        var comment = button.data('comment') || null;
        var onRequestSuccess = function(response) {
            if(typeof window.deleteCommentCallback !== 'undefined') {
                deleteCommentCallback(response, button);
            }
        };

        if(null !== url && null !== comment && null !== item) {
            $.post(url, { comment: comment, item: item }, null, 'json').done(onRequestSuccess).fail(onRequestError);
        }
	}; */

    $(function() {
        var showAuthorColumn = Boolean(~~parseInt('<?php echo (int) (bool) have_right('manage_seller_item_comments'); ?>', 10));
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

        dataT = dtCommentsList = $('#dtCommentsList').dataTable({
            sDom: '<"top"i>rt<"bottom"lp><"clear">',
            language: {
                url: location.origin + '/public/plug/jquery-datatables-1-10-12/i18n/' + __site_lang + '.json'
            },
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: location.origin + '/items_comments/ajax_list_my_dt',
            aoColumnDefs: [].concat(
                [{ sClass: "w-425", aTargets: ['item_dt'], mData: "item", bSortable: true  }],
                showAuthorColumn ? [{ sClass: "w-200", aTargets: ['author_dt'], mData: "author", bSortable: false }] : [],
                [{ sClass: "dn-xl",        aTargets: ['comment_dt'], mData: "comment",    bSortable: false }],
                [{ sClass: "w-100 dn-lg",  aTargets: ['created_dt'], mData: "created_at", bSortable: true  }],
                [{ sClass: "w-40 tac vam", aTargets: ['actions_dt'], mData: "actions",    bSortable: false }]
            ),
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
