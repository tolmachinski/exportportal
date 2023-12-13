<form class="validateModal relative-b" id="select-category-form">
   <div class="wr-form-content w-500">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-160">Category of support</td>
                    <td>
                        <select name="category_support" class="pull-left w-100pr validate[required]">
                        <?php if(!empty($list_category)){
                        foreach($list_category as $category){?>
                        <option value="<?php echo $category['id_spcat'];?>" <?php echo (!empty($record['id_suppcat']) && $record['id_suppcat'] === $category['id_spcat'] ? 'selected' : '');?>><?php echo $category['category'];?></option>
                        <?php }}else{?>
                        <option value="">Category doesn't found</option>
                        <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="w-160">Export Portal Staff</td>
                    <td id="list_user">
                        <select name="user_id" class="pull-left w-100pr validate[required]">
                            <?php if(!empty($list_user)){
                            $userIds = explode(',', $list_user['user_list']);
                            $userFullName = explode(',', $list_user['full_name']);?>
                            <option value="">Select User</option>
                            <?php foreach($userIds as $i => $id){?>
                            <option value="<?php echo $id;?>" <?php echo (!empty($record['attach_user']) && $record['attach_user'] === $id ? 'selected' : '');?>><?php echo $userFullName[$i]?></option>
                            <?php }}?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="id_record" value="<?php echo $record['id_mess']?>">
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script>
    $(document).ready(function () {
        $("#select-category-form").on('change', 'select[name="category_support"]', function(){
            var $this = $(this),
                $container = $('#list_user');

            $.ajax({
                type: "POST",
                url: "<?php echo __SITE_URL?>email_message/get_users_category",
                data: {id_category: $this.val()},
                success: function(html) {
                    $container.find('select[name="user_id"]').remove();
                    $container.html('<select name="user_id" class="pull-left w-100pr validate[required]">' + html + '</select>');
                }
            });
        });
    });

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>email_message/ajax_emai_mess_operation/assign_user',
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
