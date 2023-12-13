<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 h-500">

    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr m-auto vam-table">
        <tbody>
			<tr>
				<td class="w-150">Category</td>
				<td>
					<select class="w-100pr validate[required]" name="category" >
						<option value="">Select category</option>
						<?php if(isset($event_categories) && !empty($event_categories)){
							foreach($event_categories as $event_category){ ?>
								<option value="<?php echo $event_category['id_category'];?>"
								<?php if(isset($event)) echo selected($event['id_category'], $event_category['id_category']);?> >
									<?php echo $event_category['title_category'];?>
								</option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Title event</td>
				<td>
					<input class="w-100pr validate[required, maxSize[200]]" type="text" name="title" placeholder="Headline or title of your news" value="<?php if(isset($event)) echo $event['title_event'];?>"/>
					<input type="hidden" name="id" value="<?php if(isset($event)) echo $event['id_event'];?>"/>
				</td>
			</tr>
			<tr>
				<td>Date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control validate[required,custom[dateTimeFormat],dateTimeRange[grp2]]" type="text" name="date_start" id="edit-d-time" placeholder="From" value="<?php if(isset($event)) echo formatDate($event['date_event'], 'm/d/Y H:i:s A');?>" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control validate[required,custom[dateTimeFormat],dateTimeRange[grp2]]" type="text" name="date_end" id="edit-e-time" placeholder="To" value="<?php if(isset($event)) echo formatDate($event['date_end_event'], 'm/d/Y H:i:s A');?>" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Event description</td>
				<td>
					<textarea class="w-100pr validate[required]" name="description" id="edit_news_text_block" placeholder="Write your news here"><?php if(isset($event)) echo $event['description_event'];?></textarea>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr validate[required]" id="edit_event_country" name="port_country">
						<option value="">Select country</option>
						<?php foreach($port_country as $mconutry){ ?>
							<option value='<?php echo $mconutry['id']?>' <?php if(isset($event)) echo selected($event['id_country'], $mconutry['id']); ?>>
								<?php echo $mconutry['country']?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>State</td>
				<td id="event_state">
					<select class="validate[required]" name="states" id="country_states">
						<option value="">Select state or province</option>
						<?php if(isset($event) && ($event['id_state'] > 0)){ ?>
							<?php foreach($states as $state){?>
								<option value="<?php echo $state['id'];?>" <?php if(!empty($event['id_state'])) echo selected($event['id_state'], $state['id']);?>>
									<?php echo $state['state'];?>
								</option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>City</td>
				<td id="event_city" class="wr-select2-h35">
					<select name="port_city" class="validate[required] select-city" id="port_city">
						<option value="">Select country first</option>
						<?php if(isset($city_selected) && !empty($city_selected)){ ?>
							<option value="<?php echo $city_selected['id'];?>" selected>
								<?php echo $city_selected['city'];?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>ZIP</td>
				<td><input class="validate[required,custom[zip_code],maxSize[20]] w-100pr" maxlength="20" placeholder="ZIP" type="text" name="zip" value="<?php if(isset($event)) echo $event['zip_event'];?>"></td>
			</tr>
			<tr>
				<td>Street Address</td>
				<td>
					<input class="w-100pr validate[required, maxSize[200]]" type="text" name="address" placeholder="Headline or title of your news" value="<?php if(isset($event)) echo $event['address_event'];?>"/>
					<input type="hidden" name="id" value="<?php if(isset($event)) echo $event['id_event'];?>"/>
				</td>
			</tr>
			<tr>
				<td>Phone Number</td>
				<td>
					<div class="row">
						<div class="col-xs-3">
							<select class="validate[required] w-100pr" name="phone_code_event" id="phone_code_event">
								<option></option>
								<?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface> $phone_codes */ ?>
								<?php foreach($phone_codes as $phone_code) { ?>
									<option
										value="<?php echo cleanOutput($phone_code->getId()); ?>"
										data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
										data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
										data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
										<?php if ($selected_phone_code && $selected_phone_code->getId() === $phone_code->getId()) { ?>selected<?php } ?>>
										<?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
									</option>
								<?php } ?>
							</select>
						</div>
						<div class="col-xs-9">
							<input class="validate[required,maxSize[20],custom[phoneNumber]] w-100pr" maxlength="20" type="text" name="phone" value="<?php if(isset($event)) echo $event['phone_event'];?>">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>What type of event?</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon tal h-30">
							<input class="vam" class="validate[required]" type="radio" name="type" value="public" <?php if(isset($event)) echo checked($event['type_event'],'public'); else echo 'checked="checked"';?>>
							<span class="input-group__desc">PUBLIC</span>
						</label>
						<label class="input-group-addon tal h-30">
							<input class="vam" class="validate[required]" type="radio" name="type" value="private" <?php echo checked($event['type_event'],'private');?>>
							<span class="input-group__desc">PRIVATE</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Places available</td>
				<td><input class="w-100pr validate[required,maxSize[9],natural]" maxlength="9" type="text" name="available" value="<?php if(isset($event)) echo $event['available_places'];?>"></td>
			</tr>
			<tr>
				<td>Monetize</td>
				<td>
					<div class="input-group input-group--checks mb-5">
						<label class="input-group-addon">
							<input class="vam" name="price" type="radio" value="1" <?php if($event['price_event'] > 0) echo 'checked="checked"';?>>
							<span class="input-group__desc">PRICE</span>
						</label>
						<input class="form-control" type="text" name="price_value" placeholder="20" value="<?php echo $event['price_event'];?>">
						<label class="input-group-addon">USD</label>
					</div>
					<div class="input-group input-group--checks">
						<label class="input-group-addon tal h-30">
							<input class="vam" type="radio" name="price" value="0"  <?php echo checked($event['price_event'],'0');?>>
							<span class="input-group__desc">FREE</span>
						</label>
					</div>
				</td>
			</tr>

		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php if(isset($event)) echo $event['id_event'];?>"/>
		<input name="current_url" class="pull-right" type="hidden" value="<?php echo $curl;?>">
		<button class="pull-right btn btn-default" name="edit_event" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js"></script>
<?php if(__SITE_LANG != 'en'){?>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/jquery-ui-1-12-1-custom/i18n/datepicker-<?php echo __SITE_LANG; ?>.js"></script>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/jquery-timepicker-addon-1-6-3/js/i18n/jquery-ui-timepicker-<?php echo __SITE_LANG; ?>.js"></script>
<?php }?>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>

<script type="text/javascript">
	var $selectCity, selectState, $selectCcodePhone;
	$(document).ready(function(){
		$selectCity = $(".select-city");
		initSelectCity($selectCity);

		$('body').on('change', "select#country_states", function(){
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

		$('body').on('change', "#edit_event_country", function(){
			selectCountry($(this), 'select#country_states');
			selectState = 0;
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

		$selectCcodePhone = $('select#phone_code_event').select2({
			theme: "default ep-select2-h30",
			templateResult: formatCcode,
			width: '100%',
			dropdownAutoWidth : true
		});

		<?php if(!empty($event['id_state'])){?>
			selectState = <?php echo $event['id_state'];?>;
		<?php }?>

		tinymce.init({
			selector:'#edit_news_text_block',
			menubar: false,
			statusbar : false,
			height : 140,
			plugins: ["autolink lists link textcolor"],
			dialog_type : "modal",
			toolbar: "bold italic underline forecolor backcolor link | numlist bullist ",
			resize: false
		});

		$('#edit-d-time, #edit-e-time').datetimepicker({
			timeFormat: "hh:mm:00 TT",
			minDate: 0,
			millisec_slider: false,
			numberOfMonths: 1
		});
	});

	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>events/ajax_administration/edit_event',
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
