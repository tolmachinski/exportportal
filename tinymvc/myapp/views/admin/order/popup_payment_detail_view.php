<div class="wr-form-content w-700 mh-600">
    <div class="mt-15 tac">
        <i class="ep-icon ep-icon_dollar-circle"></i> Bill payment detail
    </div>
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered mt-5 w-100pr vam-table">
        <tbody>
            <tr>
                <td class="w-25pr tac">
                    <strong class="txt-red">
                        Amount<br>
                        $ <?php echo get_price($bill_detail['balance'], false);?>
                    </strong>
                </td>
                <td class="w-25pr tac">
                    <strong class="txt-orange">
                        Paid
                        <br>$ <?php echo get_price($bill_detail['amount'], false);?>
                    </strong>
                </td>
                <td class="w-25pr tac">
                    <strong class="txt-green">
                        Confirmed
                        <br>
                        <?php if($bill_detail['status'] == 'confirmed'){?>
                            $ <?php echo get_price($bill_detail['amount'], false);?>
                        <?php } else{?>
                            $ 0.00
                        <?php }?>
                    </strong>
                </td>
                <td class="w-25pr tac">
                    <strong>
                        Balance
                        <br>$ <?php echo get_price($bill_detail['balance'] - $bill_detail['amount'], false);?>
                    </strong>
                </td>
            </tr>
        </tbody>
    </table>
    <?php if(!empty($pay_detail)){?>
	<table class="table-company-info w-100pr mt-15 mb-15">
		<thead>
			<tr>
				<th class="mnw-135">Name</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($pay_detail as $key => $detail) { ?>
				<tr>
                    <td class="mnw-135"><?php echo cleanOutput($detail['label']); ?></td>
                    <td>
                        <?php if ('payment_context' === $key) { ?>
                            <?php dump(json_decode($detail['value'])); ?>
                        <?php } else { ?>
                            <?php echo cleanOutput($detail['value']); ?>
                        <?php } ?>
                    </td>
                </tr>
			<?php } ?>
		</tbody>
	</table>
    <?php }?>
    <div class="mt-15 tac">
        <i class="ep-icon ep-icon_clock"></i> Bill timeline
    </div>
    <?php if(!empty($bill_detail['note'])){?>
        <?php $bill_notes = array_reverse(json_decode('['.$bill_detail['note'].']', true));?>
        <table class="data table-striped table-bordered mt-15 w-100pr">
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
<div class="wr-form-btns clearfix">
    <?php if (null !== $file) { ?>
        <a href="<?php echo $file; ?>" class="btn btn-primary pull-left" rel="nofollow noopener noreferrer" target="_blank">
            Download payment form
        </a>
    <?php } ?>
    <span class="btn btn-default pull-right ml-10 call-function" atas="admin-users__payment-form__close-button" title="Close" data-callback="closeFancyBox" data-message="Are you sure you want to close Payment detail?">Close</span>
    <?php if($bill_detail['status'] == 'paid'){?>
        <!-- <a class="btn btn-danger pull-right ml-10 fancybox.ajax fancyboxValidateModal tooltipstered" data-title="Decline Bill" href="<?php echo __SITE_URL;?>billing/popup_forms/decline_bill/<?php echo $bill_detail['id_bill'];?>">Decline</a> -->
        <span class="btn btn-success pull-right ml-10 confirm-dialog btn-bill-confirm" <?php echo addQaUniqueIdentifier("admin-users_payment-form_confirm-btn")?> href="#" data-callback="confirm_bill" data-message="Are you sure you want to confirm this payment?" data-bill="<?php echo $bill_detail['id_bill'];?>">Confirm payment</span>
    <?php }?>
</div>

<script>
    var confirm_bill = function(element){
        var $this = $(element);
        var bill = intval($this.data('bill'));
        $.ajax({
			type: 'POST',
			url: __site_url + 'billing/ajax_bill_operations/confirm_bill',
			data: { bill : bill },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );
				if(resp.mess_type == 'success'){
					$.fancybox.close();
				}
			}
		});
    }
</script>
