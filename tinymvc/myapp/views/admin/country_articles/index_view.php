<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">

var articlesFilters;
var dtArticles;
function delete_country_article_i18n(obj){
    var $this = $(obj);
    var country_article = $this.data('country_article');
    var lang_country_article = $this.data('lang');

    $.ajax({
    type: 'POST',
        url: '<?php echo __SITE_URL?>country_articles/ajax_articles_operation/delete_country_article_i18n',
        data: {country_article: country_article, lang:lang_country_article},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(resp){
            systemMessages( resp.message, 'message-' + resp.mess_type );
            if(resp.mess_type == 'success'){
                callbackManageArticles(resp);
            }
        }
    });
}

function callbackManageArticles(resp){
    dtArticles.fnDraw(false);
}

$(document).ready(function(){
	remove_article = function(obj){
		var $this = $(obj);
		var article = $this.data('article');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>country_articles/ajax_articles_operation/remove_article',
			data: {article: article},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){

				systemMessages( data.message, 'message-' + data.mess_type );
				dtArticles.fnDraw();

				if(data.mess_type == 'success'){ }
			}
		});
	}

	change_visible_article = function(obj){
		var $this = $(obj);
		var article = $this.data("article");

		$.ajax({
			type: "POST",
			context: $(this),
			url: '<?php echo __SITE_URL?>country_articles/ajax_articles_operation/change_visible_article',
			data: { article: article },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtArticles.fnDraw();

				if(resp.mess_type == 'success'){
					$this.toggleClass('ep-icon_invisible ep-icon_visible');
				}

			}
		});
	}

	dtArticles = $('#dtArticles').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL . 'country_articles/ajax_country_articles_administration';?>",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_country_article'], "mData": "dt_id_country_article" },
			{ "sClass": "w-100 tac", "aTargets": ['dt_country'], "mData": "dt_country" },
			{ "sClass": "w-60 tac", "aTargets": ['dt_type'], "mData": "dt_type" },
			{ "sClass": "w-150 tac", "aTargets": ['dt_meta_data'], "mData": "dt_meta_data", "bSortable": false },
			{ "sClass": "w-150 tac", "aTargets": ['dt_photo'], "mData": "dt_photo", "bSortable": false },
			{ "sClass": "", "aTargets": ['dt_text'], "mData": "dt_text", "bSortable": false },
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false  },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[1, "asc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!articlesFilters){
				articlesFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtArticles.fnDraw(); },
					onSet: function(callerObj, filterObj){

					},
					onDelete: function(filter){

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
					if(data.mess_type == 'error' || data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

			var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
			if( keywordsSearch !== '' )
				$("#dtArticles tbody *").highlight(keywordsSearch, "highlight");
		}
	});

	$('body').on('click', '.btn-article-more', function(e){
		e.preventDefault();
		var $thisBtn = $(this);
		var $textB = $thisBtn.closest('td').find('.hidden-b');
		$textB.toggleClass('h-50');

		($textB.hasClass('h-50'))?$thisBtn.attr('title','view more'):$thisBtn.attr('title','hide more');
		$thisBtn.toggleClass('ep-icon_arrows-down ep-icon_arrows-up');
	});

});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Country articles</span>
			<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" data-table="dtArticles" href="<?php echo __SITE_URL . 'country_articles/popup_forms/add_article';?>" data-title="Add article"></a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/country_articles/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtArticles" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id_country_article">#</th>
					<th class="dt_country">Country</th>
					<th class="dt_type">Type</th>
					<th class="dt_photo">Photo</th>
					<th class="dt_text">Text</th>
					<th class="dt_meta_data">Meta data</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_tlangs">Translate</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
