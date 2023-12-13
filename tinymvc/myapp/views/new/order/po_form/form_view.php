<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form" id="purchase-order--form--wrapper">
		<ul class="nav nav-tabs nav-form-items" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" href="#delivery-from-location" aria-controls="title" role="tab" data-toggle="tab">
					<i class="nav-form-items__nr">1</i>
					<span class="nav-form-items__name">Delivery <br>from location</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#general-information" aria-controls="title" role="tab" data-toggle="tab">
					<i class="nav-form-items__nr">2</i>
					<span class="nav-form-items__name">General <br>information</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#items-information" aria-controls="title" role="tab" data-toggle="tab">
					<i class="nav-form-items__nr">3</i>
					<span class="nav-form-items__name">Item(s) <br>information</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#package-size" aria-controls="title" role="tab" data-toggle="tab">
					<i class="nav-form-items__nr">4</i>
					<span class="nav-form-items__name">Box/Package <br>sizes</span>
				</a>
			</li>
		</ul>

		<form id="purchase-order--form" class="modal-flex__content validateModalTabs" autocomplete="off">
			<div class="tab-content">
				<!-- START tab 1 -->
				<div role="tabpanel" class="tab-pane tab-pane-submit fade show active" id="delivery-from-location">
                    <div class="container-fluid-modal">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="input-label input-label--required">Country</label>
                                <select class="validate[required]" id="country" name="port_country">
									<?php echo getCountrySelectOptions($countries, $shipping_from['country']);?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6" id="state_block">
                                <label class="input-label input-label--required">State / Region</label>

                                <select class="validate[required]" name="states" id="states">
                                    <option value="">Select State / Region</option>
                                    <?php if(isset($states) && !empty($states)){ ?>
                                        <?php foreach($states as $state){?>
                                            <option value="<?php echo $state['id'];?>" <?php echo selected($shipping_from['state'], $state['id']);?>><?php echo $state['state'];?></option>
                                        <?php } ?>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 wr-select2-h50" id="city_block">
                                <label class="input-label input-label--required">City</label>

                                <select class="validate[required] select-city" name="port_city" id="port_city">
                                    <option value="">Select city</option>
                                    <?php if(isset($city_selected) && !empty($city_selected)){ ?>
                                        <option value="<?php echo $city_selected['id'];?>" selected>
                                            <?php echo $city_selected['city'];?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="input-label input-label--required">Zip</label>
                                <input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php echo $shipping_from['zip']?>"/>
                            </div>
                            <div class="col-12">
                                <label class="input-label input-label--required">Address</label>
                                <input class="validate[required]" type="text" name="address" value="<?php echo $shipping_from['address']?>"/>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="input-label input-label--required">Available area for delivering, in km</label>
                                <div class="input-group">
                                    <input class="form-control validate[required, custom[positive_integer], min[0], max[<?php echo config('order_min_available_area_for_delivery', 40000);?>]]" type="text" name="delivery_area" value="<?php echo (int)$order['seller_delivery_area'];?>" placeholder="e.g. 100"/>
                                    <div class="input-group-btn">
                                        <span class="btn btn-primary info-dialog" data-message="The maximum distance the seller can deliver the products to the freight forwarder or another person assigned by the buyer at the seller’s warehouse or another named place." data-title="What is: Available area for delivering?">
                                            <i class="ep-icon ep-icon_info-stroke"></i>
                                        </span>
                                    </div>
                                </div>
							</div>
							<div class="col-12 col-md-6">
								<label class="input-label input-label--required">Type of shipment</label>
								<div class="form-group">
                                    <div class="input-info-right input-info-right--btn">
                                        <div id="js-btn-shipment-type" class="btn btn-light call-function input-info-right__btn" data-callback="shippingTypeOpenModal"><span id="js-selected-shipping-type-name"><?php echo (int)$order['shipment_type'] > 0 ? $shipping_types[$order['shipment_type']]['type_name'] : "Select Type of Shipment";?></span><span class="ep-icon ep-icon_arrow-down fs-11 ml-15"></span></div>
                                        <input id="js-input-shipment-type" type="hidden" name="shipment_type" value="<?php echo (int)$order['shipment_type'] > 0 ? $order['shipment_type'] : "";?>">
                                        <div class="input-info-right__txt">
                                            <span>More information about our shipping methods can be found here:</span>
                                            <a href="<?php echo __SITE_URL;?>landing/shipping_methods" target="_blank">
                                                Learn more
                                            </a>
                                        </div>
                                    </div>
                                </div>
							</div>
						</div>
                    </div>
				</div>
				<!-- END tab 1 -->
				<!-- START tab 2 -->
				<div role="tabpanel" class="tab-pane tab-pane-submit fade" id="general-information">
					<div class="container-fluid-modal">
						<div class="row">
							<div class="col-12 col-md-6">
								<label class="input-label">Issue date</label>
								<p class="lh-40"><?php echo date('m/d/Y');?></p>
							</div>
							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">Invoice Due date</label>
									<input class="js-invoice-duedate-datepicker" value="<?php echo !empty($order['purchase_order']['due_date']) && validateDate($order['purchase_order']['due_date'], 'Y-m-d') ? getDateFormat($order['purchase_order']['due_date'], 'Y-m-d', 'm/d/Y') : '';?>" type="text" placeholder="Click to select date" name="invoice_due_date" readonly>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">PO number</label>
									<input type="text"
											name="po_number"
											maxlength="12"
											class="validate[required,custom[alphaNumeric],maxSize[12]]"
											placeholder="Enter the PO number"
											value="<?php echo !empty($order['purchase_order']['invoice']) ? $order['purchase_order']['invoice']['po_number']: '';?>"/>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label">Order Discount, %</label>
									<input type="text"
										id="purchase-order--formfield--discount"
										name="discount"
										class="validate[required,custom[positive_number],min[0],max[99]]"
										placeholder="Enter the discount"
										value="<?php echo normalize_discount($order['discount']);?>"/>
								</div>
							</div>

							<div class="col-12">
								<label class="input-label">Order Notes <a class="ep-icon ep-icon_info fs-16 info-dialog" data-message="Additional order information displayed on the invoice." data-title="What is: Order Notes?" title="What is: Order Notes?"></a></label>
								<textarea name="notes"
									id="purchase-order--formfield--notes"
									class="textcounter"
									data-max="1000"
									placeholder="Enter your note here"><?php echo !empty($order['purchase_order']['invoice']) ? $order['purchase_order']['invoice']['notes']: '';?></textarea>
							</div>
						</div>
					</div>
				</div>
				<!-- END tab 2 -->
				<!-- START tab 3 -->
				<div role="tabpanel" class="tab-pane tab-pane-submit fade" id="items-information">
					<div class="container-fluid-modal">
						<div class="row" id="purchase-order--items-information-container">
							<div class="col-12">
								<table id="purchase-order--ordered-items" class="main-data-table">
									<thead>
										<tr>
											<th colspan="2">Ordered items</th>
											<th class="w-150">Quantity</th>
											<th class="w-150">Unit price</th>
											<th class="w-150">Amount</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($order['purchase_order']['invoice']['ordered_items'])) { ?>
											<?php foreach ($order['purchase_order']['invoice']['ordered_items'] as $key => $item) { ?>
												<tr>
													<td data-title="Ordered item" colspan="2">
														<div class="grid-text">
															<div class="grid-text__item">
																<a class="order-detail__prod-link" href="<?php echo  makeItemUrl($item['id_item'], $item['name']); ?>" target="_blank">
																	<?php echo cleanOutput($item['name']); ?>
																</a>
															</div>
														</div>
														<?php echo cleanOutput($item['detail_ordered']); ?>
													</td>
													<td data-title="Quantity">
														<?php echo cleanOutput($item['quantity']); ?>
													</td>
													<td data-title="Unit price">
														$ <?php echo get_price($item['unit_price'], false); ?>
													</td>
													<td data-title="Amount" id="total-tr">
														$ <?php echo get_price(($item['quantity'] * $item['unit_price']), false); ?>
													</td>
												</tr>
											<?php } ?>
										<?php } else { ?>
											<?php foreach ($order['ordered'] as $key => $item) { ?>
												<tr>
													<td colspan="2">
														<div class="grid-text">
															<div class="grid-text__item">
																<a class="order-detail__prod-link" href="<?php echo  makeItemUrl($item['id_item'], $item['title']); ?>" target="_blank">
																	<?php echo cleanOutput($item['title']); ?>
																</a>
															</div>
														</div>
														<?php echo cleanOutput($item['detail_ordered']); ?>
													</td>
													<td>
														<?php echo cleanOutput($item['quantity_ordered']); ?>
													</td>
													<td>
														$ <?php echo get_price($item['price_ordered'], false); ?>
													</td>
													<td id="total-tr">
														$ <?php echo get_price(($item['quantity_ordered'] * $item['price_ordered']), false); ?>
													</td>
												</tr>
											<?php } ?>
										<?php } ?>
									</tbody>
								</table>
							</div>

							<?php if ($order['order_type'] !== 'po') { ?>
								<div class="col-12 mt-15">
									<table id="purchase-order--additional-items" class="main-data-table">
										<thead>
											<tr>
												<th colspan="2">Additional items</th>
												<th class="w-150">Quantity</th>
												<th class="w-150">Unit price</th>
												<th class="w-150">Amount</th>
											</tr>
										</thead>
										<tbody>
											<?php if (!empty($order['purchase_order']['invoice']['additional_items'])) { ?>
												<?php foreach ($order['purchase_order']['invoice']['additional_items'] as $index => $item) { ?>
													<tr class="aditional">
														<td class="w-40">
															<span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 confirm-dialog" data-callback="delete_existent_aditional_row" data-message="Are you sure you want to remove this row?" title="Delete row"></span>
														</td>
														<td data-title="Additional item">
															<?php echo cleanOutput($item['name']); ?>
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][title]" value="<?php echo cleanOutput($item['name']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][quantity]" value="<?php echo cleanOutput($item['quantity']) ;?>" class="js-calc-additional-quantity">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][unit_price]" value="<?php echo cleanOutput($item['unit_price']) ;?>" class="js-calc-additional-unit_price">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][country_abr]" value="<?php echo cleanOutput($item['country_abr']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][hs_code]" value="<?php echo cleanOutput($item['hs_tariff_number']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][length]" value="<?php echo cleanOutput($item['item_length']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][width]" value="<?php echo cleanOutput($item['item_width']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][height]" value="<?php echo cleanOutput($item['item_height']) ;?>">
															<input type="hidden" name="po_items[<?php echo $index + 1 ;?>][weight]" value="<?php echo cleanOutput($item['item_weight']) ;?>" class="js-calc-additional-weight">
														</td>
														<td data-title="Quantity">
															<?php echo cleanOutput($item['quantity']); ?>
														</td>
														<td data-title="Unit price">
															$ <?php echo get_price($item['unit_price'], false); ?>
														</td>
														<td data-title="Amount" id="total-tr">
															$ <?php echo get_price(($item['quantity'] * $item['unit_price']), false); ?>
														</td>
													</tr>
												<?php } ?>
											<?php } else { ?>
												<tr>
													<td colspan="5">
														No additional items.
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>

								<div class="col-12 mt-15">
									<span class="btn btn-light call-function pull-right" data-callback="add_aditional_row" id="js-add_aditional_row">Add additional item</span>
								</div>
							<?php } ?>

							<div class="col-12 mt-15">
								<table class="main-data-table">
									<tbody>
										<tr>
											<td class="tar vam bdt-none">
												<strong>Subtotal</strong>
											</td>
											<td class="w-150 bdt-none">
												<strong id="purchase-order--formfield--subtotal">$ <?php echo get_price($order['price'], false); ?></strong>
											</td>
										</tr>
										<tr>
											<td class="tar vam">
												<strong>Order Discount</strong>
											</td>
											<td class="w-150 vam">
												<strong><span id="purchase-order--formfield--discount-text"><?php echo normalize_discount($order['discount']);?></span> %</strong>
											</td>
										</tr>
										<tr>
											<td class="tar">
												<strong>Amount Due</strong>
											</td>
											<td class="w-150 vam">
												<strong id="purchase-order--formfield--amount-due">$ <?php echo get_price($order['final_price'], false); ?></strong>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<?php if ($order['order_type'] == 'po') { ?>
								<div class="col-12">
									<div class="row">
										<div class="col-12 col-lg-8">
											<label class="input-label">Split total amount</label>
										</div>
									</div>

									<div id="purchase-order--formtemplate--partial-payment-container">
										<?php if (!empty($order['purchase_order']['invoice']['invoice_map'])) { ?>
											<?php foreach($order['purchase_order']['invoice']['invoice_map'] as $bill_key => $bill_info){?>
												<div class="row bdt-1-gray <?php if($bill_key > 0){ echo 'mt-20'; }?> partial-payment">
													<div class="col-12 col-md-6 col-lg-3">
														<div class="relative-b">
															<label class="input-label input-label--required">Amount</label>
															<input type="text"
																class="form-control input-percent validate[required,custom[noWhitespaces],custom[positive_number],min[1]]"
																name="bill[<?php echo $bill_key;?>][amount]"
																placeholder="0.00"
																value="<?php echo get_price($bill_info['price'], false);?>"/>
														</div>
													</div>

													<div class="col-12 col-md-6 col-lg-3">
														<div class="relative-b">
															<label class="input-label input-label--required">Pay in N days</label>
															<input type="text"
																class="validate[required,custom[noWhitespaces],custom[natural],min[1],max[30]]"
																name="bill[<?php echo $bill_key;?>][due_date]"
																placeholder="Enter nr. of days"
																value="<?php echo $bill_info['due_date'];?>"/>
														</div>
													</div>

													<div class="col-12 col-lg-6">
														<div class="relative-b">
															<label class="input-label input-label--required">Note</label>

															<div class="input-group mb-3">
																<input type="text"
																	class="form-control validate[required,maxSize[500]]"
																	name="bill[<?php echo $bill_key;?>][note]"
																	placeholder="Enter the note"
																	value="<?php echo $bill_info['note'];?>"/>
																<div class="input-group-append">
																	<a class="ep-icon ep-icon_remove-stroke lh-40 pl-10 call-function" data-callback="deletePartialPayment"></a>
																</div>
															</div>
														</div>
													</div>
												</div>
											<?php }?>
										<?php } else{ ?>
											<div class="row bdt-1-gray partial-payment">
												<div class="col-12 col-md-6 col-lg-3">
													<div class="relative-b">
														<label class="input-label input-label--required">Amount</label>
														<input type="text"
															class="form-control input-percent validate[required,custom[noWhitespaces],custom[positive_number],min[1]]"
															name="bill[0][amount]"
															placeholder="e.g. 1"/>
													</div>
												</div>

												<div class="col-12 col-md-6 col-lg-3">
													<div class="relative-b">
														<label class="input-label input-label--required">Pay in N days</label>
														<input type="text"
															class="validate[required,custom[noWhitespaces],custom[natural],min[1],max[30]]"
															name="bill[0][due_date]"
															placeholder="Enter nr. of days"/>
													</div>
												</div>

												<div class="col-12 col-lg-6">
													<div class="relative-b">
														<label class="input-label input-label--required">Note</label>
														<input type="text"
															class="validate[required,maxSize[500]]"
															name="bill[0][note]"
															placeholder="Enter the note"/>
													</div>
												</div>
											</div>
										<?php } ?>
									</div>
								</div>
								<div class="col-12 mt-15">
									<span class="btn btn-light call-function" data-callback="addPartialPayment">Add row</span>
								</div>
							<?php } ?>
						</div>

						<div class="row display-n" id="purchase-order--additional-items-form-container"></div>
					</div>
				</div>
				<!-- END tab 3 -->
				<!-- START tab 4 -->
				<div role="tabpanel" class="tab-pane tab-pane-submit fade" id="package-size">
					<?php $package = json_decode($order['package_detail'], true);?>
					<div class="container-fluid-modal">
						<div class="row">
							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">Length, cm</label>
									<input type="text"
										name="package[length]"
										class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
										value="<?php echo !empty($package) ? $package['length'] : ''; ?>"
										placeholder="Enter the length of the box"/>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">Width, cm</label>
									<input type="text"
										name="package[width]"
										class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
										value="<?php echo !empty($package) ? $package['width'] : ''; ?>"
										placeholder="Enter the width of the box"/>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">Height, cm</label>
									<input type="text"
										name="package[height]"
										class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
										value="<?php echo !empty($package) ? $package['height'] : ''; ?>"
										placeholder="Enter the height of the box"/>
								</div>
							</div>

							<div class="col-12 col-md-6">
								<div class="relative-b">
									<label class="input-label input-label--required">Weight, kg</label>
									<input
										class="validate[required,custom[noWhitespaces],min[0.1],max[9999999999.99]]"
										type="text"
										name="package[weight]"
										value="<?php echo !empty($package) ? $package['weight'] : ''; ?>"
										placeholder="Enter the weight of the box"
                                    />
								</div>
							</div>
                            <div class="col-12 col-md-6">
                                <label class="input-label input-label--required">Estimate time for packaging, days</label>
                                <?php $timeline_countdowns = json_decode($order['timeline_countdowns'], true);?>
                                <div class="input-group">
                                    <input class="form-control validate[required, custom[positive_integer], min[<?php echo config('order_min_estimate_time_for_packaging', 1);?>], max[<?php echo config('order_max_estimate_time_for_packaging', 180);?>]]" type="text" name="packaging" value="<?php if(isset($timeline_countdowns['time_for_packaging'])){echo $timeline_countdowns['time_for_packaging'];};?>" placeholder="e.g. 5"/>
                                    <div class="input-group-btn">
                                        <span class="btn btn-primary info-dialog" data-message="The number of days the seller needs for product packaging." data-title="What is: Estimate time for packaging?">
                                            <i class="ep-icon ep-icon_info-stroke"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
				</div>
				<!-- END tab 4 -->
			</div>

			<input type="hidden" value="<?php echo $order['id'];?>" name="id_order" />
		</form>

		<div class="modal-flex__btns">
			<div class="modal-flex__btns-left">
				<button class="btn btn-dark js-formStepNav js-prevFormStep call-function" data-callback="prevFormStep">Back</button>
			</div>

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary js-formStepNav js-nextFormStep call-function" data-callback="nextFormStep">Next</button>
				<button class="btn btn-success js-formStepNav js-submitFormStep call-function display-n" data-callback="submitFormStep">Submit</button>
			</div>
		</div>
	</div>
