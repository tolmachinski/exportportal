<?php
if (!empty($list_icc_country)) {
    tmvc::instance()->controller->view->display('new/library_settings/script_get_email_view');
}
?>

<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">International Chamber of Commerce</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>International Chamber of Commerce is the largest business organization in the world. With thousands of member companies in over 130 countries, we make it easy to discover new partnerships in any field of interest.</p>
    <p>To find an organization to partner with, please select a country in the search column.</p>
</div>

<?php if (!empty($list_icc_country)) { ?>
    <?php if ($list_sort_by) { ?>
        <div class="minfo-save-search pt-20 pb-5">
            <div class="minfo-save-search__item">
                <span class="minfo-save-search__ttl">Sort by</span>
                <div class="dropdown show dropdown--select">
                    <a class="dropdown-toggle" href="#" role="button" id="list-sorter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo isset($sort_by) ? $list_sort_by[$sort_by] : $list_sort_by['agencies-asc'] ; ?>
                        <i class="ep-icon ep-icon_arrow-down"></i>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="list-sorter">
                        <?php foreach ($list_sort_by as $sort_key => $item_label) { ?>
                            <a class="dropdown-item" href="<?php echo $page_link; ?>?sort_by=<?php echo $sort_key; ?>"><?php echo $item_label; ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <ul class="lib-list">
        <?php foreach ($list_icc_country as $item_icc_country) { ?>
            <li class="lib-list__item">
                <?php if (!empty($item_icc_country['agencies'])) { ?>
                    <div class="lib-list__ttl">
                        <?php echo $item_icc_country['agencies']; ?>
                    </div>
                <?php } ?>

                <?php if (!empty($item_icc_country['country'])) { ?>
                    <div class="lib-list__addr">
                        <img
                            class="image"
                            width="24"
                            height="24"
                            src="<?php echo getCountryFlag($item_icc_country['country']); ?>"
                            alt="<?php echo $item_icc_country['country']; ?>"
                            title="<?php echo $item_icc_country['country']; ?>"
                        />
                        <?php echo $item_icc_country['country']; ?>
                    </div>
                <?php } ?>

                <div class="lib-list__params">
                    <?php if (!empty($item_icc_country['phone'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_phone"></i>
                            <span class="text-nowrap"><?php echo $item_icc_country['phone']; ?></span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_icc_country['email'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_envelope-stroke"></i>
                            <a class="link call-function" data-item_id="<?php echo $item_icc_country['id_icc']; ?>" data-callback="get_email" href="#">Email</a>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_icc_country['url_site'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_globe-stroke"></i>
                            <a class="link" href="<?php echo $item_icc_country['url_site']; ?>" target="_blank">Site link</a>
                        </div>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="clearfix"><?php tmvc::instance()->controller->view->display('new/paginator_view'); ?></div>
<?php } else { ?>
    <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Sorry, we couldn't find any results for this search.</div>
<?php } ?>


