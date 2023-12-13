<?php
    $all_params_are_empty = true;
    $is_not_empty_company_index_name = !empty($company['index_name']);
?>

<?php if (!empty($company['description_company'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info" <?php echo addQaUniqueIdentifier('seller__wall_item'); ?>>
        <div class="detail-info__ttl">
            <h1 class="detail-info__ttl-name"><?php echo translate('seller_home_page_description_company_block_title');?></h1>
        </div>
        <div class="detail-info__toggle">
            <div class="ep-tinymce-text txt-break-word" <?php echo addQaUniqueIdentifier('seller__wall_news_text'); ?>>
                <?php echo $company['description_company']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if (!empty($company['video_company'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info" <?php echo addQaUniqueIdentifier('seller__wall_item'); ?>>
        <div class="detail-info__ttl">
            <h1 class="detail-info__ttl-name"><?php echo translate('seller_home_page_company_overview_block_title');?></h1>
        </div>
        <div class="detail-info__toggle">
            <a class="wr-video-link fancybox.iframe fancyboxVideo" href="<?php echo get_video_link($company['video_company_code'], $company['video_company_source']);?>" data-title="<?php echo translate('seller_home_page_company_overview_block_title', null, true);?>" <?php echo addQaUniqueIdentifier("seller__wall-item_video-img"); ?> >
                <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
                <img class="image" src="<?php echo $videoImagePath?>" alt="<?php echo $company['name_company'];?>">
            </a>
        </div>
    </div>
<?php }?>


<?php if ($is_not_empty_company_index_name && !empty($about_page['text_about_us'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info">
        <div class="detail-info__ttl">
            <h2 class="detail-info__ttl-name">
                <?php echo translate('seller_home_page_about_company_block_title');?>
            </h2>
        </div>
        <div class="detail-info__toggle">
            <div class="ep-tinymce-text">
                <?php echo $about_page['text_about_us']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if ($is_not_empty_company_index_name && !empty($about_page['text_history'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info">
        <div class="detail-info__ttl">
            <h2 class="detail-info__ttl-name">
                <?php echo translate('seller_home_page_company_history_block_title');?>
            </h2>
        </div>
        <div class="detail-info__toggle">
            <div class="ep-tinymce-text" id="block_history">
                <?php echo $about_page['text_history']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if ($is_not_empty_company_index_name && !empty($about_page['text_what_we_sell'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info">
        <div class="detail-info__ttl">
            <h2 class="detail-info__ttl-name">
                <?php echo translate('seller_home_page_products_services_company_block_title');?>
            </h2>
        </div>
        <div class="detail-info__toggle">
            <div class="ep-tinymce-text" id="block_what_we_sell">
                <?php echo $about_page['text_what_we_sell']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if ($is_not_empty_company_index_name && !empty($about_page['text_research_develop_abilities'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info">
        <div class="detail-info__ttl">
            <h2 class="detail-info__ttl-name">
                <?php echo translate('seller_home_page_company_research_and_develop_abilities_block_title');?>
            </h2>
        </div>
        <div class="detail-info__toggle">
            <div class="ep-tinymce-text" id="block_research_develop_abilities">
                <?php echo $about_page['text_research_develop_abilities']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if ($is_not_empty_company_index_name && !empty($about_page['text_development_expansion_plans'])) { ?>
    <?php $all_params_are_empty = false;?>

    <div class="detail-info">
        <div class="detail-info__ttl">
            <h2 class="detail-info__ttl-name">
                <?php echo translate('seller_home_page_company_development_expansion_plans_block_title');?>
            </h2>
        </div>

        <div class="detail-info__toggle">
            <div class="ep-tinymce-text" id="block_development_expansion_plans">
                <?php echo $about_page['text_development_expansion_plans']; ?>
            </div>
        </div>
    </div>
<?php }?>

<?php if ($is_not_empty_company_index_name && $company['user_group'] == 6) { ?>
    <?php if (!empty($about_page['text_prod_process_management'])) { ?>
        <?php $all_params_are_empty = false;?>

        <div class="detail-info">
            <div class="detail-info__ttl">
                <h2 class="detail-info__ttl-name">
                    <?php echo translate('seller_home_page_company_production_process_management_block_title');?>
                </h2>
            </div>
            <div class="detail-info__toggle">
                <div class="ep-tinymce-text" id="block_prod_process_management">
                    <?php echo $about_page['text_prod_process_management'];?>
                </div>
            </div>
        </div>
    <?php }?>

    <?php if (!empty($about_page['text_production_flow'])) { ?>
        <?php $all_params_are_empty = false;?>

        <div class="detail-info">
            <div class="detail-info__ttl">
                <h2 class="detail-info__ttl-name">
                    <?php echo translate('seller_home_page_company_production_flow_block_title');?>
                </h2>
            </div>
            <div class="detail-info__toggle">
                <div class="ep-tinymce-text" id="block_production_flow">
                    <?php echo $about_page['text_production_flow']; ?>
                </div>
            </div>
        </div>
    <?php }?>
<?php }?>

<?php if ($is_not_empty_company_index_name && !empty($about_page_additional)) {?>
    <?php $all_params_are_empty = false;?>

    <?php foreach ($about_page_additional as $item) { ?>
        <div class="detail-info">
            <div class="detail-info__ttl">
                <h2 class="detail-info__ttl-name">
                    <?php echo $item['title_block'];?>
                </h2>
            </div>

            <div class="detail-info__toggle">
                <div class="ep-tinymce-text" id="block_<?php echo $item['id_block']; ?>">
                    <?php echo $item['text_block']; ?>
                </div>
            </div>
        </div>
    <?php }?>
<?php }?>

<?php if ($all_params_are_empty || isset($backstopTest)) {?>
    <div class="bg-arrived-board">
        <img class="image" src="<?php echo __IMG_URL . 'public/img/arrived_board/bg-en.jpg';?>" alt="<?php echo translate('seller_home_page_arrived_on_the_board_img_alt', null, true);?>">
    </div>
<?php }?>