</div>

<input type="hidden" name="purchase-order--field-price" id="purchase-order--field-price" value="<?php echo $order['price'];?>">

<div class="display-n" id="js-select-shipping-type-template">
    <h3 class="heading8">Select Type of Shipment</h3>

    <div class="checkbox-list">
        <?php foreach($shipping_types as $shipping_type){?>
            <div class="checkbox-list__item">
                <label class="checkbox-list__label custom-radio">
                    <input
                        class="js-check-radio"
                        type="radio"
                        name="shipment_type"
                        value="<?php echo $shipping_type['id_type'];?>"
                        data-name="<?php echo $shipping_type['type_name'];?>"
                        <?php echo checked($shipping_type['id_type'], $order['shipment_type']);?>
                    >
                    <div class="checkbox-list__txt custom-radio__text">
                        <strong><?php echo cleanOutput($shipping_type['type_name']);?></strong>
                        <p><?php echo cleanOutput($shipping_type['type_description']);?></p>
                    </div>
                </label>
            </div>
        <?php }?>
    </div>
</div>

<div class="display-n" id="purchase-order--shipping_type-details">
	<?php echo implode('', $shipping_types_details);?>
</div>

<script type="text/template" id="purchase-order--formtemplate--add-additional-item">
	<div class="col-12">
		<label class="input-label">
			<h2 class="title-public__txt">Add additional item</h2>
		</label>
	</div>
	<div class="col-12">
		<div class="relative-b">
			<label class="input-label input-label--required">Item title</label>
			<input type="text"
				maxlength="250"
				class="validate[required,maxSize[250]]"
				name="aditional_item_title"
				placeholder="Enter the item title">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Quantity</label>
			<input type="text"
				maxlength="6"
				class="validate[required,custom[noWhitespaces],min[1],max[999999],custom[positive_number]]"
				name="aditional_item_quantity"
				placeholder="Enter the quantity">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Unit Price, in USD</label>
			<input type="text"
				maxlength="6"
				class="validate[required,custom[noWhitespaces],min[0],max[999999],custom[positive_number]]"
				name="aditional_item_unit_price"
				placeholder="Enter the unit price">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">
				Harmonized Tariff Schedule
				<a class="ep-icon ep-icon_info lh-16 info-dialog" data-content="#info-dialog__hr_tariff_number" title="What is: Harmonized Tariff Schedule?" data-title="What is: Harmonized Tariff Schedule?"></a>
				<div class="display-n" id="info-dialog__hr_tariff_number">
					<?php echo $block_info['hr_tariff_number']['menu_description']; ?>
				</div>
			</label>
			<input type="text"
				name="aditional_item_hs_code"
				class="validate[required,custom[tariffNumber]]"
				maxlength="13"
				placeholder="Enter code, e.g. 8803.03.0003"
				title="The first six digit code as specified by harmonized system for tariffs">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Origin country</label>
			<select name="aditional_item_country_abr" class="validate[required]">
				<option value="" selected disabled>Select origin country</option>
				<?php foreach ($countries as $conutry) { ?>
					<option value="<?php echo cleanOutput($conutry['abr']); ?>">
						<?php echo cleanOutput($conutry['country']); ?>
					</option>
				<?php } ?>
			</select>
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Length, cm</label>
			<input type="text"
				name="aditional_item_length"
				class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
				maxlength="4"
				placeholder="Enter the length of the box">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Width, cm</label>
			<input type="text"
				name="aditional_item_width"
				class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
				maxlength="4"
				placeholder="Enter the width of the box">
		</div>
	</div>

	<div class="col-12 col-md-6 relative-b">
		<div class="relative-b">
			<label class="input-label input-label--required">Height, cm</label>
			<input type="text"
				name="aditional_item_height"
				class="validate[required,custom[noWhitespaces],min[1],max[5000],custom[positive_number]]"
				maxlength="4"
				placeholder="Enter the height of the box">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="relative-b">
			<label class="input-label input-label--required">Weight, Kg</label>
			<input type="text"
				name="aditional_item_weight"
				class="validate[required,custom[noWhitespaces],min[0.001],max[500000]]"
				maxlength="6"
				placeholder="Enter the weight of the box">
		</div>
	</div>

	<div class="col-12 tar mt-15 mb-10">
		<span class="btn btn-danger w-130 call-function" data-callback="cancel_aditional_row">Cancel</span>
		<span class="btn btn-success w-130 call-function" data-callback="add_aditional_item">Add</span>
	</div>
