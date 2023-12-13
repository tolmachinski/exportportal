
<?php views()->display('new/filter_panel_main_view', array('filter_panel' => 'new/blog/my/delete_29_06_2022_filter_panel_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("blog_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                href="<?php echo __SITE_URL;?>blogs/popup_blogs/add_user_blog"
                title="<?php echo translate("blog_dashboard_add_post_modal_title"); ?>"
                data-title="<?php echo translate("blog_dashboard_add_post_button_title"); ?>">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add blog</span>
            </a>

            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/blogs_doc?user_type=<?php echo strtolower(user_group_type());?>"
                title="<?php echo translate("blog_dashboard_documentation_button_title"); ?>"
                data-title="<?php echo translate("blog_dashboard_documentation_modal_title"); ?>"
                target="_blank">
                <?php echo translate("blog_dashboard_documentation_button_text"); ?>
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter"
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
		<span><?php echo translate('blogs_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dtPostList">
        <thead>
            <tr>
                <th class="post_dt"><?php echo translate("blog_dashboard_dt_column_post_text"); ?></th>
                <th class="description_dt"><?php echo translate("blog_dashboard_dt_column_description_text"); ?></th>
                <th class="created_dt"><?php echo translate("blog_dashboard_dt_column_create_date_text"); ?></th>
                <th class="country_dt"><?php echo translate("blog_dashboard_dt_column_country_text"); ?></th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php views()->display('new/file_upload_scripts'); ?>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="application/javascript">
    var myFilters;
    var dtPostList;
    var onSetFilters = function(caller, filter) {
        if(filter.name === 'start_from'){
            $("#start_to").datepicker("option", "minDate", $("#start_from").datepicker("getDate"));
        }
        if(filter.name === 'start_to'){
            $("#start_from").datepicker("option","maxDate", $("#start_to").datepicker("getDate"));
        }
    };
    var onDeleteFilters = function(filter) {
        if(filter.name === 'start_from'){
            $("#start_to").datepicker("option", "minDate", null);
        }
        if(filter.name === 'start_to'){
            $("#start_from").datepicker("option","maxDate", null);
        }
    };
    var onEditBlog = function (response) {
		dtPostList.fnDraw();
	};
	var onAddBlog = function () {
		dtPostList.fnDraw();
	};
	var onDeleteImage = function () {
		dtPostList.fnDraw();
	};
    var changeVisibility = function(caller) {
        var button = $(caller);
        var post = button.data('post') || null;
        var url = __site_url + '/blogs/ajax_blogs_operation/change_visible_blog';
        var onRequestSuccess = function(response) {
            systemMessages(response.message, response.mess_type);
            dtPostList.fnDraw();
        }
        if(null !== post) {
            $.post(url, { blog: post }, null, 'json').done(onRequestSuccess).fail(onRequestError);
        }
    };
    var deleteBlogPost = function(caller) {
        var button = $(caller);
        var post = button.data('post') || null;
        var url = __site_url + '/blogs/ajax_blogs_operation/remove_blog';
        var onRequestSuccess = function(response) {
            systemMessages(response.message, response.mess_type);
            dtPostList.fnDraw();
        }
        if(null !== post) {
            $.post(url, { blog: post }, null, 'json').done(onRequestSuccess).fail(onRequestError);
        }
    };

    $(function() {
        var onDatagridServerResponse = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            if(!myFilters){
                myFilters = initDtFilter();
            }

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json').done(onRequestSuccess);
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
            sAjaxSource: location.origin + '/blogs/ajax_blogs_my',
            aoColumnDefs: [
                { sClass: "w-300",          aTargets: ['post_dt'],        mData: "post",        bSortable: true  },
                { sClass: "dn-xl",          aTargets: ['description_dt'], mData: "description", bSortable: false },
                { sClass: "w-90 dn-lg",    aTargets: ['created_dt'],     mData: "created_at",  bSortable: true  },
                { sClass: "w-110 dn-lg",    aTargets: ['country_dt'],     mData: "country",     bSortable: false },
                { sClass: "w-40 tac vam",   aTargets: ['actions_dt'],     mData: "actions",     bSortable: false }
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
            fnServerData: onDatagridServerResponse,
            fnDrawCallback: onDatagridDraw,
        };

        dataT = dtPostList = $('#dtPostList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        $(".datepicker-init").datepicker(datepickerOptions);
    });
</script>
