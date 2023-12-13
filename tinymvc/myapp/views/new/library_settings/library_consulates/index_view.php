<?php views()->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">List of Consulates</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>Here we have information on official foreign government representatives in different countries. To facilitate trade and promote cooperation between businesses, consulates assist people with bureaucratic issues in international transactions.</p>
    <p>To find an organization to partner with, please select a country in the search column.</p>
</div>

<ul class="countries-tab row m-0">
    <?php foreach($countries_by_char as $countries) { ?>
        <?php foreach($countries as $country){?>
            <li class="col-tn-12 col-6 col-md-4 countries-tab__item">
                <a class="link" href="<?php echo __SITE_URL . $library_detail; ?>/country/<?php echo strForUrl($country['country_main'] . ' ' . $country['id_country']); ?>">
                    <img
                        class="image"
                        width="32"
                        height="32"
                        src="<?php echo getCountryFlag($country['country_main']); ?>"
                        alt="<?php echo $country['country_main']; ?>"
                        title="<?php echo $country['country_main']; ?>"
                    >
                    <?php echo $country['country_main']; ?>
                </a>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
