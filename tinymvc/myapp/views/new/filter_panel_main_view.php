<div id="dtfilter-hidden" class="display-n">
    <div class="dtfilter-popup clearfix inputs-40">
        <ul class="nav nav-tabs nav--borders dn-md-min" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#filter-inputs" aria-controls="title" role="tab" data-toggle="tab">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" <?php echo addQaUniqueIdentifier("global__dashboard_filter-panel_active-filters-btn")?> href="#filter-selected" aria-controls="title" role="tab" data-toggle="tab">Active</a>
            </li>
        </ul>

        <div class="tab-content tab-content--borders flex-display pt-0">
            <div class="col-12 col-md-7 pr-0-sm-max pl-0 tab-pane active" id="filter-inputs">
                <?php tmvc::instance()->controller->view->display($filter_panel); ?>
            </div>
            <div class="col-12 col-md-5 pr-0 tab-pane" id="filter-selected">
                <label class="input-label">Active filters</label>
                <div class="dtfilter-list"></div>
            </div>
        </div>
    </div>
</div>
