<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">
        Global Rules for Worldwide Exporting and Importing
    </h2>
</div>

<div class="ep-tinymce-text pb-5">
    <div class="minfo-about-imginfo__txt-ttl pb-30"><?php echo translate('about_us_international_standards_block_1_header'); ?></div>

    <p><?php echo translate('about_us_international_standards_block_1_1'); ?></p>
    <p><?php echo translate('about_us_international_standards_block_2'); ?></p>
</div>


<div class="tab-content nav-info clearfix">
    <ul class="row m-0">
        <?php foreach ($countries_by_char as $char_key => $countries) { ?>
            <?php foreach ($countries as $country) { ?>
                <li class="col-tn-12 col-6 col-md-4 countries-tab__item">
                    <a class="link" href="<?php echo __SITE_URL ?>library_international_standards/detail/country/<?php echo strForUrl($country['country'] . ' ' . $country['id']); ?>">
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
        <?php } ?>
    </ul>
</div>
