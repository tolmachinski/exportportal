<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
	var dtTradeNews,
		tradeNewsFilters;

	var change_visible_news = function(obj){
		var $this = $(obj);
		var news = $this.data("news");

		$.ajax({
			type: "POST",
			context: $(this),
			url: '<?php echo __SITE_URL?>trade_news/ajax_news_operation/change_visible_news',
			data: { news: news },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtTradeNews.fnDraw();

			}
		});
	}

	var remove_news = function(obj){
		var $this = $(obj);
		var news = $this.data('news');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>trade_news/ajax_news_operation/remove_news',
			data: { news: news },
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){

				systemMessages( data.message, 'message-' + data.mess_type );
				dtTradeNews.fnDraw();

			}
		});
	}

	$(function(){
		dtTradeNews = $('#js-dt-trade-news').dataTable( {
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL?>trade_news/ajax_trade_news_administration",
			"sServerMethod": "POST",
			"aoColumnDefs": [
				{ "sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
				{ "sClass": "w-140 tac", "aTargets": ['dt_main_image'], "mData": "dt_main_image", "bSortable": false  },
				{ "sClass": "w-250", "aTargets": ['dt_title'], "mData": "dt_title" },
				{ "sClass": "", "aTargets": ['dt_short_description'], "mData": "dt_short_description", "bSortable": false },
				{ "sClass": "w-80 tac", "aTargets": ['dt_date'], "mData": "dt_date"},
				{ "sClass": "w-80 tac", "aTargets": ['dt_date_update'], "mData": "dt_date_update"},
				{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
			],
			"sorting": [[0, "desc"]],
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!tradeNewsFilters){
					tradeNewsFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						'debug':false,
						callBack: function(){ dtTradeNews.fnDraw(); },
						onSet: function(callerObj, filterObj){},
						onDelete: function(filter){}
					});
				}

				aoData = aoData.concat(tradeNewsFilters.getDTFilter());
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
				if( keywordsSearch !== '' ){
					$("#js-dt-trade-news tbody *").highlight(keywordsSearch, "highlight");
				}
			}
		});
	});
</script>

<div class="row">
	<div class="col-12">
		<div class="titlehdr h-30">
			<span>Trade News</span>

			<a
				class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
				href="<?php echo __SITE_URL;?>trade_news/popup_forms/add_news"
				data-title="Add Trade News"
				data-table="dtTradeNews"
			>
			</a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/trade_news/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table id="js-dt-trade-news" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_main_image">Main image</th>
					<th class="dt_title">Title</th>
					<th class="dt_short_description">Short description</th>
					<th class="dt_date">Date Created</th>
					<th class="dt_date_update">Date Updated</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
