<div class="no-results-card">
    <div class="no-results-card__ttl"><?php echo translate('no_results_card_title');?></div>
    <div class="no-results-card__message">
        <?php echo $cheerupMessage ?? translate('no_results_card_subtitle');?>
    </div>

    <div class="no-results-card__desc">
        <?php echo $cheerupDescription ?? translate('no_results_card_contact_us_text');?>
    </div>

    <div class="no-results-card__footer">
        <div>
            <div class="no-results-card__info-ttl">
                <?php echo translate('no_results_card_search_tips_block_title');?>
            </div>

            <ul class="no-results-card-list">
                <li class="no-results-card-list__item">
                    <?php echo translate('no_results_card_search_tips_li_1');?>
                </li>
                <li class="no-results-card-list__item">
                    <?php echo translate('no_results_card_search_tips_li_2');?>
                </li>
                <li class="no-results-card-list__item">
                    <?php echo translate('no_results_card_search_tips_li_3');?>
                </li>
            </ul>
        </div>

        <div>
            <div class="no-results-card__info-ttl">
                <?php echo translate('no_results_card_contact_us_block_title');?>
            </div>
            <button
                class="no-results-card__btn btn btn-new16 btn-primary fancybox.ajax fancyboxValidateModal"
                data-title="<?php echo translate('no_results_card_contact_us_btn', null, true);?>"
                data-fancybox-href="<?php echo __SITE_URL . 'contact/popup_forms/contact_us/webpack';?>"
                type="button"
            >
                <?php echo translate('no_results_card_contact_us_btn');?>
            </button>
        </div>
    </div>
</div>
