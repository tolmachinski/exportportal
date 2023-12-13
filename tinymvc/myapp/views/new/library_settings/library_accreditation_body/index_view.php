<?php
	if (!empty($list_accreditation)) {
        views()->display('new/library_settings/script_get_email_view');
	}

	views()->display('new/two_mobile_buttons_view');
?>

<div  class="title-public pt-0 mt-50">
	<h2 class="title-public__txt title-public__txt--26">Business Accreditation Body</h2>
</div>

<div class="ep-tinymce-text pb-5">
	<p>Accreditation Body section helps businesses find certified registrars in foreign countries. These organizations approve and confirm that businesses meet standard requirements.</p>
	<p>To find certified registrars, please select a country in the search column.</p>
</div>

<?php if (!empty($list_accreditation)) { ?>
	<ul class="lib-list">
		<?php foreach ($list_accreditation as $item_accreditation) { ?>
			<li class="lib-list__item">
				<?php if (!empty($item_accreditation['body'])) { ?>
					<div class="lib-list__ttl"><?php echo $item_accreditation['body']; ?></div>
				<?php } ?>

				<?php if (!empty($item_accreditation['contact'])) { ?>
					<div class="lib-list__user">
						<i class="ep-icon ep-icon_user-ok"></i>
						<?php echo $item_accreditation['contact']; ?>
					</div>
				<?php } ?>

				<?php if (!empty($item_accreditation['address'])) { ?>
					<div class="lib-list__addr">
						<?php if (!empty($item_accreditation['country'])) {?>
							<img
                                class="image"
                                width="24"
                                height="24"
                                src="<?php echo getCountryFlag($item_accreditation['country']); ?>"
                                alt="<?php echo $item_accreditation['country']; ?>"
                                title="<?php echo $item_accreditation['country']; ?>"
                            />
							<?php echo $item_accreditation['country']; ?>,
						<?php } ?>

						<?php echo $item_accreditation['address']; ?>
					</div>
				<?php } ?>

				<div class="lib-list__params">
					<?php if (!empty($item_accreditation['phone'])) { ?>
						<div class="lib-list__param">
							<i class="ep-icon ep-icon_phone"></i>
							<span class="text-nowrap"><?php echo $item_accreditation['phone']; ?></span>
						</div>
					<?php } ?>

					<?php if (!empty($item_accreditation['email'])) { ?>
						<div class="lib-list__param">
							<i class="ep-icon ep-icon_envelope-stroke"></i>
							<a class="link call-function" data-item_id="<?php echo $item_accreditation['id_acc']; ?>" data-callback="get_email" href="#">Email</a>
						</div>
					<?php } ?>

					<?php if (!empty($item_accreditation['url_site'])) { ?>
						<div class="lib-list__param">
							<i class="ep-icon ep-icon_globe-stroke"></i>
							<a class="link" href="<?php echo $item_accreditation['url_site']; ?>" target="_blank">Site link</a>
						</div>
					<?php } ?>
				</div>
			</li>
		<?php } ?>
	</ul>

	<div class="clearfix"><?php views()->display('new/paginator_view'); ?></div>
<?php } else { ?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Sorry, we couldn't find any results for this search.</div>
<?php } ?>


