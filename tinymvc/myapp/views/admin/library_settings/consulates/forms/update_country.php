<?php foreach($countres as $country){
    $country_options .= "<option value=\"{$country['id']}\">{$country['country']}</option>";
}?>
<div id="update-country-form" class=" relative-b">
    <div class="wr-form-content w-700 mh-600">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-40pr tac">
                        Value from file column
                    </td>
                    <td class="w-40pr tac">
                        Value from database
                    </td>
                    <td class="w-20pr tac">
                        Action
                    </td>
                </tr>
                <?php if(!empty($empty_country_records) && !empty($countres)){
                foreach($empty_country_records as $record){
                $value_input = "";
                if (empty($record['id_country_cons']) && !empty($record['id_country'])){
                    $value_input = $record['country_consulate'];
                } else {
                    $value_input = $record['country_main'];
                }?>
                <tr>
                    <td class="w-40pr">
                        <input class="mmr-5" type="text" disabled value="<?php echo $value_input; ?>" name="record_country">
                    </td>
                    <td class="w-40pr">
                        <select class="validate[required]" name="country">
                            <option value="" disabled selected>Select</option>
                            <?php echo $country_options; ?>
                        </select>
                    </td>
                    <td class="w-20pr">
                        <button class="w-100pr pull-right btn btn-success call-function" data-callback="update_country" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function update_country(obj){
        var $this = $(obj),
            parentTrElement = $this.closest('tr'),
            recordCountry = parentTrElement.find("input[name='record_country']").val(),
            selectElement = parentTrElement.find("select[name='country']"),
            selectCountry = selectElement.val(),
            dataTableName = $.fancybox.current.element.data('table');

        if (selectCountry){
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>library_consulates/ajax_library_operation/change_country_consulate',
                data: { record_country : recordCountry, change_country : selectCountry },
                beforeSend: function () {
                    showLoader(parentTrElement);
                },
                dataType: 'json',
                success: function(result){
                    systemMessages( result.message, 'message-' + result.mess_type );
                    if(result.mess_type == 'success'){
                        parentTrElement.remove();

                        if (($('.vam-table').find('tr').length - 1) == 0){
                            closeFancyBox();
                        }

                        if(dataTableName != undefined){
                            $(dataTableName).DataTable().draw(false)
                        }
                    }else{
                        hideLoader(parentTrElement);
                    }
                }
            });
        } else {
            systemMessages( 'Please select country', 'message-info');
        }
    }
</script>
