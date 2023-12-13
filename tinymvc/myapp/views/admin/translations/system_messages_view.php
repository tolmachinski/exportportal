<?php views('admin/file_upload_scripts'); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>System messages translations</span>
            <iframe src="" class="d-none-full" id="js-download-report"></iframe>
			<div class="dropdown pull-right">
				<button class="btn btn-default dropdown-toggle mb-5" type="button" id="actionsMenuTranslations" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					Actions
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="actionsMenuTranslations">
                    <?php if(have_right('manage_content')) { ?>
                        <li>
                            <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/add_file_translation_key_form?systmess=true" title="Add translation" data-title="Add translation" data-table="dtTranslationsList">
                                <i class="ep-icon ep-icon_plus-stroke fs-12 lh-12"></i>
                                Add translation
                            </a>
                        </li><li>
                            <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/add_file_translation_key_multiple_form" title="Add translations" data-title="Add translations" data-table="dtTranslationsList">
                                <i class="ep-icon ep-icon_plus-stroke fs-12 lh-12"></i>
                                Add multiple translations
                            </a>
                        </li>
                    <?php } ?>
					<li>
						<a class="call-function" data-callback="translation_files_db_to_files" href="#" title="Translations to Files" data-title="Translations to Files" data-table="dtTranslationsList">
							<i class="ep-icon ep-icon_updates fs-12 lh-12"></i>
							Update files
						</a>
					</li>
                    <li>
                        <a class="call-function" data-callback="export_excel" title="Export excel" data-title="Export excel">
                            <i class="ep-icon ep-icon_download fs-12 lh-12"></i>
                            Export excel
                        </a>
                    </li>
                    <li>
                        <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/create_xls_form" title="Get file to translate" data-title="Get file to translate" data-table="dtTranslationsList">
                            <i class="ep-icon ep-icon_download fs-12 lh-12"></i>
                            Get file to translate
                        </a>
                    </li>
                    <?php if(have_right('manage_content')) { ?>
                        <li>
                            <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/upload_translate_form" title="Upload translations file" data-title="Upload translations file" data-table="dtTranslationsList">
                                <i class="ep-icon ep-icon_upload fs-12 lh-12"></i>
                                Upload file
                            </a>
                        </li>
                        <li>
                            <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/upload_country_translation_form" title="Upload country translations file" data-title="Upload country translations file" data-table="dtTranslationsList">
                                <i class="ep-icon ep-icon_upload fs-12 lh-12"></i>
                                Upload country translation
                            </a>
                        </li>
                        <li>
                            <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>translations/popup_forms/upload_industry_translation_form" title="Upload industry translations file" data-title="Upload industry translations file" data-table="dtTranslationsList">
                                <i class="ep-icon ep-icon_upload fs-12 lh-12"></i>
                                Upload industry translation
                            </a>
                        </li>
                        <li>
                            <a class="confirm-dialog"
                                title="Search translation keys"
                                data-callback="parseTranslationKeys"
                                data-title="Search translation keys"
                                data-message="Do you really want to search keys in files? This would replace all custom entries."
                                data-url="<?php echo __SITE_URL;?>translations/ajax_operations/parse_files">
                            <i class="ep-icon ep-icon_gears fs-12 lh-12"></i>
                                Search keys
                            </a>
                        </li>
                    <?php } ?>
				</ul>
			</div>
		</div>

		<?php views('admin/translations/system_messages_filter_panel_view'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtTranslationsList" class="data table-bordered table-striped w-100pr">
			<thead>
				<tr>
					<th class="dt_id">#</th>
					<th class="dt_key">Key</th>
                    <th class="dt_default_value">Default text, EN</th>
                    <?php if(have_right('manage_content')) { ?>
                        <th class="dt_filename">File</th>
                    <?php } ?>
					<th class="dt_modules">Modules</th>
					<th class="dt_pages">Pages</th>
					<th class="dt_translations">Translations</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
	</div>
</div>
<?php if(have_right('manage_content')) { ?>
    <script type="application/javascript">
        var translation_key_delete = function(btn){
            var $this = $(btn);
            var id_key = $this.data('key');
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>translations/ajax_operations/translation_key_delete',
                dataType: 'JSON',
                data: {id_key:id_key},
                beforeSend: function(){
                    $this.addClass('disabled');
                },
                success: function(resp){
                    $this.removeClass('disabled');
                    systemMessages( resp.message, 'message-' + resp.mess_type );
                    dtTranslationsList.fnDraw(false);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    $this.removeClass('disabled');
                    systemMessages( 'Error. Please try again later.', 'message-error' );
                    jqXHR.abort();
                }
            });
            return false;
        };
        var parseTranslationKeys = function (button) {
            var self = $(button);
            var url = self.data('url') || self.attr('href');
            var onRequestSuccess = function (response) {
                systemMessages( response.message, 'message-' + response.mess_type );
                dtTranslationsList.fnDraw();
            };

            $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var translationKeyLocationUpdate = function (button) {
            var self = $(button);
            var url = self.data('url') || null;
            var onRequestSuccess = function (response) {
                systemMessages( response.message, 'message-' + response.mess_type );
                dtTranslationsList.fnDraw();
            };
            if(null === url) {
                return;
            }

            $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
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
        };
        var switchReviewedStatus = function(btn){
            var $this = $(btn);
            var url = $this.data('url');
            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'JSON',
                beforeSend: function(){
                    $this.addClass('disabled');
                },
                success: function(resp){
                    $this.removeClass('disabled');
                    systemMessages( resp.message, 'message-' + resp.mess_type );
                    dtTranslationsList.fnDraw(false);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    $this.removeClass('disabled');
                    systemMessages( 'Error. Please try again later.', 'message-error' );
                    jqXHR.abort();
                }
            });
            return false;
        };
    </script>
