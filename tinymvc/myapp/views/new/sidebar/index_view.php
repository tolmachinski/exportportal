<div id="js-ep-sidebar" class="sidebar<?php echo $showMd ? ' sidebar--show-md' : ''; echo $rightSide ? ' sidebar--right' : '';?>">
    <div class="sidebar__inner">
        <div class="sidebar__heading">
            <button
                class="sidebar__close-btn call-action"
                data-js-action="sidebar:toggle-visibility"
                type="button"
            >
                <i class="ep-icon ep-icon_arrow-left"></i> Hide
            </button>
        </div>
        <div class="sidebar__content">
            <?php views($sidebarContent); ?>
        </div>
    </div>

    <div class="sidebar__bg call-action" data-js-action="sidebar:toggle-visibility"></div>
</div>
