<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js'); ?>"></script>

<div class="container-1420 dashboard-container dt-new">
    <?php views()->display('new/filter_panel_main_view', ['filter_panel' => 'new/item/droplist/filter_panel_view']); ?>
    <div class="dashboard-line dashboard-line--ordered">
        <h1 class="dashboard-line__ttl"><?php echo translate('droplist_header_ttl'); ?></h1>
        <div class="dashboard-line__actions">
            <a class="btn btn-light fancybox btn-filter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel" <?php echo addQaUniqueIdentifier('page__droplist__open_filter_btn')?>>
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div class="droplist__description"><?php echo translate('droplist_description'); ?></div>
    </div>

    <table class="main-data-table" id="dtDropList">
        <thead>
        <tr>
            <th class="dt_item">Item</th>
            <th class="dt_seller">Seller</th>
            <th class="dt_droplist_price">Droplist Price</th>
            <th class="dt_current_price">Current Price</th>
            <th class="dt_added_date">Date Added</th>
            <th class="dt_price_change_date">Price Change Date</th>
            <th class="dt_actions"></th>
        </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php echo dispatchDynamicFragment("drop-list:page", null, true); ?>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/droplist/index.js'); ?>"></script>
