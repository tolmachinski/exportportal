<table cellspacing="0" cellpadding="0" class="data table-striped hideInp w-100pr" id="tablesorter">
    <thead>
        <tr>
            <th  width="25">#</th>
            <th style="text-align:left">Category Name</th>
            <th style="text-align:left">SEO</th>
            <th style="text-align:left">Type</th>
            <th style="text-align:left">For</th>
            <th  width="100">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if(count($categories)){
        foreach($categories as $key => $category){?>
        <tr id="delete_<?php echo $category['category_id']?>" >
            <td ><?php echo $category['category_id']//$key+1?></td>
            <td ><?php echo $category['name']?></td>
            <td>
                <?php if(!empty($category['title'])):?><span title="<?php echo $category['title']?>">Title |</span><?php endif;?>
                <?php if(!empty($category['h1'])):?><span title="<?php echo $category['h1']?>">H1 |</span><?php endif;?>
                <?php if(!empty($category['description'])):?><span title="<?php echo $category['description']?>">Description |</span><?php endif;?>
                <?php if(!empty($category['keywords'])):?><span title="<?php echo $category['keywords']?>">Keywords </span><?php endif;?>
            </td>
            <td><?php switch($category['cat_type']){
                    case 1: echo "Make"; break;
                    case 2: echo "Model"; break;
                    case 3:
                    default: echo "Simple"; break;
                }?>
            </td>
            <td>
                <?php if($category['p_or_m'] == 1){?> Product
                <?php }else{?> Motor<?
                    if($category['vin'] == 1):?>(VIN)<?php endif;
                }?>
            </td>
            <td class="icons">
                <a rel="edit" id="catedit-<?php echo $category['category_id']?>">
                   edit
                </a>
                <a rel="delete" id="catdelete-<?php echo $category['category_id']?>">
                    Delete Category
                </a>
            </td>
        </tr>
    <?php }
    }else{?>
        <tr>
            <td></td>
            <td colspan="3"><b></b>No category </b></td>
        </tr>
    <?php }?>
    </tbody>
</table>
