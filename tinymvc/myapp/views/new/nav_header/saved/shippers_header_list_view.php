<div class="js-epuser-saved-content epuser-popup__overflow mt-30">
	<?php if(!empty($shippers)){?>
		<ul class="companies companies--2">
			<?php foreach($shippers as $shipper){?>
				<li class="companies-wr">
					<div class="companies__item flex-card">
                        <div class="companies__img-wr flex-card__fixed">
                            <div class="companies__img image-card3">
                                <a class="link" href="<?php echo __SITE_URL . 'shipper/' . strForUrl($shipper['co_name']) . '-' . $shipper['id']; ?>" target="_blank">
                                    <img
                                        class="image"
                                        itemprop="logo"
                                        src="<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main', array( 'thumb_size' => 1 ));?>"
                                        alt="<?php echo $item['name_company']; ?>"/>
                                </a>
                            </div>
						</div>
						<div class="companies__detail flex-card__float">
							<div class="companies__ttl" title="<?php echo $shipper['co_name']; ?>">
								<a class="link" itemprop="url" href="<?php echo __SITE_URL . 'shipper/' . strForUrl($shipper['co_name']) . '-' . $shipper['id']; ?>">
									<span itemprop="name"><?php echo $shipper['co_name']; ?></span>
								</a>
                            </div>

                            <div
                                class="companies__date"
                                title="<?php echo formatDate($shipper['create_date'], 'M Y');?>"
                            >
                                <?php echo translate('text_member_from_date', array('[[DATE]]' => getDateFormat($shipper['create_date'], 'Y-m-d H:i:s', 'M Y')));?>
                            </div>

							<div class="companies__actions">
                                <div class="companies__country">
                                    <img
                                        class="image"
                                        width="24"
                                        height="24"
                                        src="<?php echo getCountryFlag($shipper['country']); ?>"
                                        alt="<?php echo $shipper['country']; ?>"
                                        title="<?php echo $shipper['country']; ?>"
                                    />
                                    <span class="text"><?php echo $shipper['country']; ?></span>
                                </div>

                                <div class="dropdown">
                                    <a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

								<div class="dropdown-menu">
									<?php echo !empty($shipper['btnChat']) ? $shipper['btnChat'] : ''; ?>

                                    <?php if (__CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) { ?>
									<a
                                        class="dropdown-item js-fancybox fancybox.ajax fancyboxValidateModal"
                                        data-type="ajax"
                                        data-mw="600"
                                        data-title="Send this freight forwarder to your followers"
                                        href="<?php echo __SHIPPER_URL;?>shipper/popup_forms/share_company/<?php echo $shipper['id'];?>"
                                        data-title="Share this freight forwarder to followers"
                                        title="Share this freight forwarder to followers"
                                    >
										<i class="ep-icon ep-icon_share-stroke"></i>
										<span>Share</span>
									</a>
									<a
                                        class="dropdown-item js-fancybox fancybox.ajax fancyboxValidateModal"
                                        data-type="ajax"
                                        data-mw="600"
                                        data-title="Send this freight forwarder's info to your friends"
                                        href="<?php echo __SHIPPER_URL;?>shipper/popup_forms/email_company/<?php echo $shipper['id'];?>"
                                        data-title="Share this freight forwarder to your friends by email"
                                        title="Share this freight forwarder to your friends by email"
                                    >
										<i class="ep-icon ep-icon_envelope-send"></i>
										<span>Email</span>
									</a>
                                    <?php } ?>

                                    <a
                                        class="dropdown-item call-function call-action"
                                        data-callback="remove_header_shipper"
                                        data-js-action="saved:remove-header-shipper"
                                        data-shipper="<?php echo $shipper['id'];?>"
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

<?php if(!empty($shippers)){?>
	<div class="js-epuser-saved-page epuser-subline-additional2">
		<div></div>

		<div class="flex-display">
			<?php
				app()->view->display('new/nav_header/pagination_block_view', array(
					'count_total' => $counter,
					'per_page' => $per_page,
					'cur_page' => $curr_page,
					'type' => 'shippers'
				));
			?>
		</div>
	</div>
<?php }?>
