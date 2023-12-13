<form class="validateModal relative-b">
    <div class="wr-form-content w-900 mh-600">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Visible</td>
                    <td>
                        <input type="checkbox" name="visible" <?php if($record['visible']){?>checked="checked"<?php }?>/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Country</td>
                    <td>
                        <select class="w-100pr validate[required]" name="country" >
                            <?php foreach($port_country as $country){?>
                            <option value="<?php echo $country['id']?>" <?php echo selected($record['id_country'], $country['id']);?>><?php echo $country['country'];?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Meta Keywords</td>
                    <td>
                        <textarea class="w-100pr h-50 validate[required]" name="meta_key"><?php echo $record['customs_meta_key'];?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Meta Description</td>
                    <td>
                        <textarea class="w-100pr h-50 validate[required]" name="meta_desc"><?php echo $record['customs_meta_desc'];?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Text</td>
                    <td>
                        <textarea class="w-100pr h-150 validate[required] requirement-text-block" name="text"><?php echo $record['customs_text'];?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="id_record" value="<?php echo $record['id_customs_req'];?>"/>
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
    $(document).ready(function(){
        tinymce.init({
            selector:'.requirement-text-block',
            menubar: false,
            statusbar : false,
            height : 320,
            plugins: ["autolink lists link textcolor"],
            dialog_type : "modal",
            toolbar: "fontsizeselect | bold italic underline forecolor backcolor link | numlist bullist | alignleft aligncenter alignright alignjustify",
            fontsize_formats: '8px 10px 12px 14px 16px 18px 20px 22px 24px 36px',
			resize: false
        });
    });

    function modalFormCallBack(form, data_table){
        tinyMCE.triggerSave();
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>customs_requirements/ajax_requirement_operation/edit_requirement',
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
                        data_table.fnDraw(false);
                }else{
                    hideLoader($form);
                }
            }
        });
    }
</script>
