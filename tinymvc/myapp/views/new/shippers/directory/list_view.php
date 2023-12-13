<div class="directory-page" itemscope itemtype="http://schema.org/ItemList">
    <div class="display-n">
        <span itemprop="numberOfItems"><?php echo count($shipper_list); ?></span>
    </div>

    <ul class="companies">
        <?php if (!empty($shipper_list)) {
            foreach ($shipper_list as $key => $shipper) { ?>
                <li class="companies-wr" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <?php app()->view->display('new/shippers/directory/list_item_view', array('shipper' => $shipper)); ?>
                    <div class="display-n">
                        <span itemprop="position"><?php echo $key; ?></span>
                    </div>
                </li>
            <?php }
        } else { ?>
            <li class="w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_partners_no_freight_forwarders');?></div></li>
        <?php } ?>
    </ul>
</div>
