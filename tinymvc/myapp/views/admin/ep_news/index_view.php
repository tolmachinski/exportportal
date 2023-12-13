<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">

var blogsFilters;
var dtEpNews;

function delete_ep_news_i18n(obj){
    var $this = $(obj);
    var id_ep_news = $this.data('ep-news-id');
    var ep_news_i18n_lang = $this.data('ep-news-i18n-lang');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>ep_news/ajax_ep_news_operations/delete_ep_news_i18n',
        data: {id_ep_news: id_ep_news, ep_news_i18n_lang: ep_news_i18n_lang},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(resp){
            systemMessages( resp.message, 'message-' + resp.mess_type );
            if(resp.mess_type == 'success'){
                dtEpNews.fnDraw(false);
            }
        }
    });
}

$(document).ready(function(){
	remove_ep_news = function(obj){
		var $this = $(obj);//alert($this.data('column'));
		var ep_news = $this.data('id');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>ep_news/ajax_ep_news_operations/remove_ep_news',
			data: {id: ep_news},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
				dtEpNews.fnDraw();

				if(data.mess_type == 'success'){ }
			}
		});
	}

	change_visible_ep_news = function(obj){
		var $this = $(obj);
		var ep_news = $this.data("id");

		$.ajax({
			type: "POST",
			url: '<?php echo __SITE_URL?>ep_news/ajax_ep_news_operations/change_visible_ep_news',
			data: { id: ep_news },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtEpNews.fnDraw();

				if(resp.mess_type == 'success'){
					$this.toggleClass('ep-icon_invisible ep-icon_visible');
				}

			}
		});
	}

	dtEpNews = $('#dtEpNews').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>ep_news/ajax_ep_news_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "w-140 tac", "aTargets": ['dt_main_image'], "mData": "dt_main_image", "bSortable": false  },
			{ "sClass": "tac", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "w-60 tac", "aTargets": ['dt_content'], "mData": "dt_content", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false },
			{ "sClass": "w-200 tac", "aTargets": ['dt_date_time'], "mData": "dt_date_time"},
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false  },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!blogsFilters){
				blogsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtEpNews.fnDraw(); },
					onSet: function(callerObj, filterObj){
					},
					onDelete: function(filter){

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
				$("#dtEpNews tbody *").highlight(keywordsSearch, "highlight");
		}
	});

	function fnFormatDetails(nTr) {
	    var aData = dtEpNews.fnGetData(nTr);

	    var sOut = '<div class="dt-details"><table class="dt-details__table">';
	    sOut += '<tr><td class="w-200">Description:</td><td>' + aData['dt_content'] + '</td></tr>'+
				'<tr><td>Content: </td><td>' + aData['dt_description'] + '</td></tr>';

	    sOut += '</table></div>';
	    return sOut;
	}

	$('body').on('click', 'a[rel=view_details]', function() {
		var $thisBtn = $(this);
		var nTr = $thisBtn.parents('tr')[0];

		if (dtEpNews.fnIsOpen(nTr))
			dtEpNews.fnClose(nTr);
		else
			dtEpNews.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>EP news</span>
			<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="ep_news/popup_forms/add_ep_news" data-title="Add EP news" data-table="dtEpNews"></a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/ep_news/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtEpNews" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_main_image">Main image</th>
					<th class="dt_title">Title</th>
					<th class="dt_date_time">Date</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_tlangs">Translate</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
