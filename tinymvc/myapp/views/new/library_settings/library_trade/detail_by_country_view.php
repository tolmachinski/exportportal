<?php
    if (!empty($trades)) {
        views()->display('new/library_settings/script_get_email_view');
    }

    views()->display('new/two_mobile_buttons_view');
?>

<div class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">Data storage and contacts for your business Trade Performance</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>Trade performance section represents results of the country's trading in different fields. This mechanism is used to evaluate a trader's return and risk tolerance or lack thereof. This data can help understand trade competitiveness of a country or trade section.</p>
</div>

<?php if (!empty($trades)) {?>
    <ul class="lib-list">
        <?php foreach ($trades as $trade) { ?>
            <li class="lib-list__item">
                <?php if (!empty($trade['industry'])) { ?>
                    <div class="lib-list__ttl"><?php echo $trade['industry']; ?></div>
                <?php } ?>

                <?php if (!empty($trade['country'])) { ?>
                    <div class="lib-list__addr">
                        <?php if (!empty($trade['country'])) {?>
                            <img
                                width="24"
                                height="24"
                                src="<?php echo getCountryFlag($trade['country']); ?>"
                                alt="<?php echo $trade['country']; ?>"
                                title="<?php echo $trade['country']; ?>"
                            />
                            <?php echo $trade['country']; ?>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="lib-list__columns">
                    <div class="lib-list__column">
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">Total export</div>
                            <div class="lib-list__column-value"><?php echo $trade['total_export']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">Total import</div>
                            <div class="lib-list__column-value"><?php echo $trade['total_import']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">World export</div>
                            <div class="lib-list__column-value"><?php echo $trade['world_export']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">World import</div>
                            <div class="lib-list__column-value"><?php echo $trade['world_import']; ?></div>
                        </div>
                    </div>
                    <div class="lib-list__column">
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">Export</div>
                            <div class="lib-list__column-value"><?php echo $trade['export']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">Import</div>
                            <div class="lib-list__column-value"><?php echo $trade['import']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name">Trade</div>
                            <div class="lib-list__column-value"><?php echo $trade['trade']; ?></div>
                        </div>
                    </div>
                    <div class="lib-list__column">
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name"><i class="ep-icon ep-icon_arrow-ne fs-10 w-15 tac"></i> Growth export</div>
                            <div class="lib-list__column-value"><?php echo $trade['growth_export']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name"><i class="ep-icon ep-icon_arrow-sw fs-10 w-15 tac"></i> Growth import</div>
                            <div class="lib-list__column-value"><?php echo $trade['growth_import']; ?></div>
                        </div>
                        <div class="lib-list__column-item">
                            <div class="lib-list__column-name"><i class="ep-icon ep-icon_arrows-updown2"></i> Net trade</div>
                            <div class="lib-list__column-value"><?php echo $trade['net_trade']; ?></div>
                        </div>
                    </div>

                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="clearfix"><?php tmvc::instance()->controller->view->display('new/paginator_view'); ?></div>
<?php } else { ?>
    <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Sorry, we couldn't find any results for this search.</div>
<?php } ?>
