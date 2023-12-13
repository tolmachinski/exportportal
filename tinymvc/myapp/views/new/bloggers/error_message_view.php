<div id="messages-container" style="display:none;">
    <?php foreach ($messages as $message) { ?>
        <?php if(is_string($message)) { ?>
            <div class="message-entry" data-type="error">
                <div class="title"></div>
                <div class="detail"><?php echo $message; ?></div>
            </div>
        <?php } else if(is_array($message)) { ?>
            <div class="message-entry" data-type="<?php echo !empty($message['type']) ? $message['type'] : 'error'; ?>">
                <div class="title"><?php echo !empty($message['title']) ? $message['title'] : ''; ?></div>
                <div class="detail"><?php echo !empty($message['detail']) ? $message['detail'] : 'Undefined error'; ?></div>
            </div>
        <?php } ?>
    <?php } ?>
</div>
<script type="application/javascript">
    $(document).ready(function() {
        var messagesContainer = $("#messages-container");
        var messagesEntries = messagesContainer.find('.message-entry');
        if(messagesEntries.length) {
            messagesEntries.each(function(i, element) {
                var node = $(element);
                var type = node.data('type') || 'error';
                var title = node.find('.title').text();
                var detail = node.find('.detail').text();
                if(Messenger) {
                    Messenger.notification(type, detail, title);
                } else {
                    alert(detail);
                }
            });
        }

        setTimeout(function(){
            $.fancybox.close();
        }, 1);
    });
</script>