</script>

<script type="text/template" id="purchase-order--formtemplate--added-item">
	<tr class="aditional">
		<td class="w-40">
			<span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 confirm-dialog" data-callback="delete_aditional_row" data-message="Are you sure you want to remove this row?" title="Delete row"></span>
		</td>
		<td data-title="Product">
			{{title}}
			<input type="hidden" name="new_items[{{index}}][title]" value="{{title}}">
			<input type="hidden" name="new_items[{{index}}][quantity]" value="{{quantity}}" class="js-calc-additional-quantity">
			<input type="hidden" name="new_items[{{index}}][unit_price]" value="{{price}}" class="js-calc-additional-unit_price">
			<input type="hidden" name="new_items[{{index}}][country_abr]" value="{{country}}">
			<input type="hidden" name="new_items[{{index}}][hs_code]" value="{{code}}">
			<input type="hidden" name="new_items[{{index}}][length]" value="{{length}}">
			<input type="hidden" name="new_items[{{index}}][width]" value="{{width}}">
			<input type="hidden" name="new_items[{{index}}][height]" value="{{height}}">
			<input type="hidden" name="new_items[{{index}}][weight]" value="{{weight}}" class="js-calc-additional-weight">
		</td>
		<td data-title="Quantity">
			{{quantity}}
		</td>
		<td data-title="Unit price">
			$ {{priceLabel}}
		</td>
		<td data-title="Amount">
			$ {{amountLabel}}
		</td>
	</tr>
