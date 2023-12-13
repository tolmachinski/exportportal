<?php if ($current_page != 'all') { ?>
    <div class="ask-community__block">
        <div>
            <div class="ask-community__title"><?php echo translate('community_find_answer_text'); ?></div>
            <p class="ask-community__subtitle"><?php echo translate('community_ask_question_here_text'); ?></p>
        </div>
        <div>
            <a
                class="btn btn-primary btn--50 mnw-200 mnw-290-sm fancybox.ajax fancyboxValidateModal call-action"
                <?php if(!logged_in()){ ?>
                    data-js-action="lazy-loading:login"
                    data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
                    data-mw="400"
                    href="<?php echo __SITE_URL . 'login'; ?>"
                <?php } else {?>
                    data-title="<?php echo translate('community_ask_a_question_text', null, true); ?>"
                    data-mw="535"
                    href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms/add_question'; ?>"
                <?php } ?>
            >
                <?php echo translate('community_ask_community_button_text'); ?>
            </a>
        </div>
    </div>
<?php }?>
