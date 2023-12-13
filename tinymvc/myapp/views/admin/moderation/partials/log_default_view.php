<div class="flex-display">
    <div class="mr-15">
        <?php echo !empty($message) ? cleanOutput($message) : '-'; ?>
    </div>
    <div style="margin-left: auto">
        <?php if(!empty($context['type']) && 'edit' === $step) { ?>
            <?php
                if(
                    \App\Moderation\Types\TYPE_COMPANY === $context['type'] ||
                    \App\Moderation\Types\TYPE_COMPANY_NAME === $context['type']
                ) {
            ?>
                <a href="<?php echo __SITE_URL . "admin?company={$resource['id']}&date={$date->format('Y-m-d')}"; ?>" target="_blank" title="Show resource activity">
                    <i class="ep-icon ep-icon_link"></i>
                </a>
            <?php } ?>
            <?php
                if(
                    \App\Moderation\Types\TYPE_ITEM === $context['type'] ||
                    \App\Moderation\Types\TYPE_ITEM_NAME === $context['type']
                ) {
            ?>
                <a href="<?php echo __SITE_URL . "admin?item={$resource['id']}&date={$date->format('Y-m-d')}"; ?>" target="_blank" title="Show resource activity">
                    <i class="ep-icon ep-icon_link"></i>
                </a>
            <?php } ?>
        <?php } ?>
    </div>
</div>