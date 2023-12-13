<form class="validateModal relative-b" id="select-category-form">
   <div class="wr-form-content w-500">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-160">Category of support</td>
                    <td>
                        <select name="category_support" class="pull-left w-100pr validate[required]">
                        <?php if(!empty($list_category)){
                        foreach($list_category as $id_sppcat => $category){?>
                        <option value="<?php echo $id_sppcat;?>"><?php echo $category['category'];?></option>
                        <?php }}else{?>
                        <option value="">Category doesn't found</option>
                        <?php }?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="id_record" value="<?php echo $id_record?>">
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script>

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>email_message/private_ajax_operation/assign_me_message',
            data: $form.serialize(),
//            beforeSend: function () {
//                showLoader($form);
//            },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){
                   closeFancyBox();
                    if(data_table != undefined)
                        data_table.fnDraw(false);
                }else{
//                    hideLoader($form);
                }
            }
        });
    }
</script>
