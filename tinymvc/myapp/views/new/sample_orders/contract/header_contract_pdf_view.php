<htmlpageheader name="myHeader1" style="display:none">
<table width="100%" autosize="0" style="border:0; border-bottom: 2px solid #E2E2E2; vertical-align: top; font-family: Roboto, sans-serif;">
    <tr>
        <td align="left" style="padding-bottom: 42px;">
            <span style="font-size:14pt; color:#707070;">
                <img width="524" height="144" src="<?php echo __IMG_URL . 'public/img/order_contract_pdf/logo-img.png';?>" alt="exportportal">
            </span>
        </td>
        <td align="right" style="padding-right: 137px; padding-top: 32.8px;">
            <div style="padding-bottom: 130px; font-family: Roboto, sans-serif; font-size:24px; color:#000000; font-weight: 500; text-transform: uppercase; text-align: right;">Email</div>
            <div style="font-size:24px; color:#000000; line-height: 39px;"><?php echo config('email_contact_us');?></div>
        </td>
        <td align="right" style="width: 400px; padding-right: 137px; padding-top: 32.8px;">
            <div style="padding-bottom: 130px; font-family: Roboto, sans-serif; font-size:24px; color:#000000; font-weight: 500; text-transform: uppercase; text-align: right;">
                International call
            </div>
            <div style="font-size:24px; color:#000000; line-height: 39px;"><?php echo config('ep_phone_number');?></div>
        </td>
        <td align="right" style="width: 230px; padding-top: 32.8px; border-bottom: 5px solid #1381F3;">
            <div style="padding-bottom: 130px; font-size:24px; color:#000000; font-weight: 500; text-transform: uppercase; text-align: right;">
                Free call
            </div>
            <div style="font-size:24px; color:#000000; line-height: 39px;"><?php echo config('ep_phone_number_free');?></div>
        </td>
    </tr>
</table>
</htmlpageheader>