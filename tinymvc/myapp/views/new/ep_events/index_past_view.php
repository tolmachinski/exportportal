<div class="container-1420">
    <div class="site-content site-content--products-list site-content--sidebar-right">
        <div class="site-main-content">
            <?php views()->display('new/ep_events/past_events_view'); ?>
        </div>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['rightSide' => true]); ?>
    </div>

    <?php
    encoreEntryLinkTags('ep_events_past_page');
    encoreEntryScriptTags('ep_events_past_page');
    ?>
</div>



