<div class="question-detail">
    <h1 class="question-detail__title" <?php echo addQaUniqueIdentifier("global__question-title")?>><?php echo cleanOutput($question['title_question']);?></h1>
    <div class="question-detail__item" id="item-question-<?php echo $question['id_question'];?>">
        <div class="flex-card">
            <div class="question-detail__img image-card2">
                <span class="link">
                    <img
                        class="image"
                        <?php echo addQaUniqueIdentifier("global__question-image")?>
                        src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 1, 'no_image_group' => $question['user_group'] ));?>"
                        alt="<?php echo $question['fname']?>"
                    />
                </span>
            </div>
            <div class="flex-card__float">
                <a class="question-detail__uname" <?php echo addQaUniqueIdentifier("global__question-name")?> href="<?php echo __SITE_URL . 'usr/' . strForURL($question['fname'] . ' ' . $question['lname']) . '-' . $question['id_user'];?>"><?php echo $question['fname'] . ' ' . $question['lname'];?></a>
                <div class="question-detail__row">
                    <span class="question-detail__additional" ><?php echo translate('community_asked_on_text'); ?></span>
                    <span class="question-detail__date" <?php echo addQaUniqueIdentifier("global__question-date")?>><?php echo getDateFormat($question['date_question'], 'Y-m-d H:i:s', 'F d, Y');?></span>
                </div>
            </div>
        </div>

        <div class="question-detail__message" <?php echo addQaUniqueIdentifier("global__question-text")?>><?php echo cleanOutput($question['text_question']);?></div>

        <div class="question-detail__row">
            <span class="question-detail__additional"><?php echo translate('community_in_word'); ?></span>
            <a
                class="question-detail__category"
                href="<?php echo replace_dynamic_uri($quest_cats[$question['id_category']]['url'], $links_tpl[$questions_uri_components['category']], __COMMUNITY_ALL_URL);?>"
                title="<?php echo $quest_cats[$question['id_category']]['title_cat'];?>"
                <?php echo addQaUniqueIdentifier('global__question-type'); ?>
            ><?php echo $quest_cats[$question['id_category']]['title_cat'];?>
            </a>
            <span class="question-detail__delimiter"></span>
            <span class="question-detail__flag">
                <a
                    href="<?php echo replace_dynamic_uri(strForURL($question['country'] . ' ' . $question['id_country']), $links_tpl[$questions_uri_components['country']], __COMMUNITY_ALL_URL);?>"
                    title="<?php echo $question['country'];?>"
                >
                    <img
                        width="24"
                        height="24"
                        class="image"
                        src="<?php echo getCountryFlag($question['country']);?>"
                        alt="<?php echo $question['country'];?>"
                        title="<?php echo $question['country'];?>"
                        <?php echo addQaUniqueIdentifier('global__question-country-flag'); ?>
                    />
                </a>
                <a
                    class="question-detail__flag-name"
                    href="<?php echo replace_dynamic_uri(strForURL($question['country'] . ' ' . $question['id_country']), $links_tpl[$questions_uri_components['country']], __COMMUNITY_ALL_URL);?>"
                    title="<?php echo $question['country'];?>"
                    <?php echo addQaUniqueIdentifier('global__question-country-name'); ?>
                >
                    <?php echo $question['country'];?>
                </a>
            </span>
        </div>

        <?php views()->display('new/questions/item_answer_view', array('answers' => $question['answers'], 'helpful_answers' => $question['helpful_answers'], 'id_question' => $question['id_question'], 'count_answers' => $question['count_answers']));?>
    </div>
</div>

<?php encoreEntryLinkTags('question_index'); ?>
<?php encoreLinks(); ?>

<?php
    $is_logged_in = logged_in();

    $json_ld_array = array(
        '@context'          => 'https://schema.org',
        '@type'             => 'QAPage',
        'mainEntity'        => array(
            '@type'             => 'Question',
            'name'              => cleanOutput($question['title_question']) . 123,
            'text'              => cleanOutput($question['text_question']),
            'answerCount'       => $question['count_answers'],
            'dateCreated'       => getDateFormat($question['date_question'], 'Y-m-d H:i:s', DATE_ISO8601),
            'author'            => array(
                '@type'            => 'Person',
                'name'             => $question['fname'] . ' ' . $question['lname'],
            ),
            'locationCreated'   => array(
                '@type'             => 'Place',
                'address'           => array(
                    '@type'             => 'PostalAddress',
                    'AddressCountry'    => $question['country_abr']
                )
            ),
            'suggestedAnswer'   => array()
        )
    );

    if (!empty($sugested_answers)) {
        foreach($sugested_answers as $sugested_answer) {
            $json_ld_array['mainEntity']['suggestedAnswer'][] = array(
                '@type'         => 'Answer',
                'text'          => cleanOutput($sugested_answer['text_answer']),
                'dateCreated'   => getDateFormat($sugested_answer['date_answer'], 'Y-m-d H:i:s', DATE_ISO8601),
                'upvoteCount'   => $sugested_answer['count_plus'] - $sugested_answer['count_minus'],
                'url'           => $question_detail_link . '#answer-' . $sugested_answer['id_answer'],
                'author'        => array(
                    '@type'         => 'Person',
                    'name'          => $sugested_answer['fname'] . ' ' . $sugested_answer['lname']
                )
            );
        }
    }
?>

<script type="application/ld+json">
<?php echo json_encode($json_ld_array, JSON_PRETTY_PRINT);?>
</script>
