<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/select2-4-0-3/js/select2.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/clipboard-2-0-1/clipboard.min.js');?>"></script>
<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">
var articlesFilters;
var dtArticles;

$(document).ready(function(){
	remove_article = function(obj){
		var $this = $(obj);
        var article = $this.data('article');
        var url = '<?php echo __SITE_URL; ?>bloggers/ajax_delete_article';

        $.post(url, { id: article }, null, 'json').done(function (response) {
            systemMessages( response.message, response.mess_type );
            dtArticles.fnDraw();
        }).fail(function (error) {
            if(error.hasOwnProperty('responseJSON')) {
                systemMessages( error.responseJSON.message, (error.responseJSON.mess_type || 'error') );
            } else {
                systemMessages( error.message, 'error');
            }
        });
    }

	change_article_status = function(button){
        var self = $(button);
        var article = self.data('article');
        var status = self.data('status');
        var url = self.data('url');

        $.post(url, { id: article, status: status }, null, 'json').done(function (response) {
            systemMessages( response.message, response.mess_type );
            dtArticles.fnDraw();
        }).fail(function (error) {
            if(error.hasOwnProperty('responseJSON')) {
                systemMessages( error.responseJSON.message, (error.responseJSON.mess_type || 'error') );
            } else {
                systemMessages( error.message, 'error');
            }
        });
	}

	$('.menu-level3 a').on('click', function(e){
		e.preventDefault();
		var $parentLi = $(this).parent('li');
		if(!$parentLi.hasClass('active')){
			$parentLi.addClass('active').siblings('li').removeClass('active');
			dtArticles.fnDraw();
		}
	});

	dtArticles = $('#dtArticles').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>bloggers/ajax_bloggers_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_article'], "mData": "dt_id_article" },
			{ "sClass": "w-100", "aTargets": ['dt_author'], "mData": "dt_author", "bSortable": false  },
			{ "sClass": "w-200 tac", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false  },
			{ "sClass": "tac w-150", "aTargets": ['dt_photo'], "mData": "dt_photo", "bSortable": false },
			{ "sClass": "", "aTargets": ['dt_short_description'], "mData": "dt_short_description", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_date_created'], "mData": "dt_date_created"},
			{ "sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
			{ "sClass": "w-75 tac", "aTargets": ['dt_lang'], "mData": "dt_lang", "bSortable": false},
			{ "sClass": "w-75 tac", "aTargets": ['dt_status'], "mData": "dt_status", "bSortable": false},
			{ "sClass": "w-75 tac", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false},
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!articlesFilters){
				articlesFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtArticles.fnDraw(); },
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

			aoData = aoData.concat(articlesFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, data.mess_type);
					if(data.mess_type == 'info')
						systemMessages(data.message, data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers"
	});

    idStartItemNew = <?php echo $last_article_id;?>;
	startCheckAdminNewItems('bloggers/ajax_check_new_articles', idStartItemNew);
});
</script>

<div class="row">
	<div class="col-12">
		<div class="titlehdr">
			<span>Articles</span>
		</div>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active">
                <a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All" href="#">
                    All
                </a>
            </li>
			<li>
                <a class="dt_filter" data-name="status" data-title="Status" data-value="new" data-value-text="New" href="#">
                    New (<?php echo (!empty($counter['new'])) ? $counter['new'] : 0; ?>)
                </a>
            </li>
			<li>
                <a class="dt_filter" data-name="status" data-title="Status" data-value="approved" data-value-text="Approved" href="#">
                    Approved (<?php echo (!empty($counter['approved'])) ? $counter['approved'] : 0; ?>)
                </a>
            </li>
			<li>
                <a class="dt_filter" data-name="status" data-title="Status" data-value="declined" data-value-text="Declined" href="#">
                    Declined (<?php echo (!empty($counter['declined'])) ? $counter['declined'] : 0; ?>)
                </a>
            </li>
		</ul>

		<?php tmvc::instance()->controller->view->display('new/bloggers/admin/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtArticles" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_article">#</th>
                    <th class="dt_photo">Photo</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_short_description">Short description</th>
                    <th class="dt_date_created">Created</th>
                    <th class="dt_author">Author</th>
                    <th class="dt_lang">Language</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_actions">Activity</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
