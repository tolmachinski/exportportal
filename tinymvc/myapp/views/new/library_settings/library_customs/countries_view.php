<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<ul class="nav simple-tabs clearfix">
	<?php $activeClass = 'active';?>
	<?php foreach($continents as $continent) {?>
		<?php if(!array_key_exists($continent['id_continent'], $countries)){continue;}?>
		<li class="simple-tabs__item" role="presentation">
			<a class="link <?php echo $activeClass; ?>" href="#continent-<?php echo $continent['id_continent']; ?>" data-toggle="tab"><?php echo $continent['name_continent']; ?></a>
		</li>
		<?php $activeClass = '';?>
	<?php }?>
</ul>

<div class="tab-content nav-info clearfix">
	<?php $activeClass = 'show active';?>
	<?php foreach($continents as $continent) {?>
		<?php if(!array_key_exists($continent['id_continent'], $countries)){continue;}?>
		<div class="tab-pane fade <?php echo $activeClass; ?>" id="continent-<?php echo $continent['id_continent']; ?>">
			<ul class="countries-tab row m-0">
				<?php foreach($countries[$continent['id_continent']] as $country) {?>
					<li class="col-tn-12 col-6 col-md-4 countries-tab__item">
						<a class="link" target="_blank" href="<?php echo __SITE_URL; ?>library_customs/country/<?php echo strForURL($country['country'] . ' ' . $country['id']); ?>">
							<img
                                class="image"
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
		<?php $activeClass = '';?>
	<?php }?>
</div>
