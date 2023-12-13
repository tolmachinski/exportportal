<?php
$user_link_start = $user_link_end = "";
if ($is_link_user) {
    $user_link_start = '<a href="' . $base_company_url . '">';
    $user_link_end = '</a>';
}
?>
<div class="detail-info">
    <div class="spersonal-history">
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <?php echo $user_link_start;?><strong><?php echo $company['name_company']; ?></strong><?php echo $user_link_end;?>
                <?php echo $wall_item['operation'] === 'add' ? 'added new' : 'edited'; ?> banner
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', array(
                'wall_item' => $wall_item
            )); ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-img image-card3">
                <a class="link" href="<?php echo $data['link']; ?>" target="_blank">
                    <img
                        class="image"
                        src="<?php echo __IMG_URL . getImage('public/img/seller_banners/' . $data['image'], 'public/img/no_image/group/noimage-other.svg'); ?>"
                        alt="banner"
                        <?php echo addQaUniqueIdentifier("seller__wall-banner-img"); ?>
                    >
                </a>
            </div>
        </div>
    </div>
</div>
