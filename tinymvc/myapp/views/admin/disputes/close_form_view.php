<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-10 vam-table">
			<tbody>
				<tr>
					<td class="w-100">Reason</td>
					<td>
						<textarea class="w-100pr h-100 validate[required,maxSize[500]] textcounter-dispute_reason" data-max="500" name="reason"></textarea>
					</td>
				</tr>
				<?php if($dispute['disput_on_order'] == 1){?>
				<tr>
					<td class="w-100">Action</td>
					<td>
						<select name="action" class="select-action w-100pr">
							<option value="continue">Continue order</option>
							<option value="cancel">Cancel order</option>
						</select>
					</td>
				</tr>
				<?php } else{?>
					<input type="hidden" name="action" value="continue"/>
				<?php }?>
			</tbody>
		</table>
		<?php if(in_array($order_detail['status_alias'], array('payment_processing', 'order_paid', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress', 'shipping_completed'))){?>
			<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
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
							<strong class="txt-blue">
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
								<i class="ep-icon ep-icon_<?php echo $status[$item['status']]['icon']?>"></i>
								<?php echo $status[$item['status']]['description'];?>
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
									<span class="lh-20">Paid: $ <?php echo get_price($item['amount'], false);?></span>
								</strong>
							</div>
							<div class="col-xs-3 pl-0 mt-5 text-left">
								<strong class="txt-green lh-30 fs-12">
									Confirmed:
									<?php if($item['status'] == 'confirmed'){?>
										$ <?php echo get_price($item['amount'], false);?>
									<?php } else{?>
										$ 0.00
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
										Balance:$ <?php echo get_price($bill_balance, false);?>
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
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-5 vam-table">
				<tbody>
					<tr>
						<td class="w-100">External bill(s) notes:</td>
						<td>
							<table class="data table-striped table-bordered vam-table w-100pr">
								<tbody>
									<tr>
										<td class="w-150">
											<label>
												<input class="mt-1" type="checkbox" value="1" name="external_bill_buyer" id="external_bill_buyer">
												<span>Refund the buyer</span>
											</label>
										</td>
										<td class="w-120">
											<input type="text" name="external_bill_buyer_amount" class="validate[max[<?php echo $dispute['max_price']?>], condRequired[external_bill_buyer]]" placeholder="0.00">
										</td>
										<td>Buyer request: $<?php echo get_price($dispute['money_back_request'], false); ?></td>

									</tr>
									<?php if(in_array($order_detail['status_alias'], array('shipping_in_progress', 'shipping_completed'))){?>
										<tr class="show_on_cancel" style="display:none;">
											<td class="w-150">
												<label>
													<input class="mt-1" type="checkbox" value="1" name="external_bill_seller" id="external_bill_seller">
													<span>Pay the seller</span>
												</label>
											</td>
											<td class="w-120">
												<input type="text" name="external_bill_seller_amount" class="validate[condRequired[external_bill_seller]]" placeholder="0.00">
											</td>
											<td>
												<?php if($order_detail['shipper_type'] == 'ishipper'){?>
													<strong class="txt-blue">Amount include shipping cost.<br>The seller paid for delivery.</strong>
												<?php }?>
											</td>
										</tr>
										<?php if($order_detail['shipper_type'] == 'ep_shipper'){?>
											<tr class="show_on_cancel" style="display:none;">
												<td class="w-150">
													<label>
														<input class="mt-1" type="checkbox" value="1" name="external_bill_shipper" id="external_bill_shipper">
														<span>Pay EP Freight Forwarder</span>
													</label>
												</td>
												<td class="w-120">
													<input type="text" name="external_bill_shipper_amount" class="validate[condRequired[external_bill_shipper]]" placeholder="0.00">
												</td>
												<td></td>
											</tr>
										<?php } ?>
									<?php } ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }?>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="disput" value="<?php echo $id_dispute; ?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
	$(function(){
		$('.textcounter-dispute_reason').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		$('.radio').click(function(){
			if($(this).attr("value") == 1)
				$(".money").show();
			else
				$(".money").hide();
		});

		if($('.radio:checked').val() == 1)
			$(".money").show();
		else
			$(".money").hide();

		$('.radio').click(function(){
			if($(this).val() == 1)
				$(".money").show();
			else
				$(".money").hide();
		});

		$('.toogle_bills_list').click(function(e){
            e.preventDefault();
            $('.'+$(this).data('toggle')).toggle();
            $.fancybox.reposition();
        });

		$('select[name="action"]').on('change', function(){
			var action = $(this).val();
			if(action == 'cancel'){
				$('.show_on_cancel').show();
			} else{
				$('.show_on_cancel').hide();
				$('.show_on_cancel').find('input[type="checkbox"]').prop('checked', false);
			}
		});

		$('.toogle_bill_detail').on('click', function(e){
            e.preventDefault();
            var toggle_element = $(this).data('toggle');
            if($(this).hasClass('active')){
                $(this).removeClass('active');
            } else{
                $(this).addClass('active');
            }
            $('#'+toggle_element).toggle();
            $.fancybox.reposition();
        });
	});

	function modalFormCallBack(form, data_table){
		if($('.radio:checked').val() == 0)
			$('.money').val('0');

		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>dispute/ajax_operation/close',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw();
				}else{
					hideLoader($form);
				}
			}
        });
	}
</script>
