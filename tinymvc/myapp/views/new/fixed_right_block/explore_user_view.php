<?php if(admin_logged_as_user()){?>
    <?php if(!isset($webpackData)){?>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/footer/explore_user.js'); ?>"></script>
    <?php }?>

    <button
        class="fixed-rigth-block__item notranslate confirm-dialog"
        data-callback="exit_explore_user"
        data-js-action="footer:exit-explore-user"
        data-message="Are you sure you want to exit from this user session?"
        title="<?php echo translate('fixed_right_btn_access_title', array('[USER]' => user_name_session()));?>"
        type="button"
    >
        <span class="fixed-rigth-block__item-icon bg-orange"><i class="ep-icon ep-icon_incognito fs-22"></i> <span>Logout</span></span>
        <span class="fixed-rigth-block__item-text">
            <span class="fixed-rigth-block__item-text-inner">
                <?php echo translate('fixed_right_btn_access_title', array('[USER]' => user_name_session()));?>
            </span>
        </span>
    </button>
<?php }?>
