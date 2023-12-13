
<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#main-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('label_menu');?>
    </a>
</div>

<div  class="title-public pt-0 pb-30 mt-50">
    <h2 class="title-public__txt title-public__txt--26">List of Trade News</h2>
</div>

<div class="trade-news-all">
    <?php echo views()->display('new/trade_news/list_trade_news_view');?>

    <?php views()->display('new/paginator_view'); ?>
</div>


