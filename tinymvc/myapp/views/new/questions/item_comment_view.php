<?php foreach ($comments as $comment) { ?>
	<?php $comment_user_name = $comment['fname'] . ' ' . $comment['lname'];?>
    <li class="questions-comments__item" id="item-comment-<?php echo $comment['id_comment'];?>" <?php echo addQaUniqueIdentifier("page__community-detail__question-answers_comment")?>>
        <div class="flex-card">
            <div class="questions-comments__img flex-card__fixed image-card2">
                <div class="link">
                    <img
                        class="image js-lazy"
                        <?php echo addQaUniqueIdentifier("global__question-image")?>
                        data-src="<?php echo getDisplayImageLink(array('{ID}' => $comment['id_user'], '{FILE_NAME}' => $comment['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $comment['user_group'] ));?>"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        alt="<?php echo $comment_user_name;?>"
                    />
                </div>
            </div>

            <div class="questions-comments__detail flex-card__float">
                <div class="questions-comments__top">
                    <div class="questions-comments__img questions-comments__img--mobile flex-card__fixed image-card2">
                        <div class="link">
                            <img
                                class="image js-lazy"
                                data-src="<?php echo getDisplayImageLink(array('{ID}' => $comment['id_user'], '{FILE_NAME}' => $comment['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $comment['user_group'] ));?>"
                                src="<?php echo getLazyImage(50, 50); ?>"
                                alt="<?php echo $comment_user_name;?>"
                            />
                        </div>
                    </div>
                    <div class="questions-comments__top-left">
                        <a class="questions-comments__uname" <?php echo addQaUniqueIdentifier("global__question-name")?> href="<?php echo __SITE_URL . 'usr/' . strForURL($comment_user_name) . '-' . $comment['id_user'];?>" title="<?php echo $comment_user_name;?>"><?php echo $comment_user_name;?></a>
                        <div class="questions-comments__date" <?php echo addQaUniqueIdentifier("global__question-date")?>><?php echo translate('community_commented_on_text'); ?> <?php echo getDateFormat($comment['date_comment'], 'Y-m-d H:i:s', 'F d, Y'); ?></div>
                    </div>
                </div>
                <p class="questions-comments__message" <?php echo addQaUniqueIdentifier("global__question-text")?>><?php echo cleanOutput($comment['text_comment']); ?></p>
            </div>
        </div>
	</li>
<?php }?>
