<div class="info-header2">
	<div class="info-header2__detail">
		<h1 class="info-header2__ttl tt-uppercase"><?php echo $cr_domain['country'];?></h1>

		<img
            width="48"
            height="48"
            src="<?php echo getCountryFlag($cr_domain['country']);?>"
            alt="<?php echo $cr_domain['country'];?>"
        >

		<p class="info-header2__txt"><?php echo $cr_domain['short_description'];?></p>

		<a class="btn btn-outline-light w-225" href="<?php echo __SITE_URL;?>learn_more"><?php echo translate('langing_button_learn_more');?></a>
	</div>
	<img class="image" src="<?php echo __IMG_URL . $cr_domain_path .'/'. $cr_domain['domain_photo'];?>" alt="Header <?php echo $cr_domain['country'];?>">
</div>
