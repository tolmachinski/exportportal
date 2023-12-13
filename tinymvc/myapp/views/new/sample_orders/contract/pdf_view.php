<link rel="stylesheet" type="text/css" media="all" href="<?php echo fileModificationTime('public/css/style_pdf_new.css');?>" />

<?php views()->display('new/sample_orders/contract/header_contract_pdf_view'); ?>

<div class="title-number tac">
    Contract number: <span class="title-number__nr">№ <?php echo $order['purchase_order']['contract']['id'];?></span> Issue date: <span class="title-number__nr"><?php echo getDateFormatIfNotEmpty($invoice['issue_date'], DATE_ATOM, 'm.d.Y');?></span>
</div>

<div class="contract-title tac">
    <div class="contract-title__name">SAMPLE ORDER PURCHASE AGREEMENT</div>
</div>

<p class="text-p"><strong>THIS SAMPLE ORDER PURCHASE AGREEMENT</strong> (this "Agreement") made and entered into this <?php echo getDateFormatIfNotEmpty($invoice['issue_date'], DATE_ATOM, 'd \d\a\y \o\f F, Y');?> (the "Effective Date"), is by and among Export Portal, Inc., a California corporation having a principal place of business at 1945 Gardena Ave., Glendale, CA 91204 (the "Company"), <?php echo empty($buyer_company['company_name']) ? $buyer['full_name'] : $buyer_company['company_name'] . ', a ' . $buyer_company['country_name'] . ' company';?> having a principal place of business at <?php echo $buyer_company['location'] ?? $buyer['location'];?> (hereinafter referred to as "Buyer"), and <?php echo $seller['legal_name_company'];?>, a <?php echo $seller['country_name'];?> company having a principal place of business at <?php echo $seller['location'];?> (hereinafter referred to as "Seller").</p>

<div class="contract-title__name tac">WITNESSETH:</div>

<p class="text-p"><strong>WHEREAS</strong>, the Company owns and operates a worldwide product distribution website known as Export Portal (the "Portal"); </p>
<p class="text-p"><strong>WHEREAS</strong>, Seller is a wholesaler of products registered to sell goods on the Portal; and </p>
<p class="text-p"><strong>WHEREAS</strong>, Buyer has agreed to buy samples of the products of Seller (the "Sample Products") via the Portal, upon price and other terms as listed more particularly in Exhibit "A", which is attached hereto and incorporated herein by reference herein.</p>
<p class="text-p"><strong>NOW, THEREFORE</strong>, for and in consideration of the mutual covenants and agreements hereinafter set forth, it is agreed as follows: </p>

