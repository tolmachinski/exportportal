<div class="wr-modal-b">
   	<form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-450">
			<div class="row">
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Language name</label>
					<input type="text" class="form-control" name="lang_name" placeholder="e.g. English">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Language original name</label>
					<input type="text" class="form-control" name="lang_name_original" placeholder="e.g. English">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">ISO letter</label>
					<input type="text" class="form-control" name="lang_iso2" placeholder="e.g. en">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Lnaguage specification</label>
					<input type="text" class="form-control" name="lang_spec" placeholder="e.g. en_US">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Google translate language abbreviation</label>
					<input type="text" class="form-control" name="lang_google_abbr" placeholder="e.g. en">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Icon (Country name)</label>
					<input type="text" class="form-control" name="lang_icon" placeholder="e.g. United States of America">
				</div>
				<div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Translation type</label>
                    <select name="lang_url_type" class="form-control">
                        <option value="">Select translation type</option>
                        <option value="google_hash">Google translate</option>
                        <option value="get_variable">Use GET variable</option>
                        <option value="domain">Domain</option>
                    </select>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="h-30 lh-30 pull-left">
                <input class="vam mmt-2" type="checkbox" name="lang_default">
                Use as default
            </label>
            <label class="h-30 lh-30 pull-left ml-10">
                <input class="vam mmt-2" type="checkbox" name="lang_active">
                Active
            </label>
            <button class="btn btn-success pull-right call-function" data-callback="translation_add_language" type="button">Submit</button>
        </div>
   </form>
</div>
<script>
	var translation_add_language = function(btn){
        var $this = $(btn);
        var $form = $this.closest('form');
        $.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/add_language',
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
