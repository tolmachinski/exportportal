<?php foreach ($itemsCompilations as $key => $itemsCompilation) {?>
    <div class="exclusive-deals__item">
        <div class="exclusive-deals__header">
            <h4 class="exclusive-deals__header-title" <?php echo addQaUniqueIdentifier('home__exclusive-deals-title'); ?>><?php echo cleanOutput($itemsCompilation['title']);?></h4>
            <a class="exclusive-deals__header-link" href="<?php echo $itemsCompilation['url'];?>" <?php echo addQaUniqueIdentifier("home__exclusive-deals-view-more-{$key}"); ?>><?php echo translate('home_title_links_view_more'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15)?></a>
        </div>
        <div class="exclusive-deals__main">
            <div class="exclusive-deals__images" <?php echo addQaUniqueIdentifier('home__exclusive-deals-imgs'); ?>>
                <?php foreach ($itemsCompilation['items'] as $item) {?>
                    <img class="exclusive-deals__image js-lazy" src="<?php echo getLazyImage(122, 122); ?>" data-src="<?php echo getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $item['image']], 'items.main', [ 'thumb_size' => 2]);?>" alt="<?php echo cleanOutput($item['title']);?>">
                <?php }?>
            </div>
            <picture class="exclusive-deals__background" <?php echo addQaUniqueIdentifier('home__exclusive-deals-background'); ?>>
                <source srcset="<?php echo getLazyImage(700, 299); ?>" data-srcset="<?php echo getDisplayImageLink(['{FILE_NAME}' => $itemsCompilation['background_images']['tablet']], 'items_compilation.tablet');?>" media="(max-width:991px)">
                <img class="js-lazy" src="<?php echo getLazyImage(453, 299); ?>" data-src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $itemsCompilation['background_images']['desktop']], 'items_compilation.desktop');?>" alt="<?php echo cleanOutput($itemsCompilation['title']);?>">
            </picture>
        </div>
    </div>
<?php }?>
