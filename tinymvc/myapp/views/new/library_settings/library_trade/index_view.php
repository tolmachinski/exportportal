<?php views()->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">Trade Performance by Country</h2>
</div>

<div class="ep-tinymce-text">
    <p>This section represents the results of a country's import/export trade in different fields. Evaluate a trader's return and risk tolerance or lack thereof with the data included here. This data can help you understand the trade competitiveness of a country or trade industry.</p>
    <p>To find an organization to partner with, please select a country in the search column.</p>
</div>

<ul class="nav simple-tabs clearfix" role="tablist">
    <?php $activeClass = 'active';?>
    <?php foreach($continents as $continent) {?>
        <li class="simple-tabs__item">
            <a class="link <?php echo $activeClass;?>" href="#continent-<?php echo $continent['id_continent']; ?>" aria-controls="title" role="tab" data-toggle="tab">
                <?php echo $continent['name_continent']; ?>
            </a>
        </li>
        <?php $activeClass = '';?>
    <?php }?>
</ul>

<div class="tab-content nav-info clearfix">
    <?php $activeClass = 'show active';?>
    <?php foreach($continents as $continent) {?>
        <div role="tabpanel" class="tab-pane fade <?php echo $activeClass; ?>" id="continent-<?php echo $continent['id_continent'];?>">
            <ul class="countries-tab row m-0">
                <?php
                    if (empty($countries_by_continents[$continent['id_continent']])) {
                        continue;
                    }
                ?>

                <?php foreach($countries_by_continents[$continent['id_continent']] as $country) {?>
                    <li class="col-tn-12 col-6 col-md-4 countries-tab__item">
                        <a class="link" href="<?php echo __SITE_URL . $library_search . '/country/' . strForUrl($country['country'] . ' ' . $country['id']);?>" title="<?php echo cleanOutput($country['country']);?>">
                            <img
                                class="image"
                                width="32"
                                height="32"
                                src="<?php echo getCountryFlag($country['country']); ?>"
                                alt="<?php echo $country['country'];?>"
                            >
                            <?php echo $country['country']; ?>
                        </a>
                    </li>
                <?php }?>
            </ul>
        </div>
        <?php $activeClass = '';?>
    <?php }?>
</div>
