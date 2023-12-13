<?php
    views()->display('new/item/add_item/partials/image_uploader_with_cropper_view', array(
        'item'       => $item,
        'photos'     => !empty($photos) ? $photos : array(),
        'fileupload' => $fileupload,
    ));
?>
<div class="form-group">
	<label class="input-label">Item video</label>
	<input <?php echo addQaUniqueIdentifier("items-my-add__video")?> class="validate[maxSize[200], custom[url]]" type="text" name="video" placeholder="URL of your video: You Tube or Vimeo link" value="<?php if(isset($item['video'])){ echo $item['video']; }?>"/>
</div>

<div class="form-group tinymce-textarea-mobile" <?php echo addQaUniqueIdentifier("items-my-add__description-in-english")?>>
    <label class="input-label">Description in English</label>

    <div class="info-alert-b mb-10">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div>• Export Portal uses English as our main language, so please type your description in English;</div>
        <div>• If you use another language using the button below, we can translate it to English for you;</div>
        <div>• Description should be at least in one language;</div>
        <div>• The translated version will be automatically added within 5 business days after the request.</div>
    </div>

	<textarea
        <?php echo addQaUniqueIdentifier("items-my-add__description-in-english-textarea")?>
		id="js-add-item-description"
		name="description"
		data-max="20000"
	><?php if(isset($item['description'])){ echo $item['description']; }?></textarea>
</div>

<?php
    $item_description_array = array();
    if(!empty($item_description)){
        $item_description_array = json_decode($item_description, true);
    }
?>

<a
    <?php echo addQaUniqueIdentifier("items-my-add__description-in-other-lang-add")?>
    id="js-btn-call-description-language"
    class="btn btn-dark btn--fs14-mobile375 mt-10 call-function<?php if((!empty($item_description) && $item_description_array['status'] != 'removed')){?> display-n<?php }?>"
    data-title="Add description in another language"
    data-callback="addDescriptionAnotherLang"
    href="<?php echo __SITE_URL;?>items/popup_forms/add_description_another_lang"
>
    Add description in another language
</a>

<div
    id="js-description-language-selected"
    class="add-info-row-wr add-info-row-wr--pd-20"
></div>

<script type="application/javascript">
	var descriptionTextarea = $('#js-add-item-description');
    var descriptionAnotherLangSelected = <?php echo (!empty($item_description))?$item_description:'{}';?>;

    var submitDescriptionAnotherLang = function($form){
        tinyMCE.triggerSave();
        var $selectedLanguage = $form.find('select[name="language"]');
        var $checkTranslate = $form.find('input[name="translate"]');
        var checkTranslate = 0;

        if($checkTranslate.length){
            checkTranslate = (($form.find('input[name="translate"]').prop('checked') == true)?1:0);
        }else{
            checkTranslate = 2;
        }

        descriptionAnotherLangSelected = Object.assign({},
            {
                languageName: $selectedLanguage.find('option:selected').text(),
                language: $selectedLanguage.val(),
                translate: checkTranslate,
                description: $form.find('textarea[name="description"]').val(),
            }
        );

        initTemplateDescriptionAnotherLangSelected();
    }

    var removeDescriptionAnotherLang = function($this){
        descriptionAnotherLangSelected = {};
        $('#js-description-language-selected').html('<input type="hidden" name="additional_description_remove" value="1">');
        $('#js-btn-call-description-language').css({'display':'inline-block'});
    }

    var classesAnotherLang = 'bootstrap-dialog--max-width-830';

    var addDescriptionAnotherLang = function($this){
        var link = $this.attr('href');

        open_modal_dialog({
            title: 'Add description in another language',
            isAjax: true,
            content: link,
            validate: true,
            classes: classesAnotherLang,
            btnSubmitText: 'Add description'
        });
    }

    var editDescriptionAnotherLang = function(){
        open_modal_dialog({
            title: 'Add description in another language',
            isAjax: true,
            content: __site_url + 'items/popup_forms/add_description_another_lang',
            validate: true,
            classes: classesAnotherLang,
            btnSubmitText: 'Edit description'
        });
    }

    function initTemplateDescriptionAnotherLangSelected(){
        var $template = '<div class="add-info-row mr-0 ml-0 add-info-row--variant">\
            <div class="add-info-row__item">\
                <span class="add-info-row__ttl">\
                    <span>Description in '+descriptionAnotherLangSelected.languageName+'</span>\
                    <input type="hidden" name="additional_description_language" value="'+descriptionAnotherLangSelected.language+'">\
                    <input type="hidden" name="additional_description_text" value="'+htmlEscape(descriptionAnotherLangSelected.description)+'">\
                </span>\
            </div>\
            <div class="add-info-row__item add-info-row__item-half">';

        if(descriptionAnotherLangSelected.translate < 2){
            $template += '<label <?php echo addQaUniqueIdentifier("items-my-add__other-lang-checkbox-translate")?>\
                    class="custom-checkbox">\
                    <input \
                        class="js-addition-description" \
                        type="checkbox" \
                        name="additional_description_translate" \
                        value="1"'+
                        ((descriptionAnotherLangSelected.translate == 1)?'checked="checked"':'')
                    +'>\
                    <span class="custom-checkbox__text">Translate in English</span>\
                </label>';
        }

        $template += '</div>\
            <div class="add-info-row__actions add-info-row__actions--3">\
                <a\
                    <?php echo addQaUniqueIdentifier("items-my-add__other-lang-edit-button")?>\
                    class="btn btn-light call-function"\
                    data-callback="editDescriptionAnotherLang"\
                    title="Edit description"\
                >\
                    <i class="ep-icon ep-icon_pencil"></i>\
                </a>\
                <a\
                    <?php echo addQaUniqueIdentifier("items-my-add__other-lang-remove-button")?>\
                    class="btn btn-light confirm-dialog"\
                    data-message="Are you sure you want to remove this description?"\
                    data-callback="removeDescriptionAnotherLang"\
                    title="Remove description"\
                >\
                    <i class="ep-icon ep-icon_trash-stroke"></i>\
                </a>\
            </div>\
        </div>';

        $('#js-btn-call-description-language').hide();
        $('#js-description-language-selected').html($template);
        BootstrapDialog.closeAll();
    }

	var callVideoPlaceholder = function(){
        var $inputVideo = $('#js-add-item-form input[name="video"]');
        var placeholderVideo = "URL of your video: You Tube or Vimeo link";

        if ($(window).width() < 767 ) {
            placeholderVideo = "URL of your video";
        }

        $inputVideo.attr("placeholder", placeholderVideo);
    }

	$(function(){
        <?php if(!empty($item_description) && $item_description_array['status'] != 'removed'){?>
            initTemplateDescriptionAnotherLangSelected();
        <?php }?>

        callVideoPlaceholder();
        jQuery(window).on('resizestop', function () {
            callVideoPlaceholder();
        });
	});
</script>
