<table style="width: 100%; border-collapse:collapse; font-size: 14px;color: #000000; border:none;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:50%;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td rowspan="2"><img width="70" height="80" src="<?php echo __IMG_URL . 'public/img/ep-logo/img-logo-header.png';?>" alt="exportportal"/></td>
                    <td style="vertical-align: bottom; padding: 0; height: 45px;"><h1 style="font-size: 28px; font-weight: bold; color: #1d6e0f; margin:0;">EXPORT<span style="color: #0f6c94;">PORTAL</span></h1></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-size: 12px; color: #000000; text-transform: uppercase; padding-left: 5px;"><?php echo translate('home_header_logo_subtitle');?></td>
                </tr>
            </table>
        </td>
        <td style="width:50%;vertical-align:middle;">
            <table style="width: 100%; vertical-align: top;text-align:right;">
                <tr>
                    <td style="text-transform: uppercase; font-size: 24px; font-weight:bold; color: #000000; text-align:right;">
                        Order number <?php echo orderNumber($order['id']);?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:50%; padding-top: 25px; font-size: 14px; color: #000000;"><strong>Issue date:</strong> <?php echo getDateFormatIfNotEmpty($invoice['issue_date'] ?? null, DATE_ATOM, 'F d, Y');?></td>
        <td style="width:50%; padding-top: 25px; font-size: 14px; color: #000000; text-align:right;"><strong>Due date:</strong> <?php echo getDateFormatIfNotEmpty($invoice['due_date'] ?? null, DATE_ATOM, 'F d, Y');?></td>
    </tr>
    <tr>
        <td style="padding-top: 50px; vertical-align:top;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; font-weight:bold; color: #000000;">Recipient</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Company name:</strong> ExportPortal</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Email:</strong> <?php echo config('email_contact_us');?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Address:</strong> <?php echo config('ep_address');?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>International call:</strong> <?php echo config('ep_phone_number');?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Free call:</strong> <?php echo config('ep_phone_number_free');?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>WhatsApp:</strong> <?php echo config('ep_phone_whatsapp');?></td>
                </tr>
            </table>
        </td>
        <td style="padding-top: 50px; vertical-align:top;">
            <table style="width: 100%; vertical-align: top; text-align: left;">
                <tr>
                    <td style="font-size: 22px; font-weight:bold; color: #000000;">Invoiced to</td>
                </tr>

                <?php if (!empty($buyer_company)) {?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Company legal name:</strong> <?php echo $buyer_company['company_legal_name'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Company representative:</strong> <?php echo !empty($buyer['legal_name']) ? $buyer['legal_name'] : $buyer['fname'] . ' ' . $buyer['lname'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Email:</strong> <?php echo $buyer['email'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Address:</strong> <?php echo $buyer_company['location'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Phone:</strong> <?php echo empty($buyer_company['company_phone']) ? '&mdash;' : $buyer_company['company_phone_code'] . ' ' . $buyer_company['company_phone'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Fax:</strong> <?php echo empty($buyer_company['company_fax']) ? '&mdash;' : $buyer_company['company_fax_code'] . ' ' . $buyer_company['company_fax'];?></td>
                    </tr>
                <?php } else {?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Customer:</strong> <?php echo !empty($buyer['legal_name']) ? $buyer['legal_name'] : $buyer['fname'] . ' ' . $buyer['lname'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Email:</strong> <?php echo $buyer['email'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Address:</strong> <?php echo $buyer['location'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Phone:</strong> <?php echo empty($buyer['phone']) ? '&mdash;' : $buyer['phone_code'] . ' ' . $buyer['phone'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Fax:</strong> <?php echo empty($buyer['fax']) ? '&mdash;' : $buyer['fax_code'] . ' ' . $buyer['fax'];?></td>
                    </tr>
                <?php }?>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 30px;">
            <table style="width: 100%; border-collapse:collapse;color: #000000;border:1px solid #d2d2d2;padding:5px;" border="1" cellpadding="0" cellspacing="0" >
                <thead>
                    <tr>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:40px;">№</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:130px;">HS Code</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;">Product</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:80px;">Quantity</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:80px;">Unit</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: right;width:150px;">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $key => $item){?>
                        <tr>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 40px;"><?php echo ++$key;?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 50px;text-align:right;"><?php echo $item['hs_tariff_number'] ?? '&mdash;'; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;">
                                <div style="width: 505px;word-wrap: break-word;"><?php echo $item['name']; ?></div>
                                <div style="width: 505px;"><?php echo $item['details']; ?></div>
                            </td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 50px;text-align:right;"><?php echo $item['quantity']; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 50px;text-align:right;"><?php echo $item['unit_type']['name']; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: right;">$<?php echo get_price($item['total_price'], false)?></td>
                        </tr>
                    <?php }?>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong style="font-size: 16px;">
                                Total
                            </strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong style="font-size: 16px;">
                                $<?php echo get_price($order['final_price'], false);?>
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding-top: 30px;width: 50%; vertical-align:top;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Seller</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company legal name:</strong> <?php echo $seller['legal_name_company'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company representative:</strong> <?php echo !empty($seller['legal_name']) ? $seller['legal_name'] : $seller['fname'] . ' ' . $seller['lname'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Email:</strong> <?php echo $seller['email'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Address:</strong> <?php echo $seller['location'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Phone:</strong> <?php echo empty($seller['phone_company']) ? '&mdash;' : $seller['phone_code_company'] . ' ' . $seller['phone_company'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Fax:</strong> <?php echo empty($seller['fax_company']) ? '&mdash;' : $seller['fax_code_company'] . ' ' . $seller['fax_company'];?></td>
                </tr>
            </table>
        </td>
        <td style="padding-top: 30px;width: 50%; vertical-align:top;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Freight Forwarder</td>
                </tr>
                <?php if (!empty($shipper)) {?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company legal name:</strong> <?php echo $shipper['legal_co_name'];?></td>
                    </tr>
                    <?php if (isset($shipper['contacts'])) {?>
                        <tr>
                            <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Contacts:</strong> <a style="color:#000000; text-decoration:none;" href="<?php echo $shipper['contacts'];?>"><?php echo wordwrap($shipper['contacts'], 36,"\n",true);?></a></td>
                        </tr>
                    <?php }?>
                <?php } else {?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;">&mdash;</td>
                    </tr>
                <?php }?>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding-top: 20px;padding-right:15px;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Delivery from address</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo $order['departure_location'];?></td>
                </tr>
            </table>
        </td>
        <td style="padding-top: 20px;padding-left:15px;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Delivery to address</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo $order['destination_location'];?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if (!empty($invoice['notes'])) {?>
        <tr>
            <td colspan="2" style="padding-top: 20px;padding-right:15px;">
                <table style="width: 100%; vertical-align: top;">
                    <tr>
                        <td style="font-size: 22px; color: #000000;">Notes</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo cleanOutput($invoice['notes']); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php }?>
</table>
