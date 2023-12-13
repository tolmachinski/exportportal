<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-650 mh-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
                <tr>
					<td>Page position</td>
					<td>
                        <div class="pull-left w-50pr pr-5">
                            <?php if(isset($bannerInfo)){ ?>
                                <input type="text" value="<?php echo $byPages[$bannerInfo['position']['id_page']]['page_name'];?>" disabled>
                            <?php }else{ ?>
                                <select
                                    id="js-select-id-page"
                                    class="validate[required]"
                                >
                                    <option disabled selected>Select page</option>
                                    <?php foreach($byPages as $byPagesItem){?>
                                        <option
                                            value="<?php echo $byPagesItem['id_page']; ?>"
                                            <?php echo selected($byPagesItem['id_page'], $bannerInfo['position']['id_page']); ?>
                                        ><?php echo $byPagesItem['page_name']; ?></option>
                                    <?php }?>
                                </select>
                            <?php } ?>
                        </div>

                        <div class="pull-left w-50pr pl-5">
                            <?php if(isset($bannerInfo)){ ?>

                                <input type="text" value="<?php echo $byPages[$bannerInfo['position']['id_page']]['positions'][$bannerInfo['position']['id_promo_banners_page_position']]['position_name']; ?>" disabled>
                            <?php }else{ ?>
                                <select
                                    id="js-select-id-page-position"
                                    class="validate[required]"
                                    name="id_page_position"
                                    <?php if(!isset($bannerInfo)){ ?>disabled<?php } ?>
                                >
                                    <?php if(isset($bannerInfo)){ ?>
                                        <?php foreach($byPages[$bannerInfo['position']['id_page']]['positions'] as $byPagesPositionItem){?>
                                            <option
                                                value="<?php echo $byPagesPositionItem['id_page_position']; ?>"
                                                <?php echo selected($byPagesPositionItem['id_page_position'], $bannerInfo['id_page_position']); ?>
                                            ><?php echo $byPagesPositionItem['position_name']; ?></option>
                                        <?php } ?>
                                    <?php }else{ ?>
                                        <option disabled selected>Select position</option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                        </div>
				 	</td>
				</tr>
				<tr>
					<td class="w-100">Title</td>
					<td>
						<input
                            class="w-100pr validate[required, maxSize[50]]"
                            type="text"
                            name="title"
                            value="<?php echo (isset($bannerInfo)) ? $bannerInfo['title'] : ''?>"
                        />
					</td>
				</tr>
				<tr>
					<td>Link</td>
					<td>
						<input
                            id="js-banner-link-input"
                            class="w-100pr validate[maxSize[250]]"
                            type="text"
                            name="link"
                            value="<?php echo (isset($bannerInfo)) ? $bannerInfo['link'] : ''?>"
                        />
				 	</td>
                </tr>
                <tr>
					<td>Order</td>
					<td>
						<input
                            class="w-100pr validate[required,custom[number],min[0],max[99]]"
                            type="text"
                            name="order_banner"
                            value="<?php echo (isset($bannerInfo)) ? $bannerInfo['order_banner'] : ''?>"
                        />
				 	</td>
				</tr>
                <tr>
					<td>Images</td>
					<td>
                        <div id="js-fileupload-banner-wrapper">
                            <?php
                                if(!empty($bannerInfo)){
                                    $imagesSelectedPosition = $bannerByPosition[$bannerInfo['id_page_position']];
                                    $imagesUploaded = json_decode($bannerInfo['image'], true);

                                    foreach ($imagesSelectedPosition as $imagesSelectedPositionKey => $imagesSelectedPositionItem) {
                            ?>
                                    <h3 class="w-100pr tt-capitalize"><?php echo $imagesSelectedPositionKey;?></h3>
                                    <div class="flex-card mb-10">
                                        <div class="flex-card__fixed mw-180 m-0">
                                            <div
                                                id="js-fileupload-banner-preview-<?php echo $imagesSelectedPositionKey;?>"
                                                class="fileupload-queue files"
                                            >
                                                <?php if(!empty($imagesUploaded[$imagesSelectedPositionKey])){?>
                                                    <div class="uploadify-queue-item item-medium image-card3 m-0 mr-15">
                                                        <div class="img-b link">
                                                            <img
                                                                class="image"
                                                                src="<?php echo getDisplayImageLink(['{ID}' => $bannerInfo['id_promo_banners'], '{FILE_NAME}' => $imagesUploaded[$imagesSelectedPositionKey]], 'promo_banners.main');?>"
                                                            />
                                                        </div>
                                                    </div>
                                                <?php }?>
                                            </div>
                                        </div>
                                        <div class="flex-card__float">
                                            <span class="btn btn-success fileinput-button">
                                                <i class="ep-icon ep-icon_plus"></i>
                                                <span>Select files...</span>
                                                <input
                                                    class="js-fileupload-banner"
                                                    id="js-fileupload-banner-<?php echo $imagesSelectedPositionKey;?>"
                                                    data-type="<?php echo $imagesSelectedPositionKey;?>"
                                                    data-position="<?php echo $byPages[$bannerInfo['position']['id_page']]['positions'][$bannerInfo['position']['id_promo_banners_page_position']]['id_page_position'];?>"
                                                    type="file"
                                                    name="files[]">
                                            </span>
                                            <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL; ?>public/img/loader.gif" alt="loader"> Uploading...</span>
                                            <div class="info-alert-b">
                                                <i class="ep-icon ep-icon_info"></i>
                                                <div> &bull; Width: <strong><?php echo $imagesSelectedPositionItem['size']['w'];?>px</strong>, Height: <strong><?php echo $imagesSelectedPositionItem['size']['h'];?>px</strong>.</div>
                                                <div> &bull; The maximum file size has to be 2MB.</div>
                                                <div> &bull; You cannot upload more than 1 photo(s).</div>
                                                <div> &bull; File available formats (jpg,jpeg,png).</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }?>
                            <?php }?>
                        </div>
					</td>
				</tr>
                <tr>
                    <td>Will open popup?</td>
                    <td>
                        <select id="js-select-will-open-popup" class="validate[required]" name="will_open_popup">
                            <option value="0" <?php echo isset($bannerInfo) && $bannerInfo["will_open_popup"] === "0" ? "selected" : ""; ?>>No</option>
                            <option value="1" <?php echo isset($bannerInfo) && $bannerInfo["will_open_popup"] === "1" ? "selected" : ""; ?>>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Popup action</td>
                    <td>
                       <input
                        id="js-popup-action-input"
                        class="<?php echo isset($bannerInfo) && $bannerInfo["will_open_popup"] === "1" ? "" : "display-n" ?>"
                        type="text"
                        name="popup_action"
                        value="<?php echo (isset($bannerInfo)) ? $bannerInfo['popup_action'] : ''?>"
                    >
                    </td>
                </tr>
                <tr>
                    <td>Popup legacy action</td>
                    <td>
                       <input
                        id="js-popup-legacy-action-input"
                        class="<?php echo isset($bannerInfo) && $bannerInfo["will_open_popup"] === "1" ? "" : "display-n" ?>"
                        type="text"
                        name="popup_legacy_action"
                        value="<?php echo (isset($bannerInfo)) ? $bannerInfo['popup_legacy_action'] : ''?>"
                    >
                    </td>
                </tr>
                <tr>
                    <td>Popup background path</td>
                    <td>
                       <input
                        id="js-popup-background-input"
                        class="<?php echo isset($bannerInfo) && $bannerInfo["will_open_popup"] === "1" ? "" : "display-n" ?>"
                        type="text"
                        name="popup_bg_path"
                        value="<?php echo (isset($bannerInfo)) ? $bannerInfo['popup_bg_path'] : ''?>"
                    >
                    </td>
                </tr>
			</tbody>
		</table>

        <?php if(isset($bannerInfo)){ ?>
            <input type="hidden" name="id_promo_banners" value="<?php echo $bannerInfo['id_promo_banners']; ?>">
        <?php } ?>
        <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder;?>"/>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/template" id="js-banner-upload-template">
    <h3 class="w-100pr tt-capitalize">{{TYPE}}</h3>
    <div class="flex-card mb-10">
        <div class="flex-card__fixed mw-180 m-0">
            <div id="js-fileupload-banner-preview-{{ID}}" class="fileupload-queue files">
            </div>
        </div>
        <div class="flex-card__float">
            <span class="btn btn-success fileinput-button">
                <i class="ep-icon ep-icon_plus"></i>
                <span>Select files...</span>
                <input class="js-fileupload-banner" id="js-fileupload-banner-{{ID}}" type="file" name="files[]">
            </span>
            <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
            <div class="info-alert-b">
                <i class="ep-icon ep-icon_info"></i>
                <div> &bull; Width: <strong>{{WIDTH}}px</strong>, Height: <strong>{{HEIGHT}}px</strong>.</div>
                <div> &bull; The maximum file size has to be 2MB.</div>
                <div> &bull; You cannot upload more than 1 photo(s).</div>
                <div> &bull; File available formats (jpg,jpeg,png).</div>
            </div>
        </div>
    </div>
</script>

<?php foreach($byPages as $byPagesItem){ ?>
    <script type="text/template" id="js-page-position-templates-<?php echo $byPagesItem['id_page']?>">
        <option disabled selected>Select position</option>
        <?php foreach($byPagesItem['positions'] as $byPagesPositionItem){?>
            <option
                value="<?php echo $byPagesPositionItem['id_page_position']; ?>"
            ><?php echo $byPagesPositionItem['position_name']; ?></option>
        <?php }?>
    </script>
<?php } ?>

<script>
    var pagePositions = <?php echo json_encode($bannerByPosition);?>;

    $(function(){
        var $wrapperImage = $('#js-fileupload-banner-wrapper'),
            $position = $('#js-select-id-page-position');

        if($wrapperImage.find('.js-fileupload-banner').length){
            $wrapperImage.find('.js-fileupload-banner').each(function(){
                var $this = $(this),
                    type = $this.data('type'),
                    position = $this.data('position');

                fileuploadBannerInit(type, position);
            });
        }

        $('body').on('change', '#js-select-id-page', function(){
            var $this = $(this),
                idPosition =  $this.val(),
                $template = $('#js-page-position-templates-' + idPosition);

            $wrapperImage.html("");
            $position.prop('disabled', false).html($template.html());
        });

        $('body').on('change', '#js-select-id-page-position', function(){
            var $this = $(this),
                idPosition =  $this.val(),
                $templateBanner = $('#js-banner-upload-template'),
                templateBannerHtml = $templateBanner.html()
                banners = '';

            $wrapperImage.html("");
            $.each(pagePositions[idPosition], function(index, element){
                var template = templateBannerHtml;
                var banner = template.replaceAll('{{ID}}', index)
                                        .replace('{{WIDTH}}', element.size.w)
                                        .replace('{{HEIGHT}}', element.size.h)
                                        .replace('{{TYPE}}', index);

                $wrapperImage.append(banner);
                fileuploadBannerInit(index, idPosition);
            });

            $.fancybox.reposition();
        });
    });

    function fileuploadBannerInit(type, position){
        var $fileupload = $('#js-fileupload-banner-' + type),
            $fileuploadBtn = $fileupload.find('.fileinput-loader-btn'),
            $fileuploadQueue = $('#js-fileupload-banner-preview-' + type);

        $fileupload.fileupload({
            url: __site_url + 'promo_banners/ajax_banner_upload_photo/<?php echo $uploadFolder;?>/' + position + '/' + type,
            dataType: 'json',
            maxFileSize: <?php echo $fileuploadMaxFileSize?>,
            beforeSend: function () {
                $fileuploadBtn.fadeIn();
            },
            done: function (e, data) {
                if(data.result.mess_type == 'success'){
                    $.each(data.result.files, function (index, file) {
                        var itemID = +(new Date());

                        $fileuploadQueue.html(templateFileUpload('img','item-medium',itemID));

                        var $item = $('#fileupload-item-'+itemID);
                        $item.addClass('image-card3 m-0 mr-15')
                            .find('.img-b').addClass('link')
                            .append('<img class="image" src="' + file.url + '"> <input type="hidden" name="image[' + type + ']" value="' + file.path + '">');

                        $item.find('.cancel')
                            .append('<a data-action="promo_banners/ajax_banner_delete_files/<?php echo $uploadFolder;?>" data-file="' + file.name + '" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');
                    });
                } else{
                    systemMessages( data.result.message, 'message-' + data.result.mess_type );
                }
                $fileuploadBtn.fadeOut();
            },
            processalways: function(e,data){
                if (data.files.error){
                    systemMessages( data.files[0].error, 'message-error' );
                }
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    }

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>promo_banners/ajaxOperation/<?php echo ((isset($bannerInfo) ? "edit" : "add"))?>_banner',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                hideLoader($form);

                if(data.mess_type == 'success'){
                    closeFancyBox();

                    if(data_table != undefined)
                        data_table.fnDraw(false);
                }
            }
        });
    }
</script>

<script>
    var selectWillOpenPopup = $("#js-select-will-open-popup");
    var selectWillOpenPopupCurrentValue = selectWillOpenPopup.val();

    selectWillOpenPopup.on('change', function(e) {
        e.preventDefault();
        BootstrapDialog.show({
            message: "If you change this value, the current data for this banner will be deleted and you must add the correct data and correct link for it. Are you sure you want to change this value?",
            closable: false,
            draggable: true,
            buttons: [{
                label: translate_js({plug:'BootstrapDialog', text: 'ok'}),
                cssClass: 'btn-success',
                hotkey: 13,
                action: function(dialogRef){
                    changePopupData();
                    $.fancybox.reposition();
                    dialogRef.close();
                }
            },
            {
                label: translate_js({plug:'BootstrapDialog', text: 'cancel'}),
                hotkey: 32,
                action: function(dialogRef){
                    selectWillOpenPopup.val(selectWillOpenPopupCurrentValue);
                    dialogRef.close();
                }
            }]
        });
    });

    function changePopupData() {
        var inputPopupAction = $("#js-popup-action-input");
        var inputPopupLegacyAction = $("#js-popup-legacy-action-input");
        var inputPopupBackground = $("#js-popup-background-input");

        if (selectWillOpenPopup.val() === "1") {
            inputPopupAction.removeClass("display-n");
            inputPopupLegacyAction.removeClass("display-n");
            inputPopupBackground.removeClass("display-n");
        } else {
            inputPopupAction.addClass("display-n").val("");
            inputPopupLegacyAction.addClass("display-n").val("");
            inputPopupBackground.addClass("display-n").val("");
        }
    }
</script>