<?php } ?>
<script type="application/javascript">
	var dtTranslationsList;
	var dtTranslationsFilters;
    var translation_files_db_to_files = function(btn){
		var $this = $(btn);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/translation_files_db_to_files',
            dataType: 'JSON',
			beforeSend: function(){
				$this.addClass('disabled');
			},
            success: function(resp){
				console.log(resp);
				$this.removeClass('disabled');
                systemMessages( resp.message, 'message-' + resp.mess_type );
            },
            error: function(jqXHR, textStatus, errorThrown){
				$this.removeClass('disabled');
                systemMessages( 'Error. Please try again later.', 'message-error' );
                jqXHR.abort();
            }
        });
        return false;
	};
    var translations_files_callback = function(){
		dtTranslationsList.fnDraw(false);
	};

	$(function(){
		dtTranslationsList = $('#dtTranslationsList').dataTable( {
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL . 'translations/ajax_operations/system_messages_dt';?>",
			"sServerMethod": "POST",
			"iDisplayLength": 100,
			"aoColumnDefs": [
				{"sClass": "w-50", "aTargets": ['dt_id'], "mData": "dt_id" , 'bSortable': false},
				{"sClass": "mw-150 mw-340", "aTargets": ['dt_key'], "mData": "dt_key" , 'bSortable': false},
				{"sClass": "mnw-200", "aTargets": ['dt_default_value'], "mData": "dt_default_value" , 'bSortable': false},
				{"sClass": "w-200 tac", "aTargets": ['dt_filename'], "mData": "dt_filename" , 'bSortable': false},
				{"sClass": "w-200 tac vam", "aTargets": ['dt_modules'], "mData": "dt_modules" , 'bSortable': false},
				{"sClass": "w-200 tac vam", "aTargets": ['dt_pages'], "mData": "dt_pages" , 'bSortable': false},
				{"sClass": "w-200 tac vam", "aTargets": ['dt_translations'], "mData": "dt_translations" , 'bSortable': false},
				{"sClass": "w-160 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
			],
			"sorting" : [],
			"sPaginationType": "full_numbers",
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!dtTranslationsFilters){
					dtTranslationsFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						'debug':true,
						callBack: function(){
							dtTranslationsList.fnDraw();
						},
						onDelete: function(filter){},
						onSet: function(callerObj, filterObj) {},
						onReset: function(){}
					});
				}

				aoData = aoData.concat(dtTranslationsFilters.getDTFilter());
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
			"fnDrawCallback": function(oSettings) {}
		});
	});

    var export_excel = function(){
        var exportUrl = "<?php echo  __SITE_URL?>translations/export_syst_mess";
        $('#js-download-report').attr('src', exportUrl);
    }
</script>
