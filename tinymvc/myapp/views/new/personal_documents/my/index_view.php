<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/personal_documents/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <div class="flex-display flex-ai--c flex-w--w pb-15">
            <h1 class="dashboard-line__ttl mr-10 pb-0">
                Verification documents
            </h1>

            <div class="elem-powered-by">
                <div class="elem-powered-by__txt">Secured by</div>
                <div class="elem-powered-by__name">EP Docs</div>
            </div>
        </div>

        <div class="dashboard-line__actions">
            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <table class="main-data-table" id="dtDocumentsList">
        <thead>
            <tr>
                <th class="document_dt">Document</th>
                <th class="description_dt">Description</th>
                <th class="created_dt">Created</th>
                <th class="updated_dt">Updated</th>
                <th class="expires_dt">Expires</th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<script src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<?php views()->display('new/personal_documents/my/common/scripts_view'); ?>
<script>
    $(function() {
        var myFilters;
        var dtDocumentsList;

        var dropPathParams = function (pathname, keys, skip) {
            var eachSlice = function (array, size) {
                var length = array.length;
                if (!length || size < 1) {
                    return [];
                }
                var index = 0;
                var resIndex = 0;
                var result = new Array(Math.ceil(length / size));
                while (index < length) {
                    result[resIndex++] = array.slice(index, (index += size));
                }

                return result;
            };
            var fromPairs = function (pairs) {
                var length = null !== pairs ? pairs.length : 0;
                if (pairs == null || !length) {
                    return {};
                }

                return pairs.reduce(function(accumulator, value) {
                    accumulator[value[0]] = value[1];

                    return accumulator;
                }, {});
            };
            var toPairs = function (props) {
                return Object.keys(props).map(function(key) {
                    return [key, props[key]];
                });
            };
            var flatten = function (array) {
                return array.reduce(function(accumulator, value) {
                    return accumulator.concat(value);
                }, []);
            }

            var parts = fromPairs(eachSlice(pathname.split('/').filter(function(f) { return f }).slice(skip || 0), 2));
            (Array.isArray(keys) ? keys : [keys]).forEach(function(key) {
                if (parts[key]) {
                    delete parts[key];
                }
            });

            return flatten(toPairs(parts)).join('/');
        };
        var rewriteHystory = function (url, filter) {
            if(
                typeof window.history !== 'undefined' &&
                typeof window.history.pushState !== 'undefined'
            ){
                var value = null;
                var segments = {};
                var parts = location.pathname.split('/').filter(function(p) { return p; });
                for (var i = 0; i < parts.length; i = i + 2) {
                    var part = parts[i];
                    segments[part] = parts[i + 1] || null;
                }
                if(segments.hasOwnProperty('group')) {
                    value = segments.group || null;
                }

                history.replaceState({ filter: { name: filter.name, value: value } }, $('title').text(), location.href);
                history.pushState({ filter: { name: filter.name, value: null } }, $('title').text(), url);
            } else {
                window.location.href = url;
            }
        };
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
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: __group_site_url + 'personal_documents/ajax_operation/documents',
            aoColumnDefs: [
                { sClass: "w-350",        aTargets: ['document_dt'],    mData: "document",    bSortable: false },
                { sClass: "dn-xl",        aTargets: ['description_dt'], mData: "description", bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],     mData: "created_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],     mData: "updated_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['expires_dt'],     mData: "expires_at",  bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],     mData: "actions",     bSortable: false },
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
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters
        });
    });
</script>
