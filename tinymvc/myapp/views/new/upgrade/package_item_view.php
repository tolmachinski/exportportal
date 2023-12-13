<?php
    $classesItem = '';
    if ((int)$packagesItem['price_saved'] > 0) {
        $classesItem = ' upgrade-packages__item--year';
    }

    if (
        $itemType == 'current'
        || $itemType == 'downgrade'
    ) {
        $classesItem = ' upgrade-packages__item--verified';
    }
?>

<div class="upgrade-packages__item<?php echo $classesItem;?>">
    <div class="upgrade-packages__item-inner">
        <div class="upgrade-packages__title">
            <?php if ((int)$packagesItem['price_saved'] > 0) { ?>
            <div class="upgrade-packages__title-save-wr">
                <div class="upgrade-packages__title-save">
                    Save $<?php echo $packagesItem['price_saved']; ?>
                </div>
            </div>
            <?php } ?>

            <div class="upgrade-packages__title-txt">
                <?php
                    echo $packagesItem['short_title'];

                    if (!empty($packagesItem['period_name'])) {
                        echo ' ' . $packagesItem['period_name'];
                    }
                ?>
            </div>

            <?php if(
                isset($upgradeRequest)
                && !empty($upgradeRequest['date_expire'])
                && (int)$packagesItem['days'] === (int)$currentPackage['days']
            ){ ?>
                <div class="upgrade-packages__subtitle">
                    Expires on <?php echo getDateFormat($upgradeRequest['date_expire'], 'Y-m-d', 'j M, Y'); ?>
                </div>
            <?php } ?>
        </div>

        <div class="upgrade-packages__content">
            <div class="upgrade-packages__price">
                <?php if (isset($packagesItem['static_price'])) {?>
                    <div class="upgrade-packages__price-number">
                        <?php echo $packagesItem['static_price']; ?>
                    </div>
                <?php } else {?>
                    <div class="upgrade-packages__price-number">
                        <?php
                            $priceMonth = (int)$packagesItem['price_month'];
                            $priceTotal = (int)$packagesItem['price'];

                            if ($priceMonth > 0) {
                                echo '$'.$priceMonth;
                            }else if($priceTotal === 0){
                                echo 'free';
                            }else{
                                echo '$'.$priceTotal;
                            }
                        ?>
                    </div>
                    <?php if($priceTotal > 0){ ?>
                        <div class="upgrade-packages__price-txt">
                            <div class="upgrade-packages__price-period">/ <?php if($priceMonth > 0){ ?>month<?php }else{ ?>monthly<?php } ?></div>
                            <?php if($priceMonth > 0){ ?>
                                <div class="upgrade-packages__price-total">Total: $<?php echo (int)$packagesItem['price']; ?></div>
                            <?php }?>
                        </div>
                    <?php }?>
                <?php }?>
            </div>

            <p class="upgrade-packages__desc">
                <?php echo $packagesItem['short_description'];?>
            </p>

            <?php if($itemType == 'extend'){ ?>
                <a
                    class="btn btn-primary fancybox.ajax fancyboxValidateModal"
                    <?php echo addQaUniqueIdentifier("upgrade_monthly_extend_btn")?>
                    data-title="Extend Upgrade"
                    href="<?php echo __SITE_URL?>upgrade/popup_forms/process?package=<?php echo $packagesItem['idpack'];?>"
                >Extend upgrade</a>
            <?php }elseif($itemType == 'upgrade'){ ?>
                <a
                    class="js-call-upgrade-modal btn btn-primary fancybox.ajax fancyboxValidateModal"
                    <?php echo addQaUniqueIdentifier("upgrade_monthly_upgrade_btn")?>
                    data-title="Upgrade to <?php echo $packagesItem['gt_name'];?>"
                    href="<?php echo __SITE_URL?>upgrade/popup_forms/process/<?php echo $packagesItem['gr_to'];?>?package=<?php echo $packagesItem['idpack'];?>"
                >Upgrade Your Account</a>
            <?php }elseif($itemType == 'downgrade'){ ?>
                <a
                    class="btn btn-light confirm-dialog"
                    <?php echo addQaUniqueIdentifier("upgrade_monthly_downgrade_btn")?>
                    data-callback="callDowngradeAccount"
                    data-message="<?php echo translate('systmess_upgrade_downgrade_account_confirm_message', null, true);?>"
                    href="#"
                >Downgrade</a>
            <?php }elseif($itemType == 'current'){ ?>
                <span class="btn btn-light cur-default" <?php echo addQaUniqueIdentifier("upgrade_monthly_current_btn")?>>Your Current Package</span>
            <?php }?>
        </div>

        <div class="upgrade-packages__benefits">
            <ul class="upgrade-packages__benefits-list">
                <?php
                    $grTo = $packagesItem['gr_to'];

                    if (
                        $itemType == 'extend'
                        || $itemType == 'current'
                    ) {
                        $grTo = $idGroup;
                    }

                    foreach ($upgradeBenefits as $upgradeBenefitsItem) {
                        if (!in_array($grTo, $upgradeBenefitsItem['benefit_groups'])) {
                            continue;
                        }
                ?>
                    <li
                        class="upgrade-packages__benefits-item"
                    >
                        <div class="upgrade-packages__benefits-name">
                            <i class="upgrade-packages__benefits-ico-ok ep-icon ep-icon_ok-stroke2"></i> <?php echo $upgradeBenefitsItem['benefit_name']; ?>
                        </div>
                        <a
                            class="upgrade-packages__benefits-ico-info ep-icon ep-icon_info info-dialog"
                            data-title="<?php echo cleanOutput($upgradeBenefitsItem['benefit_name']); ?>"
                            data-message="<?php echo cleanOutput($upgradeBenefitsItem['benefit_text']); ?>"
                            href="#"
                        ></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
