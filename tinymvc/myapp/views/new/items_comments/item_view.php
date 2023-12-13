<?php foreach($comments as $item) { ?>
    <li class="product-comments__item" id="li-comment-<?php echo $item['id_comm']?>">
	    <?php tmvc::instance()->controller->view->display('new/items_comments/main_comment_view', array('comment_item' => $item)); ?>
    </li>
<?php } ?>
