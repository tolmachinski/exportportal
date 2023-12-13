<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Site languages</span>
			<div class="pull-right btns-actions-all">
                <a class="btn btn-success pull-right fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>translations/popup_forms/add_language_form" title="Add language" data-title="Add language">
					<i class="ep-icon ep-icon_plus fs-12 lh-12"></i>
					Add language
				</a>
			</div>
		</div>

		<table id="dtLanguages" class="data table-bordered table-striped w-100pr">
			<thead>
				<tr>
					<th class="dt_id w-50">#</th>
					<th class="dt_lang_name">Name</th>
					<th class="dt_lang_iso2 w-100">ISO 2</th>
					<th class="dt_lang_google_abbr w-150">Google abbr</th>
					<th class="dt_lang_url_type w-150">Translation type</th>
					<th class="dt_lang_default w-100">Default</th>
					<th class="dt_active w-100">Active</th>
					<th class="dt_updated w-150">Updated</th>
					<th class="dt_actions w-100">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script>
    var dtLanguages;
	function translations_lang_callback(){
        dtLanguages.fnDraw(false);
	}

	var change_lang_active = function(btn){
		var $this = $(btn);
		var id_lang = $this.data('lang');
		$.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/change_language_active',
            type: 'POST',
            dataType: 'json',
            data: {id_lang:id_lang},
            beforeSend: function () {},
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    dtLanguages.fnDraw(false);
                }
            }
        });
	}
	
	$(document).ready(function(){
        dtLanguages = $('#dtLanguages').dataTable( {
            "sDom": 'rt<"bottom"i><"clear">',
			"pageLength": 250,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>translations/ajax_operations/languages_list_dt",
            "sServerMethod": "POST",
            "sorting": [],
            "aoColumnDefs": [
                {"sClass": "w-150 vam tac", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": false},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_lang_name'], "mData": "dt_lang_name"},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_lang_iso2'], "mData": "dt_lang_iso2", 'bSortable': false},
                {"sClass": "w-200 vat tac", "aTargets": ['dt_lang_google_abbr'], "mData": "dt_lang_google_abbr", 'bSortable': false},
                {"sClass": "w-100 vat tal", "aTargets": ['dt_lang_url_type'], "mData": "dt_lang_url_type"},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_lang_default'], "mData": "dt_lang_default"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_active'], "mData": "dt_active"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_updated'], "mData": "dt_updated"},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				$.ajax( {
					"dataType": 'JSON',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);
					}
				});
            },
            "fnDrawCallback": function( oSettings ) {}
        });
    });
</script>
