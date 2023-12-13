<h3 class="payment-form__title">
    <span class="payment-form__text"><?php echo translate('billing_documents_step2_label_text'); ?></span>
</h3>
<ul class="methods-pay">
    <?php $first = 0; ?>
    <?php foreach($pay_methods as $key => $method) { ?>
        <?php if($method['enable']) { ?>
            <li <?php echo addQaUniqueIdentifier("upgrade_payment_method-pay_btn")?> class="methods-pay__item" data-method="<?php echo $method['id']; ?>">
                <i class="ico-pay-method i-<?php echo strForURL($method['method']); ?>"></i>
                <div class="methods-pay__name"><?php echo cleanOutput(payment_method_i18n($method, 'method')); ?></div>
            </li>
            <?php $first++; ?>
        <?php } ?>
    <?php } ?>
</ul>

<div class="fs-14 txt-red mt-15">
    <?php echo translate('billing_documents_step1_additional_information', array('[LINK]' => __SITE_URL . 'payments/bank_requisites')); ?>
</div>
