<div class="popover popover-password js-popover-password" role="tooltip" style="display:none;">
    <div class="pass-strength-popup js-pass-strength-popup">
        <?php views()->display('new/epl/authenticate/password_security_view'); ?>

        <div class="pass-strength-popup__progress js-pass-strength-popup__progress"></div>
        <div class="pass-strength-popup__txt">
            <strong><?php echo translate('form_label_password_strength'); ?></strong>
            <span class="pass-strength-popup__verdict js-pass-strength-popup__verdict"></span>
        </div>
        <div class="pass-strength-popup__errors js-pass-strength-popup__errors"></div>
    </div>
</div>
