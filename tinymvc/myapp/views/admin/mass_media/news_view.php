<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
var dtNewsList;

$(document).ready(function(){

	dtNewsList = $('#dtNewsList').dataTable({
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL . 'mass_media/ajax_news_administration';?>",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_news'], "mData": "dt_id_news", "bSortable": false },
			{ "sClass": "w-200", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false },
			{ "sClass": "", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_date'], "mData": "dt_date", "bSortable": true },
			{ "sClass": "w-120 tac", "aTargets": ['dt_type'], "mData": "dt_type", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_img'], "mData": "dt_img", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_visible'], "mData": "dt_visible", "bSortable": false },
			{ "sClass": "w-40 tac", "aTargets": ['dt_lang'], "mData": "dt_lang", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting" : [[3,'desc']],
		"fnServerParams": function(aoData) { },
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function(oSettings) { }
	});

	newsRemove = function(obj){
		var $this = $(obj);
		var news = $this.data('news');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>mass_media/ajax_news_operation/delete_news',
			data: { news : news},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtNewsList.fnDraw();
				}
			}
		});
	}

	$('body').on('click', '#get-rss', function(e){
		e.preventDefault()
		var ttl = $("[name='title']").val();
		var url = $("[name='link_rss']").val();

		$.ajax({
			type: 'POST',
			async: false,
			url: "mass_media/ajax_news_operation/get_rss",
			data: {url: url, title: ttl},
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$("[name='date']").val(resp.news.date);
					$("[name='description']").val(resp.news.description);
					$("[name='link']").val(resp.news.link);

					if(resp.news.img != undefined){
						$("[name='img_rss']").val(resp.news.img);
						$(".wr-img-rss").html('<img class="mt-5 mw-75 mh-50 manually-hide" src="'+resp.news.img+'">');
					}
				}
			}
		});
	});

	$('body').on('change', '#type-news', function(){
		hideInput($(this).val());
	});
})
</script>

<div class="row">
    <div class="col-xs-12">
    	<div class="titlehdr h-30">
            <span>News list</span>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL . 'mass_media/news_popups/add_news';?>" data-table="dtNewsList" data-title="Add news" title="Add news"></a>
        </div>

           <table id="dtNewsList" class="data table-striped table-bordered w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id_news">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_description">Description</th>
                    <th class="dt_date">Date</th>
                    <th class="dt_type">Type</th>
                    <th class="dt_img">Image</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_visible">Visible</th>
                    <th class="dt_lang">Lang</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
