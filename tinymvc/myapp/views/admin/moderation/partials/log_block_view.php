<div class="flex-display">
    <div class="mr-15">
        <?php echo !empty($message) ? cleanOutput($message) : '-'; ?>
    </div>
    <div style="margin-left: auto">
        <a class="collapse-handler"
            role="button"
            href="#collapse-block-<?php echo $block['ref']; ?>"
            aria-expanded="false"
            aria-controls="collapse-block-<?php echo $block['ref']; ?>">
            <i class="ep-icon ep-icon_plus"></i>
        </a>
    </div>
</div>

<div class="collapse mt-15" id="collapse-block-<?php echo $block['ref']; ?>" style="display: none">
    <div class="well">
        <div>
            <strong>Message:</strong>
            <p>
                <?php echo !empty($block['message']) ? ucfirst($block['message']) : '-'; ?>
            </p>
        </div>
    </div>
</div>