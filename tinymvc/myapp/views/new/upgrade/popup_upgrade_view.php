<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="confirm_start_upgrade">
		<div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('upgrade_page_package_period_label');?></label>

            <?php
                $issetFreePackage = false;

                if(
                    !is_null($dateFreePackage)
                    && $activeFreeCertification = filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)
                ){
                    $issetFreePackage = true;
                }
            ?>

            <?php if($issetFreePackage){?>
                <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('upgrade_page_free_upgrade_description', array('{{DATE}}' => getDateFormat($dateFreePackage, 'Y-m-d', 'j M, Y'), '{{START_HTML_1}}' => '<div class="txt-medium">', '{{END_HTML_1}}' => '</div>'));?></span></div>
            <?php }?>
            <?php $isset_active_packages = false;
                foreach($upgrade_packages as $id_group => $upgrade_package){
                    foreach($upgrade_package['prices'] as $package_price){
                        if ($package_price['is_active'] && !$package_price['is_already_used']) {
                            $isset_active_packages = true;
                        }
            ?>
                    <label
                        class="input-label custom-radio <?php echo ($issetFreePackage && $package_price['price'] !== '0.00') || $package_price['is_already_used'] ? 'txt-gray' : '';?>"
                        <?php echo addQaUniqueIdentifier("upgrade_popup-upgrade_price_radio")?>
                    >
                        <input
                            class="validate[required]"
                            <?php echo ($issetFreePackage && $package_price['price'] !== '0.00') || $package_price['is_already_used'] ? 'disabled="disabled"' : '';?>
                            type="radio"
                            name="package"
                            value="<?php echo $package_price['id_package'];?>"
                            <?php if(isset($package_price['selected']) && (bool) $package_price['selected']){?>checked<?php }?>
                        />
                        <span
                            class="<?php echo ($issetFreePackage && $package_price['price'] !== '0.00') ? 'txt-line-through' : '';?> custom-radio__text"
                        >
                            <?php echo empty((int) $package_price['price']) ? 'FREE' : '$' . get_price($package_price['price'], false) . ' / per ' . $package_price['period'];?>
                        </span>
                        <?php if ($package_price['is_already_used']) {?>
                            <span class="fs-12 txt-orange lh-24">already used</span>
                        <?php }?>
                    </label>
                <?php }?>
            <?php }?>

            <?php if(!empty($aditional_documents)){?>
                <label class="input-label input-label--required"><?php echo translate('upgrade_page_documents_needed_label');?></label>
                <div class="ep-middle-text">
                    <?php echo translate('upgrade_page_documents_needed_description');?>
                </div>

                <table class="main-data-table mt-15 mb-15">
                    <tbody>
                        <?php foreach($aditional_documents as $document){?>
                            <tr>
                                <td class="vam">
                                    <a class="ep-icon ep-icon_info info-dialog" data-content="#info-dialog__document-<?php echo (int) $document['id_document'];?>-details" title="What is: <?php echo cleanOutput($document['document_title']);?>?" data-title="What is: <?php echo cleanOutput($document['document_title']);?>?"></a>
                                    <?php echo cleanOutput($document['document_title']);?>
                                    <?php if (!empty($document['country_title'])) { ?>
                                        (<?php echo cleanOutput($document['country_title']);?>)
                                    <?php } ?>
                                    <div class="display-n" id="info-dialog__document-<?php echo (int) $document['id_document'];?>-details">
                                        <?php echo cleanOutput($document['document_description']);?>
                                    </div>
                                </td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            <?php }?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-left">
                <button class="btn btn-dark call-function" <?php echo addQaUniqueIdentifier("upgrade_popup-upgrade_cancel_btn")?> data-callback="closeFancyBox" type="button">Cancel</button>
            </div>
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier("upgrade_popup-upgrade_confirm_btn")?>  type="submit" <?php echo $isset_active_packages ? '' : 'disabled';?>>Confirm</button>
            </div>
		</div>
	</form>
</div>
<script>
    <?php if(null !== $extend_upgrade && $extend_upgrade){?>
        var confirmExtendUpgrade = function(form){
            var $form = $(form);
            var $wr_loader = $form.closest('.js-modal-flex');

            $.ajax({
                type: 'POST',
                url: __site_url + 'upgrade/ajax_operations/extend',
                data: $form.serialize(),
                beforeSend: function(){
                    showLoader($wr_loader);
                },
                dataType: 'json',
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        window.scroll(0, 0);
                        location.reload(true);
                    } else{
                        hideLoader($wr_loader);
                        systemMessages( resp.message, resp.mess_type );
                    }
                }
            });
        }

        mix(window, { confirm_start_upgrade: confirmExtendUpgrade }, false);
    <?php } else{?>
        var confirmStartUpgrade = function(form){
            var $form = $(form);
            var $wr_loader = $form.closest('.js-modal-flex');

            $.ajax({
                type: 'POST',
                url: __site_url + 'upgrade/ajax_operations/start',
                data: $form.serialize(),
                beforeSend: function(){
                    showLoader($wr_loader);
                },
                dataType: 'json',
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        window.scroll(0, 0);
                        location.reload(true);
                    } else{
                        hideLoader($wr_loader);
                        systemMessages( resp.message, resp.mess_type );
                    }
                }
            });
        }

        mix(window, { confirm_start_upgrade: confirmStartUpgrade }, false);
    <?php }?>
</script>
