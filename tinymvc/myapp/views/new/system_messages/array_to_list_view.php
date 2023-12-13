<?php foreach ($systemMessages as $systemMessage) {?>
    <li class="<?php echo "system-messages__card system-messages__card--{$ulClass}";?>">
        <div class="system-messages__card-ttl">
            <strong><?php echo $ulClass;?></strong>
            <i class="ep-icon ep-icon_remove-stroke call-function" data-callback="systemMessagesCardClose"></i>
        </div>
        <div class="system-messages__card-txt">
            <?php echo $systemMessage;?>
        </div>
    </li>
<?php }?>
