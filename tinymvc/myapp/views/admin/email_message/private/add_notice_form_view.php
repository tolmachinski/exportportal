<form class="validateModal relative-b">
   <div class="wr-form-content w-500">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-50">Notice</td>
                    <td>
                        <textarea class="w-100pr h-70 validate[required]" name="notice_message"></textarea>
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
            url: '<?php echo __SITE_URL?>email_message/private_ajax_operation/<?php echo $action?>',
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
