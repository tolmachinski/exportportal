<?php
    $user_link_start = $user_link_end = "";
    if ($is_link_user) {
        $user_link_start = '<a href="' . $base_company_url . '" ' . addQaUniqueIdentifier('seller__wall_item_name') . '>';
        $user_link_end = '</a>';
    }

    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item' => $wall_item,
            'share_link' => __SITE_URL . 'seller_updates/popup_forms/share/' . $data['id_update'],
            'share_title' => 'Share this update',
            'email_link' => __SITE_URL . 'seller_updates/popup_forms/email/' . $data['id_update'],
            'email_title' => 'Email this update'
        );

        $additional_class = '';
    }else{
        $dropdown_params = array(
            'wall_item' => $wall_item
        );

        $additional_class = ' spersonal-history--removed';
    }
?>
<div class="detail-info">
    <div class="spersonal-history<?php echo $additional_class;?>" <?php echo addQaUniqueIdentifier('seller__wall_item'); ?>>
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <?php echo $user_link_start;?><strong><?php echo $company['name_company']; ?></strong><?php echo $user_link_end;?>
                <?php echo $wall_item['operation'] === 'add' ? 'added a new' : 'edited an'; ?> update in <a class="link" target="_blank" href="<?php echo $base_company_url; ?>/updates" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?>>Updates</a>
            </div>

            <?php
                tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params);
            ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-update flex-card">
                <?php if(!empty($data['photo'])){?>
                    <div class="spersonal-history-update__image flex-card__fixed image-card3">
                        <a class="link fancyboxGallery" href="<?php echo __SITE_URL . getImage('public/wall/' . $wall_item['id_seller']  . '/' .$wall_item['id'] . '/' . $data['photo'], 'public/img/no_image/group/noimage-other.svg');?>" data-title="<?php echo $company['name_company'];?>" title="<?php echo $company['name_company'];?>">
                            <img class="image" src="<?php echo __SITE_URL . getImage('public/wall/' . $wall_item['id_seller']  . '/' .$wall_item['id'] . '/thumb_150xR_' . $data['photo'], 'public/img/no_image/group/noimage-other.svg');?>" alt="<?php echo $company['name_company'];?>" <?php echo addQaUniqueIdentifier("seller__wall-update-img"); ?>/>
                        </a>
                    </div>
			    <?php }?>
                <div class="flex-card__float ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__wall_news_text'); ?>>
                    <?php echo $data['text']; ?>
                </div>
            </div>
        </div>
    </div>
</div>
