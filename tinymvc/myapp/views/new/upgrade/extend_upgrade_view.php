<script>
    $(function() {
        // if(existCookie('upgrade_open_bill_popup')){
        //     var $btn_bill = $('.btn[data-open="upgrade_open_bill_popup"]');

        //     if($btn_bill.length){
        //         $btn_bill.trigger('click');
        //     }
        // }

        function paymentCallback(data){
            closeFancyBox();
            showLoader($('#js-upgrade-list'), 'Processing...');
            // removeCookie('upgrade_open_bill_popup');
            location.reload(true);
        }

        mix(window, {
            payment_callback: paymentCallback
        }, false);
    });

    var scrollToBlock = function(btn){
        var $this = $(btn);
        var block = $this.data('block');
        scrollToElement(block, -1);
    };
</script>

<div class="upgrade-extend-header">
    <div class="upgrade-extend-header__gr">
        <img
            class="image"
            src="<?php echo cleanOutput($group['thumbnail']); ?>"
            alt="<?php echo cleanOutput($group['name']); ?>"
        >
    </div>

    <div class="upgrade-extend-header__txt"><?php echo translate('upgrade_extend_header_ttl'); ?></div>

    <h2 class="upgrade-extend-header__name"><?php echo cleanOutput($group['name']); ?></h2>

    <div class="upgrade-extend-header__date">
        <?php if(!is_null($upgrade_request['date_expire'])){?>
            <?php echo translate('upgrade_extend_header_date_text_1'); ?> <?php echo getDateFormat($upgrade_request['date_expire'], 'Y-m-d', 'j M, Y'); ?>
        <?php } else {?>
            <?php echo translate('upgrade_extend_header_date_text_2'); ?>
        <?php }?>
    </div>

    <a
        class="upgrade-extend-header__btn  btn btn-primary fancyboxValidateModal fancybox.ajax"
        <?php echo addQaUniqueIdentifier("upgrade_current-plan_extend-upgrade_btn")?>
        href="<?php echo __SITE_URL;?>upgrade/popup_forms/process"
        data-title="Extend package period"
    ><?php echo translate('upgrade_extend_header_btn_extend'); ?></a>
</div>

<div
    class="content-upgrade"
>
    <?php views()->display('new/upgrade/packages_view');?>
    <?php views()->display('new/upgrade/upgrade_circle_view');?>
</div>
