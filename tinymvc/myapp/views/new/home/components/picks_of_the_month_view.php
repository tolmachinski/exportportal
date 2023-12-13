<section class="home-section picks-of-month container-1420">
    <div class="section-header section-header--title-only">
        <?php
            $picksOfMonthTitle = [
                "date"  => date('F'),
                "atas"  => addQaUniqueIdentifier("home__picks-of-month_title_date"),
            ];
        ?>
        <h2 class="section-header__title"><?php echo translate('home_picks_of_month_title', ['[[MONTH]]' => "<span {$picksOfMonthTitle['atas']}>{$picksOfMonthTitle['date']}</span>"]); ?></h2>
    </div>
    <div class="picks-of-month__content">
        <div class="picks-of-month__list js-picks-of-month loading" data-lazy-name="picks-of-month"></div>
        <p class="picks-of-month__subscribe">
            <a class="picks-of-month__subscribe-link" href="<?php echo __SITE_URL . 'subscribe'; ?>" <?php echo addQaUniqueIdentifier("home__picks-of-month-subscribe"); ?>><?php echo translate('home_picks_of_month_subscribe'); ?></a> <?php echo translate('home_picks_of_month_subtitle'); ?>
        </p>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
