<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "mass_media/ajax_news_operation/<?php echo (isset($news) ? 'update' : 'create')?>_news",
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}

$(document).ready(function(){
	var url = 'mass_media/ajax_news_upload_photo/<?php echo $upload_folder;?>/<?php echo $news['id_news'];?>'

	$('#add_fileupload').fileupload({
		url: url,
		dataType: 'json',
		maxFileSize: <?php echo $fileupload_max_file_size?>,
		beforeSend: function (event, data) {
            var upload_files = data.files.length;
            var total_files = $('.fileupload-queue').find('.uploadify-queue-item').length;

            if(upload_files + total_files > 1){
                event.abort();
                systemMessages( 'You can not upload more than 1 image for one report.', 'error' );
            } else{
                $('.fileinput-loader-btn').fadeIn();
            }
		},
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				$.each(data.result.files, function (index, file) {
					var itemID = +(new Date());
					$('.fileupload-queue').append(templateFileUpload('img','item-medium',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'" alt="img">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="mass_media/ajax_news_delete_photo/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
				});
			} else{
				systemMessages( data.result.message, 'message-' + data.result.mess_type );
			}
			$('.fileinput-loader-btn').fadeOut();
		},
		processalways: function(e,data){
			if (data.files.error){
				systemMessages( data.files[0].error, 'message-error' );
			}
		}
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

	$( "#type-news option:selected" ).each(function() {
		hideInput($(this).val());
	});

	$( "form input[name=date]" ).datepicker({ dateFormat: 'dd-mm-yy' });
});

function hideInput(className){

	if(className == 'rss'){
		$('.for-manually, .for-site').hide();
		$('.rss-readonly').prop('readonly', true);
		$('form input[name=date]').datepicker("destroy");
	}else if(className == 'manually'){
		$('.for-rss, .for-site').hide();
		$('.rss-readonly').prop('readonly', false);
		$( "form input[name=date]" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}else if(className == 'site'){
		$('.for-rss, .for-manually').hide();
		$('.rss-readonly').removeAttr('readonly');
		$( "form input[name=date]" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}

	$('.for-'+className).show();
}

tinymce.init({
	selector:'#text-block',
	menubar: false,
	statusbar : false,
	plugins: [
		"autolink lists link code"
	],
    style_formats: [
        {title: 'H3', block: 'h3'},
        {title: 'H4', block: 'h4'},
        {title: 'H5', block: 'h5'},
        {title: 'H6', block: 'h6'},
    ],
	toolbar: "styleselect | bold italic underline | numlist bullist | link code",
	resize: false
});
</script>

<form class="validateModal relative-b" method="post" enctype="multipart/form-data">
	<div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table" >
            <tr>
                <td class="w-150">Language</td>
                <td>
                    <select class="w-100pr validate[required, maxSize[2]]" name="lang">
                        <?php foreach($languages as $language) { ?>
                            <option value="<?php echo $language['lang_iso2']; ?>" <?php echo isset($news) && $language["lang_iso2"] == $news["lang"] ? "selected" : ""; ?>><?php echo $language['lang_name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="w-150">Channel</td>
				<td>
					<select class="w-100pr validate[required,custom[integer],max[999],min[1]]" name="channel" >
					<?php foreach($media as $media_item) {?>
						<option value="<?php echo $media_item['id_media']?>" <?php if(isset($news)) echo selected($news['id_media'],$media_item['id_media'])?>><?php echo $media_item['title_media'] ?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr validate[required,custom[integer],max[999],min[1]]" name="country" >
					<?php foreach($country as $country_item) {?>
						<option value="<?php echo $country_item['id']?>" <?php if(isset($news)) echo selected($news['id_country'],$country_item['id'])?>><?php echo $country_item['country'] ?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Title</td>
				<td><input class="w-100pr validate[required,maxSize[255]]" type="text" name="title" value="<?php if(isset($news)) echo $news['title_news']?>" /></td>
			</tr>
			<tr>
				<td>Published</td>
				<td><input type="checkbox" name="published"  <?php if(isset($news)) echo checked($news['published_news'], 1)?>/></td>
			</tr>

			<tr class="<?php if(isset($news)) echo 'manually-hide'?>">
				<td>Type</td>
				<td>
					<select class="w-100pr validate[required]" name="type" id="type-news">
						<option value="site" <?php if(!isset($news)){?>selected="selected"<?php }else{ echo selected($news['type_news'], 'site'); }?>>URL</option>
						<option value="manually" <?php if(isset($news)){ echo selected($news['type_news'], 'manually');}?>>Manually</option>
						<option value="rss" <?php if(isset($news)){ echo selected($news['type_news'], 'rss');}?>>RSS</option>
					</select>
				</td>
			</tr>
			<tr class="for-site">
				<td>Link from news</td>
				<td><input type="text" name="link_site" class="w-100pr validate[required, custom[url]]" value="<?php if(isset($news)){ echo $news['link_news'];}?>" /></td>
			</tr>
			<tr class="for-rss">
				<td>Link from RSS</td>
				<td><input type="text" name="link_rss" class="w-100pr validate[custom[url]]" value="" /></td>
			</tr>
			<tr>
				<td>Date</td>
				<td><input class="w-100pr rss-readonly validate[required]" type="text" name="date" value="<?php if(isset($news)) echo formatDate($news['date_news'],'d-m-Y')?>" /></td>
			</tr>
			<tr class="for-rss">
				<td>Link from news</td>
				<td><input class="w-100pr <?php if(!isset($news)){ ?>rss-readonly<?php } ?> validate[custom[url]]" type="text" name="link" value="<?php if(isset($news)) echo $news['link_news']?>" /></td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea class="w-100pr h-100 rss-readonly validate[required,maxSize[1000]]" name="description" ><?php if(isset($news)) echo $news['description_news']?></textarea>
				</td>
			</tr>
			<tr class="for-manually">
				<td>Full description</td>
				<td>
					<textarea class="w-100pr h-200 validate[required]" name="full_description" id="text-block"><?php if(isset($news)) echo $news['fulltext_news']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Image News</td>
				<td>
					<input type="text" name="img_rss" class="for-rss rss-readonly w-100pr"/>
					<div class="wr-img-rss for-rss"></div>

					<span class="btn btn-success fileinput-button for-manually for-site">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload" type="file" name="files[]" multiple>
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Min width: 155px, Min height: 90px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($news['img_news'])){?>
							<div class="uploadify-queue-item item-medium">
								<div class="img-b">
									<img src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $news['img_news']], 'press_releases.main')?>" />
								</div>
								<div class="cancel"><a data-action="mass_media/ajax_news_delete_db_photo" data-file="<?php echo $news['id_news'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($news)){?>
			<input type="hidden" name="news" value="<?php if(isset($news)) echo $news['id_news']?>" />
		<?php }?>
		<button class="pull-right btn btn-primary" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
		<button class="pull-right btn btn-default for-rss mr-10" id="get-rss"><span class="ep-icon ep-icon_link"></span> Get rss info</button>
	</div>
</form>
