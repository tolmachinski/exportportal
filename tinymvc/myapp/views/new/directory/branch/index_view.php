<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "Organization",
	"url": "<?php echo __SITE_URL . 'branch/' . strForUrl($company['name_company']) . '-' . $company['id_company']; ?>",
	"name": "<?php echo $company['name_company']; ?>",
    "logo": "<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main'); ?>",
	"description": "<?php echo strip_tags(truncWords($company['description_company'])); ?>",
	<?php if ($company['rating_count_company'] > 0) { ?>
	"aggregateRating": {
		"@type": "AggregateRating",
		"ratingValue": "<?php echo $company['rating_company']; ?>",
		"reviewCount": "<?php echo $company['rating_count_company']; ?>"
	},
	<?php } ?>
	"member": {
		"@type": "OrganizationRole",
		"member": {
			"@type": "Person",
			"name": "<?php echo $user_main['fname'] . ' ' . $user_main['lname']; ?>"
		},
		"roleName": "Seller"
	},
	"address": {
		"@type": "PostalAddress",
		"addressLocality": "<?php echo $company['country'] ?><?php if (!empty($company['state'])) { ?>,<?php echo $company['state']; ?><?php } ?><?php if (!empty($company['city'])) { ?>,<?php echo $company['city']; ?><?php } ?>",
		"postalCode": "<?php echo $company['zip_company']; ?>",
		"streetAddress": "<?php echo $company['address_company']; ?>"
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
    <a itemprop="url" href="<?php echo __SITE_URL . 'branch/' . strForUrl($company['name_company']) . '-' . $company['id_company']; ?>"><?php echo $company['name_company']; ?></a>
    <h2 itemprop="name"><?php echo $company['name_company']; ?></h2>
    <img
        itemprop="logo"
        src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main'); ?>"
        alt="<?php echo $user_main['name_company']; ?>"/>

    <div itemprop="description">
        <?php echo strip_tags(truncWords($company['description_company'])); ?>
    </div>

    <?php if ($company['rating_count_company'] > 0) { ?>
        <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/aggregateRating">
            <span itemprop="ratingValue"><?php echo $company['rating_company']; ?></span>
            <span itemprop="reviewCount"><?php echo $company['rating_count_company'] ?></span>
        </div>
    <?php } ?>

    <div itemprop="member" itemscope itemtype="http://schema.org/OrganizationRole">
        <div itemprop="member" itemscope itemtype="http://schema.org/Person">
            <span itemprop="name"><?php echo $user_main['fname'] . ' ' . $user_main['lname']; ?></span>
        </div>
        <span itemprop="roleName">Seller</span>
    </div>

    <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
        <span itemprop="addressLocality">
        <?php echo $company['country'] ?><?php if (!empty($company['state'])) { ?>,
                <?php echo $company['state']; ?><?php } ?><?php if (!empty($company['city'])) { ?>,
                <?php echo $company['city']; ?><?php } ?></span> <span itemprop="streetAddress"><?php echo $company['address_company']; ?></span>
        <span itemprop="postalCode"><?php echo $company['zip_company']; ?></span>
    </div>
</div>

<div class="hide-mn-767">
    <?php views('new/directory/list_item_view', array('item' => $company));?>
    <div class="row pt-25 pb-50">
        <div class="col-6 pr-5">
            <a class="btn btn-light btn-block btn-panel-left fancyboxSidebar fancybox" data-title="<?php echo $company['name_company'];?>" href="#main-flex-card__fixed-left">
                <i class="ep-icon ep-icon_menu"></i>
                Menu
            </a>
        </div>
        <div class="col-6 pl-5"></div>
    </div>
</div>

<?php if (!empty($company['description_company'])) { ?>
    <div class="title-public pt-0">
        <h2 class="title-public__txt">Description</h2>
    </div>

    <div class="note ep-tinymce-text" <?php echo addQaUniqueIdentifier('page__branch__company-text') ?>>
        <?php echo $company['description_company']; ?>
    </div>
<?php } ?>

<?php if (!empty($company['video_company'])) { ?>
    <div class="title-public">
        <h2 class="title-public__txt">Company Overview</h2>
    </div>

    <a class="ppersonal-company-video wr-video-link fancybox.iframe fancyboxVideo" href="<?php echo get_video_link($company['video_company_code'], $company['video_company_source']); ?>" data-title="Company Overview">
        <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
        <img class="image" <?php echo addQaUniqueIdentifier('page__branch__company-video-bg') ?> src="<?php echo $videoImagePath; ?>" alt="<?php echo $company['name_company']; ?>">
    </a>
<?php } ?>

<?php if (!empty($company['pictures'])) { ?>
    <div class="title-public">
        <h2 class="title-public__txt">Pictures</h2>
    </div>

    <ul class="ppersonal-pictures">
        <?php
        $count_pictures = count($company['pictures']);
        foreach ($company['pictures'] as $key => $photo) {
            $company_image = getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $photo['photo_name']), 'company_branches.photos');
            $company_thumb = getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $photo['photo_name']), 'company_branches.photos', array( 'thumb_size' => 2 ));
        ?>
            <li class="ppersonal-pictures__item <?php echo ($key > 2) ? 'display-n' : ''; ?>">
                <a class="link fancyboxGallery" rel="galleryUser" href="<?php echo $company_image; ?>" data-title="<?php echo $company['name_company']; ?>" title="<?php echo $company['name_company']; ?>">
                    <img class="image" <?php echo addQaUniqueIdentifier('page__branch__company-picture') ?> src="<?php echo $company_thumb; ?>" alt="<?php echo $company['name_company']; ?>"/>
                </a>
            </li>
            <?php if ($count_pictures > 3 && $key == 2) { ?>
                <li class="ppersonal-pictures__item call-function" data-callback="personalPicturesMore">
                    <a class="ppersonal-pictures__more" <?php echo addQaUniqueIdentifier('page__branch__company-more-picture-btn') ?> href="#">
                        + <?php echo ($count_pictures - 3); ?>
                        <span>photos</span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } ?>
