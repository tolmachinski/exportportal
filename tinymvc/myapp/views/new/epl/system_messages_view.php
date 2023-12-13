<?php $mess = session()->getMessages();?>
<div class="system-messages" <?php if (empty($mess)) { ?>style="display:none" <?php } ?>>
    <div class="system-messages__ttl">
        System Message
        <i class="ep-icon ep-icon_remove-stroke call-action" data-js-action="system-mesages:close"></i>
    </div>

    <ul class="system-messages__cards"></ul>
</div>

<?php
if (!empty($mess)) {
    $dataMessage = [];
    foreach ($mess as $type => $messages) {
        foreach ($messages as $message) {
            array_push($dataMessage, ["message" => $message, "type" => $type]);
        }
    }
    echo dispatchDynamicFragment("footer:system-message-init", ["message" => $dataMessage], true);
}
?>
