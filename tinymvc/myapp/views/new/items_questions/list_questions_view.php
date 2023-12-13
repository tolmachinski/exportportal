<ul class="product-comments" id="question-ul-block">
    <?php if (!empty($questions)) {
    $item_qestion_param = ['questions' => $questions, 'helpful' => $helpful];
    if (isset($about)) {
        $item_qestion_param['about'] = $about;
    } ?>
        <?php views()->display('new/items_questions/item_view', $item_qestion_param); ?>

        <?php if (intval($count_questions) > 2 && !$page_questions_all) { ?>
            <li><a class="product-comments__more btn btn-light btn-new16" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($item['title']); ?> - <?php echo $item['id']; ?>/questions"><?php echo translate('item_details_show_all_questions_btn'); ?></a></li>
        <?php } ?>
    <?php
} else { ?>
        <li class="mt-10" id="no-questions-item">
            <div class="default-alert-b">
                <i class="ep-icon ep-icon_remove-circle"></i> <?php echo translate('item_details_no_questions_yet_title'); ?>
                <a class="txt-black fancybox.ajax fancyboxValidateModal" data-title="Add question" href="<?php echo __SITE_URL; ?>items_questions/popup_forms/add_question/<?php echo $item['id']; ?>"><?php echo translate('item_details_be_the_first_to_ask_question_btn'); ?></a>
            </div>
        </li>
    <?php } ?>
</ul>
