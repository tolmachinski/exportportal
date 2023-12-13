<h3 class="payment-form__title">
	<span class="payment-form__text"><?php echo translate('billing_documents_step3_label_text', null, true); ?></span>
	<span class="pull-right lh-22">
        <?php echo cleanOutput(payment_method_i18n($method, 'method')); ?>
        <i class="mr-10 pull-left ico-pay-method i-<?php echo strForUrl($method['method']); ?>"
            title="<?php echo cleanOutput(payment_method_i18n($method, 'method')); ?>"></i>
    </span>
</h3>

<?php echo $content_step; ?>
