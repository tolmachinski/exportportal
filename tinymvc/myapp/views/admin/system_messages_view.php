<?php $mess = session()->getMessages();?>
<div <?php if(empty($mess)){?>style="display:none"<?php }?> class="system-messages system-text-messages-b">
	<div class="ttl-b">System Message <span class="pull-right ep-icon ep-icon_remove"></span></div>
    <ul class="system-messages__cards">
        <?php if (!empty($mess['success'])) {?>
            <?php views(
                'new/system_messages/array_to_list_view',
                [
                    'systemMessages'    => $mess['success'],
                    'ulClass'           => 'message-success',
                ]
            );?>
        <?php }?>

        <?php if (!empty($mess['errors'])) {?>
            <?php views(
                'new/system_messages/array_to_list_view',
                [
                    'systemMessages'    => $mess['errors'],
                    'ulClass'           => 'message-error',
                ]
            );?>
        <?php }?>

        <?php if (!empty($mess['info'])) {?>
            <?php views(
                'new/system_messages/array_to_list_view',
                [
                    'systemMessages'    => $mess['info'],
                    'ulClass'           => 'message-info',
                ]
            );?>
        <?php }?>

        <?php if (!empty($mess['warning'])) {?>
            <?php views(
                'new/system_messages/array_to_list_view',
                [
                    'systemMessages'    => $mess['warning'],
                    'ulClass'           => 'message-warning',
                ]
            );?>
        <?php }?>
    </ul>
</div>
