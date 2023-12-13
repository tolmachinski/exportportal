<?php
    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item'   => $wall_item,
            'share_link'  => __SITE_URL . "seller_pictures/popup_forms/share/{$data['id_photo']}",
            'share_title' => 'Share this photo',
            'email_link'  => __SITE_URL . "seller_pictures/popup_forms/email/{$data['id_photo']}",
            'email_title' => 'Email this photo'
        );

        $link_start = '<a
                            class="link"
                            data-title="'. $data['title'] .'"
                            href="'. $base_company_url.'/picture/'.strForUrl($data['title']).'-'.$data['id_photo'] .'"
                            target="_blank"
                        >';
        $link_end = '</a>';
        $additional_class = '';
    }else{
        $dropdown_params = array(
            'wall_item' => $wall_item
        );

        $link_start = '<span>';
        $link_end = '</span>';
        $additional_class = ' spersonal-history--removed';
    }
?>

<div class="detail-info">
    <div class="spersonal-history<?php echo $additional_class;?>" <?php echo addQaUniqueIdentifier('seller__wall_item'); ?>>
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <?php if ($is_link_user) { ?>
                    <a href="<?php echo $base_company_url; ?>" <?php echo addQaUniqueIdentifier('seller__wall_item_name'); ?>>
                        <strong><?php echo $company['name_company']; ?></strong>
                    </a>
                <?php } else { ?>
                    <strong><?php echo $company['name_company']; ?></strong>
                <?php } ?>
                <?php if($wall_item['operation'] === 'add')  { ?>
                    added a new photo in
                <?php } else { ?>
                    edited a photo in
                <?php } ?>
                <a class="link" target="_blank" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?> href="<?php echo $base_company_url; ?>/pictures">Photos</a>
            </div>
            <?php
                tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params);
            ?>
        </div>

        <div class="spersonal-history__content">
            <h3 class="spersonal-history-item__ttl mb-25" <?php echo addQaUniqueIdentifier('seller__wall_item_title'); ?>>
                <?php echo $link_start;?>
                    <?php echo $data['title']; ?>
                <?php echo $link_end;?>
            </h3>
            <div class="spersonal-history-img image-card3">
                <?php echo $link_start;?>
                    <img
                        class="image"
                        <?php echo addQaUniqueIdentifier('seller__wall-photo-img'); ?>
                        src="<?php echo __IMG_URL . getImage("public/wall/{$wall_item['id_seller']}/{$wall_item['id']}/{$data['path_photo']}", 'public/img/no_image/group/noimage-other.svg'); ?>"
                        alt="<?php echo $data['title']; ?>"
                    >
                <?php echo $link_end;?>
            </div>
        </div>
    </div>
</div>
