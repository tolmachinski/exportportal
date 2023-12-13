
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js'); ?>"></script>
<script src="<?php echo fileModificationTime('public/plug/lodash-custom-4-17-5/lodash.custom.min.js'); ?>"></script>
<?php views()->display('new/file_upload_scripts'); ?>
<?php views()->display('new/download_script'); ?>
<script>
    var dtItemsList;
    var itemsFilters;
    var refeature_item = function(opener) {
        var $this = $(opener);
        var feat_item = $this.data('item');
        var url = "featured/ajax_featured_operation/refeature_item/" + feat_item;
        $.ajax({
            type: "POST",
            url: url,
            dataType: "JSON",
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw(false);
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    var rehighlight_item = function(opener) {
        var $this = $(opener);
        var high_item = $this.data('item');
        var url = "highlight/ajax_highlight_operation/rehighlight_item/" + high_item;
        $.ajax({
            type: "POST",
            url: url,
            dataType: "JSON",
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw();
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    var feature_item = function(opener) {
        var $this = $(opener);
        var item = $this.data("item");
        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/apply_feature",
            data: {
                item: item
            },
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw(false);
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    var highlight_item = function(opener) {
        var $this = $(opener);
        var item = $this.data("item");
        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/apply_highlight",
            data: {
                item: item
            },
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw(false);
                    closeFancyBox();
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    var visibility_item = function(opener) {
        var $this = $(opener);
        var item = $this.data("item");
        var visible = $this.data("visible");

        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/change_visibility",
            data: {
                item: item,
                visible: visible
            },
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw(false);
                }
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }

    var delete_draft_item = function(opener) {
        var $this = $(opener);
        var item = $this.data("item");

        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/delete_draft",
            data: {
                item: item
            },
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw();
                }
                systemMessages(resp.message, 'message-' + resp.mess_type);
            }
        });
    }

    var add_to_archive = function(element) {
        var item = $(element).data("item");

        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/add_to_archive",
            data: {item: item},
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw();
                }
                systemMessages(resp.message, 'message-' + resp.mess_type);
            }
        });
    }

    var return_from_archive = function(element) {
        var item = $(element).data("item");

        $.ajax({
            type: "POST",
            url: "items/ajax_item_operation/return_from_archive",
            data: {item: item},
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type == 'success') {
                    dtItemsList.fnDraw();
                }
                systemMessages(resp.message, 'message-' + resp.mess_type);
            }
        });
    }

    <?php
    if (
        verifyNeedCertifyUpgrade()
        && empty($popup_add)
    ) {
    ?>
        var callMakeYourItemsMoreVisible = function(params) {
            var title = params.title || '',
                subTitle = params.subTitle || '',
                link = 'upgrade/popup_forms/upgrade_your_account_now',
                titleImage = __site_url + 'public/img/upgrade_page/modals/upgrade_now.jpg';
            closeCallBack = params.closeCallBack || function() {}

            callHeaderImageModal({
                titleUppercase: true,
                title: title,
                subTitle: subTitle,
                titleImage: titleImage,
                content: link,
                buttons: [{
                    label: 'Get started',
                    cssClass: "btn btn-primary mnw-185",
                    action: function(dialog) {
                        location.href = __site_url + 'upgrade'
                    },
                }],
                closeCallBack: closeCallBack
            });
        }
    <?php } ?>

    filters_has_datepicker = true;

    $(document).ready(function() {
        dataT = dtItemsList = $('#dtItemsList').dataTable({
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>items/ajax_my_items",
            "sServerMethod": "POST",
            "aoColumnDefs": [{
                    "sClass": "",
                    "aTargets": ['dt_item'],
                    "mData": "dt_item",
                    'bSortable': false
                },
                {
                    "sClass": "w-5 w-130 dn-xl",
                    "aTargets": ['dt_address'],
                    "mData": "dt_address",
                    'bSortable': false
                },
                {
                    "sClass": "w-180",
                    "aTargets": ['dt_price'],
                    "mData": "dt_price",
                    'bSortable': false
                },
                {
                    "sClass": "w-180",
                    "aTargets": ['dt_quantity'],
                    "mData": "dt_quantity"
                },
                {
                    "sClass": "w-100",
                    "aTargets": ['dt_update_date'],
                    "mData": "dt_update_date"
                },
                {
                    "sClass": "w-130 dn-xl",
                    "aTargets": ['dt_statistics'],
                    "mData": "dt_statistics",
                    'bSortable': false
                },
                {
                    "sClass": "w-40 tac vam dt-actions",
                    "aTargets": ['dt_actions'],
                    "mData": "dt_actions",
                    'bSortable': false
                }
            ],
            "sorting": [
                [4, 'desc']
            ],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function(sSource, aoData, fnCallback) {
                if (!itemsFilters) {
                    //view template initDtFilter in scripts_new
                    itemsFilters = initDtFilter();
                }

                aoData = aoData.concat(itemsFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "fnDrawCallback": function(oSettings) {
                hideDTbottom(this);
                mobileDataTable($('.main-data-table'));
                $('.rating-bootstrap').rating();
            }
        });
        dataTableScrollPage(dataT);

        $(".datepicker-init").datepicker({
            beforeShow: function(input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });

        <?php if (!empty($select_category)) { ?>
            $('.fancyboxAddItem').attr('href', '<?php echo __SITE_URL; ?>items/add/category/<?php echo $select_category; ?>').trigger('click');
        <?php } ?>

        <?php if (!empty($popup_add)) { ?>
            $('.fancyboxAddItem').attr('href', '<?php echo __SITE_URL; ?>items/add').trigger('click');
        <?php } ?>

        <?php if (isset($requestDraftExtend)) { ?>
            openFancyboxValidateModal('<?php echo __SITE_URL; ?>items/popup_forms/show_draft_request/' + <?php echo !empty($requestDraftExtend) ? $requestDraftExtend : '0'; ?>, 'Add Extend Request');
        <?php } ?>

        function downloadBulkUploadExample(button) {
            button.addClass('disabled').prop('disabled', true);

            getRequest(__group_site_url + 'items/ajax_item_operation/download_draft_example')
                .then(function(response) {
                    downloadFile(response.file, response.name);
                })
                .catch(function(e) {
                    onRequestError(e);
                })
                .finally(function() {
                    button.removeClass('disabled').prop('disabled', false);
                })
        }

        <?php if ($filter_featured > 0) { ?>
            $('.js-dt-filter-featured').find('option[value="1"]').prop('selected', true).end().trigger('change');
        <?php } ?>

        <?php if (isset($filterExpiresDraft)) {
            $filterExpiresDraft = getDateFormat($filterExpiresDraft, 'Y-m-d', 'm/d/Y'); ?>
            $('#js-expiration_date').val(<?php echo $filterExpiresDraft; ?>).datepicker('setDate', '<?php echo $filterExpiresDraft; ?>').trigger('change');
        <?php } ?>

        mix(globalThis, {
            downloadBulkUploadExample: downloadBulkUploadExample
        });

        // NEW POPUP CALL items_more_visible
        <?php if (
            verifyNeedCertifyUpgrade()
            && empty($popup_add)
            && empty($select_category)
            && !isset($requestDraftExtend)
        ) { ?>
            if (!__disable_popup_system) {
                setTimeout(
                function() {
                    dispatchCustomEvent("popup:call-popup", globalThis, {detail: { name: "items_more_visible" }});
                },
                2000
            );
            }
        <?php } ?>

    });
</script>

<?php views()->display('new/filter_panel_main_view', ['filter_panel' => 'new/item/my/filter_panel_view']); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line dashboard-line--ordered">
        <h1 class="dashboard-line__ttl">My Items</h1>

        <div class="dashboard-line__actions">
            <div class="btn-group">
                <a class="btn btn-light btn-block fancybox fancybox.iframe" href="https://survey.zohopublic.com/zs/TNCshD" data-title="Complete the survey" data-w="100%" data-h="95%"><?php echo translate("items_my_complete_survey_btn") ?></a>
            </div>

            <a class="btn btn-primary pl-20 pr-20 fancyboxAddItem fancybox.ajax" href="<?php echo __SITE_URL; ?>items/add" data-title="Add item" <?php echo addQaUniqueIdentifier("items-my__add-item") ?>>
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add item</span>
            </a>

            <div class="btn-group">
                <span title="Bulk upload" class="btn btn-light btn-block fancybox.ajax fancyboxValidateModal" data-dashboard-class="inputs-40" data-fancybox-href="<?php echo getUrlForGroup('items/popup_forms/bulk_upload'); ?>" data-w="100%" data-title="Bulk upload">
                    Bulk upload
                </span>

                <span title="<?php echo translate('seller_products_dashboard_download_example_button_title', null, true); ?>" class="btn btn-light call-function bdl-1-gray2 pr-20 pl-20" data-callback="downloadBulkUploadExample">
                    <i class="ep-icon ep-icon_download-stroke fs-16"></i>
                </span>
            </div>

            <!-- <a id="bt-items-tour" class="btn btn-light fancyboxVideo fancybox.iframe" href="<?php echo get_video_link($video_tour['key_link'], 'youtube') ?>" title="View video tour" data-title="View video tour" target="_blank">Video guide</a> -->
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL; ?>user_guide/popup_forms/show_doc/items_doc?user_type=<?php echo strtolower(user_group_type()); ?>" title="View items documentation" data-title="View items documentation" target="_blank">User guide</a> -->

            <a class="btn btn-dark fancybox btn-filter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div class="mb-5">
            <ul class="my-items-info-list">
                <li class="my-items-info-list__item">
                    <?php echo translate('my_items_info_first_line', [
                        '{{START_TAG}}'   => '<button class="bulk-upload__btn call-function" data-callback="downloadGuide" data-guide-name="item_bulk_upload" data-lang="en" data-group="all">',
                        '{{END_TAG}}'     => '</button>',
                        '{{START_VIDEO}}' => '<button class="bulk-upload__btn call-function" data-callback="openVideoModal" data-title="' . translate('popup_bulk_item_upload_ttl', null, true) . '" data-href="' .  config("my_items_bulk_upload_video_url") . '" data-autoplay="true" title="' .  translate('popup_bulk_item_upload_ttl', null, true) . '" data-mw="1920" data-w="80%" data-h="88%">',
                        '{{END_VIDEO}}'   => '</button>'
                    ]); ?>
                </li>
                <li class="my-items-info-list__item"><p><?php echo translate('items_my_description'); ?></p></li>
                <li class="my-items-info-list__item"><p><?php echo translate('my_items_info_third_line'); ?></p></li>
            </ul>
        </div>
    </div>

    <table class="main-data-table" id="dtItemsList">
        <thead>
            <tr>
                <th class="dt_item">Item</th>
                <th class="dt_address">Location</th>
                <th class="dt_price">Price</th>
                <th class="dt_quantity">Quantity</th>
                <th class="dt_update_date">Updated</th>
                <th class="dt_statistics">Statistics</th>
                <th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<div id="js-template-email-invite-success"></div>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/user_guide/index.js'); ?>"></script>
