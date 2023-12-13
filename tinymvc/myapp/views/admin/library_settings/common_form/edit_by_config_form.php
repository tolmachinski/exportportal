<form id="add-library-setting-form" class="validateModal relative-b">
   <div class="wr-form-content w-700 mh-600">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <?php if(!empty($config)){ ?>
                <?php if(!empty($records_by_relation) && !empty($relation_config['config_row'])){
                foreach($relation_config['config_row'] as $char => $relation){
                if (is_array($relation)) {
                    $char = key($relation); $relation = $relation[$char];
                }
                $list_char[$char] = $relation;?>
                <tr>
                    <td class="w-150">Value from file  column <?php echo $config[$char]['field']?> (<?php echo $char;?>)</td>
                    <td>
                        <input class="w-50pr mmr-5" type="text" disabled value="<?php echo $record[$config[$char]['db_colum']];?>">
                        <select class="w-50pr select_list" name="<?php echo $relation; ?>">
                            <option value="" disabled selected>Select</option>
                            <?php foreach($records_by_relation[$relation] as $val){?>
                            <option value="<?php echo $val['id'];?>" <?php echo $record[$relation] == $val['id'] ? 'selected' : ''; ?>><?php echo $val['value'];?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <?php }} ?>
                <?php foreach($config as $key => $item){
                if (!empty($list_char[$key])) continue;?>
                <tr>
                    <td class="w-150"><?php echo $item['field']?></td>
                    <td>
                        <?php
                            switch($item['type_insert']){
                                case 'input':
                                    echo '<input class="w-100pr validate[' . $item['rule_js'] . ']" type="text" value="' . $record[$item['db_colum']] . '" name="' . $item['db_colum'] . '" placeholder="' . $item['sample'] . '">';
                                break;

                                case 'textarea':
                                    echo '<textarea class="w-100pr h-70 validate[' . $item['rule_js'] . ']" name="' . $item['db_colum'] . '">' . $record[$item['db_colum']] . '</textarea>';
                                break;

                                case 'select':
                                    $option = '';
                                    $select_head  = '<select class="w-100pr validate[' . $item['rule_js'] . ']" name="' . $item['db_colum'] . '">';
                                    if(!empty($item['select_val'])){
                                        foreach($item['select_val'] as $key=>$value){
                                            $option  .= '<option value="'. $key .'" ' . ($value == $record[$item['db_colum']] ? 'selected' : '') .'>'. $value .'</option>';
                                        }
                                    }else{
                                        $option .= '<option value="">Set Value Select</option>';
                                    }
                                    $select_footer='</select>';
                                    echo $select_head . $option . $select_footer;
                                break;

                                default:
                                    echo 'Unknown type input data!';
                                break;
                            }
                        ?>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="id_record" value="<?php echo $id_record;?>">
        <input type="hidden" name="type_record" value="<?php echo $record['type_record'];?>">
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>

<script>
    function modalFormCallBack(form, data_table){
        var $form = $(form),
            $data  = $form.serializeArray();
        <?php if(!empty($records_by_relation)){?>
        var $select= $('.select_list');
        $.each($select, function (i, item) {
            var itemName = $(item).attr('name');
            var parents= {'name' : 'item_' + itemName, 'value': $('select[name="'+itemName+'"] option:selected').text()};
            $data.push(parents);
        });
        <?php }?>
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . $current_contoller?>/ajax_library_operation/edit_manual_record',
            data: $data,
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
            success: function(result){
                systemMessages( result.message, 'message-' + result.mess_type );

                if(result.mess_type == 'success'){
                    closeFancyBox();
                    if(data_table != undefined){
                        data_table.fnDraw(false);
                    }
                }else{
                    hideLoader($form);
                }
            }
        });
    }
</script>
