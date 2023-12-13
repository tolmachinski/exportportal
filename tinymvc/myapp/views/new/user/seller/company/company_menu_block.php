<div class="hide-mn-767">
    <?php views()->display('new/directory/list_item_view', array('item' => array_merge($company, ['btnChat' => null]))); ?>

    <div class="row pt-25 pb-50">
        <div class="col-12">
            <div class="spersonal-btns">
                <?php echo (new \App\Common\Buttons\ChatButton(
                    ['recipient' => $company['id_user'], 'recipientStatus' => $company['status']],
                    ['classes' => 'btn btn-primary btn-block', 'title' => '', 'icon' => '', 'tag' => 'a']
                ))->render(); ?>

                <a class="btn btn-outline-primary btn-block" href="<?php echo $base_company_url . '/products';?>">See all items</a>
            </div>

            <a class="btn btn-light btn-block btn-panel-left fancyboxSidebar fancybox" <?php echo addQaUniqueIdentifier('seller__wall_mobile_menu_btn'); ?> data-title="<?php echo $company['name_company'];?>" href="#main-flex-card__fixed-left">
                <i class="ep-icon ep-icon_menu"></i>
                <?php echo translate('seller_home_page_menu_btn');?>
            </a>
        </div>
    </div>
</div>
