<?php
    $userTypesTranslations = [
        'shipper' => 'Shipper',
        'buyer'   => 'Buyer',
        'seller'  => 'Seller',
    ];
?>
<?php foreach ($userGuides as $userGuide) {?>
    <?php
        $breadcrumbs = [];
        $userGuideBreadcrumbs = json_decode('[' . $userGuide['menu_breadcrumbs'] . ']', true);

        foreach ($userGuideBreadcrumbs as $breadcrumbsSegment) {
            foreach ($breadcrumbsSegment as $userGuideId => $userGuideTitle) {
                $showGuideUrl = __SITE_URL . "user_guide/popup_forms/show_doc/{$userGuideId}";
                $breadcrumbs[] = <<<BREADCRUMBS_SEGMENT
                    <a class="user-guide-breadcrumbs__link fancybox fancybox.ajax" href="{$showGuideUrl}" data-title="{$userGuideTitle}" title="{$userGuideTitle}">
                        {$userGuideTitle}
                    </a>
                BREADCRUMBS_SEGMENT;
            }
        }
    ?>

    <div class="user-guide__new">
        <span class="user-guide__new-type"><?php echo implode(', ', array_intersect_key($userTypesTranslations, $userGuide['rel_user_types']));?></span>
        <a class="topics-title fancybox fancybox.ajax" href="<?php echo __SITE_URL . "user_guide/popup_forms/show_doc/{$userGuide['menu_alias']}";?>" data-title="<?php echo $userGuide['menu_title']?>" title="<?php echo $userGuide['menu_title'];?>"><?php echo $userGuide['menu_title'];?></a>
        <p class="user-guide-text">
            <?php echo $userGuide['menu_intro'];?>
        </p>
        <div class="user-guide-breadcrumbs">
            <?php echo implode('<span><i class="user-guide-breadcrumbs__arrow ep-icon ep-icon_arrow-right"></i></span>', $breadcrumbs);?>
        </div>
    </div>
<?php }?>
