<div class="wr-modal-b">
   	<form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-450">
			<div class="row">
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Language name</label>
					<input type="text" class="form-control" name="lang_name" placeholder="e.g. English" value="<?php echo $tlanguage['lang_name'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Language original name</label>
					<input type="text" class="form-control" name="lang_name_original" placeholder="e.g. English" value="<?php echo $tlanguage['lang_name_original'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">ISO letter</label>
					<input type="text" class="form-control" name="lang_iso2" placeholder="e.g. en" value="<?php echo $tlanguage['lang_iso2'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Lnaguage specification</label>
					<input type="text" class="form-control" name="lang_spec" placeholder="e.g. en_US" value="<?php echo $tlanguage['lang_spec'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Google translate language abbreviation</label>
					<input type="text" class="form-control" name="lang_google_abbr" placeholder="e.g. en" value="<?php echo $tlanguage['lang_google_abbr'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Icon (Country name)</label>
					<input type="text" class="form-control" name="lang_icon" placeholder="e.g. United States of America" value="<?php echo $tlanguage['lang_icon'];?>">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Translation type</label>
                    <select name="lang_url_type" class="form-control">
                        <option value="">Select translation type</option>
                        <option value="google_hash" <?php echo selected('google_hash', $tlanguage['lang_url_type']);?>>Google translate</option>
                        <option value="get_variable" <?php echo selected('get_variable', $tlanguage['lang_url_type']);?>>Use GET variable</option>
                        <option value="domain" <?php echo selected('domain', $tlanguage['lang_url_type']);?>>Domain</option>
                    </select>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="h-30 lh-30 pull-left">
                <input class="vam mmt-2" type="checkbox" name="lang_active" <?php echo checked($tlanguage['lang_active'], 1);?>>
                Active
            </label>
            <input type="hidden" name="id_lang" value="<?php echo $tlanguage['id_lang'];?>">
            <button class="btn btn-success pull-right call-function" data-callback="translation_add_language" type="button">Submit</button>
        </div>
   </form>
</div>
<script>
	var translation_add_language = function(btn){
        var $this = $(btn);
        var $form = $this.closest('form');
        $.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/edit_language',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){
                    translations_lang_callback();
                    closeFancyBox();
                } else{
                    hideLoader($form);
                }
            }
        });
    }
</script>
