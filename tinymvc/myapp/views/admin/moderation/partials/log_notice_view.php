<div class="flex-display">
    <div class="mr-15">
        <?php echo !empty($message) ? cleanOutput($message) : '-'; ?>
    </div>
    <div style="margin-left: auto">
        <a class="collapse-handler"
            role="button"
            href="#collapse-notice-<?php echo $notice['ref']; ?>"
            aria-expanded="false"
            aria-controls="collapse-notice-<?php echo $notice['ref']; ?>">
            <i class="ep-icon ep-icon_plus"></i>
        </a>
    </div>
</div>

<div class="collapse mt-15" id="collapse-notice-<?php echo $notice['ref']; ?>" style="display: none">
    <div class="well">
        <div>
            <strong>Subject:</strong>
            <p>
                <?php echo !empty($notice['subject']) ? ucfirst($notice['subject']) : '-'; ?>
            </p>
        </div>
        <div>
            <strong>Message:</strong>
            <p>
                <?php echo !empty($notice['message']) ? ucfirst($notice['message']) : '-'; ?>
            </p>
        </div>
    </div>
</div>