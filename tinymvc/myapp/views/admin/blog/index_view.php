<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/select2-4-0-3/js/select2.min.js"></script>
<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">
var blogsFilters;
var dtBlogs;

$(document).ready(function(){

	change_visible_blog = function(obj){
		var $this = $(obj);
		var blog = $this.data("blog");

		$.ajax({
			type: "POST",
			context: $(this),
			url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/change_visible_blog',
			data: { blog: blog },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtBlogs.fnDraw();

			}
		});
	}

	change_moderated_blog = function(obj){
		var $this = $(obj);
		var blog = $this.data("blog");

		$.ajax({
			type: "POST",
			context: $(this),
			url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/change_moderated_blog',
			data: { blog: blog },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtBlogs.fnDraw();

			}
		});
	}

	remove_blog = function(obj){
		var $this = $(obj);//alert($this.data('column'));
		var blog = $this.data('blog');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/remove_blog',
			data: {blog: blog},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){

				systemMessages( data.message, 'message-' + data.mess_type );
				dtBlogs.fnDraw();
			}
		});
	}

	$('.menu-level3 a').on('click', function(e){
		e.preventDefault();
		var $parentLi = $(this).parent('li');
		if(!$parentLi.hasClass('active')){
			$parentLi.addClass('active').siblings('li').removeClass('active');
			dtBlogs.fnDraw();
		}
	});

	dtBlogs = $('#dtBlogs').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>blogs/ajax_blogs_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_blog'], "mData": "dt_id_blog" },
			{ "sClass": "w-100", "aTargets": ['dt_author'], "mData": "dt_author", "bSortable": false  },
			{ "sClass": "w-200 tac", "aTargets": ['dt_blog'], "mData": "dt_blog", "bSortable": false  },
			{ "sClass": "tac w-150", "aTargets": ['dt_photo'], "mData": "dt_photo", "bSortable": false },
			{ "sClass": "", "aTargets": ['dt_short_description'], "mData": "dt_short_description", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_date_created'], "mData": "dt_date_created"},
			{ "sClass": "w-120 tac", "aTargets": ['dt_publish_on'], "mData": "dt_publish_on"},
			{ "sClass": "w-60 tac", "aTargets": ['dt_country'], "mData": "dt_country"},
			{ "sClass": "w-60 tac vam", "aTargets": ['dt_lang'], "mData": "dt_lang", "bSortable": false },
			{ "sClass": "w-60 tac vam", "aTargets": ['dt_views'], "mData": "dt_views"},
			{ "sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!blogsFilters){
				blogsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtBlogs.fnDraw(); },
					onSet: function(callerObj, filterObj){

						if(filterObj.name == 'status'){
							$('.menu-level3').find('a[data-value="'+filterObj.value+'"]').parent('li').addClass('active').siblings('li').removeClass('active');
						}

					},
					onDelete: function(filter){

						if(filter.name == 'status'){
							var $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
							$li.addClass('active').siblings('li').removeClass('active');
						}
					}
				});
			}

			aoData = aoData.concat(blogsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);
					if(data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		}
	});

	idStartItemNew = <?php echo $last_blogs_id;?>;
	startCheckAdminNewItems('blogs/ajax_blogs_operation/check_new', idStartItemNew);
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">
			<span>Blogs</span>
			<a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" data-title="Add blog" title="Add blog" data-table="dtBlogs" href="<?php echo __SITE_URL;?>blogs/popup_blogs/add_blog"></a>
		</div>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All" href="#">All</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="new" data-value-text="New" href="#">New (<?php echo (!empty($counter['new']['counter']))?$counter['new']['counter']:0; ?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="moderated" data-value-text="Moderated" href="#">Moderated (<?php echo (!empty($counter['moderated']['counter']))?$counter['moderated']['counter']:'0';?>)</a></li>
		</ul>

		<?php tmvc::instance()->controller->view->display('admin/blog/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtBlogs" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_blog">#</th>
					 <th class="dt_photo">Photo</th>
					 <th class="dt_blog">Title</th>
					 <th class="dt_short_description">Short description</th>
					 <th class="dt_date_created">Created</th>
					 <th class="dt_publish_on">Publish on</th>
					 <th class="dt_author">Author</th>
                     <th class="dt_country">Country</th>
                     <th class="dt_lang">Lang</th>
                     <th class="dt_views">Views</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
