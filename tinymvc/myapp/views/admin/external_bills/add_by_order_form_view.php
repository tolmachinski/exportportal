<form class="relative-b validateModal">
	<div class="wr-form-content w-700">
        <span class="btn btn-default btn-block mt-10" id="toggle_external_bills_list">View all notices for external bills</span>
        <div class="external_bills_list" style="display:none;">
            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
                <thead>
                    <tr>
                        <th>For</th>
                        <th class="mnw-80">Amount ($)</th>
                        <th>Comment</th>
                        <th class="w-80">Date</th>
                        <th class="w-80">By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $external_bills = json_decode('['.$order_detail['external_bills'].']', true);?>
                    <?php foreach($external_bills as $external_bill){?>
                        <tr>
                            <td><?php echo ucfirst($external_bill['user_type']);?></td>
                            <td class="tac"><?php echo get_price($external_bill['money'], false);?></td>
                            <td><?php echo $external_bill['comment'];?></td>
                            <td class="tac"><?php echo formatDate($external_bill['date_time']);?></td>
                            <td><?php echo $external_bill['add_by'];?></td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
        <?php if(!empty($requests)){?>
            <div class="requests_list">
                <p class="mt-10 mb-5 tac"><strong>External bills list</strong></p>
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
                    <thead>
                        <tr>
                            <th>For</th>
                            <th class="mnw-80">Name</th>
                            <th class="mnw-80">Amount ($)</th>
                            <th class="w-80">Date</th>
                            <th class="w-80">Modified</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statuses = array(
                            'waiting' => array(
                                'text' => 'Waiting',
                                'icon' => '<i class="ep-icon ep-icon_hourglass-processing txt-orange"></i>'
                            ),
                            'processed' => array(
                                'text' => 'Processed',
                                'icon' => '<i class="ep-icon ep-icon_ok-circle txt-green"></i>'
                            )
                        );
                        ?>
                        <?php foreach($requests as $request){?>
                            <tr>
                                <td><?php echo ucfirst($request['type']);?></td>
                                <td><?php echo $request['user_name'];?></td>
                                <td class="tac"><?php echo '$' . get_price($request['money'], false);?></td>
                                <td class="tac"><?php echo $request['date_time'];?></td>
                                <td class="tac"><?php echo $request['last_update'];?></td>
                                <td class="tac">
                                    <span title="<?php echo $statuses[$request['status']]['text'];?>">
                                        <?php echo $statuses[$request['status']]['icon'];?>
                                    </span>
                                </td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        <?php }?>
        <table cellspacing="0" cellpadding="0" class="data table-striped mt-5 w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-25pr tac">
                        <strong class="txt-red">
                            Order price
                            <br>
                            $ <?php echo get_price($order_detail['final_price'], false);?>
                        </strong>
                    </td>
                    <td class="w-25pr tac">
                        <strong class="txt-orange">
                            Shipping price
                            <br>
                            $ <?php echo get_price($order_detail['ship_price'], false);?>
                        </strong>
                    </td>
                    <td class="w-25pr tac">
                        <strong class="txt-green">
                            Confirmed bills amount
                            <br>
                            <?php $total_order_price = $order_detail['final_price'] + $order_detail['ship_price'];?>
                            <?php if($amount_confirmed > $total_order_price){?>
                                $ <?php echo get_price($total_order_price, false);?>
                            <?php } else{?>
                                $ <?php echo get_price($amount_confirmed, false);?>
                            <?php }?>
                        </strong>
                    </td>
                    <td class="w-25pr tar">
                        <span class="btn btn-default toogle_bills_list" data-toggle="bills_list">Bills list details</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="bills_list mh-400 overflow-y-a overflow-x-h" style="display:none;">
            <ul class="bills-list-b">
                <?php foreach($bills as $item){?>
                    <li id="bill-<?php echo $item['id_bill'];?>-block" class="relative-b">
                        <div class="clearfix">
                        <?php if($item['name_type'] == 'order'){?>
                            <p class="type-b">Order bill</p>
                        <?php }else{?>
                            <p class="type-b">Shipping bill</p>
                        <?php }?>
                            <p class="status-b <?php echo $status[$item['status']]['class']?>">
                                <i class="ep-icon <?php echo $status[$item['status']]['icon']?>"></i>
                                <?php echo $status[$item['status']]['text'];?>
                            </p>
                            <p class="date-b">
                                <?php echo formatDate($item['create_date'], 'm/d/Y');?>
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-xs-3 pr-0 mt-5">
                                <strong class="total-amount-b txt-red lh-30 fs-12">
                                    Amount: $ <?php echo get_price($item['balance'], false);?>
                                </strong>
                            </div>
                            <div class="col-xs-3 pr-0 mt-5 text-left">
                                <strong class="txt-orange lh-30 fs-12">
                                    <span>Paid:</span>
                                    <span>$ <?php echo get_price($item['amount'], false);?></span>
                                </strong>
                            </div>
                            <div class="col-xs-3 pl-0 mt-5 text-left">
                                <strong class="txt-green lh-30 fs-12">
                                    <span>Confirmed:</span>
                                    <?php if($item['status'] == 'confirmed'){?>
                                        <span>$ <?php echo get_price($item['amount'], false);?></span>
                                    <?php } else{?>
                                        <span>$ 0.00</span>
                                    <?php }?>
                                </strong>
                            </div>
                            <div class="col-xs-3 pl-0 text-right mt-5">
                                <strong class="lh-30 fs-12">
                                    <?php $bill_balance = $item['balance'] - $item['amount'];?>
                                    <?php if($bill_balance < 0){?>
                                        <?php if(in_array($order_detail['status_alias'], $finished_statuses)){?>
                                            <?php if(!$item['refund_bill_request']){?>
                                                <span class="txt-red">*To refund: $ <?php echo get_price(-$bill_balance, false);?></span>
                                            <?php } else{?>
                                                <span class="txt-gray-light">Refunded: $ <?php echo get_price(-$bill_balance, false);?></span>
                                            <?php }?>
                                        <?php } else{?>
                                            <span class="txt-red">*To refund: $ <?php echo get_price(-$bill_balance, false);?></span>
                                        <?php }?>
                                    <?php } else{?>
                                        <span>Balance: $ <?php echo get_price($bill_balance, false);?></span>
                                    <?php }?>
                                </strong>
                            </div>
                        </div>

                        <div class="clearfix">
                            <?php if($bill_balance < 0){?>
                                <span class="txt-gray-light lh-30 fs-12">
                                    *The refund by bills request should be made separately.
                                </span>
                            <?php }?>
                            <?php if(in_array($item['status'], array('paid','confirmed','unvalidated'))){?>
                                <div class="btn btn-default btn-xs pull-right mt-5 toogle_bill_detail" data-toggle="bill-detai-<?php echo $item['id_bill'];?>"><i class="ep-icon ep-icon_visible lh-16"></i> Details</div>
                            <?php }?>
                        </div>

                        <div class="additional-desc ml-0" id="bill-detai-<?php echo $item['id_bill'];?>" style="display:none;">
                            <p><strong>Description:</strong> <?php echo $item['bill_description'];?></p>
                            <?php $pay_detail = unserialize($item['pay_detail']);?>
                            <?php if(!empty($pay_detail)){?>
                            <table class="data table-striped mt-15 w-100pr">
                                <caption><i class="ep-icon ep-icon_dollar-circle"></i> Payment details</caption>
                                <thead>
                                    <tr>
                                        <th class="w-150">Name</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($pay_detail as $detail){?>
                                    <tr>
                                        <td><?php echo $detail['label']; ?></td>
                                        <td><?php echo $detail['value']; ?></td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <?php }?>
                            <?php if(!empty($item['note'])){?>
                            <?php $bill_notes = array_reverse(json_decode('['.$item['note'].']', true));?>
                            <table class="data table-striped mt-15 w-100pr">
                                <caption><i class="ep-icon ep-icon_clock"></i> Bill timeline</caption>
                                <thead>
                                    <tr>
                                        <th class="w-150">Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($bill_notes as $bill_note){?>
                                    <tr>
                                        <td class="w-115"><?php echo formatDate($bill_note['date_note'], 'm/d/Y H:i');?></td>
                                        <td><?php echo $bill_note['note'];?></td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                            <?php }?>
                        </div>
                    </li>
                <?php }?>
            </ul>
        </div>
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
            <thead>
                <tr>
                    <th colspan="2" class="tac"><strong>Create external bills requests</strong></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="w-100" colspan="2">
                        <i class="ep-icon ep-icon_user "></i> <strong>Refund the buyer</strong>
                        <span data-toggle="refund_buyer_row" class="ep-icon ep-icon_plus pull-right toggle_rows cur-pointer"></span>
                    </td>
                </tr>
                <tr class="refund_buyer_row" style="display:none;">
                    <td class="w-100">Refund amount</td>
                    <td>
                        <input type="text" name="refund_buyer" class="validate[custom[positive_number]] w-100pr" value="" placeholder="Refund amount"/>
                    </td>
                </tr>
                <tr class="refund_buyer_row" style="display:none;">
                    <td class="w-100">Notes</td>
                    <td>
                        <textarea class="w-100pr"  name="refund_buyer_notes" rows="5" placeholder="Refund notes"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="w-100" colspan="2">
                        <i class="ep-icon ep-icon_user"></i> <strong>Pay the seller<?php if($order_detail['shipper_type'] == 'ishipper'){?> - <span class="txt-red">The payment include amount for international shipping company!!!</span><?php }?></strong>
                        <span data-toggle="pay_seller_row" class="ep-icon ep-icon_plus pull-right toggle_rows cur-pointer"></span>
                    </td>
                </tr>
                <tr class="pay_seller_row" style="display:none;">
                    <td class="w-100">Pay amount</td>
                    <td>
                        <input type="text" name="pay_seller" class="validate[custom[positive_number]] w-100pr" value="" placeholder="Pay amount"/>
                    </td>
                </tr>
                <tr class="pay_seller_row" style="display:none;">
                    <td class="w-100">Notes</td>
                    <td>
                        <textarea class="w-100pr"  name="pay_seller_notes" rows="5" placeholder="Pay notes"></textarea>
                    </td>
                </tr>
				<?php if($order_detail['shipper_type'] == 'ep_shipper'){?>
					<tr>
						<td class="w-100" colspan="2">
							<i class="ep-icon ep-icon_truck-move "></i> <strong>Pay Export Portal Freight Forwarder</strong>
							<span data-toggle="pay_shipper_row" class="ep-icon ep-icon_plus pull-right toggle_rows cur-pointer"></span>
						</td>
					</tr>
					<tr class="pay_shipper_row" style="display:none">
						<td class="w-100">Pay amount</td>
						<td>
							<input type="text" name="pay_shipper" class="validate[custom[positive_number]] w-100pr" value="" placeholder="Pay amount"/>
						</td>
					</tr>
					<tr class="pay_shipper_row" style="display:none">
						<td class="w-100">Notes</td>
						<td>
							<textarea class="w-100pr"  name="pay_shipper_notes" rows="5" placeholder="Pay notes"></textarea>
						</td>
					</tr>
				<?php }?>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
        <?php if($request_type == 'order'){?>
            <a title="Go back to bills list" class="pull-left btn btn-default fancybox fancybox.ajax" href="<?php echo __SITE_URL . 'order/popups_order/admin_bills_list/' . $order_detail['id'];?>" data-title="All bills"><i class="ep-icon ep-icon_arrows-left "></i> Go back to bills list</a>
        <?php }?>
		<a title="Cancel" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Cancel</a>
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Send</button>
	</div>
</form>
<script type="text/javascript">
	function modalFormCallBack(form){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'external_bills/ajax_external_bills_operation/add_request/by_order/' . $order_detail['id'];?>',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
                    $(globalThis).trigger('external-bills:success-add-request-by-order');

                    closeFancyBox();

                    try {
                        dtMyOrders.fnDraw(false);
                    } catch (error) {
                        // If the function was undefined
                    }
				}else{
					hideLoader($form);
				}
			}
        });
	}
    $(function(){
		$('.toogle_bills_list').click(function(e){
            e.preventDefault();
            $('.'+$(this).data('toggle')).toggle();
            $.fancybox.reposition();
        });
        $('#toggle_external_bills_list').click(function(e){
            e.preventDefault();
            $('.external_bills_list').toggle();
            $(this).text($(this).text() == 'View all notices for external bills' ? 'Hide all notices for external bills' : 'View all notices for external bills');
            $.fancybox.reposition();
        });
        $('.toggle_rows').click(function(e){
            e.preventDefault();
            var toggle_class = $(this).data('toggle');
            $('.'+toggle_class).toggle();
            $(this).toggleClass('ep-icon_plus ep-icon_minus');
            $.fancybox.reposition();
        });
    });

</script>
