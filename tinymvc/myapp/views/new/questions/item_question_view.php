<?php $is_logged_in = logged_in();?>
<?php foreach($questions as $key => $question) {?>
	<li class="questions__item" id="item-question-<?php echo $question['id_question'];?>">
		<div class="flex-card">
			<div class="questions__img image-card2">
				<div class="link">
                    <?php if($key < 2){ ?>
                        <img
                            class="image"
                            src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>"
                            alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>"
                            <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                    />
                    <?php } else { ?>
                        <img
                            class="image js-lazy"
                            data-src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>"
                            src="<?php echo getLazyImage(50, 50); ?>"
                            alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>"
                            <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                    />
                    <?php }?>
				</div>
			</div>
			<div class="questions__detail flex-card__float">
                <a
                    class="questions__subject"
                    href="<?php echo __COMMUNITY_URL . 'question/' . strForURL(cleanOutput($question['title_question'])) . '-' . $question['id_question'];?>"
                    <?php echo addQaUniqueIdentifier('global__question-title'); ?>
                >
                    <?php echo cleanOutput($question['title_question']);?>
                </a>

                <div class="questions__row--mobile">
                    <div class="questions__img--mobile image-card2">
                        <?php if($key < 2){ ?>
                            <img
                                class="image"
                                src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>"
                                alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>"
                                <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                        />
                        <?php } else { ?>
                            <img
                                class="image js-lazy"
                                data-src="<?php echo getDisplayImageLink(array('{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question['user_group'] ));?>"
                                src="<?php echo getLazyImage(50, 50); ?>"
                                alt="<?php echo $question['fname'] . ' ' . $question['lname'];?>"
                                <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                        />
                        <?php }?>

                    </div>
                    <div class="questions__row">
                        <a
                            class="questions__uname"
                            href="<?php echo __SITE_URL . 'usr/' . strForURL($question['fname'] . ' ' . $question['lname']) . '-' . $question['id_user'];?>"
                            <?php echo addQaUniqueIdentifier('global__question-name'); ?>
                        >
                            <?php echo $question['fname'] . ' ' . $question['lname'];?>
                        </a>
                        <div>
                            <span class="questions__additional"><?php echo translate('community_asked_on_text'); ?></span>
                            <span class="questions__date" <?php echo addQaUniqueIdentifier('global__question-date'); ?>>
                                <?php echo getDateFormat($question['date_question'], 'Y-m-d H:i:s', 'F d, Y'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="questions__message" <?php echo addQaUniqueIdentifier('global__question-text'); ?>>
                    <?php echo truncWords(cleanOutput($question['text_question']), 50); ?>
                </div>

				<div class="questions__row">
                    <div class="questions__row--mobile">
                        <span class="questions__additional pr-5"><?php echo translate('community_in_word'); ?></span>
                        <div class="questions__category" <?php echo addQaUniqueIdentifier('global__question-type'); ?>>
                            <?php echo $quest_cats[$question['id_category']]['title_cat'];?>
                        </div>
                        <span class="questions__delimiter"></span>
                        <div class="questions__flag">
                            <?php if($key < 2){ ?>
                                <img
                                    class="image"
                                    width="24"
                                    height="24"
                                    src="<?php echo getCountryFlag($question['country']);?>"
                                    alt="<?php echo $question['country'];?>"
                                    title="<?php echo $question['country'];?>"
                                    <?php echo addQaUniqueIdentifier('global__question-country-flag'); ?>
                                />
                            <?php } else { ?>
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
                            <?php }?>
                            <span class="questions__flag-name" <?php echo addQaUniqueIdentifier('global__question-country-name'); ?>>
                                <?php echo $question['country'];?>
                            </span>
                        </div>
                    </div>
                    <a
                        class="questions__replies-count"
                        href="<?php echo __COMMUNITY_URL . 'question/' . strForURL(cleanOutput($question['title_question'])) . '-' . $question['id_question'];?>"
                        <?php echo addQaUniqueIdentifier('global__question-replies'); ?>
                    >
                        <span class="counter"><?php echo $question['count_answers']?></span> <?php echo translate('community_replies_word'); ?>
                    </a>
				</div>
            </div>
		</div>
	</li>
<?php }?>
