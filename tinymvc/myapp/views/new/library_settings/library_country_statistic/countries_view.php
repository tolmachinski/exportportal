<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<ul class="nav simple-tabs clearfix" role="tablist">
	<?php
	$activeClass = 'active';
	foreach($continents as $continent) {
	?>
		<li class="simple-tabs__item">
			<a class="link <?php echo $activeClass; ?>" href="#continent-<?php echo $continent['id_continent']; ?>" aria-controls="title" role="tab" data-toggle="tab">
                <?php echo $continent['name_continent']; ?>
            </a>
		</li>
	<?php
		$activeClass = '';
		}
	?>
</ul>

<div class="tab-content nav-info clearfix">
	<?php
	$activeClass = 'show active';
	foreach($continents as $continent) {
	?>
		<div role="tabpanel" class="tab-pane fade <?php echo $activeClass; ?>" id="continent-<?php echo $continent['id_continent']; ?>">
			<ul class="countries-tab row m-0">
				<?php
				foreach($countries as $country) {
					if ($country['id_continent'] != $continent['id_continent']) {
						continue;
					}
				?>
					<li class="col-tn-12 col-6 col-md-4 countries-tab__item">
						<a class="link" href="<?php echo __SITE_URL; ?>library_country_statistic/country/<?php echo strForURL($country['country'] . ' ' . $country['id']); ?>/">
							<img
                                width="32"
                                height="32"
                                src="<?php echo getCountryFlag($country['country']); ?>"
                                alt="<?php echo $country['country']; ?>"
                            >
							<?php echo $country['country']; ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<?php
		$activeClass = '';
	}
	?>
</div>

<div class="ep-tinymce-text ep-links-break pt-50 pb-50">
	<?php echo $bottom_text['text_block']; ?>
</div>