</script>

<script type="text/template" id="purchase-order--formtemplate--partial-payment">
	<div class="row bdt-1-gray mt-20 partial-payment">
		<div class="col-12 col-md-6 col-lg-3">
			<div class="relative-b">
				<label class="input-label input-label--required">Amount</label>
				<input type="text"
					class="form-control input-percent validate[required,custom[noWhitespaces],custom[positive_number],min[1]]"
					name="bill[{{index}}][amount]"
					placeholder="e.g. 1"/>
			</div>
		</div>

		<div class="col-12 col-md-6 col-lg-3">
			<div class="relative-b">
				<label class="input-label input-label--required">Pay in N days</label>
				<input type="text"
					class="validate[required,custom[noWhitespaces],custom[natural],min[1],max[30]]"
					name="bill[{{index}}][due_date]"
					placeholder="Enter nr. of days"/>
			</div>
		</div>

		<div class="col-12 col-lg-6">
			<div class="relative-b">
				<label class="input-label input-label--required">Note</label>
				<div class="input-group mb-3">
					<input type="text"
						class="form-control validate[required,maxSize[500]]"
						name="bill[{{index}}][note]"
						placeholder="Enter the note"/>
					<div class="input-group-append">
						<a class="ep-icon ep-icon_remove-stroke lh-40 pl-10 call-function" data-callback="deletePartialPayment"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/javascript">
	var statusValidate = false;
	var $selectCity;
	var selectState = intval('<?php echo $shipping_from['state'];?>');
	var ttlText = 'Purchase Order (PO)';
	var existPurchaseOrder = Boolean(intval('<?php echo (int) $order['status_alias'] == 'new_order'; ?>'));
	var purchaseOrderAmount = floatval('<?php echo $order['price']; ?>');
	var orderProductsWeight = floatval('<?php echo $order['purchase_order']['products_weight']; ?>');
	var totalWeightInit = floatval('<?php echo $weightCalc; ?>');
	var calcTotalWeight = floatval('<?php echo $weightCalc; ?>');

	var validateTabInit = function() {
		$('.validateModalTabs').validationEngine("detach");
		$('.validateModalTabs').validationEngine("attach", {
			updatePromptsPosition:true,
			promptPosition : "topLeft:0",
			autoPositionUpdate : true,
			focusFirstField: false,
			scroll: false,
			showArrow : false,
			addFailureCssClassToField : 'validengine-border',
			onValidationComplete: function(form, status){
				if(status){
					if($(form).data("callback") != undefined)
						window[$(form).data("callback")](form, $caller_btn);
					else
						modalFormCallBack(form, $caller_btn);
				}else{
					systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
				}
			}
		});
	};

	var submitFormStep = function ($this) {
		validateAll($this);
		return false;
	};

	var modalFormCallBack = function (form) {
		var $form = $(form);
		var $wrapper = $form.closest('.js-modal-flex');
        var $btnSubmit = $wrapper.find('button[data-callback="submitFormStep"]');
        $btnSubmit.prop('disabled', true);

		setTimeout(function() {
			var fdata = $form.serialize();

			$.ajax({
				type: 'POST',
				url: __site_url + 'order/ajax_order_operations/purchase_order',
				data: fdata,
				dataType: 'JSON',
				beforeSend: function(){
                    showLoader($wrapper);
                },
				success: function(resp){
					systemMessages( resp.message, resp.mess_type );

					if(resp.mess_type == 'success'){
						current_status = resp.order_status_alias;
						loadOrderList(true);
						showOrder(resp.order);
						update_status_counter_active(resp.order_status_name, current_status);

						closeFancyBox();
					} else{
						hideLoader($wrapper);
						$btnSubmit.prop('disabled', false);
					}
				}
			}).fail(function(jqXHR, textStatus) {
                systemMessages(textStatus);
                hideLoader($wrapper);
                $btnSubmit.prop('disabled', false);
            });
		}, 300);

		return false;
	};

	var nextFormStep = function() {
		$('.nav-form-items .active').closest('.nav-item').next('.nav-item').find('.nav-link').trigger('click');
	};

	var prevFormStep = function() {
		$('.nav-form-items .active').closest('.nav-item').prev('.nav-item').find('.nav-link').trigger('click');
	};

	var doCalculate = function () {
		var type = '$';
		var subtotal = floatval($('#purchase-order--field-price').val());
		var new_subtotal = 0;
		$("#purchase-order--ordered-items tbody tr.aditional").each(function(){
			if($(this).find(".js-calc-additional-quantity").length){
				var q = parseInt($(this).find(".js-calc-additional-quantity").val(), 10);
				var u = parseFloat($(this).find(".js-calc-additional-unit_price").val(), 10);

				if(q > 0 && u > 0){
					t = q * u;
					new_subtotal += t;
				}else{
					t = 0;
				}
			}
		});

		new_subtotal = new_subtotal + subtotal;

		$('#purchase-order--formfield--subtotal').html( type + ' ' + get_price(new_subtotal, false));
		var discount = normalize_discount($('#purchase-order--formfield--discount').val());
		if(discount > 100){
			discount = 100;
		}

		$('#purchase-order--formfield--discount').val(discount);
		$('#purchase-order--formfield--discount-text').text(discount);
		if (discount > 0) {
			var amountDue = new_subtotal - (new_subtotal * (discount / 100));
		} else {
			var amountDue = new_subtotal;
		}

		purchaseOrderAmount = amountDue;
		$('#purchase-order--formfield--bill-percent').html( type + ' ' + get_price(purchaseOrderAmount, false));
		$('#purchase-order--formfield--amount-due').html( type + ' ' + get_price(amountDue, false));
	};

	var onShowAdditionalItems = function (itemsContainer, formContainer, template) {
		formContainer.html(template).show();
		itemsContainer.hide();
	};

	var onDeleteAdditionalItems = function (button) {
		var self = $(button);
		var $parent_row = self.closest('tr');
		var q = intval($parent_row.find(".js-calc-additional-quantity").val());
		var w = floatval($parent_row.find(".js-calc-additional-weight").val());

		orderProductsWeight -= floatval(q * w);
		$parent_row.remove();
		doCalculate();
	};

	var onDeleteExistentAdditionalItems = function (button) {
		var self = $(button);
		var $parent_row = self.closest('tr');
		var subtotal = floatval($('#purchase-order--field-price').val());
		var q = intval($parent_row.find(".js-calc-additional-quantity").val());
		var u = floatval($parent_row.find(".js-calc-additional-unit_price").val());
		var w = floatval($parent_row.find(".js-calc-additional-weight").val());

		if(q > 0 && u > 0){
			t = q * u;
		} else{
			t = 0;
		}

		orderProductsWeight -= floatval(floatval(q * w));
		$('#purchase-order--field-price').val(floatval(subtotal - t));
		$parent_row.remove();
		doCalculate();
	};

	var onHideAdditionalItems = function (itemsContainer, formContainer) {
		itemsContainer.show();
		formContainer.hide().empty();
	};

	var onAddAdditionalItems = function (template, form, table, callback) {
		var additionalInputs = form.find('[name^="aditional_item"]');
		var isValidated = additionalInputs.toArray().reduce(function(accumulator, formfield) {
			var fieldIsValid = $(formfield).validationEngine("validate");

			return accumulator && fieldIsValid;
		}, true);

		if(!isValidated) {
			return;
		}

		var tableBody = table.find('tbody');
		if(tableBody.find('.aditional').length === 0){
			tableBody.html('');
		}

		var index = uniqid('additional-item_');
		var additionalItem = template;
		var itemUnitPrice = parseFloat(additionalInputs.filter('[name="aditional_item_unit_price"]').val());
		var itemUnitQuantity = parseFloat(additionalInputs.filter('[name="aditional_item_quantity"]').val());
		var content = {
			quantity: itemUnitQuantity,
			price: itemUnitPrice,
			index: index,
			code: additionalInputs.filter('[name="aditional_item_hs_code"]').val(),
			title: additionalInputs.filter('[name="aditional_item_title"]').val(),
			width: additionalInputs.filter('[name="aditional_item_width"]').val(),
			length: additionalInputs.filter('[name="aditional_item_length"]').val(),
			height: additionalInputs.filter('[name="aditional_item_height"]').val(),
			weight: additionalInputs.filter('[name="aditional_item_weight"]').val(),
			country: additionalInputs.filter('[name="aditional_item_country_abr"]').val(),
			priceLabel: get_price(itemUnitPrice, false),
			amountLabel: get_price(itemUnitQuantity * itemUnitPrice, false),
		};

		for (var key in content) {
			if (content.hasOwnProperty(key)) {
				additionalItem = additionalItem.replace(new RegExp('{{' + key + '}}', 'g'), content[key])
			}
		}

		orderProductsWeight += floatval(content.weight * content.quantity);
		tableBody.append($(additionalItem));
		doCalculate();
		callback();
	};

	var addPartialPayment = function (btn) {
		var $this = $(btn);
		var template = $('#purchase-order--formtemplate--partial-payment').text();
		var partialPaymentContainer = $('#purchase-order--formtemplate--partial-payment-container');

		var totalBlocks = partialPaymentContainer.find('.partial-payment').length;
		var partialPaymentBlock = $(template.replace(/{{index}}/g, totalBlocks));

		partialPaymentContainer.append(partialPaymentBlock);
	};

	var deletePartialPayment = function (btn) {
		var $this = $(btn);
		$this.closest('.partial-payment').remove();
	};

	var updateFormStepsNav = function (navCurrentIndex) {
		var navLength = $('.nav-form-items .nav-item').length;
		$('.modal-flex__btns .js-formStepNav').hide();

		if(navCurrentIndex == 0){
			$('.modal-flex__btns .js-nextFormStep').show();
		} else if(navLength == (navCurrentIndex + 1) ){
			$('.modal-flex__btns .js-prevFormStep, .modal-flex__btns .js-submitFormStep').show();
		}else{
			$('.modal-flex__btns .js-prevFormStep, .modal-flex__btns .js-nextFormStep').show();
		}
	};

	var changeTtlPopup = function () {
		var $this = $('.nav-tabs.nav-form-items .nav-link.active');
		var $closeBtn = '<a title="Close" class="pull-right call-function" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?"><span class="ep-icon ep-icon_remove-stroke"></span></a>';

		if($this.closest('.fancybox-skin').find('.fancybox-title a').length){
			$closeBtn = $this.closest('.fancybox-skin').find('.fancybox-title a')[0].outerHTML;
		}

		if($(window).width() < 661){
			$this.closest('.fancybox-skin').find('.fancybox-title').html(
				$this.find('.nav-form-items__name').text() + $closeBtn
			);
		}else{
			$this.closest('.fancybox-skin').find('.fancybox-title').html(
				'<span class="fancybox-ttl">' + ttlText + '</span> ' + $closeBtn
			);
		}
	};

	var validateTab = function (tab, tabContent) {
		if($(tabContent).find('.formError').length){
			tab.classList.add("bg-red")
		} else {
			tab.classList.remove("bg-red");
		}

		return validate_visible();
	};

	var validateAll = function ($btnSubmit) {
		statusValidate = false;
		$('.validateModalTabs').validationEngine("validate", {
			validateNonVisibleFields: true,
			updatePromptsPosition:true,
			promptPosition : "topLeft:0",
			autoPositionUpdate : true,
			focusFirstField: false,
			scroll: false,
			showArrow : false,
			addFailureCssClassToField : 'validengine-border',
			onValidationComplete: function(form, status){
				statusValidate = true;
				var myForm = $('.validateModalTabs');
				if(status){
					if($(form).data("callback") != undefined)
						window[$(form).data("callback")](myForm, $btnSubmit);
					else
						modalFormCallBack(myForm, $btnSubmit);
				}else{
					$('.nav-link').removeClass('required');

					$('.modal-flex__content .tab-pane-submit').each(function(element){
						if($(this).find('.formError').length){
							$('.nav-link[href="#'+$(this).attr('id')+'"]').addClass('required');

							$(this).find('.formError').remove();
						}
					});

					systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
				}
			}
		});
	};

	var onChangeDiscount = function () {
		doCalculate();
	};

	$(function(){
		var isPO = Boolean(intval('<?php echo (int) !empty($order) && $order['order_type'] === 'po'; ?>'));
		var formWrapper = $('#purchase-order--form--wrapper');
		var purchaseOrderWrapper = $('#purchase-order--form--save-wrapper');
		var purchaseOrderForm = $('#purchase-order--form');
		var orderedItemsTable = $('#purchase-order--ordered-items');
		var additionalItemsTable = $('#purchase-order--additional-items');
		var additionalItemsButton = $('#js-add_aditional_row');
		var additionalItemsFormContainer = $('#purchase-order--additional-items-form-container');
		var additionalItemsFormTemplate = $('#purchase-order--formtemplate--add-additional-item');
		var additionalItemTemplate = $('#purchase-order--formtemplate--added-item');
		var purchaseOrderTables = $('#purchase-order--form .main-data-table');
		var discountInput = $('#purchase-order--formfield--discount');
		var notesTextarea = $('#purchase-order--formfield--notes');
		var itemsContainer = $('#purchase-order--items-information-container');
		var invoiceDueDateDatepicker = $('.js-invoice-duedate-datepicker');
		var onOpenAddItemForm = onShowAdditionalItems.bind(
			null,
			itemsContainer,
			additionalItemsFormContainer,
			additionalItemsFormTemplate.text()
		);
		var onCloseAddItemForm = onHideAdditionalItems.bind(
			null,
			itemsContainer,
			additionalItemsFormContainer
		);
		var onAddItem = onAddAdditionalItems.bind(
			null,
			additionalItemTemplate.text(),
			purchaseOrderForm,
			additionalItemsTable,
			onCloseAddItemForm
		);

		var dateToday = new Date();
		dateToday.setDate(dateToday.getDate());

		invoiceDueDateDatepicker.datepicker({
			minDate: dateToday,
			beforeShow: function (input, instance) {
				$('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
			},
		});

		discountInput.on('change', onChangeDiscount);

		$selectCity = $(".select-city");

		initSelectCity($selectCity);

		$("#state_block").on('change', "select#states", function(){
			selectState = this.value;
			$selectCity.empty().trigger("change").prop("disabled", false);

			if(selectState != '' || selectState != 0){
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
			} else{
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
				$selectCity.prop("disabled", true);
			}
			$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
		});

		$("#country").on('change', function(){
			selectCountry($(this), 'select#states');
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

		notesTextarea.textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		$selectCity.data('select2').$container.attr('id', 'select-сity--formfield--tags-container')
			.addClass('validate[required]')
			.setValHookType('selectselectCity');

		$.valHooks.selectselectCity = {
			get: function (el) {
				return $selectCity.val() || [];
			},
			set: function (el, val) {
				$selectCity.val(val);
			}
		};

		if (true === existPurchaseOrder) {
			validateTabInit();
		}

		updateFormStepsNav(0);

		$('.nav-form-items a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab

			var $this = $(e.target);
			var $navCurrent = $this.closest('.nav-item');
			var navCurrentIndex = $navCurrent.index();

			$this.removeClass('required');

			updateFormStepsNav(navCurrentIndex);
			onHideAdditionalItems(itemsContainer, additionalItemsFormContainer);

			if(statusValidate){
				$('.validateModalTabs').validationEngine('validate', {
					updatePromptsPosition:true,
					promptPosition : "topLeft:0",
					autoPositionUpdate : true,
					focusFirstField: false,
					scroll: false,
					showArrow : false,
					addFailureCssClassToField : 'validengine-border'
				});
			}

			changeTtlPopup();
			$.fancybox.update();
		});

		$('#purchase-order--form').on('submit', function(e){
			e.preventDefault;
			return false;
		});

		setTimeout(function(){
			changeTtlPopup();
		}, 100);

		var onOrientationChange = function () {
			normalizeTables();
			setTimeout(normalizeTables, 500);
		};

		var normalizeTables = function() {
			if(purchaseOrderTables.length !== 0){
				if($(window).width() < 768) {
					purchaseOrderTables.addClass('main-data-table--mobile');
				} else {
					purchaseOrderTables.removeClass('main-data-table--mobile');
				}
			}
		};

        var selectedShippingTypeName = $("#js-selected-shipping-type-name");
        var selectShippingTypeTemplate = $("#js-select-shipping-type-template");
        var inputShipmentType = $("#js-input-shipment-type");
        var btnShipmentType = $("#js-btn-shipment-type");

        var selectShippingType = function (modalBody) {
            var checkedInput = modalBody.find('.js-check-radio[name="shipment_type"]:checked');
            if (undefined === checkedInput.val()) {
                return;
            }
            var value = parseInt(checkedInput.val(), 10);
            var name = checkedInput.data("name");
            selectShippingTypeTemplate.find("input").removeAttr("checked").end()
                .find('.js-check-radio[value="' + value + '"]').attr("checked", "checked");
            notesTextarea.val(name + " has been chosen as a shipping method.");
            inputShipmentType.val(value);
            selectedShippingTypeName.text(name);

            btnShipmentType.val();
            hidePromptBtnShipmentType();
        }

        var shippingTypeOpenModal = function (btn) {
            open_result_modal({
                title: "Shipping Methods",
                subTitleCustom: '<div class="info-alert-b bootstrap-dialog-sub-title-custom tal"><i class="ep-icon ep-icon_info-stroke"></i> <span>If the Buyer confirms the Purchase Order (PO), you won’t be able to change it.</span></div>',
                content: selectShippingTypeTemplate.html(),
                type: 'info',
                closable: true,
                classContent: "",
                buttons: [
                    {
                        label: translate_js({ plug: "BootstrapDialog", text: "cancel" }),
                        cssClass: "btn btn-light",
                        action: function (dialog) {
                            dialog.close();
                        },
                    },
                    {
                        label: translate_js({ plug: "BootstrapDialog", text: "confirm" }),
                        cssClass: "btn btn-primary",
                        action: function (dialog) {
                            selectShippingType(dialog.getModalBody());
                            dialog.close();
                        },
                    }
                ]
            });
        }

        var hidePromptBtnShipmentType =  function () {
            var errorBox = btnShipmentType.siblings(".js-btn-shipment-typeformError");
            if(errorBox.length) {
                btnShipmentType.removeClass("validengine-border");
                errorBox.remove();
            }
        };

        btnShipmentType
            .addClass('validate[required]')
			.setValHookType('shipmentTypeBtn');

        $.valHooks.shipmentTypeBtn = {
            get: function () {
                return inputShipmentType.val() || [];
            }
        };

		mobileDataTable(purchaseOrderTables, false);
		normalizeTables();
		doCalculate();
		mix(window, {
			add_aditional_row: onOpenAddItemForm,
			add_aditional_item: onAddItem,
			cancel_aditional_row: onCloseAddItemForm,
			delete_aditional_row: onDeleteAdditionalItems,
			delete_existent_aditional_row: onDeleteExistentAdditionalItems,
			shippingTypeOpenModal: shippingTypeOpenModal
		}, false);

		$(window).on('resizestop', function () {
			changeTtlPopup();
		});
	});
</script>
