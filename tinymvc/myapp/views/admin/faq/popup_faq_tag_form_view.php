<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>

<div class="wr-modal-b">
    <form class="modal-b__form validateModal">
        <div class="modal-b__content w-700">
            <label class="modal-b__label">Tag name</label>
            <input type="text" name="tag_name" class="validate[required, maxLength[50]] w-98pr" value="<?php echo arrayGet($faq_tag, 'name') ?>" id="form-validation-field-1">
            <div class="clearfix"></div>

            <label class="modal-b__label">Top priority</label>
            <input type="text" name="top_priority" class="validate[required, maxLength[50]] w-98pr" value="<?php echo arrayGet($faq_tag, 'top_priority') ?>" id="form-validation-field-1">
        </div>
        <div class="modal-b__btns clearfix">
            <input type="hidden" name="faq_tag" value="<?php echo arrayGet($faq_tag, 'id_tag') ?>"/>
            <button class="btn btn-primary pull-right" type="submit">Submit</button>
        </div>
    </form>
</div>

<script>

<?php if(!empty($faq_tag)){?>
    var link = 'faq/ajax_faq_operation/edit_faq_tag';
<?php }else{?>
    var link = 'faq/ajax_faq_operation/add_faq_tag';
<?php }?>

function modalFormCallBack(form, data_table){
    var $form = $(form);
    var $wrform = $form.closest('.wr-modal-b');
    var fdata = $form.serialize();

    $.ajax({
        type: 'POST',
        url: link,
        data: fdata,
        dataType: 'JSON',
        beforeSend: function(){
            showFormLoader($wrform);
            $form.find('button[type=submit]').addClass('disabled');
        },
        success: function(resp){
            hideFormLoader($wrform);
            systemMessages( resp.message, 'message-' + resp.mess_type );

            if(resp.mess_type == 'success'){
                closeFancyBox();

                callbackManageFaq(resp);
            }else{
                $form.find('button[type=submit]').removeClass('disabled');
            }
        }
    });
}
</script>
