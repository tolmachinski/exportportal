<h3 class="minfo-sidebar-ttl mt-0">
    <span class="minfo-sidebar-ttl__txt"><?php echo translate('seller_home_page_sidebar_right_seller_rating');?></span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <div class="ppersonal-seller-rate">
            <?php if(!empty($user_main['stamp_pic'])) { ?>
                <img class="ppersonal-seller-rate__image" src="<?php echo __IMG_URL . 'public/img/groups/' . $user_main['stamp_pic'] ?>" alt="seller-badge">
            <?php } ?>

            <?php if(!empty($user_main['gr_name'])) { ?>
                <div class="ppersonal-seller-rate__group<?php echo userGroupNameColor($user_main['gr_name']); ?>">
                    <?php $userGroupName = $user_main['is_verified'] ? $user_main['gr_name'] : trim(str_replace('Verified', '', $user_main['gr_name']));?>
                    <span class="ppersonal-seller-rate__type"><?php echo $userGroupName . ($company['group_name_suffix'] ?? '');?></span>
                </div>
            <?php } ?>

            <?php if(!empty($company['registered_company'])) { ?>
                <div class="ppersonal-seller-rate__timeframe" <?php echo addQaUniqueIdentifier("seller__sidebar-experience"); ?>>
                    <?php
                        if(($time_ago = timeAgo($company['registered_company'], 'Y,m', false, false)) == 'recently') {
                            echo translate('seller_home_page_sidebar_right_recently_registered_company'); }
                        else {
                            echo translate('seller_home_page_sidebar_right_time_of_experience', array('{{TIME_AGO}}' => $time_ago));
                        }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt"><?php echo translate('seller_home_page_sidebar_right_additional_info');?></span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <ul class="minfo-sidebar-box__list">
            <li class="minfo-sidebar-box__list-item">
                <span class="minfo-sidebar-box__list-ico">
                    <i class="ep-icon ep-icon_user"></i>
                </span>
                <a class="ppersonal-user-detail__link fn txt-black" <?php echo addQaUniqueIdentifier('global__additional-info__user'); ?> href="<?php echo __SITE_URL.'usr/'.strForURL($user_main['fname'].' '.$user_main['lname']).'-'.$user_main['idu']?>"><?php echo $user_main['fname'].' '.$user_main['lname']?></a>
            </li>
            <li class="minfo-sidebar-box__list-item">
                <span class="minfo-sidebar-box__list-ico">
                    <img
                        width="24"
                        height="24"
                        <?php echo addQaUniqueIdentifier('global__additional-info__country-flag'); ?>
                        src="<?php echo getCountryFlag($company['country']);?>"
                        alt="<?php echo $company['country']?>"
                    >
                </span>
                <span <?php echo addQaUniqueIdentifier('global__additional-info__country-name'); ?>><?php echo $company['country']?></span>
            </li>
            <li class="minfo-sidebar-box__list-item">
                <span class="minfo-sidebar-box__list-ico">
                    <i class="ep-icon ep-icon_marker-stroke"></i>
                </span>
                <span <?php echo addQaUniqueIdentifier('global__additional-info__location-city'); ?>>
                    <?php echo $company['city']?><?php if(!empty($company['state'])){?>, <?php echo $company['state']; }?>
                </span>
            </li>
        </ul>

        <?php if(!empty($user_social)){?>
            <div class="clearfix mt-20">
                <?php foreach($user_social as $field_social){?>
                    <a class="mr-6 fs-30 ep-icon ep-icon_<?php echo $field_social['icon']?>" href="<?php echo (($field_social['name_field'] == 'Skype')?'skype:':'');?><?php echo $field_social['value_field']?>" target="_blank"></a>
                <?php }?>
            </div>
        <?php }?>
    </div>
</div>

<ul class="seller-statistic">
    <li class="seller-statistic__item">
        <div class="seller-statistic__name"><?php echo translate('seller_home_page_sidebar_right_statistic_followers');?></div>
        <a class="seller-statistic__nr" <?php echo addQaUniqueIdentifier('seller__statistics_number'); ?> href="<?php echo $base_company_url.'/followers' ?>"><?php echo $user_statistic['followers_user']?></a>
    </li>
    <li class="seller-statistic__item">
        <div class="seller-statistic__name"><?php echo translate('seller_home_page_sidebar_right_statistic_partners');?></div>
        <a class="seller-statistic__nr" <?php echo addQaUniqueIdentifier('seller__statistics_number'); ?> href="<?php echo $base_company_url.'/partners' ?>"><?php echo $user_statistic['b2b_partners']?></a>
    </li>
</ul>
