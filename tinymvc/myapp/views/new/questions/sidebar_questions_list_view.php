<?php if ($current_page == 'questions') { ?>
<div class="w-100pr" <?php echo addQaUniqueIdentifier('help__banner-demo'); ?>>
    <?php echo widgetShowBanner('community_help_sidebar', 'promo-banner-wr--community'); ?>
</div>
<?php } ?>

<?php if (!empty($related_questions) || !empty($recent_questions)) { ?>
<div
    class="sidebar-questions-wr<?php echo $current_page === "all" ? " sidebar-questions-wr--all" : ""; ?>"
    <?php echo addQaUniqueIdentifier('community__sidebar_questions-list'); ?>
>
    <h2 class="community-sidebar-title"><?php echo translate(isset($related_questions) || ($current_page == 'all' && !empty($search_params)) ? 'community_you_may_be_interested' : 'community_recent_questions'); ?></h2>

    <ul class="sidebar-questions">
        <?php $is_logged_in = logged_in();?>

        <?php
            $to_show_questions = $recent_questions;
            if(isset($related_questions)){
                $to_show_questions = $related_questions;
            }
        ?>

        <?php foreach($to_show_questions as $question) { ?>
            <li class="sidebar-questions__item" id="item-recent-question-<?php echo $question['id_question'];?>">
                <div class="flex-card">
                    <div class="sidebar-questions__detail flex-card__float">
                        <a
                            class="sidebar-questions__subject"
                            href="<?php echo __COMMUNITY_URL . 'question/' . strForURL(cleanOutput($question['title_question'])) . '-' . $question['id_question'];?>"
                            <?php echo addQaUniqueIdentifier('global__question-title'); ?>
                        >
                            <?php echo cleanOutput($question['title_question']);?>
                        </a>

                        <div class="sidebar-questions__row">
                            <span class="txt-gray pr-5"><?php echo translate('community_in_word'); ?></span>
                            <span class="sidebar-questions__category" <?php echo addQaUniqueIdentifier('global__question-type'); ?>>
                                <?php echo $quest_cats[$question['id_category']]['title_cat'];?>
                            </span>
                            <span class="sidebar-questions__delimiter"></span>
                            <span class="sidebar-questions__flag">
                                <img
                                    class="image js-lazy"
                                    width="24"
                                    height="24"
                                    data-src="<?php echo getCountryFlag($question['country']);?>"
                                    src="<?php echo getLazyImage(24, 24); ?>"
                                    alt="<?php echo $question['country'];?>"
                                    title="<?php echo $question['country'];?>"
                                    <?php echo addQaUniqueIdentifier('global__question-country-flag'); ?>
                                />
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
