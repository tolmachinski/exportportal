<?php
if (!empty($list_lawyer)) {
    tmvc::instance()->controller->view->display('new/library_settings/script_get_email_view');
}
?>

<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">List of Lawyers</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>Export Portal allows you to find lawyers and law firms all over the world practicing export import law, international trade law and business law.</p>
    <p>To find the organization in the country of interest, please enter a country in the “Filter by Country” search bar.</p>
</div>


<?php if (!empty($list_lawyer)) { ?>
    <?php if ($list_sort_by) { ?>
        <div class="minfo-save-search pt-20 pb-5">
            <div class="minfo-save-search__item">
                <span class="minfo-save-search__ttl">Sort by</span>
                <div class="dropdown show dropdown--select">
                    <a class="dropdown-toggle" href="#" role="button" id="list-sorter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo $list_sort_by[$sort_by]; ?>
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
        <?php foreach ($list_lawyer as $item_lawyer) { ?>
            <li class="lib-list__item">
                <?php if (!empty($item_lawyer['company'])) { ?>
                    <div class="lib-list__ttl"><?php echo $item_lawyer['company']; ?></div>
                <?php } ?>

                <?php if (!empty($item_lawyer['address'])) { ?>
                    <div class="lib-list__addr">
                        <?php echo $item_lawyer['address']; ?>
                    </div>
                <?php } ?>

                <div class="lib-list__params">
                    <?php if (!empty($item_lawyer['phone'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_phone"></i>
                            <span class="text-nowrap"><?php echo $item_lawyer['phone']; ?></span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_lawyer['email'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_envelope-stroke"></i>
                            <a class="link call-function" data-item_id="<?php echo $item_lawyer['id_law']; ?>" data-callback="get_email" href="#">Email</a>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_lawyer['url_site'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_globe-stroke"></i>
                            <a class="link" href="<?php echo $item_lawyer['url_site']; ?>" target="_blank">Site link</a>
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
