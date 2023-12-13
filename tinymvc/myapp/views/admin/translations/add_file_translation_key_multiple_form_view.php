<div class="wr-modal-b">
    <form action="<?php echo $action; ?>" class="modal-b__form validateModal">
		<div class="modal-b__content mh-100pr pb-0 w-900 mh-700">
			<div class="row">
                <div class="col-xs-4">
                    <label class="modal-b__label">Select file</label>
                    <select name="file_name" class="form-control validate[required]">
                        <option value="" disabled selected>Select file</option>
                        <?php foreach($file_names as $file_name){?>
                            <option value="<?php echo $file_name;?>"><?php echo $file_name;?></option>
                        <?php }?>
                        <option value="other">Other</option>
                    </select>
                </div>
				<div class="col-xs-4" id="new_file_name">
                    <label class="modal-b__label">New file name</label>
                    <input type="text" class="form-control validate[required,maxSize[50]]" name="file_name" placeholder="New file name" disabled>
                </div>
                <div class="col-xs-4">
                    <label class="modal-b__label">File type</label>
                    <select name="file_type" class="form-control validate[required]">
                        <option value="php">PHP</option>
                        <option value="js">JS</option>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label" >Select pages</label>
                    <select name="pages[]" class="form-control validate[required]" id="new-key-selected-pages" multiple>
                        <option></option>
                        <?php foreach ($pages as $page_id => $page_name) { ?>
                            <option value="<?php echo $page_id; ?>"><?php echo $page_name; ?></option>
                        <?php } ?>
                        <option value="0">Used on all pages</option>
                    </select>
                </div>
                <div class="add-translation-pair">
                    <div class="col-xs-5 initial-b">
                        <label class="modal-b__label">Translation key</label>
                        <input type="text" class="form-control validate[required,maxSize[1000],custom[noDuplicateValueByName]]" name="translation_keys[key][]">
                    </div>
                    <div class="col-xs-7">
                        <label class="modal-b__label" >Text</label>
                        <textarea class="form-control validate[required] mnh-60" name="translation_keys[value][]"></textarea>
                    </div>
                </div>
                <div class="add-translation-pair">
                    <div class="col-xs-5 initial-b">
                        <label class="modal-b__label">Translation key</label>
                        <input type="text" class="form-control validate[required,maxSize[1000],custom[noDuplicateValueByName]]" name="translation_keys[key][]">
                    </div>
                    <div class="col-xs-7">
                        <label class="modal-b__label" >Text</label>
                        <textarea class="form-control validate[required] mnh-60" name="translation_keys[value][]"></textarea>
                    </div>
                </div>
                <div id="additional-key-value-pairs"></div>
                <div class="col-xs-12 mt-10">
                    <button class="call-function btn btn-primary pull-right" type="button" id="js-add-translations-inputs" data-callback="addInputTranslationKeyValue" >
                        <i class="ep-icon ep-icon_plus-stroke"></i>
                    </button>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="lh-30 vam">
                <input type="checkbox" name="old"> Used only in old design
            </label>
            <button class="btn btn-success pull-right" type="submit" id="translation-key--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script>
    var del_row_pair = function(obj){
        var $this = $(obj);
        $this.closest(".add-translation-pair").fadeOut('normal', function(){
            $(this).remove();
        });
    }
    var modalFormCallBack = function (formNode, dataGrid){
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();
        var keys_vals = [];

        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                closeFancyBox();
                if(dataGrid) {
                    $(dataGrid).DataTable().draw(false);
                }
            }
        };
        showLoader(form);

        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });

    };
    var addInputTranslationKeyValue = function(obj){
        var $thisBtn = $(obj);
        var $thisForm = $('.validateModal');
        var $row = $thisBtn.parent().prevAll('.add-translation-pair').first();
        var key = $row.find('input[name="translation_keys[key][]"]').val().trim();
        var val = $row.find('textarea[name="translation_keys[value][]"]').val().trim();

        if(key == "" || val == ""){
            systemMessages( "Please fill in the existing inputs first", "warning" );
            $thisForm.validationEngine('validate');
            return false;
        }

        var row = '<div class="add-translation-pair">\
                    <div class="col-xs-5 initial-b">\
                        <label class="modal-b__label">Translation key</label>\
                        <input type="text" class="form-control validate[required,maxSize[1000],custom[noDuplicateValueByName]]" name="translation_keys[key][]">\
                    </div>\
                    <div class="col-xs-6">\
                        <label class="modal-b__label" >Text</label>\
                        <textarea class="form-control validate[required] mnh-60" name="translation_keys[value][]"></textarea>\
                    </div>\
                    <div class="col-xs-1">\
                        <a class="btn btn-danger mt-30 call-function" data-callback="del_row_pair"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
                    </div>\
                </div>';

        $('#additional-key-value-pairs').append(row);
    }

    $(function(){
        $('#new-key-selected-pages').select2({
            width: '100%',
            multiple: true,
            placeholder: "Selected pages",
            minimumResultsForSearch: 2,
        });
        $('select[name="file_name"]').on('change', function(){
            var option = $(this).val();
            $('input[name="file_name"]').val(option == 'other'?'':option).prop('disabled', option != 'other');
        });
    });
</script>
