<form
    id="js-form-add-another-lang"
    class="validateModal"
    data-callback="submitDescriptionAnotherLang"
>
    <div class="form-group">
        <label class="input-label input-label--required">Language</label>
        <select class="validate[required] half-input half-input--mw-305" name="language" <?php echo addQaUniqueIdentifier("items-my-add-popup__other-lang-select")?>>
            <?php foreach($languages as $languages_item){?>
            <option value="<?php echo $languages_item['id_lang'];?>"><?php echo $languages_item['lang_name'];?></option>
            <?php }?>
        </select>
    </div>

    <div class="form-group tinymce-textarea-mobile" <?php echo addQaUniqueIdentifier("items-my-add-popup__other-lang-description")?>>
        <label class="input-label input-label--required">Description</label>
        <textarea
            <?php echo addQaUniqueIdentifier("items-my-add-popup__other-lang-description-textarea")?>
            id="js-add-item-description-another-lang"
            class="validate[]"
            name="description"
            data-max="20000"
        ></textarea>
    </div>

    <ul class="js-desc-lang-list list-form-checked-info pt-5">
        <li class="list-form-checked-info__item list-form-checked-info__item--ai-top">
            <label class="custom-checkbox" <?php echo addQaUniqueIdentifier("items-my-add-popup__other-lang-checkbox")?>>
                <input type="checkbox"
                    name="translate"
                    class="js-desc-lang"
                    value="1"
            >
                <div class="custom-checkbox__text">
                    I want Export Portal to translate the description in English
                    <div class="fs-12 txt-blue2 pt-5">Translation will be added within 5 business days after publishing</div>
                </div>
            </label>
        </li>
    </ul>
</form>

<script>
    var modalAddDescAnotherLangTextarea = 'js-add-item-description-another-lang';
    var $modalAddDescAnotherLangForm = $('#js-form-add-another-lang');

    var initializeDescriptionLang = function(editor) {
        initNewTinymce(editor, {valHook: 'editorDescLang'});
    };

    $.valHooks.editorDescLang = {
        get: function (el) {
            return tinymce.get(modalAddDescAnotherLangTextarea).getContent({format : 'text'}) || "";
        }
    };

    var initializeAnotherLangValues = function() {
        return new Promise(function (resolve) {

            if(Object.size(descriptionAnotherLangSelected) > 0){
                if(descriptionAnotherLangSelected.translate == 1 && descriptionAnotherLangSelected.status != 'removed'){
                    $modalAddDescAnotherLangForm.find('input[name="translate"]').prop('checked', true);
                }else if(descriptionAnotherLangSelected.translate == 2){
                    $modalAddDescAnotherLangForm.find('.js-desc-lang-list').remove();
                }

                if(descriptionAnotherLangSelected.status != 'removed'){
                    $modalAddDescAnotherLangForm.find('select[name="language"] option[value="'+descriptionAnotherLangSelected.language+'"]').prop('selected', true);
                    $modalAddDescAnotherLangForm.find('#'+modalAddDescAnotherLangTextarea).val(descriptionAnotherLangSelected.description);
                }

                resolve(true);
            }else{
                resolve(true);
            }

        });
    };

    $(function(){

        initializeAnotherLangValues().then(function () {
            tinymce.remove('#' + modalAddDescAnotherLangTextarea);
            tinymce.init({
                content_css : "/public/css/tinymce-content-style.css",
                selector: '#' + modalAddDescAnotherLangTextarea,
                menubar: false,
                statusbar : true,
                placeholder : 'Description in another language',
                height : 300,
                plugins: ["placeholder lists charactercount powerpaste"],
                style_formats: [
                    {title: 'H3', block: 'h3'},
                    {title: 'H4', block: 'h4'},
                    {title: 'H5', block: 'h5'},
                    {title: 'H6', block: 'h6'},
                ],
                powerpaste_html_import: "merge",
                toolbar: "styleselect | bold italic underline | numlist bullist ",
                init_instance_callback: initializeDescriptionLang,
                resize: false,
                entity_encoding : "named",
                mobile: {
                    theme: 'mobile'
                }
            });
        });
    });
</script>
