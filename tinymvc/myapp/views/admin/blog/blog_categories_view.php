<script type="text/javascript">
var dtBlogs, groupsFilters;
$(document).ready(function(){

	dtBlogs = $('#dtBlogs').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>blogs/ajax_blogs_category_administration",
		"sServerMethod": "POST",
		"iDisplayLength": 50,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_category'], "mData": "dt_id_category" },
			{ "sClass": "vam", "aTargets": ['dt_name'], "mData": "dt_name" },
			{ "sClass": "w-150 tac", "aTargets": ['dt_updated_at'], "mData": "dt_updated_at" },
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
			{ "sClass": "w-100 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtBlogs.fnDraw(); },
                    onSet: function (callerObj, filterObj) {},
                    onDelete: function (filter) {}
                });
            }

            aoData = aoData.concat(groupsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info') {
						systemMessages(data.message, 'message-' + data.mess_type);
                        }

					fnCallback(data, textStatus, jqXHR);
				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});

	//remove category blog
	remove_category_blog = function(obj){
		var $this = $(obj);//alert($this.data('column'));
		var category = $this.data('category');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/remove_category_blog',
			data: {category: category},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){

				systemMessages( data.message, 'message-' + data.mess_type );
				dtBlogs.fnDraw();

				if(data.mess_type == 'success'){ }
			}
		});
	}

	//remove category blog
	remove_category_blog_i18n = function(obj){
		var $this = $(obj);
		var category = $this.data('category');
		var lang = $this.data('lang');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/remove_category_blog_i18n',
			data: {category: category,lang:lang},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtBlogs.fnDraw();
				}
			}
		});
	}
});
</script>

<div class="row">
	<div class="col-xs-12">
        <div class="titlehdr">
            <span>Blog categories</span>
                <?php if(have_right('manage_blogs')) { ?>
                    <a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" data-table="dtBlogs" href="<?php echo __SITE_URL;?>blogs/popup_blogs/add_blog_category/" data-title="Add blog category"></a>
                <?php } ?>
            </div>

		<?php tmvc::instance()->controller->view->display('admin/blog/blog_categories_filter_panel'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtBlogs" cellspacing="0" cellpadding="0" >
			<thead>
			<tr>
				<th class="dt_id_category">#</th>
				<th class="dt_name">Name</th>
				<th class="dt_updated_at">EN updated at</th>
				<th class="dt_tlangs_list">Translated in</th>
				<th class="dt_actions">Actions</th>
			</tr>
			</thead>
			<tbody></tbody>
		</table>
	 </div>
</div>
