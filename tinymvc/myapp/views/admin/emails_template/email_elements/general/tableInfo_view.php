<tr>
    <td
        class="color-dark"
        style="padding: 0px 0px 13px 0px;"
    >
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; padding: 0;">
            <tbody>
            <tr>
                <?php if((int)$params["tableInfoImage"]){ ?>
                <td width="100px" valign="middle">
                    <img
                        width="100px"
                        src="[tableInfoImage]"
                        alt="[tableInfoTitle]"
                    />
                </td>
                <?php } ?>

                <td style="font-family: Arial; font-size: 14px; line-height:18px; <?php if((int)$params["tableInfoImage"]){ ?>padding-left:30px;<?php } ?>">
                    <?php if((int)$params["tableInfoTitle"]){ ?>
                    <div
                        class="color-dark"
                        style="padding: 0px 0px 3px 0px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 24px; word-wrap: break-word; word-break: break-word; max-width: 520px; color: #000000; font-weight: bold"
                    >
                        [tableInfoTitle]
                    </div>
                    <?php } ?>

                    <?php if((int)$params["tableInfoDescription"]){ ?>
                    <div class="color-dark" style="word-wrap: break-word; word-break: break-word; max-width:520px;">
                        [tableInfoDescription]
                    </div>
                    <?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>
