<table style="width: 100%; border-collapse:collapse; font-size: 14px;color: #000000; border:none;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:50%;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td rowspan="2"><img width="70" height="80" src="<?php echo __IMG_URL;?>public/img/ep-logo/img-logo-header.png" alt="exportportal"/></td>
                    <td style="vertical-align: bottom; padding: 0; height: 45px;"><h1 style="font-size: 28px; font-weight: bold; color: #1d6e0f; margin:0;">EXPORT<span style="color: #0f6c94;">PORTAL</span></h1></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-size: 12px; color: #000000; text-transform: uppercase; padding-left: 5px;">The <strong>Nr.1</strong> Export &amp; Import Source</td>
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
        <td style="width:50%; padding-top: 25px; font-size: 14px; color: #000000;"><strong>Issue date:</strong> <?php echo getDateFormat($invoice_info['issue_date'],'Y-m-d H:i:s', 'F d, Y');?></td>
        <td style="width:50%; padding-top: 25px; font-size: 14px; color: #000000; text-align:right;"><strong>Due date:</strong> <?php echo getDateFormat($invoice_info['due_date'], 'Y-m-d', 'F d, Y');?></td>
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

                <?php if(isset($company_buyer_info) && !empty($company_buyer_info)){?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Company legal name:</strong> <?php echo $company_buyer_info['company_legal_name'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Company representative:</strong> <?php echo $buyer_info['buyer_name'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Email:</strong> <?php echo $buyer_info['email'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Address:</strong> <?php echo $company_buyer_info['company_address'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Phone:</strong> <?php echo (!empty($company_buyer_info['company_phone']))?$company_buyer_info['company_phone_code'] . ' ' . $company_buyer_info['company_phone']:'&mdash;';?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Fax:</strong> <?php echo (!empty($company_buyer_info['company_fax']))?$company_buyer_info['company_fax_code'] . ' ' . $company_buyer_info['company_fax']:'&mdash;';?></td>
                    </tr>
                <?php }else{?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Customer:</strong> <?php echo $buyer_info['buyer_name'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Email:</strong> <?php echo $buyer_info['email'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Address:</strong> <?php echo $buyer_info['buyer_location'];?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Phone:</strong> <?php echo (!empty($buyer_info['phone']))?$buyer_info['phone_code'] . ' ' . $buyer_info['phone']:'&mdash;';?></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px;"><strong>Fax:</strong> <?php echo (!empty($buyer_info['fax']))?$buyer_info['fax_code'] . ' ' . $buyer_info['fax']:'&mdash;';?></td>
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
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:40px;">#</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:130px;">HS Code</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;">Product</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: left;width:80px;">Quantity</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: right;width:120px;">Unit Value</th>
                        <th style="border:1px solid #d2d2d2;padding:5px;text-align: right;width:150px;">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $key => $item){?>
                        <tr>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 40px;"><?php echo $key+1; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 50px;text-align:right;"><?php echo !empty($item['hs_tariff_number']) ? $item['hs_tariff_number'] : '&mdash;'; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;">
                                <div style="width: 505px;word-wrap: break-word;"><?php echo $item['name']; ?></div>
                                <div style="width: 505px;"><?php echo $item['detail_ordered']; ?></div>
                            </td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: left;"><div style="width: 50px;text-align:right;"><?php echo $item['quantity']; ?></div></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: right;">$<?php echo get_price($item['unit_price'], false)?></td>
                            <td style="border:1px solid #d2d2d2;padding:5px;text-align: right;">$<?php echo get_price(($item['unit_price'] * $item['quantity']), false)?></td>
                        </tr>
                    <?php }?>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>Subtotal</strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>$<?php echo get_price($order['price'], false);?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>Discount</strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong><?php echo normalize_discount($invoice_info['discount']);?>%</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>Order final price</strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>$<?php echo get_price($order['final_price'], false); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong>Shipping price</strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <?php echo $order['id_shipper'] > 0 ? '<strong>$' . get_price($order['ship_price'], false) .'</strong>' : '&mdash;';?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong style="font-size: 16px;">
                                Total
                            </strong>
                        </td>
                        <td style="border:1px solid #d2d2d2;padding:5px;text-align:right;">
                            <strong style="font-size: 16px;">
                                $<?php echo get_price(($order['final_price']+$order['ship_price']), false);?>
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
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company legal name:</strong> <?php echo $seller_info['legal_name_company'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company representative:</strong> <?php echo !empty($seller_info['legal_name']) ? $seller_info['legal_name'] : $seller_info['fname'].' '.$seller_info['lname'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Email:</strong> <?php echo $seller_info['email'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Address:</strong> <?php echo $seller_info['company_location'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Phone:</strong> <?php echo $seller_info['phone_code_company'] . ' ' . $seller_info['phone_company'];?></td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Fax:</strong> <?php echo $seller_info['fax_code_company'] . ' ' . $seller_info['fax_company'];?></td>
                </tr>
            </table>
        </td>
        <td style="padding-top: 30px;width: 50%; vertical-align:top;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Freight Forwarder</td>
                </tr>
                <?php if(!empty($shipper_info)){?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Company legal name:</strong> <?php echo $shipper_info['legal_co_name'];?></td>
                    </tr>
                    <?php if(isset($shipper_info['contacts'])){?>
                        <tr>
                            <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Contacts:</strong> <a style="color:#000000; text-decoration:none;" href="<?php echo $shipper_info['contacts'];?>"><?php echo wordwrap($shipper_info['contacts'], 36,"\n",true);?></a></td>
                        </tr>
                    <?php } else{?>
                        <tr>
                            <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Email:</strong> <?php echo $shipper_info['email'];?></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Phone:</strong> <?php echo $shipper_info['phone_code'] . ' ' . $shipper_info['phone'];?></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><strong>Fax:</strong> <?php echo $shipper_info['fax_code'] . ' ' . $shipper_info['fax'];?></td>
                        </tr>
                    <?php }?>
                <?php } else{?>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;">The freight forwarder information will be provided on the next step, after it’s assignment by the Buyer.</td>
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
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo $order['ship_from'];?></td>
                </tr>
            </table>
        </td>
        <td style="padding-top: 20px;padding-left:15px;">
            <table style="width: 100%; vertical-align: top;">
                <tr>
                    <td style="font-size: 22px; color: #000000;">Delivery to address</td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo $order['ship_to'];?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if(!empty($invoice_info['notes'])){?>
        <tr>
            <td colspan="2" style="padding-top: 20px;padding-right:15px;">
                <table style="width: 100%; vertical-align: top;">
                    <tr>
                        <td style="font-size: 22px; color: #000000;">Notes</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #000000;padding-top:5px; padding-right:15px;"><?php echo $invoice_info['notes'];?></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php }?>
</table>
