<div class="bulk-upload__row">
    <img class="image" src="<?php echo asset('public/build/images/bulk_popup/bulk.png') ?>" alt="">
    <?php echo translate('my_items_bulk_upload_download_text',[
            '{{START_TAG}}'         => '<a href="#" class="bulk-upload__btn call-function" data-callback="downloadGuide" data-guide-name="item_bulk_upload" data-lang="en" data-group="all">',
            '{{END_TAG}}'           => '</a>',
    ]); ?>
</div>
<div class="bulk-upload__row">
    <img class="image" src="<?php echo asset('public/build/images/bulk_popup/play.png') ?>" alt="">
    <?php echo translate('my_items_bulk_upload_video_text',[
            '{{START_TAG}}'         => '<a class="bulk-upload__btn call-function" data-callback="openVideoModal" href="#" data-title="' . translate('popup_bulk_item_upload_ttl', null, true) . '" data-href="' .  config("my_items_bulk_upload_video_url") . '" data-autoplay="true" title="' .  translate('popup_bulk_item_upload_ttl', null, true) . '" data-mw="1920" data-w="80%" data-h="88%">',
            '{{END_TAG}}'           => '</a>',
    ]); ?>
</div>
