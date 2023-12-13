<form class="validateModal relative-b">
    <div class="wr-form-content w-700 mh-600">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Title Setting</td>
                    <td>
                        <input type="text" value="<?php echo $record['lib_title'];?>" class="w-100pr  validate[required]" name="title">
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Admin page</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <?php echo __SITE_URL;?>
                            </span>
                            <input type="text" value="<?php echo $record['link_admin'];?>" class="w-100pr form-controll" name="link_admin">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Public page</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <?php echo __SITE_URL;?>
                            </span>
                            <input type="text"value="<?php echo $record['link_public'];?>" class="w-100pr form-controll" name="link_public">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Public detail</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <?php echo __SITE_URL;?>
                            </span>
                            <input type="text"value="<?php echo $record['link_public_detail'];?>" class="w-100pr form-controll" name="link_public_detail">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Public search</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <?php echo __SITE_URL;?>
                            </span>
                            <input type="text"value="<?php echo $record['link_public_search'];?>" class="w-100pr form-controll" name="link_public_search">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>
                        <textarea class="w-100pr h-70 validate[required]" name="description"><?php echo $record['lib_description'];?></textarea>
                    </td>
                </tr>
                <?php if(!empty($record['file_name'])){?>
                <tr>
                    <td class="w-100">File Name</td>
                    <td>
                        <input type="text" value="<?php echo $record['file_name'];?>" class="w-100pr  validate[required]" name="file_name">
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="type_control" class="type_control" value="<?php echo ($record['lib_type'] === 'file' ? 'on' : '')?>">
        <input type="hidden" name="id_record" value="<?php echo $record['id_lib'];?>"/>
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>library_setting/ajax_library_setting_operation/edit_library_setting',
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
