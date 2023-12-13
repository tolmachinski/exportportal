<?php if(!empty($faq_list)){?>
    <script>
        $(document).ready(function(){
            $("body").on('click', '.sliding-block__title-wrapper', function(e) {
                if (e.target.tagName === "A" || e.target === "BUTTON") return;

                $(this).find(".ep-icon").toggleClass('ep-icon_plus-stroke ep-icon_minus-stroke');
                $(this).next('.sliding-block__wrapper').slideToggle("fast");
            });
        });
    </script>

    <div class="sliding-block-new">
        <?php foreach($faq_list as $faq_item){?>
            <div class="sliding-block">
                <div class="sliding-block__title-wrapper">
                    <div class="sliding-block__title">
                        <h2
                            class="sliding-block__title__txt"
                            <?php echo addQaUniqueIdentifier('page__faq__list-title'); ?>
                        ><?php echo $faq_item['question'];?></h2>
                    </div>
                    <i class="ep-icon ep-icon_plus-stroke sliding-block__cross" <?php echo addQaUniqueIdentifier('page__faq__list_more-btn'); ?>></i>

                    <?php if (!empty($faq_tags_attached[$faq_item['id_faq']])) { ?>
                    <div class="sliding-block__tags">
                        <?php foreach($faq_tags_attached[$faq_item['id_faq']] as $attached_tag) { ?>
                            <?php $id_tag = $attached_tag['id_tag']; ?>
                            <a
                                class="sliding-block__tags-item"
                                href="faq/all/tag/<?php echo $faq_tags_list[$id_tag]['slug'] ?>"
                                <?php echo addQaUniqueIdentifier('page__faq__list-tag'); ?>
                            >#<?php echo $attached_tag['tag_name'] ?></a>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <div class="sliding-block__wrapper">
                    <div class="sliding-block__text ep-tinymce-text" <?php echo addQaUniqueIdentifier('page__faq__list-text'); ?>>
                        <?php echo $faq_item['answer'];?>
                    </div>
                </div>
            </div>
        <?php }?>
    </div>
<?php }else{ ?>
    <?php tmvc::instance()->controller->view->display('new/help/results_not_found_view');?>
<?php }?>
