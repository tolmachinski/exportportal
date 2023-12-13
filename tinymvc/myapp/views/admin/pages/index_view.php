<script src="<?php echo __SITE_URL . 'public/plug_admin/select2-4-0-3/js/select2.min.js';?>"></script>
<script>
	var dtPages;
    var myFilters;
    var removePage = function (button){
        var self = $(button);
        var page = self.data('page');
        var url = self.attr('href');
        var onSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            dtPages.fnDraw();
        };

        $.post(url, { id: page }, null, 'json').done(onSuccess).fail(onRequestError);
    };

    function reverseLangConfig(obj){
        var element = $(obj);
        var id_page = element.data('id-page');
        var page_column = element.data('page-column');

        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: "<?php echo __SITE_URL . 'pages/ajax_operations/change_language_config/';?>" + id_page,
            data: { column : page_column},
            success: function(data, textStatus, jqXHR) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if (data.mess_type == 'success') {
                    dtPages.fnDraw(false);
                }
            }
        });
    }

    function reverseTranslationStatus(obj){
        var element = $(obj);
        var id_page = element.data('id-page');
        var current_status = element.data('current-status');
        var ajax_url = element.attr('href');

        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: ajax_url,
            data: { current_page_status : current_status},
            success: function(data, textStatus, jqXHR) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if (data.mess_type == 'success') {
                    dtPages.fnDraw(false);
                }
            }
        });
    }

    var validateReInit = function (formSelector, options){
        options = options || {};
            formSelector = formSelector || ".validateModal";

        var form = $(formSelector);
        var callback = form.data().jqv.onValidationComplete || null
        form.validationEngine('detach').validationEngine('attach',  $.extend(true, {}, {
            promptPosition : "topLeft",
            autoPositionUpdate : true,
            onValidationComplete: callback
        }, options));
    }

	$(document).ready(function(){
		dtPages = $('#dtPages').dataTable({
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: "<?php echo __SITE_URL . 'pages/administration_dt';?>",
			aoColumnDefs: [
				{sClass: "vam w-50 tac", aTargets: ['dt_id'], mData: "dt_id"},
				{sClass: "vam w-200", aTargets: ['dt_name'], mData: "dt_name"},
				{sClass: "vam", aTargets: ['dt_description'], mData: "dt_description", bSortable: false},
				{sClass: "vam w-200", aTargets: ['dt_controller'], mData: "dt_controller"},
				{sClass: "vam w-150", aTargets: ['dt_action'], mData: "dt_action"},
				{sClass: "w-150 tac vam", aTargets: ['dt_ready_for_translation'], mData: "dt_ready_for_translation", bSortable: false},
				{sClass: "w-200 tac vam", aTargets: ['dt_languages'], mData: "dt_languages", bSortable: false},
				{sClass: "w-120 tac vam", aTargets: ['dt_created_at'], mData: "dt_created_at"},
				{sClass: "w-120 tac vam", aTargets: ['dt_updated_at'], mData: "dt_updated_at"},
				{sClass: "w-80 tar vam", aTargets: ['dt_actions'], mData: "dt_actions", bSortable: false}
			],
			fnServerData: function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtPages.fnDraw();
                        }
                    });
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax({
                    dataType: 'json',
                    type: "POST",
                    url: sSource,
                    data: aoData,
                    success: function(data, textStatus, jqXHR) {
                    if (data.mess_type == 'error')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);

                    }
                });
			},
			sorting : [[0,'desc']],
			sPaginationType: "full_numbers",
			fnDrawCallback: function(oSettings) {}
		});
	});
</script>
<!-- <div class="container-fluid content-dashboard"> -->
<div class="content-dashboard">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span>Pages</span>
				<a href="<?php echo __SITE_URL . 'pages/popup_forms/add';?>"
                    class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
                    data-table="dtPages"
                    data-title="Add page">
                </a>
			</div>

			<?php views()->display('admin/pages/filter_panel_view'); ?>
			<div class="wr-filter-list mt-10 clearfix"></div>

			<table id="dtPages" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_name">Title</th>
                    <th class="dt_description">Description</th>
                    <th class="dt_controller">Controller</th>
                    <th class="dt_action">Action</th>
                    <th class="dt_ready_for_translation">Ready for translation</th>
                    <th class="dt_languages">Languages</th>
                    <th class="dt_created_at">Created at</th>
                    <th class="dt_updated_at">Updated at</th>
                    <th class="dt_actions">Actions</th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>
