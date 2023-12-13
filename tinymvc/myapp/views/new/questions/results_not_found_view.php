<div class="questions-empty">
    <div class="questions-empty__information">
        <h2 class="questions-empty__title"><?php echo translate('community_no_results_title'); ?></h2>
        <p class="questions-empty__txt"><?php echo translate('community_search_no_results_text'); ?></p>
        <p class="questions-empty__txt questions-empty__txt--gray"><?php echo translate('community_check_resources_text'); ?></p>
    </div>
    <div class="questions-empty__help-options">
        <p><?php echo translate('community_other_resources_text'); ?>: </p>
        <?php if ('faq' !== $current_page) { ?>
            <a class="questions-empty__option" href="<?php echo __SITE_URL . 'faq'; ?>"><?php echo translate('header_navigation_link_faq'); ?></a>,
        <?php } ?>
        <?php if ('topics' !== $current_page) { ?>
            <a class="questions-empty__option" href="<?php echo __SITE_URL . 'topics/help'; ?>"><?php echo translate('help_topics'); ?></a>
        <?php } ?>
    </div>
    <div class="questions-empty__help-options">
        <p><?php echo translate('community_have_a_question_text'); ?></p>
        <a class="questions-empty__option fancybox.ajax fancyboxValidateModal" title="<?php echo translate('community_ask_a_question_text', null, true); ?>" data-mw="535" data-title="<?php echo translate('community_ask_a_question_text', null, true); ?>" href="<?php echo __SITE_URL;?>community_questions/popup_forms/add_question"><?php echo translate('community_submit_question_button_text'); ?></a>
    </div>
</div>

