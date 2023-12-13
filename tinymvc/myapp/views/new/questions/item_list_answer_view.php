<?php
    $is_logged_in = logged_in();
    foreach ($answers as $answer) {

        $answer_user_name = $answer['fname'] . ' ' . $answer['lname'];
        $comments_btn_class = ($is_logged_in && $answer['count_comments'] < 1) ? 'load-hide' : 'load-ajax';
    ?>
        <li class="questions-answers__item answer-<?php echo $answer['id_answer'];?>" id="<?php echo 'answer-' . $answer['id_answer'];?>">
            <div class="flex-card">
                <div class="questions-answers__img image-card2">
                    <div class="link">
                        <img
                            class="image js-lazy"
                            <?php echo addQaUniqueIdentifier("global__question-image")?>
                            data-src="<?php echo getDisplayImageLink(array('{ID}' => $answer['id_user'], '{FILE_NAME}' => $answer['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $answer['user_group'] ));?>"
                            src="<?php echo getLazyImage(50, 50); ?>"
                            alt="<?php echo $answer_user_name;?>"
                        />
                    </div>
                </div>

                <div class="questions-answers__detail flex-card__float">
                    <div class="questions-answers__top">
                        <div class="questions-answers__img questions-answers__img--mobile image-card2">
                            <div class="link">
                                <img
                                    class="image js-lazy"
                                    data-src="<?php echo getDisplayImageLink(array('{ID}' => $answer['id_user'], '{FILE_NAME}' => $answer['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $answer['user_group'] ));?>"
                                    src="<?php echo getLazyImage(50, 50); ?>"
                                    alt="<?php echo $answer_user_name;?>"
                                />
                            </div>
                        </div>
                        <div class="questions-answers__top-left">
                            <a class="questions-answers__uname" <?php echo addQaUniqueIdentifier("global__question-name")?> href="<?php echo __SITE_URL . 'usr/' . strForURL($answer_user_name) . '-' . $answer['id_user'];?>" title="<?php echo $answer_user_name;?>"><?php echo $answer_user_name;?></a>
                            <div class="questions-answers__date" <?php echo addQaUniqueIdentifier("global__question-date")?>><?php echo translate('community_answered_on_text'); ?> <?php echo getDateFormat($answer['date_answer'], 'Y-m-d H:i:s', 'F d, Y'); ?></div>
                        </div>

                        <div class="did-help<?php isset($helpful_answers[$answer['id_answer']]) ? ' rate-didhelp' : '';?>">
                            <?php
                                $isset_my_helpful_answers = isset($helpful_answers[$answer['id_answer']]) && $is_logged_in;
                                $btn_count_plus_class = ($isset_my_helpful_answers && $helpful_answers[$answer['id_answer']] == 1) ? ' active' : '';
                                $btn_count_minus_class = ($isset_my_helpful_answers && $helpful_answers[$answer['id_answer']] == 0) ? ' active' : '';
                            ?>
                            <div class="did-help__txt"><?php echo translate('community_did_it_help_text'); ?></div>
                            <span
                                class="i-up didhelp-btn js-didhelp-btn"
                                data-item="<?php echo $answer['id_answer']?>"
                                data-page="community_questions"
                                data-type="answers"
                                data-action="y">
                                <span class="counter-b js-counter-plus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $answer['count_plus']?></span>
                                <span class="didhelp-btn--up js-arrow-up<?php echo $btn_count_plus_class;?>"></span>
                            </span>
                            <span
                                class="i-down didhelp-btn js-didhelp-btn"
                                data-item="<?php echo $answer['id_answer']?>"
                                data-page="community_questions"
                                data-type="answers"
                                data-action="n">
                                <span class="counter-b js-counter-minus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $answer['count_minus'];?></span>
                                <span class="didhelp-btn--down js-arrow-down<?php echo $btn_count_minus_class;?>"></span>
                            </span>
                        </div>
                    </div>

                    <p class="questions-answers__message" <?php echo addQaUniqueIdentifier("global__question-text")?>><?php echo cleanOutput($answer['text_answer']);?></p>

                    <div class="did-help did-help--mobile <?php isset($helpful_answers[$answer['id_answer']]) ? 'rate-didhelp' : '';?>">
                        <?php
                            $isset_my_helpful_answers = isset($helpful_answers[$answer['id_answer']]) && $is_logged_in;
                            $btn_count_plus_class = ($isset_my_helpful_answers && $helpful_answers[$answer['id_answer']] == 1) ? ' active' : '';
                            $btn_count_minus_class = ($isset_my_helpful_answers && $helpful_answers[$answer['id_answer']] == 0) ? ' active' : '';
                        ?>
                        <div class="did-help__txt"><?php echo translate('community_did_it_help_text'); ?></div>
                        <div>
                            <span class="i-up didhelp-btn js-didhelp-btn" data-item="<?php echo $answer['id_answer']?>" data-page="community_questions" data-type="answers" data-action="y">
                                <span class="counter-b js-counter-plus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $answer['count_plus']?></span>
                                <span class="didhelp-btn--up js-arrow-up<?php echo $btn_count_plus_class;?>"></span>
                            </span>
                            <span class="i-down didhelp-btn js-didhelp-btn" data-item="<?php echo $answer['id_answer']?>" data-page="community_questions" data-type="answers" data-action="n">
                                <span class="counter-b js-counter-minus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $answer['count_minus'];?></span>
                                <span class="didhelp-btn--down js-arrow-down<?php echo $btn_count_minus_class;?>"></span>
                            </span>
                        </div>
                    </div>

                    <div class="questions-answers__line">
                        <a class="questions-answers__count call-action <?php echo $comments_btn_class;?>"
                            data-js-action="answer:load_comments"
                            data-answer="<?php echo $answer['id_answer'];?>"
                            data-start="0"
                            href="#"
                            <?php echo addQaUniqueIdentifier('page__community-detail__question-answers_comments-count-btn'); ?>
                        >
                            <span class="js-comments-label">
                                <?php echo translate('community_word_view'); ?>
                            </span>
                            <?php echo translate('community_comments_view'); ?> (
                                <span
                                    class="p-0 js-count-comments"
                                    data-count="<?php echo $answer['count_comments'];?>"
                                    <?php echo addQaUniqueIdentifier('global__question-counter'); ?>
                                >
                                    <?php echo $answer['count_comments'];?>
                                </span>)
                        </a>

                        <div class="questions-answers__btns inputs-40">
                            <a class="btn btn-outline-dark mnw-155 mnw-290-sm fancybox.ajax fancyboxValidateModal call-action"
                            <?php echo addQaUniqueIdentifier('page__community-detail__question-answers_comment-btn')?>
                            <?php if($is_logged_in){?>
                                data-title="<?php echo translate('community_comment_answer_text', null, true); ?>"
                                data-mw="535"
                                href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms/add_comment/' . $answer['id_answer']?>"
                            <?php } else { ?>
                                data-js-action="lazy-loading:login"
                                data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
                                data-mw="400"
                                href="<?php echo __SITE_URL . 'login'; ?>"
                            <?php } ?>
                            >
                                <?php echo translate('community_comment_button_text'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="questions-comments__block">
                <ul class="questions-comments display-n">
                    <?php views()->display('new/questions/comments_list_view', array('comments' => $answer['comments'], 'count_comments' => $answer['count_comments'], 'answer' => $answer)); ?>
                </ul>

                <?php if((int)$answer['count_comments'] > config('community_answer_comments_per_page', 2)){?>
                    <div class="js-load-more-comments display-n">
                        <div class="questions-comments__loadmore">
                            <a
                                class="btn-block btn btn-light mw-200 call-action"
                                data-js-action="answer:load_more_comments"
                                data-id_answer="<?php echo $answer['id_answer']; ?>"
                                href="#"
                            ><?php echo translate('community_show_more_comments_text'); ?></a>
                        </div>
                    </div>
                <?php }?>
            </div>

            <div class="questions-answers__line mt-20">
                <a class="questions-answers__count questions-answers__count--mobile call-action <?php echo $comments_btn_class;?>"
                    data-js-action="answer:load_comments"
                    data-answer="<?php echo $answer['id_answer'];?>"
                    data-start="0"
                    href="#">
                    <span class="js-comments-label">
                        <?php echo translate('community_word_view'); ?>
                    </span>
                    <?php echo translate('community_comments_view'); ?> (
                        <span
                            class="p-0 js-count-comments"
                            data-count="<?php echo $answer['count_comments'];?>"
                            <?php echo addQaUniqueIdentifier('global__question-counter'); ?>
                        >
                            <?php echo $answer['count_comments'];?>
                        </span>)
                </a>
            </div>

        </li>
    <?php }?>
