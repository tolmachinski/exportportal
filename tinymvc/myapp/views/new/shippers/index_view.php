<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "Organization",
	"url": "<?php echo __SITE_URL.'shipper/'.strForUrl($company['co_name']).'-'.$company['id']; ?>",
	"name": "<?php echo $shipper['co_name'];?>",
    "logo": "<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main', array( 'thumb_size' => 1 ));?>",
	"description": "<?php echo strip_tags(truncWords($shipper['description'])); ?>",
	"member": {
		"@type": "OrganizationRole",
		"member": {
			"@type": "Person",
			"name": "<?php echo $user['fname'].' '.$user['lname']; ?>"
		},
		"roleName": "Freight Forwarder"
	},
	"address": {
		"@type": "PostalAddress",
		"addressLocality": "<?php echo $address['country']?><?php if(!empty($address['state'])){?>,<?php echo $address['state']; ?><?php }?><?php if(!empty($address['city'])){?>,<?php echo $address['city']; ?><?php }?>",
		"postalCode": "<?php echo $shipper['zip'];?>",
		"streetAddress": "<?php echo $shipper['address'];?>"
	}
}
</script>

<script>
    function personalPicturesMore($btn){
        $btn.closest('.ppersonal-pictures').find('.display-n').fadeIn();
        $btn.remove();
    }
</script>

<div class="display-n" itemscope itemtype="http://schema.org/Organization">
    <a itemprop="url" href="<?php echo __SITE_URL . 'shipper/' . strForUrl($company['co_name']) . '-' . $company['id']; ?>"><?php echo $shipper['co_name']; ?></a>
    <h2 itemprop="name"><?php echo $shipper['co_name']; ?></h2>
    <img
        itemprop="logo"
        src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 )); ?>"
        alt="<?php echo $shipper['co_name']; ?>"/>

    <div itemprop="description">
        <?php echo strip_tags(truncWords($shipper['description'])); ?>
    </div>

    <div itemprop="member" itemscope itemtype="http://schema.org/OrganizationRole">
        <div itemprop="member" itemscope itemtype="http://schema.org/Person">
            <span itemprop="name"><?php echo $user['fname'] . ' ' . $user['lname']; ?></span>
        </div>
        <span itemprop="roleName">Freight Forwarder</span>
    </div>

    <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<span itemprop="addressLocality">
			<?php echo $address['country'] ?><?php if (!empty($address['state'])) { ?>,
                <?php echo $address['state']; ?><?php } ?><?php if (!empty($address['city'])) { ?>,
                <?php echo $address['city']; ?><?php } ?>
		</span> <span itemprop="streetAddress"><?php echo $shipper['address']; ?></span> <span itemprop="postalCode"><?php echo $shipper['zip']; ?></span>
    </div>
</div>

<div class="hide-mn-767">
    <?php tmvc::instance()->controller->view->display('new/shippers/directory/list_item_view', array('shipper' => $shipper));?>
    <div class="pt-25 pb-50">
        <a class="btn btn-light btn-block btn-panel-left fancyboxSidebar fancybox" data-title="<?php echo $shipper['co_name'];?>" href="#main-flex-card__fixed-left">
            <i class="ep-icon ep-icon_menu"></i>
            Menu
        </a>
    </div>
</div>

<?php if (!empty($shipper['description'])) { ?>
    <div class="title-public pt-0">
        <h2 class="title-public__txt" <?php echo addQaUniqueIdentifier('shipper_description_title'); ?>>Description</h2>
    </div>

    <div class="note lh-20 txt-break-word" <?php echo addQaUniqueIdentifier('shipper_description_text'); ?>>
        <?php echo $shipper['description']; ?>
    </div>
<?php } ?>

<?php if (!empty($countries_by_continents)) {
    $count_all = 0;
    ?>
    <div class="title-public">
        <h2 class="title-public__txt" <?php echo addQaUniqueIdentifier('shipper_countries_title'); ?>>Countries</h2>
    </div>

    <div class="clearfix">
        <?php if (!$worldwide) { ?>
            <div>
                <ul class="nav mt-0 mb-0 simple-tabs clearfix" role="tablist">
                    <?php
                    $activeClass = 'active';
                    foreach ($countries_by_continents as $continent) {
                        if ($continent['count']) {
                            $count_all++;
                            ?>
                            <li class="simple-tabs__item">
                                <a class="link p-0 <?php echo $activeClass; ?>" href="#continent-<?php echo $continent['id']; ?>" aria-controls="title" role="tab" data-toggle="tab">
                                    <?php echo $continent['name']; ?>
                                </a>
                            </li>
                            <?php
                            $activeClass = '';
                        }
                    }
                    ?>
                </ul>

                <div class="tab-content nav-info clearfix">
                    <?php
                    $activeClass = 'show active';
                    foreach($countries_by_continents as $continent) {
                        if ($continent['count']) {
                            ?>
                            <div role="tabpanel" class="tab-pane fade <?php echo $activeClass; ?>" id="continent-<?php echo $continent['id']; ?>">
                                <ul class="countries-tab row m-0">
                                    <?php foreach ($continent['countries'] as $country) { ?>
                                        <li class="col-tn-12 col-6 col-md-4 countries-tab__item">
                                            <span class="countries-tab__text">
                                                <img
                                                    class="image"
                                                    width="24"
                                                    height="24"
                                                    src="<?php echo getCountryFlag($country['country']); ?>"
                                                    alt="<?php echo $country['country']; ?>"
                                                >
                                                <span class="countries-tab__country"><?php echo $country['country']; ?></span>
                                            </span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php
                            $activeClass = '';
                        }
                    }
                    ?>
                </div>
            </div>

            <?php if (!$count_all) { ?>
                <div class="info-alert-b">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span <?php echo addQaUniqueIdentifier('shipper_countries_text'); ?>>The freight forwarder did not select any countries.</span>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="info-alert-b">
                <i class="ep-icon ep-icon_globe"></i>
                <span>Worldwide</span>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if (!empty($shipper_pictures)) { ?>
    <div class="title-public">
        <h2 class="title-public__txt" <?php echo addQaUniqueIdentifier('shipper_pictures_title'); ?>>Pictures</h2>
    </div>

    <ul class="ppersonal-pictures">
        <?php
        $count_pictures = count($shipper_pictures);
        foreach ($shipper_pictures as $key => $photo) {
            $company_image = getDisplayImageLink(array('{ID}' => $photo['id_shipper'], '{FILE_NAME}' => $photo['picture']), 'shippers.photos');
            $company_thumb = getDisplayImageLink(array('{ID}' => $photo['id_shipper'], '{FILE_NAME}' => $photo['picture']), 'shippers.photos', array( 'thumb_size' => 2 ));
            ?>
            <li class="ppersonal-pictures__item <?php echo ($key > 2) ? 'display-n' : ''; ?>">
                <a class="link fancyboxGallery" rel="galleryUser" href="<?php echo $company_image; ?>" data-title="<?php echo $shipper['co_name']; ?>" title="<?php echo $shipper['co_name']; ?>">
                    <img class="image" <?php echo addQaUniqueIdentifier('shipper_pictures_image'); ?> src="<?php echo $company_thumb; ?>" alt="<?php echo $shipper['co_name']; ?>"/>
                </a>
            </li>
            <?php if ($count_pictures > 3 && $key == 2) { ?>
                <li class="ppersonal-pictures__item call-function" data-callback="personalPicturesMore">
                    <a class="ppersonal-pictures__more" href="#">
                        + <?php echo ($count_pictures - 3); ?>
                        <span>photos</span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } ?>
