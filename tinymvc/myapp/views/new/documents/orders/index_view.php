<?php views()->display('new/filter_panel_main_view', array('filter_panel' => 'new/documents/orders/list/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <div class="flex-display flex-ai--c flex-w--w pb-15">
            <h1 class="dashboard-line__ttl mr-10 pb-0">
                <?php echo translate('order_documents_dashboard_page_title', null, true); ?>
            </h1>

            <div class="elem-powered-by">
                <div class="elem-powered-by__txt"><?php echo translate('order_documents_dashboard_page_title_highlight_text_1', null, true); ?></div>
                <div class="elem-powered-by__name"><?php echo translate('order_documents_dashboard_page_title_highlight_text_2', null, true); ?></div>
            </div>
        </div>

        <div class="dashboard-line__actions">
            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('orders_docs_description'); ?></span>
	</div>

    <?php views()->display('new/documents/orders/list/grid_view'); ?>
</div>
