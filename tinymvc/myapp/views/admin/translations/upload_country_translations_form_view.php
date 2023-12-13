<div class="wr-modal-b">
   	<form class="modal-b__form validateModal" id="country_translation_form" data-callback="country_translations_callback">
		<div class="modal-b__content pb-0 w-750">
			<div class="row">
                <div class="col-xs-12">
                    <label class="modal-b__label">Select language</label>
                    <select name="lang" class="form-control validate[required]">
                        <option value="">Select language</option>
                        <?php foreach($languages as $language){?>
                            <option value="<?php echo $language['lang_iso2'];?>"><?php echo $language['lang_name'];?></option>
                        <?php }?>
                    </select>
                </div>
			</div>
			<div class="row">
				<div class="col-xs-12 mt-15">
					<div class="info-alert-b">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; You can not upload more than 1 file.</div>
						<div> &bull; File available formats (xls, xlsx).</div>
					</div>
				</div>
				<div class="col-xs-12 mt-15">
                    <input type="file" name="translations_file" class="form-control form-control-file validate[required]">
                    <span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
                        <img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...
                    </span>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button type="submit" class="btn btn-success pull-left">Save</button>
            <button class="btn btn-default pull-right call-function" data-callback="closeFancyBox" type="button">Close</button>
        </div>
   </form>
</div>
<script>
country_translations_callback = function() {
    console.log("country_translations_callback");
    var form = document.getElementById('country_translation_form');

    var file = document.querySelector('[name="translations_file"]');
    var lang = document.querySelector('[name="lang"]');

    var formdata = new FormData();
    formdata.append('translations_file', file.files[0]);
    formdata.append('lang', lang.value);

    var xhr = new XMLHttpRequest();

    xhr.onload = function() {
        $('.fileinput-loader-btn').fadeOut();
        var response = JSON.parse(xhr.response);

        systemMessages( response.message, 'message-' + response.mess_type );
        if(response.mes_type == 'error') {
            return;
        }

        closeFancyBox();
    }

    xhr.open('POST', location.origin + '/translations/ajax_upload_country_translation/<?php echo $upload_folder;?>', true);
    xhr.setRequestHeader('X-REQUESTED-WITH', 'xmlhttprequest');

    $('.fileinput-loader-btn').fadeIn(); //before send
    xhr.send(formdata);
}
</script>
