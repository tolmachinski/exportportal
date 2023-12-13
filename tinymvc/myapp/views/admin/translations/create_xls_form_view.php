<div class="wr-modal-b">
   	<form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-750">
			<div class="row">
                <div class="col-xs-12">
                    <label class="modal-b__label" >Select page</label>
                    <select name="page" class="form-control">
                        <option>Select page</option>
                        <?php foreach ($pages as $page_id => $page_name) { ?>
                            <option value="<?php echo $page_id; ?>"><?php echo $page_name; ?></option>
                        <?php } ?>
                        <option value="0">Used on all pages</option>
                    </select>
                </div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Select file to translate</label>
					<select name="file_name" class="form-control">
                        <option value="">Select file to translate</option>
                        <?php foreach($files as $file){?>
                            <option value="<?php echo $file;?>" data-file-name="<?php echo pathinfo($file, PATHINFO_FILENAME);?>"><?php echo $file;?></option>
                        <?php }?>
                    </select>
                </div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Select language</label>
					<select name="translate_to" class="form-control">
                        <option value="">Select language</option>
                        <?php foreach($tlanguages as $tlanguage){?>
                            <option value="<?php echo $tlanguage['lang_iso2'];?>" <?php echo $tlanguage['lang_iso2'] == 'en' ? 'selected' : '';?>><?php echo $tlanguage['lang_name'];?></option>                        
                        <?php }?>
                    </select>
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Select records type</label>
					<select name="type_records" class="form-control">
                        <option value="full">All records</option>
                        <option value="translated">Translated records</option>
                        <option value="not_translated">Not translated records</option>
                    </select>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right call-function" data-callback="create_translation_xls_file" type="button">Get xls</button>
        </div>
        <iframe src="" id="download_translation_xls_file" class="display-n"></iframe>
   </form>
</div>
<script>
	var create_translation_xls_file = function(btn){
        var $this = $(btn);
        var $form = $this.closest('form');
        $.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/check_create_xls_single',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
                clearSystemMessages();
            },
            success: function(data){
                hideLoader($form);

                if(data.mess_type == 'success'){
                    $form.find('iframe#download_translation_xls_file').attr('src', data.url);                  
                } else{
                    systemMessages( data.message, 'message-' + data.mess_type );
                }
            }
        });
    }
</script>
