<ol class="dd-list">
    <?php foreach ($menuList as $menu) {?>
        <li class="dd-item" data-id="<?php echo $menu['id_menu'];?>">
            <div class="dd-handle">
                <?php echo $menu['menu_title'];?>
            </div>
            <div class="dd-actions">
                <span class="fs-14 mt-2"><?php echo "{$menu['menu_alias']} | {$menu['id_menu']} | ";?></span>
                <a class="fancybox-ttl-inside fancybox.iframe fs-16 ep-icon ep-icon_visible" href="<?php echo __SITE_URL . "user_guide/view/{$menu['menu_alias']}";?>" data-title="<?php echo $menu['menu_title'];?>" title="<?php echo $menu['menu_title'];?>" data-w="1020"></a>
                <a class="fancyboxValidateModal fancybox.ajax fs-16 ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL . "user_guide/popup_forms/add_guide/{$menu['id_menu']}";?>" data-title="Add menu" title="Add submenu"></a>
                <a class="fancyboxValidateModal fancybox.ajax ep-icon ep-icon_pencil fs-16" href="<?php echo __SITE_URL . "user_guide/popup_forms/edit_guide/{$menu['id_menu']}";?>" data-title="Edit menu" title="Edit menu"></a>
                <a class="ep-icon ep-icon_remove txt-red fs-16 confirm-dialog" href="#" data-callback="remove_menu" data-menu="<?php echo $menu['id_menu'];?>" title="Remove menu" data-message="Are you sure you want to delete this menu?"></a>
            </div>
            <?php if (!empty($menu['children'])) {?>
                <?php views('admin/user_guide/menu_view', ['menuList' => $menu['children']]);?>
            <?php }?>
        </li>
    <?php }?>
</ol>
