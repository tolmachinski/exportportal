<div class="js-account-registration-another-<?php echo $input_name;?> account-registration-another__group">
    <div class="account-registration-another__group-title">
        <span class="account-registration-another__group-title-text"><?php echo $title;?> <?php echo translate('register_account_word'); ?></span>
    </div>

    <?php if(
            $input_name == 'seller'
            || $input_name == 'manufacturer'
    ){?>
        <?php views()->display('new/register/another_account_seller_inputs_view', array('input_name' => $input_name));?>
    <?php }else if($input_name == 'buyer'){?>
        <?php views()->display('new/register/step_2_buyer_inputs_view', array('suffix' => '_additional_buyer'));?>
    <?php }?>
</div>
