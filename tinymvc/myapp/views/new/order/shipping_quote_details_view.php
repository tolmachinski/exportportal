<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="assign_shipper">
		<div class="modal-flex__content pl-15 pr-15">
            <div class="row">
                <div class="col-12 mt-15">
                    <div class="img-b tac pull-left mr-10 w-55 h-40 relative-b">
                        <img class="mw-55 mh-40 img-position-center" src="<?php echo getShipperLogo($shipper['id'], $shipper['logo'], 0);?>">
                    </div>
                    <div class="text-b pull-left">
                        <div class="top-b lh-20"><?php echo $shipper['co_name'];?></div>
                        <div class="w-100pr lh-20 txt-gray">Order: <?php echo orderNumber($request_details['id_order']);?></div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Pickup date</label>
                    <?php echo getDateFormat($request_details['pickup_date'], 'Y-m-d H:i:s', 'j M, Y');?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Delivery start</label>
                    <?php echo getDateFormat($request_details['delivery_date'], 'Y-m-d H:i:s', 'j M, Y');?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Delivery time</label>
                    in <?php echo $request_details['delivery_days_from'];?> &mdash; <?php echo $request_details['delivery_days_to'];?> day(s)
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Type of shipment</label>
                    <?php echo $request_details['type_name'];?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Container Freight Station</label>
                    <?php echo $request_details['shipment_cfs'];?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Freight Forwarder</label>
                    <?php echo $request_details['shipment_ff'];?>
                </div>
                <div class="col-12">
                    <label class="input-label txt-gray">Scheduling pickup</label>
                    <?php echo $request_details['shipment_pickup'] == 'shipper' ? 'Freight Forwarder will pickup good from the seller location.' : 'Seller must be delivering goods to freight forwarder location.';?>
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
                            <?php $insurance_options = !empty($request_details['insurance_options']) ? json_decode($request_details['insurance_options'], true) : array();?>
                            <?php if(!empty($insurance_options)){?>
                                <?php foreach ($insurance_options as $insurance_option_key => $insurance_option) { ?>
                                    <tr>
                                        <td>
                                            <div class="flex-display">
                                                <label class="input-label flex--1 m-0 custom-radio">
                                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                                    <input class="js-insurance_option-radio validate[minCheckbox[1]]" type="radio" name="insurance_option" value="<?php echo $insurance_option_key;?>" data-amount="<?php echo (float) $insurance_option['amount'];?>">
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
                                            <input class="js-insurance_option-radio validate[minCheckbox[1]]" type="radio" name="insurance_option" value="no" data-amount="0">
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
                                <td><span id="js-shipping_rate--final-amount"><?php echo get_price($request_details['shipping_price'], false);?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12">
                    <label class="input-label txt-gray">Freight Forwarder's comment</label>
                    <p><?php echo $request_details['comment_shipper'];?></p>
                </div>
            </div>
		</div>
		<div class="modal-flex__btns">
		    <div class="modal-flex__btns-left">
                <a class="btn btn-dark fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>order/popups_order/order_shipping_quotes/<?php echo $request_details['id_order'];?>" data-title="Shipping rates" data-mw="900">Back to Rates</a>
            </div>

            <?php if(!$shipper_assigned && have_right('buy_item')){?>
                <input type="hidden" name="order" value="<?php echo $request_details['id_order'];?>"/>
                <input type="hidden" name="shipping_quote" value="<?php echo $request_details['id_quote'];?>"/>
                <input type="hidden" name="shipping_quote_type" value="ep_shipper"/>

                <div class="modal-flex__btns-right">
                    <button class="btn btn-success" type="submit">Assign</button>
                </div>
            <?php }?>
		</div>
	</form>
</div>
<script>
    var shipping_amount = floatval('<?php echo $request_details['shipping_price'];?>');

    <?php if(!$shipper_assigned && have_right('buy_item')){?>

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
