<?php
    $hide_user_info = isset($hide_user_info)?true:false;
?>
<?php foreach($questions as $question) {?>
	<li class="community-user-list__item flex-card">
        <?php if(!$hide_user_info){?>
            <div class="community-user-list__img flex-card__fixed image-card2">
                <span class="link">
                    <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>" alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>" />
                </span>
            </div>
        <?php }?>

        <div class="community-user-list__detail flex-card__float">
            <div class="community-user-list__subject">
                <a
                    class="community-user-list__subject-name"
                    href="<?php echo __COMMUNITY_URL . 'question/' . strForURL($question['title_question']) . '-' . $question['id_question'];?>"
                    target="_blank"
                >
                    <?php echo cleanOutput($question['title_question']);?>
                </a>
            </div>
            <div class="community-user-list__info-user-line flex-card">
                <?php if(!$hide_user_info){?>
                    <div class="community-user-list__img-mobile flex-card__fixed image-card2">
                        <span class="link">
                            <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>" alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>" />
                        </span>
                    </div>
                <?php }?>

                <div class="community-user-list__info-user-line-col flex-card__float">
                    <?php if(!$hide_user_info){?>
                        <div class="community-user-list__uname">
                            <a
                                class="link"
                                href="<?php echo __SITE_URL . 'usr/' . strForURL($question['fname'] . ' ' . $question['lname']) . '-' . $question['id_user'];?>"
                            ><?php echo $question['fname'] . ' ' . $question['lname'];?></a>
                        </div>
                    <?php }?>
                    <span class="community-user-list__info-additional">Asked on</span>
                    <span class="community-user-list__date"><?php echo getDateFormat($question['date_question'])?></span>
                </div>
            </div>

            <div class="community-user-list__message"><?php echo cleanOutput($question['text_question']); ?></div>

            <div class="community-user-list__info-filters-line">
                <div class="community-user-list__info-filters">
                    <span class="community-user-list__info-additional">in</span>
                    <a
                        class="community-user-list__category"
                        href="<?php echo __COMMUNITY_URL . 'category/' . $quest_cats[$question['id_category']]['url'];?>"
                        target="_blank"
                    ><?php echo $quest_cats[$question['id_category']]['title_cat'];?></a>
                    <span class="community-user-list__delimeter-circle"></span>
                    <a
                        class="community-user-list__flag" title="<?php echo $question['country']?>"
                        href="<?php echo __COMMUNITY_URL . 'country/' . strForURL($question['country'] . ' ' . $question['id_country']);?>"
                        target="_blank"
                    >
                        <img
                            class="image"
                            width="24"
                            height="24"
                            src="<?php echo getCountryFlag($question['country']); ?>"
                            alt="<?php echo $question['country']?>"
                            title="<?php echo $question['country']?>"
                        />
                        <span class="community-user-list__flag-name"><?php echo $question['country']?></span>
                    </a>
                </div>

                <span class="community-user-list__replies">
                    <a
                        class="link"
                        href="<?php echo __COMMUNITY_URL . 'question/' . strForURL($question['title_question']) . '-' . $question['id_question'];?>"
                        target="_blank"
                    ><?php echo $question['count_answers'];?> replies</a>
                </span>
            </div>

            <?php if(!empty($question['answers'])){?>
                <div class="community-user-list__ansers">
                    <?php foreach($question['answers'] as $answers_item ){?>
                        <div class="community-user-list__ansers-item">
                            <div class="community-user-list__ansers-info-user-line">
                                <span class="community-user-list__ansers-info-additional">Answered on</span>
                                <span class="community-user-list__ansers-date"><?php echo getDateFormat($answers_item['date_answer'])?></span>
                            </div>
                            <div class="community-user-list__ansers-message">
                                <?php echo cleanOutput($answers_item['text_answer']);?>
                            </div>
                        </div>
                    <?php }?>
                </div>
            <?php }?>
        </div>
    </li>
<?php }?>
