<?php
    $user_link_start = $user_link_end = "";
    if ($is_link_user) {
        $user_link_start = '<a href="' . $base_company_url . '" ' . addQaUniqueIdentifier('seller__wall_item_name') . '>';
        $user_link_end = '</a>';
    }

    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item' => $wall_item,
            'share_link' => __SITE_URL . 'seller_news/popup_forms/share/' . $data['id_news'],
            'share_title' => 'Share this article',
            'email_link' => __SITE_URL . 'seller_news/popup_forms/email/' . $data['id_news'],
            'email_title' => 'Email this article'
        );

        $link_start = '<a
                            class="link"
                            target="_blank"
                            ' . addQaUniqueIdentifier('seller__wall_item_title') . '
                            href="'. $base_company_url .'/view_news/'. strForURL($data['title']) . '-' . $data['id_news'].'
                            "
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
                <?php echo $user_link_start;?><strong><?php echo $company['name_company']; ?></strong><?php echo $user_link_end;?>
                <?php echo $wall_item['operation'] === 'add' ? 'added a new' : 'edited an'; ?> article in <a class="link" target="_blank" href="<?php echo $base_company_url; ?>/news" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?>>News</a>
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params); ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-news">
                <h3 class="spersonal-history-item__ttl">
                    <?php echo $link_start; ?>
                        <?php echo $data['title']; ?>
                    <?php echo $link_end; ?>
                </h3>
                <?php if(!empty($data['image'])) { ?>
                    <div class="spersonal-history-news__img image-card3">
                        <span class="link">
                            <img
                                class="image"
                                <?php echo addQaUniqueIdentifier('seller__wall-news-img'); ?>
                                src="<?php echo __IMG_URL . getImage('public/wall/' . $wall_item['id_seller'] . '/' . $wall_item['id'] . '/' . $data['image'], 'public/img/no_image/group/noimage-other.svg'); ?>"
                                alt="<?php echo $data['title']; ?>
                                "
                            >
                        </span>
                    </div>
                <?php } ?>
                <div class="spersonal-history-news__desc" <?php echo addQaUniqueIdentifier('seller__wall_news_text'); ?>>
                    <?php echo $data['text']; ?>
                </div>
            </div>
        </div>
    </div>
</div>
