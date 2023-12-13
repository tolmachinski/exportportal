<?php
    $user_link_start = $user_link_end = "";
    if ($is_link_user) {
        $user_link_start = '<a href="' . $base_company_url . '" ' . addQaUniqueIdentifier('seller__wall_item_name') . '>';
        $user_link_end = '</a>';
    }

    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item' => $wall_item,
            'share_link' => __SITE_URL . 'seller_library/popup_forms/share/' . $data['id_file'],
            'share_title' => 'Share this document',
            'email_link' => __SITE_URL . 'seller_library/popup_forms/email/' . $data['id_file'],
            'email_title' => 'Email this document'
        );

        $link_start = '<a
                            class="link"
                            target="_blank"
                            ' . addQaUniqueIdentifier('seller__wall_item_title') . '
                            href="' . $base_company_url . '/document/' . $data['id_file'] . '">';
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
                <?php echo $wall_item['operation'] === 'add' ? 'added a new' : 'edited a'; ?> document in <a class="link" target="_blank" href="<?php echo $base_company_url; ?>/library" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?>>Library</a>
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params); ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-library">
                <h3 class="spersonal-history-item__ttl">
                    <?php echo $link_start; ?>
                        <?php echo $data['title']; ?>
                    <?php echo $link_end; ?>
                </h3>

                <div class="spersonal-history-library__desc" <?php echo addQaUniqueIdentifier('seller__wall_news_text'); ?>>
                    <?php echo $data['description']; ?>
                </div>

                <div class="spersonal-history-library__doc">
                    <a
                        class="link"
                        target="_blank"
                        href="<?php __SITE_URL; ?>public/wall/<?php echo $wall_item['id_seller']; ?>/<?php echo $wall_item['id']; ?>/<?php echo $data['file']; ?>"
                    >Document.<?php echo $data['extension']; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
