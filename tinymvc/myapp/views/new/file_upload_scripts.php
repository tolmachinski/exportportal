<script src="<?php echo __SITE_URL . 'public/plug/jquery-fileupload-5-42-3/jquery.ui.widget.js';?>"></script>
<script src="<?php echo __SITE_URL . 'public/plug/jquery-fileupload-5-42-3/jquery.iframe-transport.js';?>"></script>
<script src="<?php echo __SITE_URL . 'public/plug/jquery-fileupload-5-42-3/jquery.fileupload.js';?>"></script>
<script src="<?php echo __SITE_URL . 'public/plug/jquery-fileupload-5-42-3/jquery.fileupload-process.js';?>"></script>
<script src="<?php echo __SITE_URL . 'public/plug/jquery-fileupload-5-42-3/jquery.fileupload-validate.js';?>"></script>
<script>
	function fileuploadRemove($thisBtn, hasRemoteDeletion){
		hasRemoteDeletion = typeof hasRemoteDeletion !== 'undefined' ? Boolean(~~hasRemoteDeletion) : 1;
		var url = $thisBtn.data('action');
		var data = { file: $thisBtn.data('file') };
		var onRequestSuccess = function (data) {
			if(data.mess_type == 'success'){
				$thisBtn.closest('.fileupload-item').remove();
				$('#progress').fadeOut().html('');
				var additionalCallback = $thisBtn.data('additional-callback') || null;
				if(null !== additionalCallback){
					callFunction(additionalCallback, $thisBtn);
				}
			} else {
				systemMessages( data.message, data.mess_type);
			}

			return data;
		};

		if($thisBtn.closest('.fileupload-item').hasClass('item-checked')){
			systemMessages('<?php echo translate('general_image_upload_is_main_message'); ?>', 'warning');

			return Promise.reject();
		}
		if (!hasRemoteDeletion) {
			$thisBtn.closest('.fileupload-item').remove();

			return Promise.resolve(true);
		}

		return postRequest(url, data)
			.then(onRequestSuccess)
			.catch(onRequestError);
	}

	function templateFileUpload(type, className, index, iconClassName){

		if(type == 'files'){
			templateHtml = '<div id="fileupload-item-'+index+'" class="fileupload-item '+className+' icon">\
							<div class="image icon-files-'+iconClassName+'-middle"></div>\
							<div class="cancel"></div>\
						</div>';
		}else{
			templateHtml = '<div id="fileupload-item-'+index+'" class="fileupload-item '+className+'">\
							<div class="image"></div>\
							<div class="cancel"></div>\
						</div>';
		}
		return templateHtml;
	}

	function templateFileUploadNew(data){
		var className = (null === data.className || undefined === data.className)?'':data.className;

		if(data.type == 'files'){
			templateHtml = '<div id="fileupload-item-'+data.index+'" class="fileupload-item '+className+' icon">\
							<div class="fileupload-item__image icon-files-'+data.iconClassName+'-middle">'
								+ data.image +
							'</div>\
							<div class="fileupload-item__actions"></div>\
						</div>';
		}else if(data.type == 'img'){
			templateHtml = '<div id="fileupload-item-'+data.index+'" class="fileupload-item '+className+'">\
                            <div class="fileupload-item__image">\
                                <a class="link fancyboxGallery" rel="fancybox-thumb" href="' + data.image_link + '">'
									+ data.image +
								'</a>\
							</div>\
							<div class="fileupload-item__actions"></div>\
						</div>';
		}else if(data.type == 'imgnolink'){
			templateHtml = '<div id="fileupload-item-'+data.index+'" class="fileupload-item '+className+'">\
                            <div class="fileupload-item__image">\
                                <span class="link">'
									+ data.image +
								'</span>\
								</div>\
							<div class="fileupload-item__actions"></div>\
						</div>';
		}

		return templateHtml;
	}

	function fileuploadRemoveNew2($thisBtn, hasRemoteDeletion){
		hasRemoteDeletion = typeof hasRemoteDeletion !== 'undefined' ? Boolean(~~hasRemoteDeletion) : 1;
		var file = $thisBtn.data('file');
        var name = $thisBtn.data('name');

        if(file != name){
            $thisBtn.closest('.js-file-upload2').append('<input type="hidden" name="images_remove[]" value="'+file+'">');
            $thisBtn.closest('.js-fileupload-item').remove();

            return Promise.resolve(true);
        }else{
            var url = $thisBtn.data('action');
            var data = { file: $thisBtn.data('file') };
            var onRequestSuccess = function (data) {
                if(data.mess_type == 'success'){
                    $thisBtn.closest('.js-fileupload-item').remove();
                    var additionalCallback = $thisBtn.data('additional-callback') || null;
                    if(null !== additionalCallback){
                        callFunction(additionalCallback, $thisBtn);
                    }
                } else {
                    systemMessages( data.message, data.mess_type);
                }

                return data;
            };

            postRequest(url, data)
			    .then(onRequestSuccess)
                .catch(onRequestError);

            return Promise.resolve(true);
        }
	}

	function templateFileUploadNew2(data){
		var className = (null === data.className || undefined === data.className)?'':data.className;
		if(data.type == 'files'){
			templateHtml = '<div\
				id="fileupload-item-'+data.index+'"\
				class="fileupload2__item image-card3 js-fileupload-item '+className+' icon">\
					<div\
						class="link js-fileupload-image icon-files-'+data.iconClassName+'-middle"\
					>'
						+ data.image +
					'</div>\
					<div class="js-fileupload-actions fileupload2__actions"></div>\
				</div>';
		}else if(data.type == 'img'){
			templateHtml = '<div\
				id="fileupload-item-'+data.index+'"\
				class="fileupload2__item image-card3 js-fileupload-item '+className+'">\
					<a\
						class="link fancyboxGallery js-fileupload-image"\
						rel="fancybox-thumb"\
						href="' + data.image_link + '"\
					>'
						+ data.image +
					'</a>\
					<div class="js-fileupload-actions fileupload2__actions"></div>\
				</div>';
		}else if(data.type == 'imgnolink' && data.upload){
			templateHtml = '<div\
				id="fileupload-item-'+data.index+'"\
				class="fileupload2__item js-fileupload-item image-card3 '+className+'" data-name="'+data.index+'">\
					<span\
						class="link js-fileupload-image"\
					>'
						+ data.image +
					'</span>\
                    <button\
                        class="fileupload2__item-btn call-function js-set-as-main-btn"\
                        data-callback="openModal"\
                        data-name="'+data.index+'"\
                        type="button"\
                    >\
                    Set as main\
                    </button>\
                    <button\
                        class="fileupload2__item-btn fileupload2__item-btn--main call-function js-set-as-main-btn"\
                        data-callback="openModal"\
                        data-name="'+data.index+'"\
                        type="button"\
                    >\
                         Main\
                    </button>\
					<div class="js-fileupload-actions fileupload2__actions"></div>\
				</div>';
		} else if(data.type == 'imgnolink'){
			templateHtml = '<div\
				id="fileupload-item-'+data.index+'"\
				class="fileupload2__item js-fileupload-item image-card3 '+className+'">\
					<span\
						class="link js-fileupload-image"\
					>'
						+ data.image +
					'</span>\
					<div class="js-fileupload-actions fileupload2__actions"></div>\
				</div>';
		}

		return templateHtml;
	}
</script>
