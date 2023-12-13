<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="assign_shipper">
		<div class="modal-flex__content pl-15 pr-15">
            <div class="row">
                <div class="col-12 mt-15">
                    <div class="img-b tac pull-left mr-10 h-40 relative-b">
                        <img class="mh-40 img-position-center" src="<?php echo __IMG_URL . 'public/img/ishippers_logo/' . $ishipper['shipper_logo'];?>">
                    </div>
                    <div class="text-b pull-left">
                        <div class="top-b lh-20"><?php echo $ishipper['shipper_original_name'];?></div>
                        <div class="w-100pr lh-20 txt-gray">Order: <?php echo orderNumber($order_info['id']);?></div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Delivery time</label>
                    in <?php echo $ishippers_quote['delivery_from'];?> &mdash; <?php echo $ishippers_quote['delivery_to'];?> days
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Type of shipment</label>
                    <?php echo $ishippers_quote['shipment_type'];?>
                </div>

                <div class="col-12">
                    <label class="input-label txt-gray">Shipping Insurance</label>
                    <table id="purchase-order--products" class="main-data-table">
                        <thead>
                            <tr>
                                <th>Insurance details</th>
                                <th class="w-170">Amount, in USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($ishippers_quote['insurance_options'])){?>
                                <?php foreach ($ishippers_quote['insurance_options'] as $insurance_option_key => $insurance_option) { ?>
                                    <tr>
                                        <td>
                                            <div class="flex-display">
                                                <label class="input-label flex--1 m-0 custom-radio">
                                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                                    <input class="js-insurance_option-radio--ickeck validate[minCheckbox[1]]" type="radio" name="insurance_option" value="<?php echo $insurance_option_key;?>" data-amount="<?php echo (float) $insurance_option['amount'];?>">
                                                    <?php }?>

                                                    <span class="custom-radio__text"><?php echo cleanOutput($insurance_option['title']); ?></span>
                                                </label>
                                                <a class="ep-icon ep-icon_info fs-16 lh-20 ml-5 info-dialog" data-content="#js-insurance_option--details-<?php echo $insurance_option_key;?>" data-title="<?php echo cleanOutput($insurance_option['title']); ?>" title="Insurance details"></a>
                                            </div>
                                            <div class="display-n" id="js-insurance_option--details-<?php echo $insurance_option_key;?>"><?php echo cleanOutput($insurance_option['description']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo get_price($insurance_option['amount'], false); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php }?>
                            <tr>
                                <td>
                                    <label class="input-label m-0 custom-radio">
                                        <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                        <input class="js-insurance_option-radio--ickeck validate[minCheckbox[1]]" type="radio" name="insurance_option" value="no" data-amount="0">
                                        <?php }?>

                                        <span class="custom-radio__text">Shipping Insurance not needed</span>
                                    </label>
                                </td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <td class="tar">
                                    <strong>Amount Due</strong>
                                </td>
                                <td><span id="js-shipping_rate--final-amount"><?php echo get_price($ishippers_quote['amount'], false);?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-left">
                <a class="btn btn-dark fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>order/popups_order/order_shipping_quotes/<?php echo $order_info['id'];?>" data-title="Shipping rates" data-mw="900">Back to Rates</a>
            </div>

            <?php if(!$shipper_assigned && have_right('buy_item')){?>
                <input type="hidden" name="order" value="<?php echo $order_info['id'];?>"/>
                <input type="hidden" name="shipping_quote" value="<?php echo $ishippers_quote['id_shipper'];?>"/>
                <input type="hidden" name="shipping_quote_type" value="ishipper"/>
                <div class="modal-flex__btns-right">
                    <button class="btn btn-success" type="submit">Assign</button>
                </div>
            <?php }?>
		</div>
	</form>
</div>
<script>
    $(function(){
        if(($('#purchase-order--products.main-data-table--mobile').length == 0) && ($(window).width() < 660)){
			$('#purchase-order--products:not(.main-data-table--mobile)').addClass('main-data-table--mobile');
		}
    });

    <?php if(!$shipper_assigned && have_right('buy_item')){?>

        var shipping_amount = floatval('<?php echo $ishippers_quote['amount'];?>');

        $('.js-insurance_option-radio').on('change', function(){

            var $this = $(this);

            if($this.hasClass('validengine-border')){
                $this.removeClass('validengine-border');
            }

            var insurance_amount = floatval($this.data('amount'));
            $('#js-shipping_rate--final-amount').text(get_price(shipping_amount + insurance_amount, false));
        });

        function assign_shipper(form){
            var $form = $(form);
            var $wrform = $form.closest('.js-modal-flex');

            $.ajax({
                type: 'POST',
                url: __group_site_url + 'order/ajax_order_operations/assign_shipper',
                data: $form.serialize(),
                dataType: 'JSON',
                beforeSend: function(){
                    showLoader($wrform, 'Assigning freight forwarder...');
                    $form.find('button[type=submit]').addClass('disabled');
                },
                success: function(resp){
                    hideLoader($wrform);
                    systemMessages( resp.message, resp.mess_type );

                    if(resp.mess_type == 'success'){
                        assign_shipper_callback(resp);
                        closeFancyBox();
                    }else{
                        $form.find('button[type=submit]').removeClass('disabled');
                        hideLoader($wrform);
                    }
                }
            });
        }
    <?php }?>
</script>
