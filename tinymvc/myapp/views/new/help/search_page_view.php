<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Sidebar" href="#main-flex-card__fixed-right">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('label_sidebar');?>
    </a>
</div>

<div class="title-public pt-25">
    <h2 class="title-public__txt">Found <span <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>>(<?php echo $total_count ?>)</span></h2>
</div>

<?php if ($total_count > 0) { ?>
    <?php tmvc::instance()->controller->view->display('new/help/partial_list_view');?>
<?php } else { ?>
    <?php tmvc::instance()->controller->view->display('new/help/results_not_found_view');?>
<?php } ?>
