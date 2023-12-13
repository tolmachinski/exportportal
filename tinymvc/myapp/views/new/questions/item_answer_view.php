<?php $is_logged_in = logged_in(); ?>
<ul class="questions-answers">
    <div class="questions-answers__header">
        <h2 class="questions-answers__heading">
            <?php echo translate('community_all_replies_text'); ?>
            <span class="questions-answers__counter" <?php echo addQaUniqueIdentifier('global__question-counter'); ?>>
                <?php echo $count_answers; ?>
            </span>
        </h2>

        <a
            class="btn btn-primary btn--50 mnw-155 mnw-290-sm fancybox.ajax fancyboxValidateModal call-action"
            <?php echo addQaUniqueIdentifier('page__community-detail__replies_add-reply-btn'); ?>
        <?php if(!$is_logged_in){?>
            data-js-action="lazy-loading:login"
            data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
            data-mw="400"
            href="<?php echo __SITE_URL . 'login'; ?>"
        <?php }else{ ?>
            data-title="<?php echo translate('community_add_your_answer_text', null, true); ?>"
            data-mw="535"
            href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms/add_answer/' . $id_question; ?>"
        <?php } ?>
        >
            <?php echo translate('community_reply_button_text'); ?>
        </a>
    </div>


    <?php views()->display('new/questions/item_list_answer_view', array('answers' => $answers, 'is_logged_in' => $is_logged_in, 'count_answers' => $count_answers, 'helpful_answers' => $helpful_answers)); ?>
</ul>

<?php if((int)$count_answers > config('community_answers_per_page', 5)){?>
    <div class="questions-answers__loadmore">
        <a
            class="btn-block btn btn-light mw-200 call-action"
            data-js-action="answer:load_more_answers"
            data-id_question="<?php echo $answers[0]['id_question']; ?>"
            href="#"
            <?php echo addQaUniqueIdentifier('community-detail__replies_show-more-btn'); ?>
        ><?php echo translate('community_show_more_answers_text'); ?></a>
    </div>
<?php }?>
