<form class="validateModal relative-b">
   <div class="wr-form-content w-500">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Category name</td>
                    <td>
                        <input type="text" class="w-100pr validate[required,maxSize[50]]" name="category_name" value="<?php echo $record['category']?>">
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Select user</td>
                    <td>
                        <select name="user_id[]" class="select-buy-b pull-left w-100pr" multiple>
                        <?php if(!empty($list_ep_staff)){
                        $userCheck = explode(',', $record['user_list']);
                        foreach($list_ep_staff as $user){?>
                            <option value="<?php echo $user['idu']?>" <?php echo (in_array($user['idu'], $userCheck) ? 'selected' : '');?>><?php echo $user['fname']?> <?php echo $user['lname']?> - <?php echo $user['gr_name']?></option>
                        <?php }}else{?>
                            <option value="">User doesn't found</option>
                        <?php }?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" value="<?php echo $record['id_spcat']?>" name="id_record">
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/select2-4-0-3/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $(".select-buy-b").select2();
    });

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>category_support/ajax_category_support_operation/edit_category_support',
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
