<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form create-order create-order<?php echo '_'.$type; ?> validateModal">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Country</label>
						<select class="validate[required] country" id="country" name="port_country">
							<?php echo getCountrySelectOptions($port_country, $user_info['country'])?>
						</select>
					</div>
					<div class="col-12 col-md-6" id="state_td">
						<label class="input-label input-label--required">State / Region</label>
						<select name="states" id="states" class="validate[required] states">
							<option value="">Select state / region</option>
							<?php if(isset($states) && !empty($states)){ ?>
								<?php foreach($states as $state){?>
									<option value="<?php echo $state['id'];?>" <?php echo selected($user_info['state'], $state['id']);?>><?php echo $state['state'];?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-6 wr-select2-h35" id="city_td">
						<label class="input-label input-label--required">City</label>
						<select name="port_city" id="port_city" class="validate[required] select-city">
							<option value="">Select country first</option>
							<?php if(isset($city_selected) && !empty($city_selected)){ ?>
								<option value="<?php echo $city_selected['id'];?>" selected>
									<?php echo $city_selected['city'];?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Zip</label>
						<input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php echo $user_info['zip']?>"/>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Address</label>
						<input class="validate[required,maxSize[255]]" maxlength="150" type="text" name="address" value="<?php echo $user_info['address']?>"/>
					</div>
				</div>
			</div>
			<input type="hidden" name="seller" value="<?php echo $id_seller;?>"/>
			<?php if($type == 'one'){ ?>
				<input type="hidden" name="id_basket_item" value="<?php echo $id_basket_item;?>"/>
			<?php }?>
		</div>

		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary confirm-dialog" data-callback="confirmPopupOrders" data-message="Are you sure you want to start the order?" type="submit">Confirm</button>
			</div>
		</div>
	</form>
</div>

<script>

selectState = '<?php echo $user_info['state'];?>';

$(document).ready(function(){
	$(".validateModal input").each(function(){
		$(this).attr("autocomplete", "off");
	});

	var $createOrderForm = $('.create-order');
	<?php if($type == 'one'){ ?>
		var quantity = $('.item-user-basket-b #item-<?php echo $id_basket_item;?>').find('.quantity-val').val();
		$createOrderForm.find('input[name=quantity]').val(quantity);
	<?php }?>

	var location = locationSelect($createOrderForm);
	$createOrderForm.find('input[name=location]').val(location);

	$selectCity = $(".select-city");
	initSelectCity($selectCity);

	$('#request_insurance_note--textcounter').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
    });

	$('select[name="request_insurance"]').on('change', function(){
		var request_insurance = $(this).val();

		$('.js-insurance__field').hide();
		if(request_insurance == 'yes'){
			$('.js-insurance__field').show();
		}

		$.fancybox.update();
	});
});

function locationSelect($thisForm){
	var locationArray = '';

	//country
	var $selectCountry = $thisForm.find('select[name="port_country"]');
	var id_country = $selectCountry.val();
	if(id_country != '')
		locationArray += $selectCountry.children('option[value='+id_country+']').text();

	//state
	if($thisForm.find('select[name="states"]').length){
		var $selectStates = $thisForm.find('select[name="states"]');
		var id_state = $selectStates.val();
		if(id_state != '')
			locationArray += ', '+$selectStates.children('option[value='+id_state+']').text();
	}

	//city
	if($thisForm.find('select[name="port_city"]').length){
		var $selectCity = $thisForm.find('select[name="port_city"]');
		var id_city = $selectCity.val();
		if(id_city != '')
			locationArray += ', '+$selectCity.children('option[value='+id_city+']').text();
	}

	locationArray += ', '+$thisForm.find('input[name="zip"]').val();
	locationArray += ', '+$thisForm.find('textarea[name="address"]').val();

	return locationArray;
}

var confirmPopupOrders = function(obj){
	var $this = $(obj);
	var $form = $this.closest('form');
	var $wrform = $form.closest('.js-modal-flex');
	var seller = $form.find('input[name=seller]').val();

	var id_basket_item = 0;
	if($form.find('input[name="id_basket_item"]').length){
		id_basket_item = $form.find('input[name="id_basket_item"]').val();
	}

	var params = $form.serialize();

	$.ajax({
		url: '<?php echo __SITE_URL;?>basket/ajax_basket_operation/start_order',
		type: 'POST',
		data: params,
		dataType: 'json',
		beforeSend: function(){
			showLoader($wrform);
		},
		success: function(resp){
			if(resp.mess_type == 'success'){
				confirmOrder(resp.company, id_basket_item);
				closeFancyBox();
                open_result_modal({
                    subTitle: resp.message,
					type: resp.mess_type,
					closable: true,
                    buttons: [
						{
							label: translate_js({ plug: 'BootstrapDialog', text: 'view_order' }),
							cssClass: 'btn-primary',
							action: function(){
								location.href = __site_url + 'order/my/order_number/' + resp.id_order;
							}
						},
						{
							label: translate_js({ plug: 'BootstrapDialog', text: 'close' }),
							cssClass: 'btn-light',
							action: function(dialogRef){
								dialogRef.close();
							}
						}
					]
                });
			} else{
				systemMessages(resp.message, resp.mess_type);
				hideLoader($wrform);
			}
		}
	});
}
</script>
