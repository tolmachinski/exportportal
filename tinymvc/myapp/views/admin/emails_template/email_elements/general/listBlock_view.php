<?php
    $list = explode(PHP_EOL, $params['listBlockText']);
?>

<?php if (!empty($list)) {?>
<tr>
    <td class="color-dark" style="font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; color: #000000; padding-bottom: 13px">
        <table class="table-dark" border="0" cellpadding="0" cellspacing="0" style="width: 100%; padding: 0; background: #ffffff">
            <tbody>
            <?php
                foreach ($list as $listItem) {
                    if (!empty($listItem)) {
            ?>
                <tr>
                    <td style="width: 16px" valign="top">
                        <div class="color-dark" style="font-size: 24px; line-height: 24px; width: 11px; height: 24px; font-family: Arial, Helvetica, sans-serif">•</div>
                    </td>
                    <td
                        class="color-dark"
                        style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 24px; color: #000000; padding-bottom: 1px"
                        valign="top"
                    ><?php echo $listItem;?></td>
                </tr>
            <?php   }
                }
            ?>
            </tbody>
        </table>
    </td>
</tr>
<?php }?>
