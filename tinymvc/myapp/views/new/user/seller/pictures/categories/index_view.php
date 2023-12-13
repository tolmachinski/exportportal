
<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/user/seller/pictures/categories/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("seller_pictures_categories_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-categories__dashboard_add-category-btn'); ?>
                data-fancybox-href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/add_category"
                data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                title="<?php echo translate("seller_pictures_categories_dashboard_add_category_button_title", null, true); ?>">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add category</span>
            </a>

            <a class="btn btn-light"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-categories__dashboard_pictures-btn'); ?>
                href="<?php echo __SITE_URL;?>seller_pictures/my"
                title="<?php echo translate("seller_pictures_categories_dashboard_pictures_button_title", null, true); ?>">
                <?php echo translate("seller_pictures_categories_dashboard_pictures_button_text"); ?>
            </a>

            <a class="btn btn-dark btn-filter fancybox btn-counter"
                <?php echo addQaUniqueIdentifier('page__seller-pictures-categories__dashboard_filter-btn'); ?>
                href="#dtfilter-hidden"
                data-mw="740"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <table class="main-data-table" id="dtCategoriesList">
        <thead>
            <tr>
                <th class="category_dt"><?php echo translate("seller_pictures_categories_dashboard_dt_column_category_text"); ?></th>
                <th class="created_dt"><?php echo translate("seller_pictures_categories_dashboard_dt_column_create_date_text"); ?></th>
                <th class="updated_dt"><?php echo translate("seller_pictures_categories_dashboard_dt_column_updated_date_text"); ?></th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="application/javascript">
    $(function() {
        var myFilters;
        var dtCategoriesList;

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
        var deleteCategory = function(caller) {
            var button = $(caller);
            var category = button.data('category') || null;
            var url = __site_url + 'seller_pictures/ajax_pictures_operation/delete_category';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                dtCategoriesList.fnDraw();
            }

            if(null !== category) {
                $.post(url, { category: category }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var onAddCategory = function (response) {
            dtCategoriesList.fnDraw();
        };
        var onEditCategory = function (response) {
            dtCategoriesList.fnDraw();
        };
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
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
            sAjaxSource: location.origin + '/seller_pictures/ajax_pictures_categories_dt',
            aoColumnDefs: [
                { sClass: "",             aTargets: ['category_dt'], mData: "category",    bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],  mData: "created_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],  mData: "updated_at",  bSortable: true  },
                { sClass: "w-40 tac vam dt-actions", aTargets: ['actions_dt'],  mData: "actions",     bSortable: false }
            ],
            sorting: [[0, 'asc']],
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

        dataT = dtCategoriesList = $('#dtCategoriesList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        $(".datepicker-init").datepicker(datepickerOptions);

        mix(window, {
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            deleteCategory: deleteCategory,
            callbackAddPicturesCategory: onAddCategory,
            callbackEditPicturesCategory: onEditCategory,
        });
    });
</script>
