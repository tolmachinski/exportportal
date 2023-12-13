<div id="js-popup-select-accounts">
    <label class="fs-18 lh-26 mb-0"><?php echo translate('login_select_account');?>:</label>

    <?php views()->display('new/authenticate/choose_list_view');?>

    <?php if(!empty($referer)){?>
        <input type="hidden" name="referer" value="<?php echo $referer; ?>"/>
    <?php }?>
    <input type="hidden" name="remember_input" value="<?php echo $remember; ?>"/>
</div>

<?php views()->display('new/authenticate/clean_session_view', array('choose_another_account' => true)); ?>