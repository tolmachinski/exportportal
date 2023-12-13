<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<ul class="nav nav-tabs nav--borders" role="tablist">
	<li class="nav-item">
		<a class="nav-link active" href="#headquarters-contact-b" <?php echo addQaUniqueIdentifier('seller-contact__nav-headquarters'); ?> aria-controls="title" role="tab" data-toggle="tab"><?php echo translate('seller_contact_tab_headquarters_title');?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="#contacts-contact-b" <?php echo addQaUniqueIdentifier('seller-contact__nav-contacts'); ?> aria-controls="title" role="tab" data-toggle="tab"><?php echo translate('seller_contact_tab_contacts_title');?></a>
	</li>
	<?php if (!empty($branches)) { ?>
		<li class="nav-item">
			<a class="nav-link" href="#branches-contact-b" <?php echo addQaUniqueIdentifier('seller-contact__nav-branches'); ?> aria-controls="title" role="tab" data-toggle="tab"><?php echo translate('seller_contact_tab_branches_title');?></a>
		</li>
	<?php } ?>
</ul>

<div class="tab-content tab-content--borders">
	<div role="tabpanel" class="tab-pane fade show active" id="headquarters-contact-b">
		<div class="ppersonal-branches">
			<div class="ppersonal-branches__item ppersonal-branches__item--full">
				<div class="ppersonal-branches__ttl">
					<h3 class="ppersonal-branches__ttl-txt">
						<?php echo translate('seller_contact_tab_headquarters_address');?>
					</h3>
				</div>
				<div class="ppersonal-branches__row-wr">
					<div class="ppersonal-branches__row">
						<div class="ppersonal-branches__val tel" <?php echo addQaUniqueIdentifier("seller-contact__headquarters_address"); ?>>
							<?php if (!empty($address_parts = array_filter(array($company['country'], $company['state'], $company['city'], $company['address_company'])))) { ?>
								<?php echo cleanOutput(implode(', ', $address_parts)); ?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div role="tabpanel" class="tab-pane fade" id="contacts-contact-b">
		<div class="ppersonal-branches">
			<?php if (!empty($services_contacts)) { ?>
				<?php foreach ($services_contacts as $item) { ?>
					<div class="ppersonal-branches__item">
						<div class="ppersonal-branches__ttl">
							<h3 class="ppersonal-branches__ttl-txt" <?php echo addQaUniqueIdentifier("seller-contact__contacts_title"); ?>>
								<?php echo cleanOutput($item['title_service']); ?>
							</h3>
						</div>

						<div class="ppersonal-branches__row-wr">
							<div class="ppersonal-branches__row">
								<div class="ppersonal-branches__val" <?php echo addQaUniqueIdentifier("seller-contact__contacts_description"); ?>>
									<span id="service-<?php echo cleanOutput($item['id_service']); ?>-info">
										<?php echo cleanOutput($item['info_service']); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			<?php } else { ?>
				<div class="ppersonal-branches__item ppersonal-branches__item--full">
					<div class="ppersonal-branches__row-wr">
						<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_contact_tab_contacts_no_departments');?></div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php if (!empty($branches)) { ?>
		<div role="tabpanel" class="tab-pane fade" id="branches-contact-b">
			<div class="ppersonal-branches">
				<?php foreach($branches as $item) { ?>
					<div class="ppersonal-branches__item">
						<div class="ppersonal-branches__ttl">
							<h3 class="ppersonal-branches__ttl-txt" <?php echo addQaUniqueIdentifier("seller-contact__branches_title"); ?>>
								<a href="<?php echo getUrlForGroup('branch/' . strForURL($item['name_company']) . '-' . $item['id_company']); ?>"
									class="link"
									id="company-<?php echo cleanOutput($item['id_company']); ?>-title">
									<?php echo cleanOutput($item['name_company']); ?>
								</a>
							</h3>
						</div>

						<div class="ppersonal-branches__row-wr">
							<div class="ppersonal-branches__row">
								<div class="ppersonal-branches__val" <?php echo addQaUniqueIdentifier("seller-contact__branches_address"); ?>>
									<?php if (!empty($address_parts = array_filter(array($item['full_country_city']['country'], $item['full_country_city']['city'], $item['address_company'])))) { ?>
										<?php echo cleanOutput(implode(', ', $address_parts)); ?>
									<?php } else { ?>
										&mdash;
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
</div>

