<div class="mep-header-switch-account__ttl"><?php echo translate('login_select_account_label'); ?></div>
<div class="mep-header-switch-account__sub-ttl"><?php echo translate('login_select_account'); ?>:</div>
<?php
    views()->display('new/authenticate/choose_list_view', array('class_select_account' => 'select-account-list--mobile'));
?>

<?php views()->display('new/authenticate/clean_session_view', array('choose_another_account' => true));?>