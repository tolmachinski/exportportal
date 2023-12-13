<div id="js-account-form" class="account-registration account-registration-labels">
    <h1 class="account-registration__main-title">
        <?php echo translate('register_form_title', array('[USER]' => $register_ttl));?>
    </h1>

    <div id="js-wr-register-form">
        <?php app()->view->display('new/register/register_steps_view'); ?>
    </div>
</div>
