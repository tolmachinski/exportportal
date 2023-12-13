<?php app()->view->display('new/two_mobile_buttons_view'); ?>

<?php if (empty($keywords) && 1 === $page) { ?>
    <div class="title-public pb-26">
        <h2 class="title-public__txt title-public__txt--26"><?php echo translate('help_title_faq'); ?></h2>
    </div>
<?php } ?>

<?php if (!empty($keywords)) { ?>
    <div class="title-public pb-26">
        <h2 class="title-public__txt title-public__txt--26"><?php echo translate('community_questions_search_params_keywords'); ?>: <?php if (isset($keywords)) echo $keywords ?></h2>
        <span class="minfo-title__total">Found <?php echo $count_faq_list; ?> <?php echo translate('help_faqs');?></span>
    </div>
<?php } ?>

<!-- TEST TAG MENU -->
<?php if (
    !empty($faq_tags_list &&
    empty($search_params) &&
    1 === $page)
) { ?>
    <div class="faq-tag-menu">
        <?php foreach($faq_tags_list as $id_tag => $tag) { ?>
            <div class="faq-tag-menu__block">
                <a class="faq-tag-menu__link" href="<?php echo replace_dynamic_uri($tag['slug'], $tag_link, __SITE_URL . 'faq/all'); ?>">
                    <h2 class="faq-tag-menu__title"><?php echo $tag['name'] ?></h2>
                    <p class="faq-tag-menu__count"><?php echo $faq_tags_counters[$id_tag]['counter']; ?> questions</p>
                </a>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if (empty($search_params)) {?>
    <div class="title-public pb-26">
        <h2 class="title-public__txt title-public__txt--26">Important questions</h2>
    </div>
<?php }?>

<div class="bdt-1-gray">
    <?php app()->view->display('new/faq/partial_list_view');?>
</div>

<div class="pt-55 flex-display flex-jc--sb flex-ai--c">
    <?php app()->view->display("new/paginator_view"); ?>
</div>

<div class="show-767 dn-md-min" <?php echo addQaUniqueIdentifier('faq__banner-demo-bottom'); ?>>
    <?php echo widgetShowBanner('faq_before_become_a_partner', 'promo-banner-wr--faq'); ?>
</div>

<?php if(!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/webinar_requests/schedule-demo-popup.js');?>"></script>
<?php } ?>
