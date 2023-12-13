<?php if(!empty($attributes)){?>
<table>
    <?php foreach($attributes as $attribute) { ?>
        <tr>
            <td><?php echo $attribute['attr_name']?></td>
            <td>
                <?php if(in_array($attribute['attr_type'], array('select', 'multiselect'))) {?>
                    <div class="collapse-item w-100pr">
                        <div class="collapse-item_name clearfix"><i class="pull-left mr-5 txt-blue ep-icon lh-16 ep-icon_plus"></i> <span class="pull-left">All values</span></div>
                        <div class="collapse-item_block">
                            <ul class="collapse-item_list mt-10 clearfix">
                            <?php foreach($attribute['attr_values'] as $val){ ?>
                                <li>
                                    <label class="mb-0">
                                        <input class="pull-left dt_filter" type="checkbox" name="attrs_<?php echo $attribute['id']?>" data-title="<?php echo $attribute['attr_name']?>" data-value-text="<?php echo $val['value']?>" value="<?php echo $val['id']?>" />
                                        <span class="pull-left"><?php echo $val['value']?></span>
                                    </label>
                                </li>
                            <?php }?>
                            </ul>
                        </div>
                    </div>
                <?php } elseif($attribute['attr_type'] == 'range'){ ?>
                    <input class="w-50pr dt_filter" placeholder="From" data-title="<?php echo $attribute['attr_name']?> from" name="range_attrs_from_<?php echo $attribute['id']?>" type="text" value="" />
                    <input class="w-50pr dt_filter" placeholder="To" data-title="<?php echo $attribute['attr_name']?> to" name="range_attrs_to_<?php echo $attribute['id']?>" type="text" value="" />
                <?php } elseif($attribute['attr_type'] == 'text'){ ?>
                    <input class="w-100pr dt_filter" placeholder="<?php echo $attribute['attr_name']?>" data-title="<?php echo $attribute['attr_name']?>" name="text_attrs_<?php echo $attribute['id']?>" type="text" value="" />
                <?php }?>
            </td>
        </tr>
    <?php } ?>
</table>
<?php } else{ ?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> <span>Atributes not found. Please select more categories.</span></div>
<?php } ?>