<ol class="list">
    <li>
        <div class="list__ttl">SALE AND SHIPMENT OF THE PRODUCTS.</div>

        <ol class="sub">
            <li>
                <span class="sub__nr">1.1&emsp;</span> In exchange for the consideration provided by Buyer as part of the transaction entered into between Seller and Buyer via the Portal to purchase the Sample Products (the "Transaction"), which consideration may be collected from Buyer by Company via the Portal and payable to Seller in accordance with the Terms and Conditions of the Portal (the "Portal Terms") or directly by the Seller as determined between Buyer and Seller, Seller hereby sells the Sample Products to Buyer for the sole purpose of Buyer's evaluation of the Sample Products for potential future wholesale orders. The Sample Products may be customized and crafted specially for Buyer or be standard products already offered by Seller, all as more particularly described in Exhibit A. Buyer acknowledges and agrees that the Sample Products are not required to meet any quality standards, be non-defective or perform their intended function(s); the Sample Products are intended simply to allow Buyer to assess the Sample Products and the performance of Seller in their provision. Buyer may purchase as many additional or replacement Sample Products from Seller as Seller may agree to sell to Buyer from time to time.
            </li>
            <li>
                <span class="sub__nr">1.2&emsp;</span> Buyer agrees that the proprietary rights of Seller are the sole and exclusive property of Seller. BUYER ACKNOWLEDGES AND AGREES THAT THE SAMPLE PRODUCTS ARE FOR BUYER'S INTERNAL PURPOSES ONLY AND NOT ELIGIBLE FOR RESALE OR DISTRIBUTION. Buyer may not indicate in its advertising or marketing materials that it is a dealer for the Sample Products or use Seller's trademarks, service marks, trade names or fictitious business names of Seller or any other intellectual property of Seller in association with the Sample Products unless Buyer completes a subsequent wholesale order of Seller's products via the Portal, in which case the parties shall enter into a new agreement to govern such transaction. Buyer may not tamper with or remove trademark, copyright and patent notices appearing on the Sample Products or related materials unless otherwise approved by Seller.
            </li>
            <li>
                <span class="sub__nr">1.3&emsp;</span> Buyer is expressly permitted to buy, sell, market and distribute products substantially similar to the Sample Products from any other supplier other than Seller at any time, and the purchase of the Sample Products hereunder does not constitute a guarantee of any future wholesale order by Buyer of any products of Seller.
            </li>
            <li>
                <span class="sub__nr">1.4&emsp;</span> It is the responsibility of Seller and Buyer to ensure that the shipment of the Sample Products, as well as any documentation related to the Transaction is completed in its entirety and to the specifications required by all pertinent governing agencies or other overseeing entities, and that all actions related to the Transaction and the Sample Products comply with applicable law.
            </li>
            <li>
                <span class="sub__nr">1.5&emsp;</span> All communications between Seller and Buyer, or with any party in connection with the Transaction, shall be made within, and only within, the Portal.
            </li>
            <li>
                <span class="sub__nr">1.6&emsp;</span> In the event of a dispute between Seller and Buyer, the Company will follow the complaint procedures set forth below in the Company Policy Documents (as such term is hereinafter defined), but may also attempt to mediate between the parties. Each of Seller and Buyer will fully comply with the investigation of the matter by the Company, as well as any conclusions drawn by the Company and consequences effected thereby. Notwithstanding the foregoing, the Company cannot and does not guarantee or make any claims with regard to its ability to resolve any disputes between the parties or the ultimate enforcement of any resolution agreed upon by the parties. In the event disputing parties continue to have a dispute following the Company's mediation efforts, or disputing parties ultimately arbitrate or litigate a matter related to the Transaction, each of Seller and Buyer expressly release the Company from any responsibility relative to such action.
            </li>
            <li>
                <span class="sub__nr">1.7&emsp;</span> Each of Seller and Buyer acknowledge and agree that the Company shall, in its sole discretion, determine the terms of use for and the process for the Transaction and has the sole right to establish, alter or amend the Portal, and fees charged by the Company, and the Company will give Seller and Buyer timely notice of any and all changes.
            </li>
            <li>
                <span class="sub__nr">1.8&emsp;</span> Neither Seller, Buyer nor any of their respective employees or agents have any authority to bind the Company and none of them will execute any agreement on behalf of Company, nor shall they hold themselves out as having such authority.
            </li>
            <li>
                <span class="sub__nr">1.9&emsp;</span> The Transaction, and all sales, purchases, and shipments contemplated thereunder, must be made in compliance with the Portal Terms, as well as the Privacy Policy and License Agreement of the Company (the "Company Policy Documents"), and each party shall cause all of its employees, personnel and representatives to comply, with all terms contained within such Company Policy Documents, as the same may be updated from time to time.
            </li>
        </ol>
    </li>

    <li>
        <div class="list__ttl">BUYER REPRESENTATIONS AND WARRANTIES.</div>
        <p class="list__txt">Buyer agrees as follows:</p>
        <ol class="sub">
            <li>
                <span class="sub__nr">2.1&emsp;</span> That the relationship between Buyer and Seller shall at all times be that of buyer and seller and that Seller and Buyer are each independent business entities and not an agent of any other party.
            </li>
            <li>
                <span class="sub__nr">2.2&emsp;</span> That any copyrights, patents, trade secrets and other intellectual property rights in and to the Sample Products are valid, enforceable and owned by Seller.
            </li>
            <li>
                <span class="sub__nr">2.3&emsp;</span> That Seller expressly retains rights in or to any underlying intellectual property in the Sample Products, including without limitation the rights to sue for and collect past, present and future damages and to seek and obtain injunctive or any other relief for infringement in connection with the Sample Products, and no other rights, other than the rights expressly granted in the Transaction, are granted or implied.
            </li>
            <li>
                <span class="sub__nr">2.4&emsp;</span> That Buyer will not create or attempt to create, or permit others to create or attempt to create, by disassembling, reverse engineering or otherwise, or create any form of derivative work (whether oral, written, tangible or intangible) from the Sample Products or related materials made available to Buyer under this Agreement.
            </li>
            <li>
                <span class="sub__nr">2.5&emsp;</span> Buyer acknowledges and agrees that the Company's sole involvement in connection with the Transaction is the operation of the Portal on which the Transaction is being consummated, and that the Company has no responsibility for the execution or completion of the Transaction, or for ensuring the obligations of the parties in executing or completing the same, beyond processing the Transaction itself via the Portal. Buyer understands and acknowledges that the Company is not responsible for ensuring the quality, condition or suitability of the Sample Products for Buyer's purposes.
            </li>
            <li>
                <span class="sub__nr">2.6&emsp;</span> Buyer has all requisite legal and corporate power and authority to enter into this Agreement, to consummate the transactions contemplated hereby, and to carry out and perform its obligations under the terms of this Agreement. Buyer hereby warrants that all information submitted to Company and/or Seller is accurate and agrees to provide timely notice in event of any changes to information so submitted.
            </li>
            <li>
                <span class="sub__nr">2.7&emsp;</span> The execution, delivery, performance of and compliance with this Agreement has not resulted and will not result in any violation of, or conflict with, or constitute a default under (with or without notice or lapse of time, or both), or give rise to a right of termination, cancellation or acceleration of any obligation or loss of any benefit under any agreement to which Buyer is a party.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">SELLER REPRESENTATIONS AND WARRANTIES.</div>
        <p class="list__txt">Seller agrees as follows:</p>
        <ol class="sub">
            <li>
                <span class="sub__nr">3.1&emsp;</span> That the relationship between Buyer and Seller shall at all times be that of buyer and seller and that Seller and Buyer are each independent business entities and not an agent of any other party.
            </li>
            <li>
                <span class="sub__nr">3.2&emsp;</span> Seller shall fill and ship Buyer's order upon the terms agreed upon in accordance with Exhibit A, including, without limitation, all shipping procedures.
            </li>
            <li>
                <span class="sub__nr">3.3&emsp;</span> The Sample Products are not and have not been subject to any action or proceeding concerning their origination, ownership, or legality or suitability for use.
            </li>
            <li>
                <span class="sub__nr">3.4&emsp;</span> Seller acknowledges and agrees that the Company's sole involvement in connection with the Transaction is the operation of the Portal on which the Transaction is being consummated, and that the Company has no responsibility for the execution or completion of the Transaction, or for ensuring the obligations of the parties in executing or completing the same, beyond processing the Transaction itself via the Portal.
            </li>
            <li>
                <span class="sub__nr">3.5&emsp;</span> Seller has all requisite legal and corporate power and authority to enter into this Agreement, to consummate the transactions contemplated hereby, and to carry out and perform its obligations under the terms of this Agreement.
            </li>
            <li>
                <span class="sub__nr">3.6&emsp;</span> The execution, delivery, performance of and compliance with this Agreement has not resulted and will not result in any violation of, or conflict with, or constitute a default under (with or without notice or lapse of time, or both), or give rise to a right of termination, cancellation or acceleration of any obligation or loss of any benefit under any agreement to which Seller is a party.
            </li>
        </ol>
     </li>
    <li>
        <div class="list__ttl">SHIPPING AND PAYMENT.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">4.1&emsp;</span> After Buyer places the order for the Sample Products, Seller shall issue an invoice to Buyer for such order, which invoice shall include all shipping costs. After Seller receives payment, either directly or via the Portal, Seller will provide an estimated delivery date and ship the Sample Products to Buyer. Seller shall provide a shipping confirmation and tracking information for all shipments of Sample Products.
            </li>
            <li>
                <span class="sub__nr">4.2&emsp;</span> In the event payment is made via the Portal, the parties acknowledge that Company is merely an intermediary receiving payment from Buyer and disbursing the proceeds to Seller, after deduction of Company fees therefrom. Should Buyer wish to make a refund claim, or one or more parties have an unresolved matter or dispute related to Transaction payments, the affected parties must comply with the complaint procedures contained in Section 1.6, and failing resolution under Section 1.6, may avail themselves of all legal rights and remedies as set forth in Section 7.12 hereof.
            </li>
            <li>
                <span class="sub__nr">4.3&emsp;</span> Seller and Buyer shall be responsible for ensuring the Transaction and the shipment of the Sample Products is in compliance with all Customs regulations in effect in the countries of origin and destination and which may be applicable to the Transaction and the shipment of the Sample Products, and release the Company from any responsibility therefor.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">LIMITATION OF LIABILITY, INDEMNIFICATION AND INSURANCE.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">5.1&emsp;</span> In no event shall Company have any liability for lost profits, lost revenue, indirect damages or goodwill, or loss of time, loss of product/property, loss of use, or any incidental, consequential, special, exemplary or punitive damages of any kind or nature, arising out of or relating to the Transaction or this Agreement; the purchase, sale or shipping of the Sample Products contemplated herein; or related to the Portal or any information or documentation obtained from Company, including without limitation, the breach of this Agreement, an act or omission thereunder, or any termination of this Agreement, whether such liability is asserted on the basis of contract, tort (including negligence or strict liability) or otherwise, whether foreseeable or not, and even if the a party has been warned of the possibility of such loss or damages. Company disclaims any liability related to the Sample Products and/or its service hereunder and as provided via the Portal.
            </li>
            <li>
                <span class="sub__nr">5.2&emsp;</span> Without limiting the generality of the foregoing, each party specifically disclaims any warranty regarding the profitability, success or value of any Sample Products that are the subject hereof, or the results of their use. In no event will Buyer use the Sample Products for any purposes other than as intended.
            </li>
            <li>
                <span class="sub__nr">5.3&emsp;</span> Buyer shall indemnify and hold harmless Seller, the Company, and their affiliates, principals, employees, officers, directors, consultants, stockholders, representatives and agents, successors and assigns from and against all claims, disputes, debts, controversies, obligations, judgments, demands, liens, causes of action, liability, loss, damages, costs and expenses (including reasonable attorneys' fees and expenses of litigation) (collectively, "Claims") which an indemnified party may incur, suffer or be required to pay resulting from or arising in connection with any Claims arising out of or relating to: (I) the use of the Sample Products by Buyer; (II) Buyer's usage of the Portal and entrance into the Transaction, (III) any intentional act, gross malfeasance or misfeasance, or negligence (either by act or omission) by Buyer or anyone for whose acts Buyer may be liable; or (IV) any breach of this Agreement by Buyer, including without limitation the breach of any representations and warranties set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">5.4&emsp;</span> Seller shall indemnify and hold harmless Buyer, the Company, and their affiliates, principals, employees, officers, directors, consultants, stockholders, representatives and agents, successors and assigns from and against all Claims which such indemnified party may incur, suffer or be required to pay resulting from or arising in connection with any Claims arising out of: (I) the infringement by the Sample Products of any third party's valid, registered patents, trademark or other intellectual property rights, provided, however, that such infringement shall not be caused by Buyer's breach of this Agreement or Buyer's combination of the Sample Products with other materials not authorized by Seller; (II) any defects in the Sample Products; (III) Seller's usage of the Portal and entrance into the Transaction, (IV) any intentional act, gross malfeasance or misfeasance, or negligence (either by act or omission) by Seller or anyone for whose acts Seller may be liable; or (V) any breach of this Agreement by Seller, including without limitation the breach of any representations and warranties set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">5.5</span> No party shall be obligated to indemnify another party under this Agreement unless:
                <p>(a) the party seeking indemnification gives the indemnifying party prompt written notice of any claim for which it seeks indemnification;</p>
                <p>(b) the party seeking indemnification cooperates with the indemnifying party in the defense of the claim;</p>
                <p>(c) the indemnifying party shall have the sole right to manage the defense of any such claim in the manner it deems advisable, including using counsel of its choice, and;</p>
                <p>(d) the indemnifying party shall have the sole right to settle any such claim provided any such settlement completely releases the parties seeking indemnification, and requires no action by, or financial participation from, the party seeking indemnification.</p>
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">NON-SOLICITATION, NON-CIRCUMVENTION.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">6.1&emsp;</span> Each of Seller and Buyer agrees not to in any way, directly or indirectly, solicit, by-pass, compete, avoid, circumvent, or attempt to circumvent Company relative to the Transaction.
            </li>
            <li>
                <span class="sub__nr">6.2&emsp;</span> Neither Seller, Buyer, nor any of their agents, may offer, sell or market services or products similar to those offered by such party via the Portal on any outlet, portal, website or other medium that is substantially similar to the Portal until the Transaction has been completed. Seller and Buyer further agree that they shall not, outside the Portal, contract or attempt to sell to, transact with or purchase from sources that the party has come into contact with via the Transaction without the written permission from the Company, unless a business relationship between that party and the source predated this Agreement.
            </li>
        </ol>
    </li>
    <li>
        <div class="list__ttl">GENERAL PROVISIONS.</div>
        <ol class="sub">
            <li>
                <span class="sub__nr">7.1&emsp;</span> This Agreement may not be assigned without the prior written consent of the other parties; provided, however, that Company may assign all of its rights and obligations hereunder to a party that assumes Company’s obligations hereunder.
            </li>
            <li>
                <span class="sub__nr">7.2&emsp;</span> This Agreement shall be binding upon and inure to the benefit of the parties hereto and their respective successors and assigns and may not be waived or modified except by a writing referring specifically to this Agreement, signed on behalf of Company, Seller, and Buyer.
            </li>
            <li>
                <span class="sub__nr">7.3&emsp;</span> This Agreement may be executed in any number of counterparts, each of which shall be deemed an original, but all of which shall constitute a single instrument. The headings contained in this Agreement have been inserted for convenient reference only and shall not modify, define, expand or limit any of the provisions of this Agreement.
            </li>
            <li>
                <span class="sub__nr">7.4&emsp;</span> The failure of any party to enforce at any time any of the provisions of this Agreement, or any rights in respect thereof, shall not constitute any continuing waiver of such provision, or in any way affect the validity of this Agreement.
            </li>
            <li>
                <span class="sub__nr">7.5&emsp;</span> In connection with this Agreement, each party will from time to time receive proprietary data or confidential information from the other parties in the performance of its obligations hereunder ("Confidential Information"). Confidential Information includes information, whether written, electronic or oral, which a party knows or reasonably should know is proprietary, confidential or a trade secret of the other party, including any and all technical or business information, software including its source codes and documentation, specifications and design information for the Sample Products, servicing information, customer lists, pricing information, marketing information, policies, procedures and manuals regarding distributors or distribution channels, research and development and other proprietary matter relating to the Sample Products or the business of a party. Each party will refrain from using the Confidential Information except to the extent necessary to exercise its rights or perform its obligations under this Agreement. Each party will likewise restrict its disclosure of the Confidential Information to those who have a need to know such Confidential Information in order for such party to perform its obligations and enjoy its rights under this Agreement. Such persons will be informed of and will agree to the provisions of this Section and such party will remain responsible for any unauthorized use or disclosure of the Confidential Information by any of them. Upon termination of this Agreement (or earlier, upon request by a party), each party shall cease to use all Confidential Information and promptly return to the other party (or destroy, upon request by such party) any documents (whether written or electronic) in its possession or under its control that constitutes Confidential Information. This provision shall survive the termination or expiration of this Agreement.
            </li>
            <li>
                <span class="sub__nr">7.6&emsp;</span> If any provision of this Agreement, or any portion thereof, is held to be invalid or unenforceable under any applicable statute or rule of law, it is to that extent to be deemed omitted and the other provisions of this Agreement shall remain valid and in full force and effect.
            </li>
            <li>
                <span class="sub__nr">7.7&emsp;</span> This Agreement, the Company Policy Documents, as updated from time to time, contain the entire understanding of the parties with respect to the subject matter hereof, and this Agreement cancels and supersedes all other prior representations, warranties, covenants, promises or undertakings except those expressly set forth in this Agreement.
            </li>
            <li>
                <span class="sub__nr">7.8&emsp;</span> Any notice, request, demand, waiver, consent, approval or other communication which is required or permitted hereunder shall be in writing and shall be deemed given only if delivered personally or sent by Federal Express or other overnight delivery service, or sent registered or certified U.S. mail, postage prepaid, to the addresses set forth in the preamble to this Agreement, or to such other address as the addressee may have specified in a notice duly given to the sender as provided herein. Such notice, request, demand, waiver, consent, approval or other communication will be deemed to have been given as of the date so delivered or if mailed via registered or certified mail, on the seventh day after said notice was mailed.
            </li>
            <li>
                <span class="sub__nr">7.9&emsp;</span> Nothing herein shall create any association, partnership, joint venture or the relationship of principal and agent between the parties hereto, it being understood that Company, Seller and Buyer are, with respect to each other, independent contractors, and neither party shall have any authority to bind the other or the other’s representatives in any way.
            </li>
            <li>
                <span class="sub__nr">7.10&emsp;</span> In the event there is a delay, inability to provide services or materials required hereunder, or loss caused by Acts of God or of a public enemy, acts of the Government of the United States or any state or political subdivision thereof, fires, floods, explosions or other catastrophes, disease, pandemic, quarantine, labor disturbances, delays of a supplier or other events beyond either party's control, the dates for delivery shall be extended for the period equal to the time lost by reason of such delay. No party shall be liable for any damages incurred by the other as a result of any such loss or delay.
            </li>
            <li>
                <span class="sub__nr">7.11&emsp;</span> This Agreement shall be governed by, and construed in accordance with, the laws of the State of California without regard to its conflicts of law rules.
            </li>
            <li>
                <span class="sub__nr">7.12&emsp;</span> In the event of any dispute or difference arising out of or relating to this Agreement, and such dispute continues after resolution efforts by the Company in accordance with Section 1.6 hereof have been exhausted, the affected party may determine how and in what forum to pursue a legal action, in its sole discretion, and agree to not require the involvement of the Company in such action.
            </li>
            <li>
                <span class="sub__nr">7.13&emsp;</span> Each of the parties hereto hereby agrees that it will from time to time, upon the reasonable request of another party hereto, take such further action as the other may reasonably request to carry out the transactions contemplated by this Agreement.
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
                        BUYER - <?php echo empty($buyer_company['company_name']) ? $buyer['full_name'] : $buyer_company['company_name'];?>
                    </td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo '#' . $buyer['idu'];?></td>
                </tr>
                <?php if (!empty($buyer_company)) {?>
                    <tr>
                        <td class="card-user__name">Company legal name:</td>
                        <td class="card-user__val"><?php echo $buyer_company['company_legal_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Represented by:</td>
                        <td class="card-user__val"><?php echo $buyer['full_name'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $buyer_company['location'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Email:</td>
                        <td class="card-user__val"><span class="txt-underline"><?php echo $buyer['email'];?></span></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo empty($buyer_company['company_phone']) ? '&mdash;' : $buyer_company['company_phone_code'] . ' ' . $buyer_company['company_phone'];?></td>
                    </tr>
                    <?php if (!empty($buyer_company['company_fax'])) {?>
                        <tr>
                            <td class="card-user__name">Fax:</td>
                            <td class="card-user__val"><?php echo $buyer_company['company_fax_code'] . ' ' . $buyer_company['company_fax'];?></td>
                        </tr>
                    <?php }?>
                <?php } else {?>
                    <tr>
                        <td class="card-user__name">Address:</td>
                        <td class="card-user__val"><?php echo $buyer['location'];?></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Email:</td>
                        <td class="card-user__val"><span class="txt-underline"><?php echo $buyer['email'];?></span></td>
                    </tr>
                    <tr>
                        <td class="card-user__name">Phone:</td>
                        <td class="card-user__val"><?php echo empty($buyer['phone']) ? '&mdash;' : $buyer['phone_code'] . ' ' . $buyer['phone'];?></td>
                    </tr>
                    <?php if (!empty($buyer['fax'])) {?>
                        <tr>
                            <td class="card-user__name">Fax:</td>
                            <td class="card-user__val"><?php echo $buyer['fax_code'] . ' ' . $buyer['fax'];?></td>
                        </tr>
                    <?php }?>
                <?php }?>
                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo getUserLink($buyer['fname'].' '.$buyer['lname'], $buyer['idu'], 'buyer');?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid #000000;">
            <table class="card-user" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
                <tr>
                    <td class="card-user__ttl" colspan="2">SELLER  -  <?php echo $seller['name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Company legal name:</td>
                    <td class="card-user__val"><?php echo $seller['legal_name_company'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Represented by:</td>
                    <td class="card-user__val"><?php echo !empty($seller['legal_name']) ? $seller['legal_name'] : $seller['fname'] . ' ' . $seller['lname'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Unique ID:</td>
                    <td class="card-user__val"><?php echo '#' . $seller['idu'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Address:</td>
                    <td class="card-user__val"><?php echo $seller['location'];?></td>
                </tr>
                <tr>
                    <td class="card-user__name">Email:</td>
                    <td class="card-user__val"><span class="txt-underline"><?php echo $seller['email'];?></span></td>
                </tr>
                <tr>
                    <td class="card-user__name">Phone:</td>
                    <td class="card-user__val"><?php echo $seller['phone_code_company'] . ' ' . $seller['phone_company'];?></td>
                </tr>
                <?php if (!empty($seller['fax_company'])) {?>
                    <tr>
                        <td class="card-user__name">Fax:</td>
                        <td class="card-user__val"><?php echo $seller['fax_code_company'] . ' ' . $seller['fax_company'];?></td>
                    </tr>
                <?php }?>
                <tr>
                    <td class="card-user__name">EP profile link:</td>
                    <td class="card-user__val"><?php echo getCompanyURL($seller);?></td>
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

<div class="contract-title__name tac">EXHIBIT A</div>
<div class="contract-title__name tac fs-40">PRODUCTS LIST/TRANSACTION TERMS/SHIPPING INFORMATION</div>
<div class="contract-title__subline tac pb-50">This list refer to the Invoice <?php echo $invoice['id'];?>.</div>

<table class="table-items" width="100%" autosize="0" style="vertical-align: top; font-family: Roboto, sans-serif;">
    <thead>
        <tr>
            <th>№</th>
            <th>Title, Details</th>
            <th>Quantity</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $key => $item) {?>
            <tr>
                <td class=""><?php echo ++$key;?></td>
                <td class="">
                    <div class="table-items__product-name" style="padding: 0; width: 100%;"><?php echo $item['name'];?></div>

                    <div class="table-items__params">
                        <div class="table-items__params-item"><?php echo $item['details'];?></div>
                    </div>

                    <div class="table-items__date">Order date: <?php echo getDateFormatIfNotEmpty($order['creation_date'], 'Y-m-d H:i:s', 'm.d.Y');?></div>

                    <div class="table-items__link">
                        <?php echo makeItemUrl($item['item_id'], $item['name']);?>
                    </div>

                </td>
                <td class=""><?php echo $item['quantity'];?></td>
                <td class="">$<?php echo get_price($item['total_price'], false);?></td>
            </tr>
        <?php }?>
    </tbody>
   <tfoot>
       <tr>
           <td class="table-items__tfoot-name" style="padding-bottom: 30px;" colspan="3">total amount</td>
           <td style="padding-bottom: 30px;">$<?php echo get_price($order['final_price'], false);?></td>
       </tr>
   </tfoot>
</table>
