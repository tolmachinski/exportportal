<?php $cookieExist = cookies()->exist_cookie('_ep_material_downloaded');?>
<?php if (!isset($webpackData)) {?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/downloadable_materials_page_styles.css');?>" />
<?php }?>

<div class="dwn-materials">
    <div id="js-dwn-materials-info" class="dwn-materials__info">
        <h1 class="title-public__txt dwn-materials__title"><?php echo cleanOutput($materials_content['title']);?></h1>

        <div class="dwn-materials__content">
            <?php echo $materials_content['content'] ?>
        </div>

        <div class="dwn-materials__actions">

            <?php if (logged_in()) { ?>
                <button class="btn btn-primary call-function"
                    id="js-dwn-download"
                    data-callback="downloadMaterial"
                    data-id="<?php echo $materials_content["id"]; ?>">
                    <?php echo translate('dwn_download_now') ?>
                </button>
            <?php } else {
                if (!$cookieExist) { ?>
                    <button class="btn btn-primary fancybox.ajax fancyboxValidateModal"
                       data-title="Form"
                       data-mw="470"
                       href="<?php echo $materials_content['form_url']; ?>">
                       <?php echo translate('dwn_download_now') ?>
                    </button>
                <?php } else { ?>
                    <button class="btn btn-primary call-action"
                        id="js-dwn-download"
                        data-js-action="user_action:download"
                        data-id="<?php echo $materials_content["id"]; ?>">
                        <?php echo translate('dwn_download_now') ?>
                    </button>
            <?php }} ?>

            <button class="btn btn-outline-dark call-action call-function"
                    data-link="<?php echo $materials_content['share_url']; ?>"
                    data-js-action="user_register:share_view"
                    data-callback="showDMShareModal">
                <i class="ep-icon ep-icon_share-stroke "></i> <?php echo translate('general_button_share_text') ?>
            </button>
        </div>
    </div>
    <picture id="js-dwn-materials-cover" class="dwn-materials__cover">
        <?php $thumbName = (string) getTempImgThumb('downloadable_materials.cover', 0, $materials_content['cover']);?>
        <source srcset="<?php echo __SITE_URL . getImage(getDownloadableMaterialsCoverPath((int) $materials_content['id'], $thumbName), "public/img/no_image/no-image-324x489.png"); ?>" media="(max-width: 575px)">
        <img src="<?php echo __SITE_URL . getImage($materials_content['cover_path'], "public/img/no_image/no-image-324x489.png"); ?>" alt="<?php echo cleanOutput($materials_content['title']);?>">
    </picture>
</div>

<?php
    if (isset($webpackData)) {
        encoreLinks();
    }
?>

<?php if(!empty($recommended_content)) { ?>
    <div class="dwn-recommended">
        <div class="dwn-recommended__headline"><?php echo translate('dwn_we_recommend'); ?></div>
        <div class="dwn-recommended__container">
            <?php foreach ($recommended_content as $content) { ?>
                <div class="dwn-recommended__block">
                    <div class="dwn-recommended__cover">
                        <img class="js-lazy" src="<?php echo getLazyImage(324, 489); ?>" data-src="<?php echo __SITE_URL . getImage($content['thumb_0_path'], "public/img/no_image/no-image-324x489.png"); ?>" alt="<?php echo cleanOutput($content['title']); ?>">
                    </div>
                    <div class="dwn-recommended__info">
                        <a class="dwn-recommended__title" href="<?php echo $content['article_url']; ?>">
                            <?php echo cleanOutput($content['title']); ?>
                        </a>
                        <div class="dwn-recommended__description">
                            <?php echo cleanOutput($content['short_description']); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<?php views()->display('new/subscribe/subscribe_section_view', [
    'additional_class'   => 'subscribe--downloadable',
    'subscribe_subtitle' => translate('dwn_stay_informed_subtitle'),
    'isDwnMPage'         => $isDwnMPage,
]);?>

<?php echo $autoDownload ? "<input id=\"needDownload\" type=\"hidden\">" : "";?>

<?php if (!$webpackData) { ?>
    <?php views()->display('new/download_script'); ?>
    <script src="/public/plug/js/downloadable_materials/index.js"></script>
<?php } ?>
