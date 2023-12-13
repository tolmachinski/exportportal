<?php if(!empty($topics)){?>
<?php foreach($topics as $topic_item){?>
    <div class="col-md-12">
        <div class="ep-middle-text">
            <a
                class="topics-title"
                href="<?php echo __SITE_URL.'topics/detail/'.strForUrl($topic_item['title_topic']).'/'.$topic_item['id_topic'] ?>"
                <?php echo addQaUniqueIdentifier('page__topics__topic-item_title') ?>
            >
                <?php echo $topic_item['title_topic'] ?>
            </a>
            <p class="topics-text__height topics-text" <?php echo addQaUniqueIdentifier('page__topics__topic-item_text') ?>>
                <?php echo $topic_item['text_topic_small'];?>
            </p>
        </div>
    </div>
<?php }?>
<?php }else{?>
    <div class="col-md-12">
        <?php tmvc::instance()->controller->view->display('new/help/results_not_found_view');?>
    </div>
<?php }?>
