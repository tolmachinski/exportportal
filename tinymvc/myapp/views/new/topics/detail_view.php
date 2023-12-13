<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div class="title-public">
    <h2 class="title-public__txt title-public__txt--26" <?php echo addQaUniqueIdentifier('page__topics-detail__section_header') ?>>
        <?php echo $topic['title_topic'];?>
    </h2>
</div>

<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('page__topics-detail__section_text') ?>>
    <?php echo $topic['text_topic'];?>
</div>
