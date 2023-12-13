<form id="add-library-setting-form" class="validateModal relative-b">
   <div class="wr-form-content w-900 mh-600">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Title Setting</td>
                    <td>
                        <input type="text" class="w-100pr validate[required]" name="title">
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Admin page</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <?php echo __SITE_URL;?>
                            </span>
                            <input type="text" value="" class="w-100pr form-controll" name="link_admin">
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
                            <input type="text"value="" class="w-100pr form-controll" name="link_public">
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
                            <input type="text"value="" class="w-100pr form-controll" name="link_public_detail">
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
                            <input type="text"value="" class="w-100pr form-controll" name="link_public_search">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>
                        <textarea class="w-100pr h-70 validate[required]" name="description"></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Upload data with file</td>
                    <td>
                        <input type="checkbox" name="type_control" class="type_control">
                    </td>
                </tr>
                <tr class="name_config">
                    <td class="w-100">File Name</td>
                    <td>
                        <input type="text" class="w-100pr validate[required]" name="file_name">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
    var $name_config = $('.name_config');
    $(document).ready(function () {
        $name_config.hide();
    });

    $('.type_control').on('click', function() {
        var $this = $(this);
        if($this.is(':checked')){
            $name_config.show();
        }else{
            $name_config.hide();
        }
    });

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>library_setting/ajax_library_setting_operation/save_library_setting',
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
