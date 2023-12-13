<div class="js-epuser-saved-content epuser-popup__overflow mt-30">
	<?php if(!empty($contacts)){?>
		<ul id="my-contacts" class="ppersonal-followers">
			<?php foreach($contacts as $contact){?>
				<li class="ppersonal-followers__item flex-card">
					<div class="ppersonal-followers__img image-card2 flex-card__fixed">
						<span class="link">
							<img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $contact['idu'], '{FILE_NAME}' => $contact['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $contact['user_group'] ));?>" alt="<?php echo cleanOutput($company['name_company']);?>">
						</span>
					</div>
					<div class="ppersonal-followers__detail flex-card__float">
						<div class="ppersonal-followers__name mt-9">
							<a class="link" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($contact['user_name'] . ' ' . $contact['idu'])?>"><?php echo cleanOutput($contact['user_name']); ?></a>
						</div>

						<div class="ppersonal-followers__bottom">
                            <div class="ppersonal-followers__group<?php echo userGroupNameColor($contact['gr_name']);?>">
                                <?php echo cleanOutput($contact['gr_name']); ?>
                            </div>

                            <div class="dropdown mt-5">
								<a class="dropdown-toggle" href="#" data-toggle="dropdown">
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>
								<div class="dropdown-menu">
									<?php echo !empty($contact['btnChat']) ? $contact['btnChat'] : ''; ?>

                                    <a
                                        class="dropdown-item"
                                        href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($contact['user_name'] . ' ' . $contact['idu'])?>"
                                    >
										<i class="ep-icon ep-icon_info-stroke"></i>
										<span>Details</span>
									</a>
                                    <a
                                        class="dropdown-item call-function call-action"
                                        data-callback="remove_header_contact"
                                        data-js-action="saved:remove-header-contact"
                                        data-id="<?php echo $contact['idu'];?>"
                                        href="#"
                                    >
										<i class="ep-icon ep-icon_trash-stroke"></i>
										<span>Remove contact</span>
									</a>
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
<?php if(!empty($contacts)){?>
	<div class="js-epuser-saved-page epuser-subline-additional2">
		<div></div>

		<div class="flex-display">
			<?php
				app()->view->display('new/nav_header/pagination_block_view', array(
					'count_total' => $counter,
					'per_page' => $per_page,
					'cur_page' => $curr_page,
					'type' => 'contact'
				));
			?>
		</div>
	</div>
<?php }?>
