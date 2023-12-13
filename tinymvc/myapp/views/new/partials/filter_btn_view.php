<button
    class="filter-btn call-action"
    data-js-action="sidebar:toggle-visibility"
    <?php echo addQaUniqueIdentifier('global__sidebar-filter-btn'); ?>
>
    <?php if (count($items)) { ?>
        <?php echo widgetGetSvgIcon('filter', 17, 17, 'filter-btn__icon'); ?> <span class="filter-btn__txt">  Filter</span>
    <?php } else { ?>
        <?php echo widgetGetSvgIcon('magnifier', 14, 14, 'filter-btn__icon'); ?> <span class="filter-btn__txt">  New Search</span>
    <?php } ?>
</button>
