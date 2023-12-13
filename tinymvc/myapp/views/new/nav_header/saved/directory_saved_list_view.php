<div class="js-epuser-saved-content epuser-popup__overflow mt-30">
<?php if(!empty($companies)){?>
	<ul id="saved_list" class="companies companies--2">
		<?php foreach($companies as $item){?>
			<li class="companies-wr">
				<?php $item_link = getCompanyURL($item);?>
				<div class="companies__item flex-card">
                    <div class="companies__img-wr flex-card__fixed">
                        <div class="companies__img image-card3">
                            <a class="link" href="<?php echo $item_link;?>" target="_blank">
                                <img
                                    class="image"
                                    src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_company'], '{FILE_NAME}' => $item['logo_company']), 'companies.main', array( 'thumb_size' => 1 )); ?>"
                                    alt="<?php echo $item['name_company'];?>"/>
                            </a>
                        </div>
					</div>
					<div class="companies__detail flex-card__float">
						<div class="companies__ttl" title="<?php echo $item['name_company'];?>">
							<a class="link" itemprop="url" href="<?php echo $item_link;?>">
								<span itemprop="name"><?php echo $item['name_company'];?></span>
							</a>
						</div>

						<?php $groupName = $item['is_verified'] ? $item['user_group_name'] : trim(str_replace('Verified', '', $item['user_group_name']));?>
                        <div class="companies__group <?php echo userGroupNameColor($item['user_group_name']);?>">
                            <?php echo $groupName . $item['user_group_name_sufix'];?>
                        </div>

                        <?php if($item['type_company'] === 'branch' && !empty($item['main_company'])){?>
                            <div class="text-nowrap">
                                <?php $parent_company_link = getCompanyURL($item['main_company']); ?>
                                <?php echo translate('seller_card_branch_of_company', array('{{COMPANY_NAME}}' => '<a class="pl-5" href="' . $parent_company_link . '" title="' . cleanOutput($item['main_company']['name_company']) . '">' . cleanOutput($item['main_company']['name_company']) . '</a>'));?>
                            </div>
                        <?php }else{?>
                            <div class="companies__date" title="<?php echo getDateFormat($item['registered_company'], 'Y-m-d H:i:s', 'M Y');?>"><?php echo translate('text_member_from_date', array('[[DATE]]' => getDateFormat($item['registered_company'], 'Y-m-d H:i:s', 'M Y')));?></div>
                        <?php }?>

						<div class="companies__actions">
							<div class="companies__country">
								<img
                                    class="image"
                                    width="24"
                                    height="24"
                                    src="<?php echo getCountryFlag($item['country']);?>"
                                    alt="<?php echo $item['country'];?>"
                                    title="<?php echo $item['country'];?>"
                                />
								<span class="text"><?php echo $item['country'];?></span>
							</div>

							<div class="dropdown">
								<a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu">
									<a class="dropdown-item" href="<?php echo $item_link; ?>/products">
										<i class="ep-icon ep-icon_box-in"></i>
										<span class="txt">See products</span>
									</a>

									<?php echo !empty($item['btnChat']) ? $item['btnChat'] : ''; ?>

                                    <?php if (__CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) { ?>
                                    <button
                                        class="dropdown-item call-function call-action"
                                        title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_email_company_tag_title', null, true); ?>"
                                        data-callback="userSharePopup"
                                        data-js-action="user:share-popup"
                                        data-type="company"
                                        data-item="<?php echo $item['id_company']; ?>"
                                        type="button"
                                    >
                                        <i class="ep-icon ep-icon_share-stroke3"></i> <?php echo translate('seller_home_page_sidebar_menu_dropdown_share_company'); ?>
                                    </button>
                                    <?php } ?>

                                    <a
                                        class="dropdown-item call-function call-action"
                                        data-callback="remove_header_company"
                                        data-js-action="saved:remove-header-company"
                                        data-company="<?php echo $item['id_company'];?>"
                                        href="#"
                                    >
										<i class="ep-icon ep-icon_favorite"></i>
										<span class="txt">Favorited</span>
									</a>
								</div>
							</div>

						</div>

					</div>
				</div>
			</li>
		<?php } ?>
	</ul>
<?php }else{ ?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>No saved search info.</span></div>
<?php }?>
</div>

<?php if(!empty($companies)){?>
	<div class="js-epuser-saved-page epuser-subline-additional2">
		<div></div>

		<div class="flex-display">
			<?php
				app()->view->display('new/nav_header/pagination_block_view', array(
					'count_total' => $counter,
					'per_page' => $per_page,
					'cur_page' => $page,
					'type' => 'directory'
				));
			?>
		</div>
	</div>
<?php }?>
