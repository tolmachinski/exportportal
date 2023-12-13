<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/user/seller/pictures/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("seller_pictures_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-my__dashboard_add-picture-btn'); ?>
                data-fancybox-href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/add_picture"
                data-title="<?php echo translate("seller_pictures_dashboard_add_picture_modal_title", null, true); ?>"
                title="<?php echo translate("seller_pictures_dashboard_add_picture_button_title", null, true); ?>">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add picture</span>
            </a>

            <a class="btn btn-light"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-my__dashboard_categories-btn'); ?>
                href="<?php echo __SITE_URL;?>seller_pictures/categories"
                title="<?php echo translate("seller_pictures_dashboard_categories_button_title", null, true); ?>">
                <?php echo translate("seller_pictures_dashboard_categories_button_text"); ?>
            </a>

            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                href="<?php //echo __SITE_URL;?>user_guide/popup_forms/show_doc/77"
                title="<?php //echo translate("seller_pictures_dashboard_documentation_button_title", null, true); ?>"
                data-title="<?php //echo translate("seller_pictures_dashboard_documentation_modal_title", null, true); ?>">
                <?php //echo translate("seller_pictures_dashboard_documentation_button_text"); ?>
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-my__dashboard_filter-btn'); ?>
                href="#dtfilter-hidden"
                data-mw="740"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span><?php echo translate('company_pictures_description'); ?></span>
    </div>

    <table class="main-data-table" id="dtPicturesList">
        <thead>
            <tr>
                <th class="picture_dt"><?php echo translate("seller_pictures_dashboard_dt_column_picture_text"); ?></th>
                <th class="description_dt"><?php echo translate("seller_pictures_dashboard_dt_column_description_text"); ?></th>
                <th class="created_dt"><?php echo translate("seller_pictures_dashboard_dt_column_create_date_text"); ?></th>
                <th class="updated_dt"><?php echo translate("seller_pictures_dashboard_dt_column_update_date_text"); ?></th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="application/javascript">
    $(function() {
        var myFilters;
        var dtPicturesList;

        var onSetFilters = function(caller, filter) {
            if(filter.name === 'created_from'){
                $("#filter-created-to").datepicker("option", "minDate", $("#filter-created-from").datepicker("getDate"));
            }
            if(filter.name === 'created_to'){
                $("#filter-created-from").datepicker("option","maxDate", $("#filter-created-to").datepicker("getDate"));
            }
            if(filter.name === 'updated_from'){
                $("#filter-updated-to").datepicker("option", "minDate", $("#filter-updated-from").datepicker("getDate"));
            }
            if(filter.name === 'updated_to'){
                $("#filter-updated-from").datepicker("option","maxDate", $("#filter-updated-to").datepicker("getDate"));
            }
        };
        var onDeleteFilters = function(filter) {
            if(filter.name === 'created_from'){
                $("#filter-created-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'created_to'){
                $("#filter-created-from").datepicker("option","maxDate", null);
            }
            if(filter.name === 'updated_from'){
                $("#filter-updated-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'updated_to'){
                $("#filter-updated-from").datepicker("option","maxDate", null);
            }
        };
        var onCategoriesUpdate = function() {
            var filter = $('#filter-categories');
            var url = __site_url + 'seller_pictures/ajax_pictures_operation/get_categories';
            var onRequestSuccess = function(response) {
                var categories = response.categories || [];
                var selected = filter.val() || null;
                var options = [];
                categories.forEach(function(category) {
                    this.push($('<option>').val(category.id_category).text(category.category_title));
                }, options);

                filter.children().not(':first').remove();
                filter.append(options);
                filter.val(selected);
            }

            $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
        }
        var onAddPciture = function (response){
            dtPicturesList.fnDraw();
        };
        var onEditPicture = function (response){
            dtPicturesList.fnDraw(false);
        };
        var deletePicture = function(caller) {
            var button = $(caller);
            var picture = button.data('picture') || null;
            var url = __site_url + 'seller_pictures/ajax_pictures_operation/delete_picture';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                dtPicturesList.fnDraw();
            }

            if(null !== picture) {
                $.post(url, { picture: picture }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, 'message-' + response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            if(!myFilters){
                myFilters = initDtFilter();
            }

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onDatagridDraw = function(settings) {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
        };
        var onDatepickerShow = function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var datepickerOptions = {
            beforeShow: onDatepickerShow
        };
        var datagridOptions = {
            sDom: '<"top"i>rt<"bottom"lp><"clear">',
            language: {
                url: location.origin + '/public/plug/jquery-datatables-1-10-12/i18n/' + __site_lang + '.json'
            },
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: location.origin + '/seller_pictures/ajax_photo_list_dt',
            aoColumnDefs: [
                { sClass: "w-450",        aTargets: ['picture_dt'],     mData: "picture",     bSortable: true  },
                { sClass: "dn-xl",        aTargets: ['description_dt'], mData: "description", bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],     mData: "created_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],     mData: "updated_at",  bSortable: true  },
                { sClass: "w-40 tac vam dt-actions", aTargets: ['actions_dt'],     mData: "actions",     bSortable: false }
            ],
            sorting: [[2, 'desc']],
            sPaginationType: "full_numbers",
            language: {
                paginate: {
                    previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                    first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                    next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                    last: '<i class="ep-icon ep-icon_arrow-right"></i>'
                }
            },
            fnServerData: fetchServerData,
            fnDrawCallback: onDatagridDraw,
        };

        dataT = dtPicturesList = $('#dtPicturesList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        $(".datepicker-init").datepicker(datepickerOptions);
        mix(window, {
            deletePicture: deletePicture,
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            callbackAddSellerPictures: onAddPciture,
            callbackEditSellerPictures: onEditPicture,
            callbackAddPicturesCategory: onCategoriesUpdate,
            callbackEditPicturesCategory: onCategoriesUpdate,
        });
    });
</script>
