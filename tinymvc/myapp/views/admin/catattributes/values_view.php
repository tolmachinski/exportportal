<table cellspacing="0" cellpadding="0" class="dt-details__table data table-striped hideInp w-100pr" data-attribute-id="<?php echo $attr['id'];?>">
    <?php if(isset($values) && is_array($values) && count($values)){?>
        <thead>
            <tr>
                <th  style="text-align:left">Attribute value for <b><?php echo $attr['attr_name'];?></b></th>
                <th class=" center w-200">Translated in</th>
                <th class=" center w-150">Translate</th>
                <th class=" center w-100">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($values as $key => $value){?>
            <?php
                $langs = array();
                $langs_record = array_filter(json_decode($value['translation_data'], true));
                $langs_record_list = array('English');
                if(!empty($langs_record)){
                    foreach ($langs_record as $lang_key => $lang_record) {
                        if($lang_key == 'en'){
                            continue;
                        }

                        $langs[] = '<li>
                                        <div>
                                            <span class="display-ib_i lh-30 pl-5 pr-10">'.$lang_record['lang_name'].'</span>
                                            <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_value_i18n" data-value="' . $value['id'] . '" data-attr="' . $value['attribute'] . '" data-lang="'.$lang_key.'" title="Delete" data-message="Are you sure you want to delete the translation?" href="#" ></a>
                                            <a href="'.__SITE_URL.'catattr/popup_forms/edit_attr_value_i18n/'.$value['id'].'/'.$lang_key.'" data-title="Edit attribute translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
                                        </div>
                                    </li>';
                        $langs_record_list[] = $lang_record['lang_name'];
                    }
                    $langs[] = '<li role="separator" class="divider"></li>';
                }

                $langs_dropdown = '<div class="dropdown">
                                    <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                    <ul class="dropdown-menu">
                                        '.implode('', $langs).'
                                        <li><a href="'.__SITE_URL.'catattr/popup_forms/add_attr_value_i18n/'.$value['id'].'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                    </ul>
                                </div>';
            ?>
            <tr>
                <td><?php echo $value['value'];?></td>
                <td class="tac"><?php echo implode(', ', $langs_record_list);?></td>
                <td class="tac"><?php echo $langs_dropdown;?></td>
                <td class="icons tac">
                    <a class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax" href="catattr/update_forms/value/<?php echo $value['id']?>" title="Edit value" data-title="Edit value"></a>
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-val="<?php echo $value['id']?>" data-callback="valueRemove" data-message="Are you sure you want to delete this value?" title="Delete value"></a>
                </td>
            </tr>
        <?php }?>
        <?php } else{?>
            <tr>
                <td ><b>There are no values for this attribute.</b></td>
            </tr>
        <?php }?>
    	<tr>
    		<td colspan="3"></td>
    		<td class="tac"><a class="ep-icon ep-icon_plus txt-blue fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL;?>catattr/update_forms/append/<?php echo $attr['id']?>" title="Append values"  data-title="Append values"></a></td>
    	</tr>
    </tbody>
</table>
