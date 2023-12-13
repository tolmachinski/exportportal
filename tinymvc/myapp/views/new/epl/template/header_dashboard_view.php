<?php
$session = session();
echo dispatchDynamicFragment('epl-dashboard:menu', [json_decode($custom_header_menu, true)]);
?>

<div class="flex-card epuser-subline-template">
    <div class="epuser-subline-template__left">
        <div class="account">
            <?php if (false !== ($accountLink = getMyProfileLink())) { ?>
                <a class="account__current" href="<?php echo $accountLink; ?>">
                <?php } else { ?>
                <div class="account__current">
                <?php } ?>
                    <div class="account__current-img image-card">
                        <span class="link">
                            <img
                                class="js-replace-file-avatar js-lazy image"
                                data-src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 1, 'no_image_group' => group_session()]); ?>"
                                src="<?php echo getLazyImage(100, 100); ?>"
                                alt="<?php echo cleanOutput($session->fname); ?>" />
                        </span>
                    </div>
                    <div class="account__current-info">
                        <div class="account__current-name"><?php echo cleanOutput($session->fname . ' ' . $session->lname); ?></div>
                        <div class="account__current-company"><?php echo cleanOutput($session->shipper_name_company); ?></div>
                        <div class="account__current-group"><?php echo group_name_session() . ($session->group_name_suffix ?? ''); ?></div>
                    </div>
                <?php if (false !== ($accountLink = getMyProfileLink())) { ?>
                </a>
            <?php } else { ?>
                </div>
            <?php } ?>

            <a class="account__logout call-action" href="<?php echo __SHIPPER_URL . 'authenticate/logout' ?>" <?php echo addQaUniqueIdentifier("global__navigation-logout-btn")?> data-js-action="dashboard:logout">
                <div class="account__logout-icon">
                    <span class="link">
                        <i class="ep-icon ep-icon_logout"></i>
                    </span>
                </div>
                <div class="account__logout-text">Logout</div>
            </a>
        </div>
    </div>

    <div class="flex-card__float epuser-subline-template__right js-epuser-subline-content">
        <div class="epuser-subline__header">
            <ul class="nav nav-tabs nav--borders js-nav-tavs" role="tablist">
                <li class="nav__item">
                    <?php if($complete_profile['total_completed'] < 100){?>
                        <a
                            class="nav__link js-tab-btn call-action active"
                            data-js-action="tabs:show-content"
                            href="#header-toggle-complete"
                            role="tab"
                        >
                            <?php echo translate('dashboard_complete_profile'); ?>
                        </a>
                        <a
                            class="nav__link js-tab-btn call-action"
                            data-js-action="tabs:show-content"
                            href="#header-toggle-quickmenu"
                            role="tab"
                        >
                            <?php echo translate('dashboard_quick_menu'); ?>
                        </a>
                    <?php }else{?>
                        <a
                            class="nav__link active js-tab-btn call-action"
                            data-js-action="tabs:show-content"
                            href="#header-toggle-quickmenu"
                            role="tab"
                        >
                            <?php echo translate('dashboard_quick_menu'); ?>
                        </a>
                    <?php }?>


                    <a
                        class="nav__link js-tab-btn call-action"
                        data-js-action="tabs:show-content"
                        href="#header-toggle-fullmenu"
                        role="tab"
                    >
                        <?php echo translate('dashboard_full_menu'); ?>
                    </a>
                </li>

                <li class="nav__item">
                    <a class="btn btn-sm btn-light" href="<?php echo getUrlForGroup() . 'dashboard/customize_menu'; ?>">
                        <?php echo translate('dashboard_customize_menu'); ?>
                    </a>

                    <a class="btn btn-sm btn-light" href="<?php echo getUrlForGroup() . 'dashboard'; ?>">
                        <?php echo translate('dashboard_go_to_dashboard'); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content tab-content--borders tab-content--navigation">
            <?php if ($complete_profile['total_completed'] < 100) { ?>
				<div role="tabpanel" class="tab-pane tab-pane--profile-complete js-tab-pane active" id="header-toggle-complete">
					<?php views()->display('new/epl/list_complete_view', array('complete_profile' => $complete_profile)); ?>
				</div>
				<div role="tabpanel" class="tab-pane tab-pane--quick-menu js-tab-pane" id="header-toggle-quickmenu">
			<?php } else { ?>
				<div role="tabpanel" class="tab-pane active js-tab-pane" id="header-toggle-quickmenu">
			<?php } ?>
                <?php if ($complete_profile['total_completed'] < 100) { ?>
                    <div class="complete-profile-warning">
                        <div class="complete-profile-warning__info">
                            <i class="ep-icon ep-icon_warning-circle-stroke"></i> <span><?php echo translate('header_complete_profile'); ?> <button class="complete-profile-warning__button call-action" data-js-action="account:open-completion-popup">Here</button></span>
                        </div>
                        <span><?php echo "{$complete_profile['countCompleteOptions']}/{$complete_profile['countOptions']}"?></span>
                    </div>
                <?php } ?>
                <div id="js-dashboard-nav" class="dashboard-nav">
                    <div class="dashboard-nav__col">
                        <ul class="dashboard-nav-list js-dashboard-nav-list">
                            <li class="col1-cell1 dashboard-nav-list__item"></li>
                            <li class="col1-cell2 dashboard-nav-list__item"></li>
                            <li class="col1-cell3 dashboard-nav-list__item"></li>
                            <li class="col1-cell4 dashboard-nav-list__item"></li>
                            <li class="col1-cell5 dashboard-nav-list__item"></li>
                            <li class="col1-cell6 dashboard-nav-list__item"></li>
                            <li class="col1-cell7 dashboard-nav-list__item"></li>
                        </ul>
                    </div>
                    <?php if (!group_expired_session()) { ?>
                        <div class="dashboard-nav__col">
                            <ul class="dashboard-nav-list js-dashboard-nav-list">
                                <li class="col2-cell1 dashboard-nav-list__item"></li>
                                <li class="col2-cell2 dashboard-nav-list__item"></li>
                                <li class="col2-cell3 dashboard-nav-list__item"></li>
                                <li class="col2-cell4 dashboard-nav-list__item"></li>
                                <li class="col2-cell5 dashboard-nav-list__item"></li>
                                <li class="col2-cell6 dashboard-nav-list__item"></li>
                                <li class="col2-cell7 dashboard-nav-list__item"></li>
                            </ul>
                        </div>
                        <div class="dashboard-nav__col">
                            <ul class="dashboard-nav-list js-dashboard-nav-list">
                                <li class="col3-cell1 dashboard-nav-list__item"></li>
                                <li class="col3-cell2 dashboard-nav-list__item"></li>
                                <li class="col3-cell3 dashboard-nav-list__item"></li>
                                <li class="col3-cell4 dashboard-nav-list__item"></li>
                                <li class="col3-cell5 dashboard-nav-list__item"></li>
                                <li class="col3-cell6 dashboard-nav-list__item"></li>
                                <li class="col3-cell7 dashboard-nav-list__item"></li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane js-tab-pane" id="header-toggle-fullmenu">
                <div class="dashboard-nav-full">
                <?php $navTabs = session()->menu_full; ?>
                <?php foreach ($navTabs as $keyTab => $navTabItem) { ?>
                    <?php
                        if(!empty($navTabItem['params']['right']) && !have_right_or($navTabItem['params']['right'])){
                            continue;
                        }
                    ?>
                    <div class="dashboard-nav-full__item">
                        <div class="dashboard-nav-full__ttl">
                            <?php echo $navTabItem['params']['title']; ?>
                        </div>

                        <ul class="dashboard-nav-list dashboard-nav-full__list">
                        <?php foreach ($navTabItem['items'] as $navItemKey => $navItem) {?>
                            <?php
                                if (!empty($navItem['right']) && !have_right_or($navItem['right'])) {
                                    continue;
                                }
                            ?>
                            <li class="dashboard-nav-list__item dashboard-nav-full__list-item" data-name="<?php echo $navItemKey;?>">
                                <a
                                    <?php if (!empty($navItem['popup'])) { ?>
                                        class="link js-fancybox"
                                        data-type="ajax"
                                        data-src="<?php echo $navItem['link']; ?>"
                                        data-title="<?php echo $navItem['popup']; ?>"
                                        <?php if (!empty($navItem['popup_width'])) { ?>
                                            data-mw="<?php echo (int)$navItem['popup_width'] + 60; ?>"
                                        <?php } ?>
                                    <?php } else { ?>
                                        class="link <?php echo $navItem['add_class'] ? $navItem['add_class'] : '' ?>"
                                        href="<?php echo $navItem['external_link'] ? $navItem['external_link'] : $navItem['link']; ?>"
                                        <?php echo !empty($navItem['target']) ? 'target="' .$navItem['target']. '"' : '';?>
                                    <?php } ?>
                                    data-name="<?php echo $navItemKey; ?>"
                                    data-tab="<?php echo $keyTab; ?>">
                                    <i class="ep-icon ep-icon_<?php echo $navItem['icon'];?>"></i>
                                    <span class="txt-b"><?php echo $navItem['title'];?></span>
                                </a>
                            </li>
                        <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="epuser-subline-template__footer">
        <ul class="nav nav-tabs nav--borders js-nav-tavs" role="tablist">
            <li class="nav__item">
                <a
                    class="nav__link active js-tab-btn call-action"
                    data-js-action="tabs:show-content"
                    href="#header-toggle-quickmenu"
                    role="tab"
                >
                    <i class="ep-icon ep-icon_arrow-left "></i>
                    <?php echo translate('dashboard_quick_menu'); ?>
                </a>
                <a
                    class="nav__link js-tab-btn call-action"
                    data-js-action="tabs:show-content"
                    href="#header-toggle-fullmenu"
                    role="tab"
                >
                    <?php echo translate('dashboard_full_menu'); ?>
                    <i class="ep-icon ep-icon_arrow-right "></i>
                </a>
            </li>
        </ul>
    </div>
</div>
