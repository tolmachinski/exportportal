<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/user/seller/library/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("seller_library_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                data-fancybox-href="<?php echo __SITE_URL;?>seller_library/popup_forms/add_document"
                data-title="<?php echo translate("seller_library_dashboard_add_document_modal_title", null, true); ?>"
                title="<?php echo translate("seller_library_dashboard_add_document_button_title", null, true); ?>"
                <?php echo addQaUniqueIdentifier('seller-library-my__actions_add-document-btn'); ?>
            >
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add document</span>
            </a>

            <a class="btn btn-light"
                href="<?php echo __SITE_URL;?>seller_library/categories"
                title="<?php echo translate("seller_library_dashboard_categories_button_title", null, true); ?>"
                target="_blank"
                <?php echo addQaUniqueIdentifier('seller-library-my__actions_categories-btn'); ?>
            >
                <?php echo translate("seller_library_dashboard_categories_button_text"); ?>
            </a>

            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                data-fancybox-href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/77"
                data-title="<?php echo translate("seller_library_dashboard_documentation_modal_title", null, true); ?>"
                title="<?php echo translate("seller_library_dashboard_documentation_button_title", null, true); ?>"
                target="_blank">
                <?php echo translate("seller_library_dashboard_documentation_button_text"); ?>
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>"
                <?php echo addQaUniqueIdentifier('seller-library-my__actions_filter-btn'); ?>
            >
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <p>Here you can upload documents like certificates, awards, contract templates and other. Users can access your documents from your Library Company Page.</p>

    <table class="main-data-table" id="dtDocumentsList" <?php echo addQaUniqueIdentifier('seller-library-my__table'); ?>>
        <thead>
            <tr>
                <th class="document_dt"><?php echo translate("seller_library_dashboard_dt_column_document_text"); ?></th>
                <th class="description_dt"><?php echo translate("seller_library_dashboard_dt_column_description_text"); ?></th>
                <th class="created_dt"><?php echo translate("seller_library_dashboard_dt_column_create_date_text"); ?></th>
                <th class="updated_dt"><?php echo translate("seller_library_dashboard_dt_column_update_date_text"); ?></th>
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
        var dtDocumentsList;

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
            var url = __site_url + 'seller_library/ajax_library_operation/get_categories';
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
        var onAddDocument = function (response){
            dtDocumentsList.fnDraw();
        };
        var onEditDocument = function (response){
            dtDocumentsList.fnDraw(false);
        };
        var deleteDocument = function(caller) {
            var button = $(caller);
            var document = button.data('document') || null;
            var url = __site_url + 'seller_library/ajax_library_operation/delete_document';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                dtDocumentsList.fnDraw();
            }

            if(null !== document) {
                $.post(url, { document: document }, null, 'json').done(onRequestSuccess).fail(onRequestError);
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
            sAjaxSource: location.origin + '/seller_library/ajax_library_list_dt',
            aoColumnDefs: [
                { sClass: "w-450",        aTargets: ['document_dt'],    mData: "document",    bSortable: true  },
                { sClass: "dn-xl",        aTargets: ['description_dt'], mData: "description", bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],     mData: "created_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],     mData: "updated_at",  bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],     mData: "actions",     bSortable: false }
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

        dataT = dtDocumentsList = $('#dtDocumentsList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        $(".datepicker-init").datepicker(datepickerOptions);
        mix(window, {
            deleteDocument: deleteDocument,
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            callbackAddLibraryDocument: onAddDocument,
            callbackEditLibraryDocument: onEditDocument,
            callbackAddLibraryCategory: onCategoriesUpdate,
            callbackEditLibraryCategory: onCategoriesUpdate,
        });
    });
</script>
