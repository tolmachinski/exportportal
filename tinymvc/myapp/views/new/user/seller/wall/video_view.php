<?php
    $user_link_start = $user_link_end = "";
    if ($is_link_user) {
        $user_link_start = '<a href="' . $base_company_url . '" ' . addQaUniqueIdentifier('seller__wall_item_name') . '>';
        $user_link_end = '</a>';
    }

    $iFrameUrl = '';
    if ($data['source_video'] === 'youtube') {
        $iFrameUrl = 'https://www.youtube.com/embed/' . $data['short_url_video'];
    }

    if ($data['source_video'] === 'vimeo') {
        $iFrameUrl = 'https://player.vimeo.com/video/' . $data['short_url_video'] . '?title=0&byline=0&portrait=0&badge=0';
    }

    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item' => $wall_item,
            'share_link' => __SITE_URL . 'seller_videos/popup_forms/share/' . $data['id_video'],
            'share_title' => 'Share this video',
            'email_link' => __SITE_URL . 'seller_videos/popup_forms/email/' . $data['id_video'],
            'email_title' => 'Email this video'
        );

        $link_start = '<a
                            class="link"
                            target="_blank"
                            ' . addQaUniqueIdentifier('seller__wall_item_title') . '
                            href="'.$base_company_url.'/video/'.strForURL($data['title']) . '-' . $data['id_video'].'"
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
                <?php echo $wall_item['operation'] === 'add' ? 'added a new' : 'edited a'; ?> video in <a class="link" target="_blank" href="<?php echo $base_company_url; ?>/videos" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?>>Videos</a>
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params); ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-video">
                <h3 class="spersonal-history-item__ttl">
                    <?php echo $link_start;?>
                        <?php echo $data['title']; ?>
                    <?php echo $link_end;?>
                </h3>
                <div class="spersonal-history-video__img">
                    <a
                        class="link fancybox.iframe fancyboxVideo"
                        href="<?php echo $iFrameUrl; ?>"
                        data-h="350"
                        data-title="Video"
                        rel="videoItem"
                    >
                        <div class="video-play">
                            <div class="video-play__circle"></div>
                            <i class="ep-icon ep-icon_videos"></i>
                        </div>
                        <img
                            class="image"
                            <?php echo addQaUniqueIdentifier('seller__wall-video-img'); ?>
                            src="<?php echo __IMG_URL . getImage("public/wall/{$wall_item['id_seller']}/{$wall_item['id']}/{$data['image_video']}", 'public/img/no_image/group/noimage-other.svg'); ?>"
                            alt="<?php echo $data['title']; ?>"
                        >
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
