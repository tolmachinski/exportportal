<?php
	if (!empty($list_importer_exporter)) {
        views()->display('new/library_settings/script_get_email_view');
    }
?>

<?php views()->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">List of Importers &amp; Exporters</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>This section connects businesses to manufacturers and companies in countries you want to transact with.</p>
</div>

<?php if (!empty($list_importer_exporter)) { ?>
    <ul class="lib-list">
        <?php foreach ($list_importer_exporter as $item_importer_exporter) { ?>
            <li class="lib-list__item">
                <?php if (!empty($item_importer_exporter['company'])) { ?>
                    <div class="lib-list__ttl"><?php echo $item_importer_exporter['company']; ?></div>
                <?php } ?>

                <?php if (!empty($item_importer_exporter['address'])) { ?>
                    <div class="lib-list__addr">
                        <?php echo $item_importer_exporter['address']; ?>
                    </div>
                <?php } ?>

                <div class="lib-list__params">
                    <?php if (!empty($item_importer_exporter['phone'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_phone"></i>
                            <span class="text-nowrap"><?php echo $item_importer_exporter['phone']; ?></span>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_importer_exporter['email'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_envelope-stroke"></i>
                            <a class="link call-function" data-item_id="<?php echo $item_importer_exporter['id_ie']; ?>" data-callback="get_email" href="#">Email</a>
                        </div>
                    <?php } ?>

                    <?php if (!empty($item_importer_exporter['url_site'])) { ?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_globe-stroke"></i>
                            <a class="link" href="<?php echo $item_importer_exporter['url_site']; ?>" target="_blank">Site link</a>
                        </div>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="clearfix">
        <?php views()->display('new/paginator_view');?>
    </div>
<?php } else { ?>
    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i> Sorry, we couldn't find any results for this search.
    </div>
<?php } ?>
