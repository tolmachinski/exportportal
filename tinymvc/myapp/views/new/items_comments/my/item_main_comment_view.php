<?php $is_my_item = is_privileged('user', $item['id_seller']);?>

<?php foreach($comments as $comment_item){?>
    <li class="product-comments__item" id="li-comment-<?php echo $comment_item['id_comm']?>">
        <?php views()->display('new/items_comments/main_comment_view', array('comment_item' => $comment_item, 'is_my_item' => $is_my_item));?>

        <ul class="product-comments product-comments--first product-comments--replies js-product-main-comment-replies js-product-comments-replies" id="comments-<?php echo $comment_item['id_comm']?>-block">
            <?php views()->display('new/items_comments/item_reply_view', array(
                'comments' => (null !== $comment_item['replies'] ? $comment_item['replies'] : array()),
                'more' => 1
            )); ?>

            <?php if(!$unwrap){?>
                <?php if(!empty($comment_item['replies'])){?>
                    <li>
                        <a
                            class="product-comments__more-reply call-function call-action js-show-all-replies-btn"
                            data-callback="showMoreReply"
                            data-js-action="item-comments:show-more-replies"
                            href="#"
                            <?php echo addQaUniqueIdentifier('item__show-all-comments')?>
                        >
                            Show all replies in this thread
                        </a>
                    </li>
                <?php }?>
            <?php }?>
        </ul>
    </li>
<?php }?>
