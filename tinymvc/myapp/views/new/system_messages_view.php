<?php $mess = session()->getMessages();?>
<div class="system-messages" <?php if(isset($webpackData) || empty($mess)){?>style="display:none"<?php }?>>
	<div class="system-messages__ttl call-function call-action" data-callback="systemMessagesClose" data-js-action="system-mesages:close">System Message <i class="ep-icon ep-icon_remove-stroke"></i></div>

	<ul class="system-messages__cards">
        <?php if (!isset($webpackData)) {?>
            <?php if (!empty($mess['success'])) {?>
                <?php views(
                    'new/system_messages/array_to_list_view',
                    [
                        'systemMessages'    => $mess['success'],
                        'ulClass'           => 'success',
                    ]
                );?>
            <?php }?>

            <?php if (!empty($mess['errors'])) {?>
                <?php views(
                    'new/system_messages/array_to_list_view',
                    [
                        'systemMessages'    => $mess['errors'],
                        'ulClass'           => 'error',
                    ]
                );?>
            <?php }?>

            <?php if (!empty($mess['info'])) {?>
                <?php views(
                    'new/system_messages/array_to_list_view',
                    [
                        'systemMessages'    => $mess['info'],
                        'ulClass'           => 'info',
                    ]
                );?>
            <?php }?>

            <?php if (!empty($mess['warning'])) {?>
                <?php views(
                    'new/system_messages/array_to_list_view',
                    [
                        'systemMessages'    => $mess['warning'],
                        'ulClass'           => 'warning',
                    ]
                );?>
            <?php }?>
        <?php }?>
	</ul>
</div>

<?php
    if(!empty($mess) && isset($webpackData)) {
        $dataMessage = [];
        foreach($mess as $type => $messages){
            foreach($messages as $message){
                array_push($dataMessage, ["message" => $message, "type" => $type]);
            }
        }
        echo dispatchDynamicFragment("footer:system-message-init", ["message"=> $dataMessage], true);
    }
?>
