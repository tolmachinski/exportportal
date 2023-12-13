<link rel="stylesheet" type="text/css" media="all" href="<?php echo fileModificationTime('public/css/style_pdf_new.css');?>" />

<?php tmvc::instance()->controller->view->display('new/order/contract/header_contract_pdf_view'); ?>

<div class="title-number tac">
    Contract number: <span class="title-number__nr">№<?php echo orderNumberOnly($order['id']);?></span> Issue date: <span class="title-number__nr"><?php echo getDateFormat($invoice_info['issue_date'],'Y-m-d H:i:s', 'm.d.Y');?></span>
</div>

<div class="contract-title tac">
    <div class="contract-title__name">DISTRIBUTION AGREEMENT</div>
</div>

<p class="text-p"><strong>THIS DISTRIBUTION AGREEMENT</strong> (this “Agreement”) made and entered into this ___ day of ____________, 20____ (the “Effective Date”), is by and among: </p>


<table width="100%" autosize="0" style="margin-top: 83px; border:0; vertical-align: top; font-family: Roboto, sans-serif;">
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">
                        BUYER -
                        <?php if(isset($company_buyer_info) && !empty($company_buyer_info)){?>
                            <?php echo $company_buyer_info['company_name'];?>
                        <?php }else{?>
                            <?php echo $buyer_info['buyer_name'];?>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo orderNumber($buyer_info['idu']);?></td>
                </tr>
                <?php if(isset($company_buyer_info) && !empty($company_buyer_info)){?>
                    <tr>
                        <td class="card-user__name">Company legal name:</td>
                        <td class="card-user__val"><?php echo $company_buyer_info['company_legal_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Represented by:</td>
                        <td class="card-user__val"><?php echo $buyer_info['buyer_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $company_buyer_info['company_address'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo (!empty($company_buyer_info['company_phone']))?$company_buyer_info['company_phone_code'] . ' ' . $company_buyer_info['company_phone']:'&mdash;';?></td>
                    </tr>
                <?php }else{?>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $buyer_info['buyer_location'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo (!empty($buyer_info['phone']))?$buyer_info['phone_code'] . ' ' . $buyer_info['phone']:'&mdash;';?></td>
                    </tr>
                <?php }?>
                <tr>
                    <td class="card-user__name">Email:</td>
                    <td class="card-user__val"><span class="txt-underline"><?php echo $buyer_info['email'];?></span></td>
                </tr>

                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo __SITE_URL;?>usr/<?php echo strForURL($buyer_info['fname'].'-'.$buyer_info['lname']).'-'.$buyer_info['idu'];?></td>
                </tr>
            </table>
        </td>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">SELLER  -  <?php echo $seller_info['name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Company legal name:</td>
                    <td class="card-user__val"><?php echo $seller_info['legal_name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Represented by:</td>
                    <td class="card-user__val"><?php echo !empty($seller_info['legal_name']) ? $seller_info['legal_name'] : $seller_info['fname'].' '.$seller_info['lname'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo orderNumber($seller_info['idu']);?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Address:</td>
                    <td class="card-user__val"><?php echo $seller_info['company_location'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Email:</td>
                    <td class="card-user__val"><span class="txt-underline"><?php echo $seller_info['email'];?></span></td>
                </tr>
                <tr>
                    <td class="card-user__name">Phone:</td>
                    <td class="card-user__val"><?php echo $seller_info['phone_code_company'] . ' ' . $seller_info['phone_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo getCompanyURL($seller_info);?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">FREIGHT FORWARDER -  <?php echo $shipper_info['co_name'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Company legal name:</td>
                    <td class="card-user__val"><?php echo $shipper_info['legal_co_name'];?></td>
                </tr>
                <?php if(isset($shipper_info['contacts'])){?>
                    <tr>
                        <td class="card-user__name">Contacts:</td>
                        <td class="card-user__val"><a style="color:#000000; text-decoration:none;" href="<?php echo $shipper_info['contacts'];?>"><?php echo wordwrap($shipper_info['contacts'], 36,"\n",true);?></a></td>
                    </tr>
                <?php } else{?>
                    <tr>
                        <td class="card-user__name">Represented by:</td>
                        <td class="card-user__val"><?php echo $shipper_info['user_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Unique ID:</td>
                        <td class="card-user__val"><?php echo orderNumber($shipper_info['id_user']);?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $shipper_info['shipper_location'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Email:</td>
                        <td class="card-user__val"><span class="txt-underline"><?php echo $shipper_info['email'];?></span></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo $shipper_info['phone_code'] . ' ' . $shipper_info['phone'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">EP profile link:</td>
                        <td class="card-user__val"><?php echo getShipperURL($shipper_info);?></td>
                    </tr>
                <?php }?>
            </table>
        </td>
    </tr>
</table>

<pagebreak>

<table width="100%" autosize="0" style="border:0; margin-top: 10px; vertical-align: top; font-family: Roboto, sans-serif;">
    <tr>
        <td style="border: 1px solid #000000; height: 100%;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tbody>
                    <tr>
                        <td class="card-user__ttl" colspan="2">SHIPPING</td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Country of Origin:</td>
                        <td class="card-user__val"><?php echo $ship_from_exploded['country'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Origin/pickup address:</td>
                        <td class="card-user__val"><?php echo $ship_from_exploded['address'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Country of Destination:</td>
                        <td class="card-user__val"><?php echo $ship_to_exploded['country'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Destination address:</td>
                        <td class="card-user__val"><?php echo $ship_to_exploded['address'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Shipping type:</td>
                        <td class="card-user__val"><?php echo $order['shipping_quote_details']['shipment_type'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Shipping Pickup date:</td>
                        <td class="card-user__val"><?php echo getDateFormat($order['status_countdown'],'Y-m-d H:i:s', 'm.d.Y');?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Estimated delivery:</td>
                        <td class="card-user__val">
                        <?php echo !empty($order['shipping_quote_details']) ? $order['shipping_quote_details']['delivery_from_days']. ' - '. $order['shipping_quote_details']['delivery_to_days']. ' days': '&mdash;';?>
                        </td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Conditions:</td>
                        <td class="card-user__val"><?php echo !empty($order['shipping_quote_details']['shipment_conditions']) ? $order['shipping_quote_details']['shipment_conditions'] : '&mdash;';?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="card-user__footer-price" style="border-top: 1px solid #000000; padding-top: 20px;" colspan="2">
                            <span class="card-user__footer-price-name">Shipping price</span> $<?php echo get_price($order['shipping_quote_details']['amount'], false);?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </td>
    </tr>
</table>

<?php if(!empty($order['shipping_insurance_details'])){?>
    <table width="100%" autosize="0" style="border:0; margin-top: 10px; vertical-align: top; font-family: Roboto, sans-serif;">
        <tr>
            <td style="border: 1px solid #000000; height: 100%;">
                <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                    <tbody>
                        <tr>
                            <td class="card-user__ttl" colspan="2">INSURANCE</td>
                        </tr>
                        <tr>
                            <td class="card-user__name">Conditions:</td>
                            <td class="card-user__val"><?php echo $order['shipping_insurance_details']['description'];?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="card-user__footer-price" style="border-top: 1px solid #000000; padding-top: 20px;" colspan="2">
                                <span class="card-user__footer-price-name">Insurance price</span> $<?php echo get_price($order['shipping_insurance_details']['amount'], false);?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>
<?php }?>

<pagebreak>

<div class="contract-title__name tac">WITNESSETH:</div>

<p class="text-p"><strong>WHEREAS</strong>, the Company owns and operates a worldwide product distribution website known as Export Portal (the “Portal”); </p>
<p class="text-p"><strong>WHEREAS</strong>, Seller is a wholesaler of products registered to sell goods on the Portal; </p>
<p class="text-p"><strong>WHEREAS</strong>, Buyer has agreed to buy certain products of Seller (the “Products”) via the Portal, upon price and other terms as listed more particularly in Exhibit “A,” which is attached hereto and incorporated herein by reference herein; </p>
<p class="text-p"><strong>WHEREAS</strong>, Freight Forwarder warrants that it has the necessary experience and expertise to ship the Products purchased via the Portal to Buyer as specified in Exhibit A. </p>
<p class="text-p"><strong>NOW, THEREFORE</strong>, for and in consideration of the mutual covenants and agreements hereinafter set forth, it is agreed as follows: </p>

<ol class="list">
    <li>
        <div class="list__ttl">SALE AND SHIPMENT OF THE PRODUCTS.</div>

        <ol class="sub">
            <li>
                <span class="sub__nr">1.1&emsp;</span> In exchange for the consideration provided by Buyer as part of the transaction entered into between Seller and Buyer via the Portal to purchase the Products (the “Transaction”), which consideration shall be collected from Buyer by Company via the Portal and payable to Seller in accordance with the Terms and Conditions of the Portal (the “Portal Terms”), Seller hereby sells, transfers and grants to Buyer the title to the Products and the nonexclusive rights to distribute the same.
            </li>
            <li>
                <span class="sub__nr">1.2&emsp;</span> Buyer agrees that the proprietary rights of Seller are the sole and exclusive property of Seller.
            </li>
            <li>
                <span class="sub__nr">1.3&emsp;</span>Buyer may use the trademarks, service marks, trade names and fictitious business names of Seller and other intellectual property of the Seller associated with the Products in connection with the sale of the Products except as restricted in the Product listing on the Portal and as otherwise agreed between Buyer and Seller. Except as otherwise specified in the Product listing on the Portal, Buyer may indicate in its advertising and marketing materials that it is a dealer for the Products and may use Seller’s trademarks in its sales/marketing efforts to accurately describe the Products as being sourced from Seller. In the event Buyer alters images, logos or other content of Seller in its advertisements, promotional brochures and other marketing materials for the Products, Buyer will place proper trademark, copyright and patent notices as requested by Seller; all unaltered uses of images, logos or other content of Seller for the foregoing purposes shall not require the approval of Seller.  The non-exclusive right and license granted herein shall continue until the final resale of the Products by Buyer.
            </li>
            <li>
                <span class="sub__nr">1.4&emsp;</span> Buyer is expressly permitted to sell, market and distribute products substantially similar to the Products from any other supplier other than Seller at any time.
            </li>
            <li>
                <span class="sub__nr">1.5&emsp;</span> In exchange for the consideration provided as part of the Transaction costs paid by Buyer, which consideration shall be automatically deducted by the Company via the Portal and paid to Freight Forwarder subject to the terms of Company’s agreement with Freight Forwarder and the Portal Terms, and which consideration is set forth in Exhibit A, Freight Forwarder agrees to complete the freight forwarding services required for the shipment of the Products from Seller to Buyer, as agreed by the parties pursuant to the Transaction and as are detailed in Exhibit A (the “FF Services”).  The FF Services shall include, without limitation, that Freight Forwarder successfully transports the Products from the origin designated by Seller to the destination designated by Buyer, as is set forth in the Portal listing for the Products and as otherwise agreed by Seller and Buyer pursuant to the Transaction.  Some orders may have insurance and/or warehousing requirements, and Freight Forwarder shall be responsible for complying with such specifications, as well as maintaining relationships with third-party entities providing such services.  The FF Services hereunder shall additionally include, without limitation, the completion of all necessary shipping and customs paperwork, whether via the Portal or otherwise. Freight Forwarder may not, under any circumstances, cancel or terminate the FF Services hereunder after its bid to provide the FF Services for the Transaction has been accepted.  Freight Forwarder shall be required to comply with all deadlines set by the Transaction, the Company, Seller and Buyer; any single failure to meet a deadline by Freight Forwarder specified in connection with the Transaction shall be deemed a material breach of this Agreement.
            </li>
            <li>
                <span class="sub__nr">1.6&emsp;</span> The Company will provide, in a ledger on the Portal (“EP Docs”) or as otherwise determined by the Company in its sole discretion, shipment and customs clearance procedures and documentation required in connection with the FF Services (“Shipment Documentation”).  Notwithstanding the foregoing, Freight Forwarder, Seller, and Buyer each acknowledge and agree that the Shipment Documentation or other information provided by Company is provided “as is”, without any express or implied warranty, and that Company makes no assertions or guarantees as to the completeness, accuracy or legal compliance of such Shipment Documentation.  It is the responsibility of Freight Forwarder, together with Seller and Buyer, to ensure that all Shipment Documentation or any documentation related to the Transaction (including, but not limited to, the sale, distribution, or shipment of the Products) is completed in its entirety and to the specifications required by all pertinent governing agencies or other overseeing entities, and that all actions related to the Transaction and the Products comply with applicable law.
            </li>
            <li>
                <span class="sub__nr">1.7&emsp;</span> All communications between or among Freight Forwarder, Seller and/or Buyer, or with any party in connection with the Transaction, must be made within, and only within, the Portal.  Time is of the essence with reference to all aspects of the Transaction; each party must comply with all stated payment, delivery and performance timelines as required under the Transaction and each party acknowledges and agrees that the Company may, in its sole discretion, cancel or terminate the Transaction, or a party’s involvement in a Transaction, in the event a party fails to comply with any such payment, delivery or performance timelines.
            </li>
            <li>
                <span class="sub__nr">1.8&emsp;</span> In the event of a dispute between Freight Forwarder, Seller, and/or Buyer, the Company will follow the complaint procedures set forth below in the Company Policy Documents (as such term is hereinafter defined), but may also attempt to mediate between the parties.  Each of Freight Forwarder, Seller, and/or Buyer will fully comply with the investigation of the matter by the Company, as well as any conclusions drawn by the Company and consequences effected thereby.  Notwithstanding the foregoing, the Company cannot and does not guarantee or make any claims with regard to its ability to resolve any disputes between the parties or the ultimate enforcement of any resolution agreed upon by the parties.  In the event disputing parties continue to have a dispute following the Company’s mediation efforts, or disputing parties ultimately arbitrate or litigate a matter related to the Transaction, each of Freight Forwarder, Seller, and Buyer expressly release the Company from any responsibility relative to such action.
            </li>
            <li>
                <span class="sub__nr">1.9&emsp;</span> Each of Freight Forwarder, Seller, and Buyer acknowledge and agree that the Company shall, in its sole discretion, determine the terms of use for and the process for the Transaction and has the sole right to establish, alter or amend the Portal, and fees charged by the Company, and the Company will give Freight Forwarder, Seller, and Buyer timely notice of any and all changes.
            </li>
            <li>
                <span class="sub__nr">1.10&emsp;</span> Neither Freight Forwarder, Seller, Buyer nor any of their respective employees or agents have any authority to bind the Company and none of them will execute any agreement on behalf of Company, nor shall they hold themselves out as having such authority.
            </li>
            <li>
                <span class="sub__nr">1.11&emsp;</span> The Transaction, and all sales, purchases, and shipments contemplated thereunder, must be made in compliance with the Portal Terms, as well as the Privacy Policy and License Agreement of the Company (the “Company Policy Documents”), and each party shall cause all of its employees, personnel and representatives to comply, with all terms contained within such Company Policy Documents, as the same may be updated from time to time.
            </li>
        </ol>
    </li>

    <li>
        <div class="list__ttl">BUYER REPRESENTATIONS AND WARRANTIES.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">2.1&emsp;</span> That the relationship between Buyer and Seller shall at all times be that of buyer and seller and that Freight Forwarder, Seller and Buyer are each independent business entities and not an agent of any other party.
            </li>
            <li>
                <span class="sub__nr">2.2&emsp;</span> That any copyrights, patents, trade secrets and other intellectual property rights in and to the Products are valid, enforceable and owned by Seller.
            </li>
            <li>
                <span class="sub__nr">2.3&emsp;</span> That Seller expressly retains rights in or to any underlying intellectual property in the Products, including without limitation the rights to sue for and collect past, present and future damages and to seek and obtain injunctive or any other relief for infringement in connection with the Products, and no other rights, other than the rights expressly granted in the Transaction, are granted or implied.
            </li>
            <li>
                <span class="sub__nr">2.4&emsp;</span> That Buyer will not create or attempt to create, or permit others to create or attempt to create, by disassembling, reverse engineering or otherwise, or create any form of derivative work (whether oral, written, tangible or intangible) from the Products or related materials made available to Buyer under this Agreement. Buyer agrees not to remove, modify or obscure any copyright, trademark or other proprietary rights notices that appear on the Products, packaging or related materials.
            </li>
            <li>
                <span class="sub__nr">2.5&emsp;</span> Buyer shall, at all times, comply with all applicable international, national and local laws and regulations, including customs and trade laws, as well as maritime laws and all other applicable multinational regulations that govern the import/export industry in performing its obligations under this Agreement and shall require that all its employees, personnel and representatives comply with the same.
            </li>
            <li>
                <span class="sub__nr">2.6&emsp;</span> Buyer shall be responsible for all costs and expenses in connection with its purchase of the Products, including, without limitation, applicable insurance or licenses, fees, fines, permits, taxes or assessments of any kind and shall indemnify Seller and hold it harmless from paying such costs and expenses. Buyer shall maintain adequate general liability insurance at all times relating to its business and the Transaction.
            </li>
            <li>
                <span class="sub__nr">2.7&emsp;</span> Buyer may not advertise, communicate or otherwise represent any Products characteristics or capabilities which are incorrect or misleading or which might adversely affect the reputation and the goodwill of the Products or Seller.
            </li>
            <li>
                <span class="sub__nr">2.8&emsp;</span> Buyer shall cooperate fully with and assist Seller in its efforts to protect Seller’s intellectual property rights (within the territory covered by the Transaction, as applicable), and to detect any infringement of any patents, trademarks, copyrights or other intellectual property rights owned or used by Seller in connection with the Transaction.
            </li>
            <li>
                <span class="sub__nr">2.9&emsp;</span> Buyer shall reasonably cooperate with Seller in providing any information requested by customers or any governmental authority, at Seller’s expense.
            </li>
            <li>
                <span class="sub__nr">2.10&emsp;</span> Buyer acknowledges and agrees that the Company’s sole involvement in connection with the Transaction is the operation of the Portal on which the Transaction is being consummated, and that the Company has no responsibility for the execution or completion of the Transaction, or for ensuring the obligations of the parties in executing or completing the same, beyond processing the Transaction itself via the Portal.
            </li>
            <li>
                <span class="sub__nr">2.11&emsp;</span> In the event that Buyer acquires financing related to the Transaction via the Portal, from a registered financing provider thereon, Buyer acknowledges and agrees that such financing is the responsibility of the financing provider, and that the Company does not guarantee or enforce such financing.  Buyer shall execute documentation to evidence and bind the financing provided by such financing provider as Buyer may require and shall hold the Company harmless from any claim related to any financing obtained from a financing provider on the Portal.
            </li>
            <li>
                <span class="sub__nr">2.12&emsp;</span> In the event that Buyer engages a third-party verification agency via the Portal to inspect or verify certain items related to the Transaction (such as Seller’s facility, product conformity and corporate documentation), from a registered verification agency thereon, Buyer acknowledges and agrees that such verification is the responsibility of the verification agency, and that the Company does not guarantee or review such verification.  Buyer shall communicate with  the verification agency to ensure the verification is conducted to Buyer’s satisfaction and shall hold the Company harmless from any claim related to any verification obtained from a verification agency on the Portal.
            </li>
            <li>
                <span class="sub__nr">2.13&emsp;</span> Buyer has all requisite legal and corporate power and authority to enter into this Agreement, to consummate the transactions contemplated hereby, and to carry out and perform its obligations under the terms of this Agreement.  Buyer hereby warrants that all information submitted to Company, Freight Forwarder, and/or Seller is accurate and agrees to provide timely notice in event of any changes to information so submitted.
            </li>
            <li>
                <span class="sub__nr">2.14&emsp;</span> The execution, delivery, performance of and compliance with this Agreement has not resulted and will not result in any violation of, or conflict with, or constitute a default under (with or without notice or lapse of time, or both), or give rise to a right of termination, cancellation or acceleration of any obligation or loss of any benefit under any agreement to which Buyer is a party.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">SELLER REPRESENTATIONS AND WARRANTIES.</div>
        <p class="list__txt"> Seller agrees as follows: </p>
        <ol class="sub">
            <li>
                <span class="sub__nr">3.1&emsp;</span> That the relationship between Buyer and Seller shall at all times be that of buyer and seller and that Freight Forwarder, Seller and Buyer are each independent business entities and not an agent of any other party.
            </li>
            <li>
                <span class="sub__nr">3.2&emsp;</span> Seller shall fill and ship Buyer’s order upon the terms agreed upon in accordance with the Product listing and the Transaction, including, without limitation, all shipment dates.
            </li>
            <li>
                <span class="sub__nr">3.3&emsp;</span> Seller shall comply with the laws and regulations, which govern its business in its country or countries of operations, including those set forth by the American National Standards Institute (ANSI) as applicable (including any agency that is created in the future which govern the sale of the Products). Seller shall ensure that all of the Products are produced, packaged and labeled in accordance with applicable laws, regulations and standards, including those of ANSI, if applicable.
            </li>
            <li>
                <span class="sub__nr">3.4&emsp;</span> Seller shall, at all times, comply with all applicable international, national and local laws and regulations, including customs and trade laws, as well as maritime laws and all other applicable multinational regulations that govern the import/export industry in performing its obligations under this Agreement and shall require that all its employees, personnel and representatives comply with the same.
            </li>
            <li>
                <span class="sub__nr">3.5&emsp;</span> Until title passes to Buyer, Seller is the sole owner of the Products and has all right, title, claims, interest and privileges arising from such ownership, free and clear of any liens, security interests, encumbrances, rights or restrictions.
            </li>
            <li>
                <span class="sub__nr">3.6&emsp;</span> The Products are not and have not been subject to any action or proceeding concerning their origination, ownership, or legality or suitability for use and are free from any material defect.
            </li>
            <li>
                <span class="sub__nr">3.7&emsp;</span> Seller represents only that the Products are suitable for their intended purpose, and makes no other representations or warranties.
            </li>
            <li>
                <span class="sub__nr">3.8&emsp;</span> Seller agrees to maintain ongoing quality assurance and testing procedures sufficient to satisfy all applicable regulatory requirements. Seller shall be responsible for responding to all customer complaints, including responses to any governmental or certification authority that may be required.
            </li>
            <li>
                <span class="sub__nr">3.9&emsp;</span> Seller shall provide to Buyer any technical information or other information that may be required for the sale or distribution of the Products. Seller warrants that such information is true and accurate.
            </li>
            <li>
                <span class="sub__nr">3.10&emsp;</span> Seller acknowledges and agrees that the Company’s sole involvement in connection with the Transaction is the operation of the Portal on which the Transaction is being consummated, and that the Company has no responsibility for the execution or completion of the Transaction, or for ensuring the obligations of the parties in executing or completing the same, beyond processing the Transaction itself via the Portal.
            </li>
            <li>
                <span class="sub__nr">3.11&emsp;</span> In the event that Seller acquires financing related to the Transaction via the Portal, from a registered financing provider thereon, Seller acknowledges and agrees that such financing is the responsibility of the financing provider, and that the Company does not guarantee or enforce such financing.  Seller shall execute documentation to evidence and bind the financing provided by such financing provider as Seller may require and shall hold the Company harmless from any claim related to any financing obtained from a financing provider on the Portal.
            </li>
            <li>
                <span class="sub__nr">3.12&emsp;</span> Seller has all requisite legal and corporate power and authority to enter into this Agreement, to consummate the transactions contemplated hereby, and to carry out and perform its obligations under the terms of this Agreement.
            </li>
            <li>
                <span class="sub__nr">3.13&emsp;</span> The execution, delivery, performance of and compliance with this Agreement has not resulted and will not result in any violation of, or conflict with, or constitute a default under (with or without notice or lapse of time, or both), or give rise to a right of termination, cancellation or acceleration of any obligation or loss of any benefit under any agreement to which Seller is a party.
            </li>
        </ol>
     </li>
    <li>
        <div class="list__ttl">FREIGHT FORWARDER REPRESENTATIONS AND WARRANTIES.</div>
        <p class="list__txt">Freight Forwarder agrees as follows:</p>
        <ol class="sub">
            <li>
                <span class="sub__nr">4.1&emsp;</span> That Freight Forwarder, Seller and Buyer are each independent business entities and not an agent of any other party.
            </li>
            <li>
                <span class="sub__nr">4.2&emsp;</span> That Freight Forwarder shall, at all times, comply with all applicable national and local laws and regulations, as well as maritime laws and all other applicable multinational regulations that govern the import/export industry in performing its obligations under this Agreement and shall require that all its employees, personnel and representatives comply with same.
            </li>
            <li>
                <span class="sub__nr">4.3&emsp;</span> That Freight Forwarder shall be responsible for all costs and expenses in connection with the provision of the FF Services, including without limitation all shipment, labor, warehousing and customs costs, and the expense and responsibility for any applicable insurance or licenses, fees, fines, permits, taxes or assessments of any kind, unless otherwise specified as part of the Transaction. Freight Forwarder shall indemnify Company and hold it harmless from paying such business costs or taxes. Freight Forwarder shall art all times maintain adequate general liability insurance relating to its business and the shipment of the Products.
            </li>
            <li>
                <span class="sub__nr">4.4&emsp;</span> Freight Forwarder warrants that all information submitted to Company is accurate and agrees to provide timely notice in event of any changes to information so submitted.
            </li>
            <li>
                <span class="sub__nr">4.5&emsp;</span> Freight Forwarder acknowledges and agrees that the Company’s sole involvement in connection with the Transaction is the operation of the Portal on which the Transaction is being consummated, and that the Company has no responsibility for the execution or completion of the Transaction, or for ensuring the obligations of the parties in executing or completing the same, beyond processing the Transaction itself via the Portal.
            </li>
            <li>
                <span class="sub__nr">4.6&emsp;</span> Freight Forwarder has all requisite legal and corporate power and authority to enter into this Agreement, to consummate the transactions contemplated hereby, and to carry out and perform its obligations under the terms of this Agreement.
            </li>
            <li>
                <span class="sub__nr">4.7&emsp;</span> The execution, delivery, performance of and compliance with this Agreement has not resulted and will not result in any violation of, or conflict with, or constitute a default under (with or without notice or lapse of time, or both), or give rise to a right of termination, cancellation or acceleration of any obligation or loss of any benefit under any agreement to which Freight Forwarder is a party.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">SHIPPING AND PAYMENT.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">5.1&emsp;</span> After Seller receives a payment confirmation from the Company confirming that Buyer has executed the Transaction and paid for the Products, Seller will inspect and ship the Products to Buyer pursuant to the Transaction terms and Portal Terms.
            </li>
            <li>
                <span class="sub__nr">5.2&emsp;</span> As required by the Transaction terms and Product listing on the Portal, Buyer agrees to pay Seller the Transaction price, Seller agrees to package, label and otherwise ready the Products for transport, and Freight Forwarder agrees to ship the Products and provide the FF Services, all as more particularly set forth Exhibit A hereto.
            </li>
            <li>
                <span class="sub__nr">5.3&emsp;</span> Unless otherwise agreed as part of the Transaction and detailed on Exhibit A, Freight Forwarder shall ship the Products DAT from Seller’s warehouse.  Upon delivery to Freight Forwarder and visual inspection by Freight Forwarder as to the packaging and labeling of the Products shipment, Seller shall not be responsible for any delays or damage to such shipment of the Products. If Seller pays any shipping, insurance or handling costs which are the responsibility of Buyer hereunder, such costs will be billed to Buyer and shall be reimbursed by the Company after Buyer has made all such payments in accordance with the Portal Terms.  If Buyer pays any shipping, insurance or handling costs which are the responsibility of Seller hereunder, such costs may be offset against other costs due and owing Seller from Buyer or by reimbursement as determined by the Company in accordance with the Portal Terms.
            </li>
            <li>
                <span class="sub__nr">5.4&emsp;</span> The Company will only pay the Seller, less transaction fees and fees owed the Company for its services, after confirming that the Buyer has received the proper shipment of the Products.  The Company is merely an intermediary receiving payment from Buyer and disbursing the proceeds to Seller and Freight Forwarder, after deduction of Company fees therefrom.  Should Buyer wish to make a refund claim, or one or more parties have an unresolved matter or dispute related to Transaction payments, the affected parties must comply with the complaint procedures contained in Section 1.8, and failing resolution under Section 1.8, may avail themselves of all legal rights and remedies as set forth in Section 9.12 hereof.
            </li>
            <li>
                <span class="sub__nr">5.5&emsp;</span> Title to the Products shall remain vested in Seller until the Products have been delivered to its final destination as directed by Buyer pursuant to the Transaction as detailed on Exhibit A hereto, at which point title shall be transferred to Buyer or Buyer’s customer, as applicable.
            </li>
            <li>
                <span class="sub__nr">5.6&emsp;</span> Unless otherwise set forth in the Product listing on the Portal, or terms of the Transaction as detailed on Exhibit A hereto, Buyer may determine the final sales price the Products to its customers, in its sole discretion, and shall not be bound to sell or advertise Products at any Seller-specified pricing, such as MSRPs and MAPs.
            </li>
            <li>
                <span class="sub__nr">5.7&emsp;</span> Freight Forwarder, Seller, and Buyer shall be responsible for ensuring the Transaction and the shipment of the Products is in compliance with all Customs regulations in effect in the countries of origin and destination and which may be applicable to the Transaction and the shipment of the Products, and release the Company from any responsibility therefor.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">LIMITATION OF LIABILITY, INDEMNIFICATION AND INSURANCE.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">6.1&emsp;</span> In no event shall Company have any liability for lost profits, lost revenue, indirect damages or goodwill, or loss of time, loss of product/property, loss of use, or any incidental, consequential, special, exemplary or punitive damages of any kind or nature, arising out of or relating to the Transaction or this Agreement; the purchases, sales or shipping of the Products contemplated herein; or related to the Portal or any information or documentation, such as the Shipment Documentation, obtained from Company, including without limitation, the breach of this Agreement, an act or omission thereunder, or any termination of this Agreement, whether such liability is asserted on the basis of contract, tort (including negligence or strict liability) or otherwise, whether foreseeable or not, and even if the a party has been warned of the possibility of such loss or damages. Company does not warrant the completeness of accuracy of the documents or information it provides. Each party is responsible for verifying any documentation obtained from Company or via the Portal and completing documentation to ensure they are compliant with any relevant laws or regulations. Company disclaims any liability related to the Products and/or its service hereunder and as provided via the Portal.
            </li>
            <li>
                <span class="sub__nr">6.2&emsp;</span> Without limiting the generality of the foregoing, each party specifically disclaims any warranty regarding the profitability, success or value of any Products that are the subject hereof, or the results of their use. In no event will Buyer use, sell or exploit the Products for any purposes other than as intended.
            </li>
            <li>
                <span class="sub__nr">6.3&emsp;</span> Buyer shall indemnify and hold harmless Seller, Freight Forwarder, and the Company, and their affiliates, principals, employees, officers, directors, consultants, stockholders, representatives and agents, successors and assigns from and against all claims, disputes, debts, controversies, obligations, judgments, demands, liens, causes of action, liability, loss, damages, costs and expenses (including reasonable attorneys’ fees and expenses of litigation) (collectively, “Claims”) which an indemnified party may incur, suffer or be required to pay resulting from or arising in connection with any Claims arising out of or relating to: (i) the sale, use, operation, service or maintenance of the Products by Buyer; (ii) Buyer’s usage of the Portal and entrance into the Transaction, (iii) any intentional act, gross malfeasance or misfeasance, or negligence (either by act or omission) by Buyer or anyone for whose acts Buyer may be liable; or (iv) any breach of this Agreement by Buyer, including without limitation the breach of any representations and warranties set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">6.4&emsp;</span> Seller shall indemnify and hold harmless Buyer, Freight Forwarder, and the Company, and their affiliates, principals, employees, officers, directors, consultants, stockholders, representatives and agents, successors and assigns from and against all Claims which such indemnified party may incur, suffer or be required to pay resulting from or arising in connection with any Claims arising out of: (i) the infringement by the Products of any third party’s valid, registered patents, trademark or other intellectual property rights, provided, however, that such infringement shall not be caused by Buyer’s breach of this Agreement or Buyer’s combination of the Products with other materials not authorized by Seller; (ii) any defects in the Products; (iii) Seller’s usage of the Portal and entrance into the Transaction, (iv) any intentional act, gross malfeasance or misfeasance, or negligence (either by act or omission) by Seller or anyone for whose acts Seller may be liable; or (v) any breach of this Agreement by Seller, including without limitation the breach of any representations and warranties set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">6.5&emsp;</span> Freight Forwarder shall indemnify and hold harmless Buyer, Seller and the Company, and their affiliates, principals, employees, officers, directors, consultants, stockholders, representatives and agents, successors and assigns from and against all Claims which an indemnified party may incur, suffer or be required to pay resulting from or arising in connection with any Claims arising out of or relating to: (i) the FF Services, (ii) Freight Forwarder’s usage of the Portal and entrance into the Transaction, (iii) any intentional act, gross malfeasance or misfeasance, or negligence (either by act or omission) by Freight Forwarder or anyone for whose acts Freight Forwarder may be liable; or (iv) any breach of this Agreement by Freight Forwarder, including without limitation the breach of any representations and warranties set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">6.6</span>No party shall be obligated to indemnify another party under this Agreement unless:
                <p>(a) the party seeking indemnification gives the indemnifying party prompt written notice of any claim for which it seeks indemnification;</p>
                <p>(b) the party seeking indemnification cooperates with the indemnifying party in the defense of the claim;</p>
                <p>(c) the indemnifying party shall have the sole right to manage the defense of any such claim in the manner it deems advisable, including using counsel of its choice, and;</p>
                <p>(d) the indemnifying party shall have the sole right to settle any such claim provided any such settlement completely releases the parties seeking indemnification, and requires no action by, or financial participation from, the party seeking indemnification.</p>
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">DEFECTIVE PRODUCTS.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">7.1&emsp;</span> In the event of damage to any of the Products, in transit or otherwise, to the extent that the contents thereof become adulterated or rendered unfit for use, Buyer shall not sell, nor dispose of said Products, nor transfer the same to any insurance carrier or common carrier, or salvage distributor or any third party, without prior written authorization and/or instruction from Seller.
            </li>
            <li>
                <span class="sub__nr">7.2&emsp;</span> Buyer shall inspect all Products, at its own expense, for defects upon delivery.  Once Buyer has accepted delivery, all Products included in the shipment shall be deemed accepted by Buyer; no further claim shall be made after such acceptance and Seller shall not be liable for any defective Products if the delivery was accepted without any such claim. All Products that are identified as defective by Buyer upon delivery shall result in Buyer submitting a complaint pursuant to Section 1.8 hereof.  In the event such complaint process does not result in a satisfactory resolution for one or both of the parties, the parties may avail themselves of any and all legal rights and remedies available to them, as set forth in Section 9.12 hereof.
            </li>
            <li>
                <span class="sub__nr">7.3&emsp;</span> Upon receipt of any defective Products pursuant to this Section, Seller shall ship an equivalent order of a new, non-defective Products to Buyer, at Seller’s expense.
            </li>
            <li>
                <span class="sub__nr">7.4&emsp;</span> In the event Seller recalls any Products sold to Buyer, Buyer shall reasonably assist Seller with such recall. Seller agrees to give prompt notice to Buyer in the event that Seller recalls any Products, or ceases or suspends the sale of any Products due to any problem which relates to such Products’ efficacy or safety. Seller shall reimburse Buyer for all expenses incurred by Buyer for such a recall.
            </li>
            <li>
                <span class="sub__nr">7.5&emsp;</span> Buyer understands and acknowledges that the Company is not responsible for ensuring the quality, condition or suitability of the Products for Buyer’s purposes.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">NON-SOLICITATION, NON-CIRCUMVENTION.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">8.1&emsp;</span> During the Term and for a period of two (2) years thereafter, neither Freight Forwarder, Seller, nor Buyer shall directly or indirectly (i) hire, offer to hire, entice away, solicit, or any other way persuade or attempt to persuade any employee, officer, agent or independent contractor of Company to discontinue their relationship with Company, or (ii) induce or attempt to induce any Portal sellers, buyers, freight forwarders, customers, suppliers, licensees, or business relation of Company to cease doing business with Company, or in any way interfere with the relationship between Company and any Portal seller, buyer, freight forwarder, customer, supplier, licensee, or business relation of Company.  In addition to the foregoing, each of Freight Forwarder, Seller, and Buyer agrees not to in any way, directly or indirectly, solicit, by-pass, compete, avoid, circumvent, or attempt to circumvent Company relative to the Transaction.
            </li>
            <li>
                <span class="sub__nr">8.2&emsp;</span> Neither Freight Forwarder, Seller, nor Buyer, nor any of their agents, may offer, sell or market services or products similar to those offered by such party via the Portal on any outlet, portal, website or other medium that is substantially similar to the Portal until the Transaction has been completed.  Freight Forwarder, Seller, and Buyer further agree that they shall not, outside the Portal, contract or attempt to sell to, transact with or purchase from sources that the party has come into contact with via the Transaction without the written permission from the Company, unless a business relationship between that party and the source predated this Agreement.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">GENERAL PROVISIONS.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">9.1&emsp;</span> This Agreement may not be assigned without the prior written consent of the other parties; provided, however, that Company may assign all of its rights and obligations hereunder to a party that assumes Company’s obligations hereunder.
            </li>
            <li>
                <span class="sub__nr">9.2&emsp;</span> This Agreement shall be binding upon and inure to the benefit of the parties hereto and their respective successors and assigns and may not be waived or modified except by a writing referring specifically to this Agreement, signed on behalf of Company, Freight Forwarder, Seller, and Buyer.
            </li>
            <li>
                <span class="sub__nr">9.3&emsp;</span> This Agreement may be executed in any number of counterparts, each of which shall be deemed an original, but all of which shall constitute a single instrument. The headings contained in this Agreement have been inserted for convenient reference only and shall not modify, define, expand or limit any of the provisions of this Agreement.
            </li>
            <li>
                <span class="sub__nr">9.4&emsp;</span> The failure of any party to enforce at any time any of the provisions of this Agreement, or any rights in respect thereof, shall not constitute any continuing waiver of such provision, or in any way affect the validity of this Agreement.
            </li>
            <li>
                <span class="sub__nr">9.5&emsp;</span> In connection with this Agreement, each party will from time to time receive proprietary data or confidential information from the other parties in the performance of its obligations hereunder (“Confidential Information”). Confidential Information includes information, whether written, electronic or oral, which a party knows or reasonably should know is proprietary, confidential or a trade secret of the other party, including any and all technical or business information, software including its source codes and documentation, specifications and design information for the Products, servicing information, customer lists, pricing information, marketing information, policies, procedures and manuals regarding distributors or distribution channels, research and development and other proprietary matter relating to the Products or the business of a party. Each party will refrain from using the Confidential Information except to the extent necessary to exercise its rights or perform its obligations under this Agreement. Each party will likewise restrict its disclosure of the Confidential Information to those who have a need to know such Confidential Information in order for such party to perform its obligations and enjoy its rights under this Agreement. Such persons will be informed of and will agree to the provisions of this Section and such party will remain responsible for any unauthorized use or disclosure of the Confidential Information by any of them. Upon termination of this Agreement (or earlier, upon request by a party), each party shall cease to use all Confidential Information and promptly return to the other party (or destroy, upon request by such party) any documents (whether written or electronic) in its possession or under its control that constitutes Confidential Information. This provision shall survive the termination or expiration of this Agreement.
            </li>
            <li>
                <span class="sub__nr">9.6&emsp;</span> If any provision of this Agreement, or any portion thereof, is held to be invalid or unenforceable under any applicable statute or rule of law, it is to that extent to be deemed omitted and the other provisions of this Agreement shall remain valid and in full force and effect.
            </li>
            <li>
                <span class="sub__nr">9.7&emsp;</span> This Agreement, the Company Policy Documents, as updated from time to time, (and, in the case of Freight Forwarder and the Company, that certain Freight Forwarding Agreement entered into between the parties) contain the entire understanding of the parties with respect to the subject matter hereof, and this Agreement cancels and supersedes all other prior representations, warranties, covenants, promises or undertakings except those expressly set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">9.8&emsp;</span> Any notice, request, demand, waiver, consent, approval or other communication which is required or permitted hereunder shall be in writing and shall be deemed given only if delivered personally or sent by Federal Express or other overnight delivery service, or sent registered or certified U.S. mail, postage prepaid, to the addresses set forth in the preamble to this Agreement, or to such other address as the addressee may have specified in a notice duly given to the sender as provided herein. Such notice, request, demand, waiver, consent, approval or other communication will be deemed to have been given as of the date so delivered or if mailed via registered or certified mail, on the seventh day after said notice was mailed.
            </li>
            <li>
                <span class="sub__nr">9.9&emsp;</span> Nothing herein shall create any association, partnership, joint venture or the relationship of principal and agent between the parties hereto, it being understood that Company, Freight Forwarder, Seller and Buyer are, with respect to each other, independent contractors, and neither party shall have any authority to bind the other or the other’s representatives in any way.
            </li>
            <li>
                <span class="sub__nr">9.10&emsp;</span> In the event there is a delay, inability to provide services or materials required hereunder, or loss caused by Acts of God or of a public enemy, acts of the Government of the United States or any state or political subdivision thereof, fires, floods, explosions or other catastrophes, labor disturbances, delays of a supplier or other events beyond either party’s control, the dates for delivery shall be extended for the period equal to the time lost by reason of such delay.  No party shall be liable for any damages incurred by the other as a result of any such loss or delay.
            </li>
            <li>
                <span class="sub__nr">9.11&emsp;</span> This Agreement shall be governed by, and construed in accordance with, the laws of the State of California without regard to its conflicts of law rules.
            </li>
            <li>
                <span class="sub__nr">9.12&emsp;</span> In the event of any dispute or difference arising out of or relating to this Agreement, and such dispute continues after resolution efforts by the Company in accordance with Section 1.8 hereof have been exhausted, the affected parties may determine how and in what forum to pursue a legal action, in their sole discretion, and agree to not require the involvement of the Company in such action.
            </li>
            <li>
                <span class="sub__nr">9.13&emsp;</span> Any delays in performance by any party are not a breach of this Agreement if and to the extent caused by occurrences beyond the reasonable control of the party affected, including acts of God, embargoes, governmental restrictions, custom clearances, inspections, strikes or other concerted acts of workers, fire, flood, explosion, riots, wars, civil disorder, rebellion or sabotage. The party suffering such occurrence shall immediately notify the other party and any time for performance shall be extended by the actual time of delay caused by the occurrence.
            </li>
            <li>
                <span class="sub__nr">9.14&emsp;</span> Each of the parties hereto hereby agrees that it will from time to time, upon the reasonable request of another party hereto, take such further action as the other may reasonably request to carry out the transactions contemplated by this Agreement.
            </li>
        </ol>
    </li>
</ol>

<pagebreak>

<p class="text-p">
    <strong>IN WITNESS WHEREOF</strong>, the parties hereto have executed this Agreement as of the day and year first above written.
</p>

<table width="100%" autosize="0" style="margin-top: 83px; border:0; vertical-align: top; font-family: Roboto, sans-serif;">
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">
                        BUYER -
                        <?php if(isset($company_buyer_info) && !empty($company_buyer_info)){?>
                            <?php echo $company_buyer_info['company_name'];?>
                        <?php }else{?>
                            <?php echo $buyer_info['buyer_name'];?>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo orderNumber($buyer_info['idu']);?></td>
                </tr>
                <?php if(isset($company_buyer_info) && !empty($company_buyer_info)){?>
                    <tr>
                        <td class="card-user__name">Company legal name:</td>
                        <td class="card-user__val"><?php echo $company_buyer_info['company_legal_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Represented by:</td>
                        <td class="card-user__val"><?php echo $buyer_info['buyer_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $company_buyer_info['company_address'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo (!empty($company_buyer_info['company_phone']))?$company_buyer_info['company_phone_code'] . ' ' . $company_buyer_info['company_phone']:'&mdash;';?></td>
                    </tr>
                <?php }else{?>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $buyer_info['buyer_location'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo (!empty($buyer_info['phone']))?$buyer_info['phone_code'] . ' ' . $buyer_info['phone']:'&mdash;';?></td>
                    </tr>
                <?php }?>
                <tr>
                    <td class="card-user__name">Email:</td>
                    <td class="card-user__val"><span class="txt-underline"><?php echo $buyer_info['email'];?></span></td>
                </tr>
                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo __SITE_URL;?>usr/<?php echo strForURL($buyer_info['fname'].'-'.$buyer_info['lname']).'-'.$buyer_info['idu'];?></td>
                </tr>
            </table>
        </td>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">SELLER  -  <?php echo $seller_info['name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Company legal name:</td>
                    <td class="card-user__val"><?php echo $seller_info['legal_name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Represented by:</td>
                    <td class="card-user__val"><?php echo !empty($seller_info['legal_name']) ? $seller_info['legal_name'] : $seller_info['fname'].' '.$seller_info['lname'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo orderNumber($seller_info['idu']);?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Address:</td>
                    <td class="card-user__val"><?php echo $seller_info['company_location'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Email:</td>
                    <td class="card-user__val"><span class="txt-underline"><?php echo $seller_info['email'];?></span></td>
                </tr>
                <tr>
                    <td class="card-user__name">Phone:</td>
                    <td class="card-user__val"><?php echo $seller_info['phone_code_company'] . ' ' . $seller_info['phone_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo getCompanyURL($seller_info);?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="block-sign">
    <div class="block-sign__inner">
        <div class="text-p">
            For and on behalf of the Buyer
        </div>

        <div class="text-p">
            Mr./Mrs. ....................................................................
            .................................................................................
        </div>

        <div class="block-sign__line"></div>
    </div>
</div>
<div class="block-sign">
    <div class="block-sign__inner">
        <div class="text-p">
            For and on behalf of the Seller
        </div>

        <div class="text-p">
            Mr./Mrs. ....................................................................
            .................................................................................
        </div>

        <div class="block-sign__line"></div>
    </div>
</div>

<pagebreak>

<div class="contract-title__name tac">PRODUCTS LIST</div>
<div class="contract-title__subline tac pb-50">This list refer to the Invoice <?php echo orderNumber($order['id_invoice']);?>.</div>

<table class="table-items" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
    <thead>
        <tr>
            <th>Item ID</th>
            <th>Title, Details</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($products as $key => $item){?>
            <tr>
                <td class=""><?php echo isset($item['id_item']) ? orderNumber($item['id_item']) : '&mdash;';?></td>
                <td class="">
                    <div class="table-items__product-name" style="padding: 0; width: 100%;"><?php echo $item['name'];?></div>

                    <div class="table-items__params">
                        <div class="table-items__params-item"><?php echo $item['detail_ordered'];?></div>
                    </div>

                    <div class="table-items__date">Order date: <?php echo getDateFormat($order['order_date'],'Y-m-d H:i:s', 'm.d.Y');?></div>

                    <div class="table-items__link">
                        <?php echo __SITE_URL;?>items/ordered/snapshot-<?php echo $item['id_ordered_item']; ?>
                    </div>

                </td>
                <td class=""><?php echo $item['quantity'];?></td>
                <td class="">$<?php echo get_price($item['unit_price'], false);?></td>
                <td class="">$<?php echo get_price($item['total_price'], false);?></td>
            </tr>
        <?php }?>
    </tbody>
   <tfoot>
       <tr>
           <td class="table-items__tfoot-name" style="padding-top: 30px;" colspan="4">Subtotal</td>
           <td style="padding-top: 30px;">$<?php echo get_price($order['price'], false);?></td>
       </tr>
       <tr>
           <td class="table-items__tfoot-name" colspan="4">Order Discount</td>
           <td><?php echo normalize_discount($order['discount']);?>%</td>
       </tr>
       <tr>
           <td class="table-items__tfoot-name" style="padding-bottom: 30px;" colspan="4">total amount</td>
           <td style="padding-bottom: 30px;">$<?php echo get_price($order['final_price'], false);?></td>
       </tr>
   </tfoot>
</table>
