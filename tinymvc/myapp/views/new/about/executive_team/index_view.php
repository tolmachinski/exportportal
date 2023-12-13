<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<?php if (!empty($our_team)) { ?>
    <div class="minfo-title pt-70">
        <h3 class="minfo-title__name"><?php echo translate('about_team_title');?></h3>
    </div>

    <?php app()->view->display('new/our_team/our_team_list_view'); ?>
<?php } ?>

<div class="tac">
    <div class="minfo-title pt-70">
        <h3 class="minfo-title__name tt-uppercase"><?php echo translate('about_team_expert_panel_title');?></h3>
    </div>

    <p>
        <?php echo translate('about_team_expert_panel_sub_title');?>
    </p>
</div>

<div class="expert-panel-list">
    <div class="expert-panel-list__item">
        <div class="expert-panel-list__icon">
            <img class="image" src="<?php echo __IMG_URL . 'public/img/about/our_team/icon-advisors.svg';?>" alt="<?php echo translate('about_team_expert_panel_advisors', null, true);?>">
        </div>
        <div class="expert-panel-list__text"><?php echo translate('about_team_expert_panel_advisors');?></div>
        <div class="expert-panel-list__more">
            <a class="link" href="<?php echo __SITE_URL . 'landing/advisors';?>"><?php echo translate('about_team_expert_panel_btn_more');?> <i class="ep-icon ep-icon_arrow-right"></i></a>
        </div>
    </div>
    <div class="expert-panel-list__item">
        <div class="expert-panel-list__icon">
        <img class="image" src="<?php echo __IMG_URL . 'public/img/about/our_team/icon-country-ambassadors.svg';?>" alt="<?php echo translate('about_team_expert_panel_country_ambassadors', null, true);?>">
        </div>
        <div class="expert-panel-list__text"><?php echo translate('about_team_expert_panel_country_ambassadors');?></div>
        <div class="expert-panel-list__more">
            <a class="link" href="<?php echo __SITE_URL . 'landing/country_ambassador';?>"><?php echo translate('about_team_expert_panel_btn_more');?> <i class="ep-icon ep-icon_arrow-right"></i></a>
        </div>
    </div>
    <div class="expert-panel-list__item">
        <div class="expert-panel-list__icon">
        <img class="image" src="<?php echo __IMG_URL . 'public/img/about/our_team/icon-content-ambassadors.svg';?>" alt="<?php echo translate('about_team_expert_panel_content_ambassadors', null, true);?>">
        </div>
        <div class="expert-panel-list__text"><?php echo translate('about_team_expert_panel_content_ambassadors');?></div>
        <div class="expert-panel-list__more">
            <a class="link" href="<?php echo __SITE_URL . 'landing/content_ambassador';?>"><?php echo translate('about_team_expert_panel_btn_more');?> <i class="ep-icon ep-icon_arrow-right"></i></a>
        </div>
    </div>
    <div class="expert-panel-list__item">
        <div class="expert-panel-list__icon">
            <img class="image" src="<?php echo __IMG_URL . 'public/img/about/our_team/icon-industry-ambassador.svg';?>" alt="<?php echo translate('about_team_expert_panel_industry_ambassador', null, true);?>">
        </div>
        <div class="expert-panel-list__text"><?php echo translate('about_team_expert_panel_industry_ambassador');?></div>
        <div class="expert-panel-list__more">
            <a class="link" href="<?php echo __SITE_URL . 'landing/industry_ambassador';?>"><?php echo translate('about_team_expert_panel_btn_more');?> <i class="ep-icon ep-icon_arrow-right"></i></a>
        </div>
    </div>
    <div class="expert-panel-list__item">
        <div class="expert-panel-list__icon">
            <img class="image" src="<?php echo __IMG_URL . 'public/img/about/our_team/icon-government-association.svg';?>" alt="<?php echo translate('about_team_expert_panel_government_association', null, true);?>">
        </div>
        <div class="expert-panel-list__text"><?php echo translate('about_team_expert_panel_government_association');?></div>
        <div class="expert-panel-list__more">
            <a class="link" href="<?php echo __SITE_URL . 'landing/government_and_association';?>"><?php echo translate('about_team_expert_panel_btn_more');?> <i class="ep-icon ep-icon_arrow-right"></i></a>
        </div>
    </div>
</div>

<?php if(!empty($vacancies_list)){?>
    <div class="minfo-title pt-60">
        <h3 class="minfo-title__name"><?php echo translate('about_our_vacancies_title');?></h3>
    </div>
    <?php app()->view->display('new/hiring/vacancies_list_view'); ?>
<?php }?>
