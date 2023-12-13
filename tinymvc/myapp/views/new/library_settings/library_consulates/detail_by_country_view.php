<?php
	if (!empty($consulates)) {
		views()->display('new/library_settings/script_get_email_view');
    }
?>

<?php views()->display('new/two_mobile_buttons_view'); ?>

<div  class="title-public pt-0 mt-50">
    <h2 class="title-public__txt title-public__txt--26">Data storage and contacts for your business List of Consulates</h2>
</div>

<div class="ep-tinymce-text pb-5">
    <p>Consulates section provides information on official representatives of the government of one state in the territory of another, which can help facilitate trade and cooperation between the people of the two countries. Consulates provide assistance with bureaucratic issues to citizens in business and international transactions.</p>
</div>

<?php if (!empty($consulates)) { ?>
    <ul class="lib-list">
        <?php foreach ($consulates as $consulate) { ?>
            <li class="lib-list__item">
                <?php if (!empty($consulate['mission_name'])) {?>
                    <div class="lib-list__ttl">
                        <?php echo $consulate['mission_name'];?>
                    </div>
                <?php }?>

                <?php if (!empty($consulate['address'])) {?>
                    <div class="lib-list__addr">
                        <?php if (!empty($consulate['country_consulate'])) {?>
                            <img
                                width="24"
                                height="24"
                                src="<?php echo getCountryFlag($consulate['country_consulate']); ?>"
                                alt="<?php echo $consulate['country_consulate']; ?>"
                                title="<?php echo $consulate['country_consulate']; ?>"
                            />
                            <?php echo $consulate['country_consulate']; ?>,
                        <?php } ?>

                        <?php echo $consulate['address'];?>
                    </div>
                <?php }?>

                <?php if (!empty(cleanInput($consulate['head']))) {?>
                    <div class="lib-list__additional">
                        <i class=" ep-icon ep-icon_user txt-blue2"></i>
                        <?php echo $consulate['head'];?>
                    </div>
                <?php }?>

                <div class="lib-list__params">
                    <?php if (!empty($consulate['phone'])) {?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_phone"></i>
                            <span class="text-nowrap"><?php echo $consulate['phone']; ?></span>
                        </div>
                    <?php }?>

                    <?php if (!empty($consulate['email'])) {?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_envelope-stroke"></i>
                            <a class="link call-function" data-item_id="<?php echo $consulate['id_consulate']; ?>" data-callback="get_email" href="#">Email</a>
                        </div>
                    <?php }?>

                    <?php if (!empty($consulate['url_site'])) {?>
                        <div class="lib-list__param">
                            <i class="ep-icon ep-icon_globe-stroke"></i>
                            <a class="link" href="<?php echo $consulate['url_site']; ?>" target="_blank">Site link</a>
                        </div>
                    <?php }?>
                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="clearfix"><?php views()->display('new/paginator_view');?></div>
<?php } else { ?>
    <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Sorry, we couldn't find any results for this search.</div>
<?php } ?>
