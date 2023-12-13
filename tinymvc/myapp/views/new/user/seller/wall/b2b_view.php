<?php
    use App\Common\Contracts\B2B\B2bRequestLocationType;
    $user_link_start = $user_link_end = "";
    if ($is_link_user) {
        $user_link_start = '<a href="' . $base_company_url . '">';
        $user_link_end = '</a>';
    }

    if(!(int)$wall_item['is_removed']){
        $dropdown_params = array(
            'wall_item' => $wall_item,
            'share_link' => __SITE_URL . 'b2b/popup_forms/share/' . $data['id_request'],
            'share_title' => 'Share this B2B request',
            'email_link' => __SITE_URL . 'b2b/popup_forms/email/' . $data['id_request'],
            'email_title' => 'Email this B2B request'
        );

        $link_start = '<a
                            class="link"
                            href="'.__SITE_URL.'b2b/detail/'.strForURL($data['title']).'-'.$data['id_request'].'"
                            target="_blank"
                        >';

        if (!logged_in()) {
            $link_start = '<a
                                class="link fancybox.ajax js-fancybox-validate-modal call-action"
                                href="'.__SITE_URL.'login'.'"
                                data-js-action="lazy-loading:login"
                                data-mw="400"
                                data-title="Login"
                            >';
        }

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
    <div class="spersonal-history<?php echo $additional_class;?>">
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <?php echo $user_link_start;?><strong><?php echo $company['name_company']; ?></strong><?php echo $user_link_end;?>
                <?php echo $wall_item['operation'] === 'add' ? 'added new' : 'edited'; ?> <a class="link" href="<?php echo $base_company_url; ?>/partners">B2B</a>
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params); ?>
        </div>

        <div class="spersonal-history__content">
            <div class="spersonal-history-b2b">
                <h3 class="spersonal-history-item__ttl">
                    <?php echo $link_start; ?>
                        <?php echo $data['title']; ?>
                    <?php echo $link_end; ?>
                </h3>

                <div class="spersonal-history-b2b__search" <?php echo addQaUniqueIdentifier('page__seller-company__search-in'); ?>>
                    <span class="spersonal-history-b2b__search-name">Search in:</span>
                    <?php if(B2bRequestLocationType::COUNTRY === $data['type_location']){ ?>
                        <span class="spersonal-history-b2b__country">
                            <img
                                class="image"
                                width="24"
                                height="24"
                                src="<?php echo getCountryFlag($data['country_name']); ?>"
                                alt="<?php echo $data['country_name']; ?>"
                                title="<?php echo $data['country_name']; ?>"
                            >
                            <span class="spersonal-history-b2b__country-name" <?php echo addQaUniqueIdentifier('page__seller-company__country-name'); ?>>
                                <?php echo $data['country_name']; ?>
                            </span>
                            <?php if(1 < $data['total_countries']){?>
                            <span class="spersonal-history-b2b__country-more">
                                +<span><?php echo $data['total_countries']-1; ?></span> <span class="spersonal-history-b2b__country-more-text">more</span>
                            </span>
                            <?php } ?>
                        </span>
                    <?php }elseif (B2bRequestLocationType::RADIUS === $data['type_location']){?>
                        <span><?php echo $data['radius']; ?> km</span>
                    <?php }else{?>
                        <span>Globally</span>
                    <?php }?>
                </div>

                <div class="spersonal-history-b2b__categories"><?php echo $data['message']; ?></div>
            </div>
        </div>
    </div>
</div>
