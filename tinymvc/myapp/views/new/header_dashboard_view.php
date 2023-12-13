<div class="flex-card epuser-subline-template">
	<div class="epuser-subline-template__left">
		<div class="switch-account">
		<?php if(false !== ($account_link = getMyProfileLink())){?>
			<a class="switch-account__current" href="<?php echo $account_link;?>">
		<?php } else { ?>
			<div class="switch-account__current">
		<?php } ?>
                <?php if (session()->status === "restricted") { ?>
                    <span class="switch-account__current-restricted">
                        <?php echo widgetGetSvgIcon('restricted', 23, 23); ?>
                    </span>
                <?php } ?>

				<div class="switch-account__current-img image-card3 image-card3--full-default">
					<span class="link">
						<img
							class="js-replace-file-avatar js-lazy image"
							data-src="<?php echo getDisplayImageLink(array('{ID}' => session()->id, '{FILE_NAME}' => session()->user_photo), 'users.main', array( 'thumb_size' => 1, 'no_image_group' => group_session() ));?>"
                            src="<?php echo getLazyImage(100, 100); ?>"
                            alt="<?php echo session()->fname; ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
						/>
					</span>
				</div>
				<div class="switch-account__current-name" <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>>
                    <?php echo session()->fname.' '.session()->lname;?>
                </div>
				<div class="switch-account__current-company" <?php echo addQaUniqueIdentifier('global__user-info_company'); ?>>
                    <?php echo session()->name_company;?>
                </div>
				<div class="switch-account__current-group<?php echo userGroupNameColor(group_name_session());?>"><?php echo groupNameWithSuffix();?></div>
		<?php if(false !== ($account_link = getMyProfileLink())){?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>

		<?php
		if(session()->__isset('accounts')){
            foreach(session()->accounts as $account){?>
                <?php
                    if ($account['idu'] == session()->id) {
                        continue;
                    }
                ?>
				<a
					class="switch-account__another flex-card call-action"
                    data-js-action="dashboard:select-account"
					data-user="<?php echo $account['idu']; ?>"
				>
					<div class="switch-account__another-image flex-card__fixed image-card3 image-card3--full-default">
						<span class="link">
							<img
								class="image js-lazy"
								data-src="<?php echo getDisplayImageLink(array('{ID}' => $account['idu'], '{FILE_NAME}' => $account['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $account['user_group'] ));?>"
                                src="<?php echo getLazyImage(50, 50); ?>"
                                alt="<?php echo $account['user_name']; ?>"
                                <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
							/>
						</span>
					</div>

                    <?php if ($account['status'] === "restricted") { ?>
                        <span class="switch-account__another-restricted">
                            <?php echo widgetGetSvgIcon('restricted', 10, 10); ?>
                        </span>
                    <?php } ?>

					<div class="switch-account__another-info flex-card__float">
						<div class="switch-account__another-name" <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>>
                            <?php echo $account['user_name']; ?>
                        </div>
						<div class="switch-account__another-company" <?php echo addQaUniqueIdentifier('global__user-info_company'); ?>>
                            <?php echo $account['company_name'];?>
                        </div>
						<div class="switch-account__another-group<?php echo userGroupNameColor($account['gr_name']);?>"><?php echo $account['gr_name'] . $account['group_name_suffix'] ?? ''; ?></div>
					</div>
				</a>
				<?php
			}
		}?>

		<?php
		if(
				(count(session()->accounts ?? array()) < 3
				|| !isset(session()->accounts))
				&& checkRightSwitchGroup()
			){?>
			<a
				class="switch-account__another flex-card call-action"
                data-js-action="dashboard:add-accounts"
				href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>register/popup_forms/add_another_account"
				data-title="<?php echo translate('login_add_another_account', null, true); ?>"
			>
				<div class="switch-account__another-image flex-card__fixed image-card3 image-card3--full-default">
					<span class="link">
						<i class="ep-icon ep-icon_user-add"></i>
					</span>
				</div>
				<div class="switch-account__another-info flex-card__float">
					<div class="switch-account__another-name"><?php echo translate('login_add_another_account'); ?></div>
				</div>
			</a>
		<?php }?>

            <a
                class="switch-account__another flex-card call-action"
                data-js-action="dashboard:logout"
                href="<?php echo __SITE_URL . 'authenticate/logout' ?>">
                <div class="switch-account__another-image flex-card__fixed image-card3 image-card3--full-default">
                    <span class="link">
                        <i class="ep-icon ep-icon_logout pl-5"></i>
                    </span>
                </div>
                <div class="switch-account__another-info flex-card__float">
                    <div class="switch-account__another-name">Logout</div>
                </div>
            </a>
		</div>
	</div>

	<div class="flex-card__float epuser-subline-template__right">

		<div class="epuser-subline__header inputs-40">
			<ul class="nav nav-tabs nav--toggle nav--borders" role="tablist">
				<li class="nav-item flex-display">
				<?php if($complete_profile['total_completed'] < 100){?>
					<a class="nav-link active" href="#header-toggle-complete" aria-controls="title" role="tab" data-toggle="tab">
					    <?php echo translate('dashboard_complete_profile'); ?>
					</a>
					<a class="nav-link" href="#header-toggle-quickmenu" aria-controls="title" role="tab" data-toggle="tab">
					<?php echo translate('dashboard_quick_menu'); ?>
					</a>
				<?php }else{?>
					<a class="nav-link active" href="#header-toggle-quickmenu" aria-controls="title" role="tab" data-toggle="tab">
					<?php echo translate('dashboard_quick_menu'); ?>
					</a>
				<?php }?>
					<a
                        class="nav-link"
                        href="#header-toggle-fullmenu"
                        aria-controls="title"
                        role="tab"
                        data-toggle="tab"
                        <?php echo addQaUniqueIdentifier('global__dashboard-menu__full-menu-btn'); ?>
                    >
					    <?php echo translate('dashboard_full_menu'); ?>
					</a>
				</li>

				<li class="pb-5 flex-display">
					<a class="btn btn-light mr-10" href="<?php echo getUrlForGroup() . 'dashboard/customize_menu';?>">
					<?php echo translate('dashboard_customize_menu'); ?>
					</a>

					<a class="btn btn-light" href="<?php echo getUrlForGroup() . 'dashboard';?>">
					<?php echo translate('dashboard_go_to_dashboard'); ?>
					</a>
				</li>
			</ul>
		</div>

		<div class="tab-content tab-content--borders tab-content--navigation">
			<?php if($complete_profile['total_completed'] < 100){?>
				<div role="tabpanel" class="tab-pane fade show active" id="header-toggle-complete">
                    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_popup_complete_profile.css");?>" />
					<?php views()->display('new/complete_profile/complete_profile_list_view', ['complete_profile' => $complete_profile]); ?>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="header-toggle-quickmenu">
			<?php }else{?>
				<div role="tabpanel" class="tab-pane fade show active" id="header-toggle-quickmenu">
			<?php }?>

				<?php if(group_expired_session()){?>
					<div class="warning-alert-b mb-15">
						<i class="ep-icon ep-icon_warning-circle-stroke"></i>
						<span><?php echo translate('dashboard_upgrade_package_expired',
						array(
							'[[LINK_START]]' => '<a href="' .  __SITE_URL . 'upgrade">',
							'[[LINK_END]]' => '</a>'
						)); ?>
						</span>
					</div>
				<?php }?>

				<div id="js-dashboard-nav" class="flex-display">
					<div class="w-33-33pr">
						<ul class="dashboard-nav dashboard-nav--col">
							<li class="col1-cell1 dashboard-nav__item"></li>
							<li class="col1-cell2 dashboard-nav__item"></li>
                            <li class="col1-cell3 dashboard-nav__item"></li>
                            <li class="col1-cell4 dashboard-nav__item"></li>
                            <?php if(!group_expired_session()){?>
                                <li class="col1-cell5 dashboard-nav__item"></li>
                                <li class="col1-cell6 dashboard-nav__item"></li>
                                <li class="col1-cell7 dashboard-nav__item"></li>
                            <?php }?>
						</ul>
					</div>
                    <?php if(!group_expired_session()){?>
                        <div class="w-33-33pr">
                            <ul class="dashboard-nav dashboard-nav--col">
                                <li class="col2-cell1 dashboard-nav__item"></li>
                                <li class="col2-cell2 dashboard-nav__item"></li>
                                <li class="col2-cell3 dashboard-nav__item"></li>
                                <li class="col2-cell4 dashboard-nav__item"></li>
                                <li class="col2-cell5 dashboard-nav__item"></li>
                                <li class="col2-cell6 dashboard-nav__item"></li>
                                <li class="col2-cell7 dashboard-nav__item"></li>
                            </ul>
                        </div>
                        <div class="w-33-33pr">
                            <ul class="dashboard-nav dashboard-nav--col">
                                <li class="col3-cell1 dashboard-nav__item"></li>
                                <li class="col3-cell2 dashboard-nav__item"></li>
                                <li class="col3-cell3 dashboard-nav__item"></li>
                                <li class="col3-cell4 dashboard-nav__item"></li>
                                <li class="col3-cell5 dashboard-nav__item"></li>
                                <li class="col3-cell6 dashboard-nav__item"></li>
                                <li class="col3-cell7 dashboard-nav__item"></li>
                            </ul>
                        </div>
                    <?php }?>
				</div>

                <?php if (!empty($dashboardBanner)) { ?>
                    <?php views('new/banners/dashboard_banner_view'); ?>
                <?php } ?>

			</div>
			<div role="tabpanel" class="tab-pane fade" id="header-toggle-fullmenu">
				<?php if(group_expired_session()){?>
					<div class="warning-alert-b mb-15">
						<i class="ep-icon ep-icon_warning-circle-stroke"></i>
						<span><?php echo translate('dashboard_upgrade_package_expired',
						array(
							'[[LINK_START]]' => '<a href="' .  __SITE_URL . 'upgrade">',
							'[[LINK_END]]' => '</a>'
						)); ?></span>
					</div>
				<?php }?>

				<div class="dashboard-nav-full">
				<?php $nav_tabs = session()->menu_full; ?>
				<?php foreach ($nav_tabs as $key_tab => $nav_tab_item) { ?>
					<?php
						if(!empty($nav_tab_item['params']['right']) && !have_right_or($nav_tab_item['params']['right'])){
							continue;
						}
					?>
					<div class="dashboard-nav-full__item">
						<div class="dashboard-nav-full__ttl">
							<?php echo $nav_tab_item['params']['title']; ?>
						</div>

						<ul class="dashboard-nav dashboard-nav-full__list">
						<?php foreach ($nav_tab_item['items'] as $nav_item_key => $nav_item) {?>
							<?php
								if (!empty($nav_item['right']) && !have_right_or($nav_item['right'])) {
									continue;
                                }
							?>
							<li class="dashboard-nav__item dashboard-nav-full__list-item" data-name="<?php echo $nav_item_key;?>">
								<a
									<?php if (!empty($nav_item['popup'])) { ?>
										class="link fancybox.ajax fancyboxValidateModal"
										data-fancybox-href="<?php echo $nav_item['link']; ?>"
										data-title="<?php echo $nav_item['popup']; ?>"
										<?php if (!empty($nav_item['popup_width'])) { ?>
											data-mw="<?php echo $nav_item['popup_width']; ?>"
										<?php } ?>
									<?php } else { ?>
										class="link <?php echo $nav_item['add_class'] ? $nav_item['add_class'] : '' ?>"
                                        href="<?php echo $nav_item['external_link'] ? $nav_item['external_link'] : $nav_item['link']; ?>"
                                        <?php echo !empty($nav_item['target']) ? 'target="' .$nav_item['target']. '"' : '';?>
									<?php } ?>
									data-name="<?php echo $nav_item_key; ?>"
									data-tab="<?php echo $key_tab; ?>">
									<i class="ep-icon ep-icon_<?php echo $nav_item['icon'];?>"></i>
                                    <div class="txt-b"><?php echo $nav_item['title'];?>
                                        <?php if ($nav_item['new']) { ?>
                                            <span class="dashboard-nav__item-new">NEW</span>
                                        <?php } ?>
                                    </div>
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
</div>

<?php echo dispatchDynamicFragment(
    'dashboard:menu',
    [json_decode($custom_header_menu, true)]
); ?>
