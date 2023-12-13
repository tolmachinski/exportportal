<?php
	if (!empty($list_inspection_agency)) {
        tmvc::instance()->controller->view->display('new/library_settings/script_get_email_view');
    }
?>

<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">List of Inspection Agencies</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>Export Portal helps you find authorized, accredited agencies that are responsible for performing equipment inspections and certifications during the manufacturing process. Authorized inspection agencies can include jurisdictional authorities, insurance companies, and independent third-party inspection organizations.</p>
    <p>To narrow your search, please enter a country in the search section.</p>
</div>


<?php if (!empty($list_inspection_agency)) { ?>
    <?php if ($list_sort_by) { ?>

        <div class="minfo-save-search pt-20 pb-5">
            <div class="minfo-save-search__item">
                <span class="minfo-save-search__ttl">Sort by</span>
                <div class="dropdown show dropdown--select">
                    <a class="dropdown-toggle"
                       href="#"
                       role="button"
                       id="list-sorter"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false">
                        <?php echo isset($sort_by) ? $list_sort_by[$sort_by] : $list_sort_by['company-asc'] ; ?>
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
        <?php foreach ($list_inspection_agency as $item_inspection_agency) { ?>
            <li class="lib-list__item" <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-item"); ?>>
                <?php if (!empty($item_inspection_agency['company'])) { ?>
                    <div class="lib-list__ttl" <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-title"); ?>><?php echo $item_inspection_agency['company']; ?></div>
                <?php } ?>

                <?php if (!empty($item_inspection_agency['address'])) { ?>
                    <div class="lib-list__addr" <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-address"); ?>>
                        <?php if(!empty($item_inspection_agency['country'])) {?>
                            <img
                                class="image"
                                width="24"
                                height="24"
                                src="<?php echo getCountryFlag($item_inspection_agency['country']); ?>"
                                alt="<?php echo $item_inspection_agency['country']; ?>"
                                title="<?php echo $item_inspection_agency['country']; ?>"
                                <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-address-flag"); ?>
                            />
                            <span <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-address-text"); ?>><?php echo $item_inspection_agency['country']; ?>,
                        <?php } else { ?>
                            <span <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-address-text"); ?>>
                        <?php } ?>
                        <?php echo $item_inspection_agency['address']; ?></span>
                    </div>
                <?php } ?>


                <?php if (!empty($item_inspection_agency['services_provided'])) { ?>
                    <div class="lib-list__txt" <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-description"); ?>>
                        <?php echo $item_inspection_agency['services_provided']; ?>
                    </div>
                <?php } ?>


                <div class="lib-list__params">
                    <?php if (!empty($item_inspection_agency['phone'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_phone"></i>
                            <span class="text-nowrap" <?php echo addQaUniqueIdentifier("library-inspection-agency__agency-phone"); ?>><?php echo $item_inspection_agency['phone']; ?></span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_inspection_agency['email'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_envelope-stroke"></i>
                            <a class="link call-function" data-item_id="<?php echo $item_inspection_agency['id_ia']; ?>" data-callback="get_email" href="#">Email</a>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_inspection_agency['url_site'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_globe-stroke"></i>
                            <a class="link" href="<?php echo $item_inspection_agency['url_site']; ?>" target="_blank">Site link</a>
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
