<?php if (admin_logged_as_user()) {?>
    <button
        class="fixed-rigth-block__item fixed-rigth-block__item--incognito notranslate js-confirm-dialog"
        data-js-action="footer:exit-explore-user"
        data-message="Are you sure you want to exit from this user session?"
        title="<?php echo translate('fixed_right_btn_access_title', ['[USER]' => user_name_session()]); ?>"
    >
        <span class="fixed-rigth-block__item-icon">
            <i class="ep-icon ep-icon_incognito"></i>
        </span>
        <span class="fixed-rigth-block__item-text">
            <span class="fixed-rigth-block__item-text-inner">
                <?php echo translate('fixed_right_btn_access_title', ['[USER]' => user_name_session()]); ?>
            </span>
        </span>
    </button>
<?php }?>
