<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-700 mh-600">
        <div class="mt-15 tac">
            <i class="ep-icon ep-icon_dollar-circle"></i> <strong>Bill payment detail</strong>
        </div>
        <table cellspacing="0" cellpadding="0" class="data table-striped mt-5 w-100pr vam-table">
            <tbody>
                <tr>
                    <td class="w-100 tac">
                        <strong class="txt-red">
                            Amount<br>
                            $ <?php echo get_price($bill['balance'], false);?>
                        </strong>
                    </td>
                    <td class="tac">
                        <strong class="txt-orange">
                            Paid
                            <br>$ <?php echo get_price($bill['amount'], false);?>
                        </strong>
                    </td>
                    <td class="tac">
                        <strong class="txt-green">
                            Confirmed
                            <br>$ <?php echo get_price($bill['amount'], false);?>
                        </strong>
                    </td>
                    <td class="tac">
                        <strong>
                            Balance
                            <br>$ <?php echo get_price($bill['balance'] - $bill['amount'], false);?>
                        </strong>
                    </td>
                    <td class="tac w-80">
                        <span class="btn btn-default" id="payment_detail">Details</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="payment_detail" style="display:none;">
            <table class="table-company-info w-100pr mt-15 mb-15">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pay_detail as $detail){?>
                        <?php if($detail['label'] != ''){?>
                        <tr>
                            <td><?php echo $detail['label']; ?></td>
                            <td><?php echo $detail['value']; ?></td>
                        </tr>
                        <?php }?>
                    <?php }?>
                </tbody>
            </table>
            <div class="mt-15 tac">
                <i class="ep-icon ep-icon_clock"></i> <strong>Bill timeline</strong>
            </div>
            <?php if(!empty($bill['note'])){?>
                <?php $bill_notes = array_reverse(json_decode('['.$bill['note'].']', true));?>
                <table class="data table-striped mt-15 w-100pr">
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
                                <td class="tac"><?php echo get_price($request['money'], false);?></td>
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
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Refund</td>
                    <td>
                        <input type="text" name="refund_amount" class="validate[required,custom[positive_number]] w-100pr" value="<?php echo $bill['amount'] - $bill['balance'];?>" placeholder="Refund amount"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Notes</td>
                    <td>
                        <textarea class="w-100pr validate[required]"  name="notes" rows="10" placeholder="Refund notes"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
        <?php if(!empty($return_to_modal_url)){?>
            <a class="pull-left btn btn-default fancybox fancybox.ajax" href="<?php echo $return_to_modal_url;?>" data-title="All bills">
                <i class="ep-icon ep-icon_arrows-left "></i> Go back
            </a>
        <?php }?>
        <input type="hidden" name="bill" value="<?php echo $bill['id_bill']; ?>"/>
		<a title="Cancel" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Cancel</a>
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Refund</button>
	</div>
</form>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>external_bills/ajax_external_bills_operation/add_request/refund',
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
						data_table.fnDraw(false);
				}else{
					hideLoader($form);
				}
			}
        });
	}
    $(function(){
        $('#payment_detail').click(function(){
            $('.payment_detail').toggle();
            $.fancybox.reposition();
        });
    });

</script>
