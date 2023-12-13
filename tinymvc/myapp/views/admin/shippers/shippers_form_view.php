<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>
<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-1000 mh-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
        <tbody>
            <tr>
                <td class="w-100">Company Name</td>
                <td>
                    <input type="text" name="co_name" value="<?php echo ((isset($shipper['co_name']) ? $shipper['co_name'] : '')); ?>"  class="w-100pr validate[required,maxSize[255]]"/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Number of Office Locations</td>
                <td>
                    <input class="w-100pr validate[required,custom[natural]]" name="company_offices_number" value="<?php echo $shipper['offices_number'];?>" type="text">
                </td>
            </tr>
            <tr>
                <td class="w-100">Industry</td>
                <td>
                    <div class="row">
						<div class="col-xs-6">
							<label>All industries</label>
							<ul id="industry-list" class="multiple-list-select h-250">
								<?php if(!empty($industries)){
									$array_industries_selected = $shipper['industry'];?>
									<?php foreach ($industries as $industry){?>
									<li data-value="<?php echo $industry['category_id']?>" <?php if(in_array($industry['category_id'], $array_industries_selected)){?>style="display: none;"<?php }?>>
										<span><?php echo $industry['name']?></span> <i class="ep-icon ep-icon_arrows-right move-to-selected"></i>
									</li>
									<?php }?>
								<?php }else{ ?>
									<li>No industries</li>
								<?php }?>
							</ul>
						</div>

						<div class="col-xs-6">
							<label>Selected industries</label>
							<ul id="industry-list-selected" class="multiple-list-select h-250">
								<?php if(!empty($industries_selected)){?>
									<?php foreach ($industries_selected as $industry) {?>
									<li>
										<?php echo $industry['name']?>
										<input type="hidden" value="<?php echo $industry['category_id']?>" name="industries[]">
										<i class="ep-icon ep-icon_remove"></i>
									</li>
									<?php } ?>
								<?php } ?>
							</ul>
						</div>
					</div>
                </td>
            </tr>
		   <tr>
			   <td class="w-130">Country</td>
			   <td>
				   <select id="country" class="validate[required] w-100pr" name="country">
					   <option value="" >Select Country</option>
					   <?php foreach($countries as $mconutry){ ?>
						   <option value='<?php echo $mconutry['id']?>' <?php if(isset($shipper)) echo selected($shipper['id_country'], $mconutry['id']); ?>>
						   		<?php echo $mconutry['country']?>
						   </option>
					   <?php } ?>
				   </select>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">State</td>
			   <td id="state_td">
				   <select name="states" class="w-100pr" id="states">
					   <option value="">Select state or province</option>
				   		<?php if(isset($states) && !empty($states)){ ?>
						   <?php foreach($states as $state){?>
							   <option value="<?php echo $state['id'];?>" <?php if(!empty($shipper['id_state'])) echo selected($shipper['id_state'], $state['id']);?>>
								   <?php echo $state['state'];?>
							   </option>
						   <?php } ?>
					   <?php }?>
				   </select>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">City</td>
			   <td id="city_td" class="wr-select2-h35">
				    <select name="port_city" class="w-100pr validate[required] select-city" id="port_city">
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
                <td class="w-100">Address</td>
                <td>
                    <input class="w-100pr validate[required,minSize[2],maxSize[255]]" type="text" name="address" value="<?php if(isset($shipper)) echo $shipper['address']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">ZIP</td>
                <td>
                    <input class="w-100pr validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php if(isset($shipper)) echo $shipper['zip']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Annual full container load volume (TEU's)</td>
                <td>
                    <input class="w-100pr validate[required, custom[natural]]" type="text" name="company_teu" value="<?php if(isset($shipper)) echo $shipper['co_teu']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Government tax ID number</td>
                <td>
                    <input class="w-100pr" type="text" name="company_tax_id" value="<?php if(isset($shipper)) echo $shipper['tax_id']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">DUNS number</td>
                <td>
                    <input class="w-100pr" type="text" name="company_duns" value="<?php if(isset($shipper)) echo $shipper['co_duns']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Website</td>
                <td>
                    <input class="w-100pr validate[custom[url],maxSize[100]]" type="text" name="website" value="<?php if(isset($shipper)) echo $shipper['co_website']?>" placeholder=""/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Phone</td>
                <td>
                    <input class="w-100pr validate[required,minSize[12],maxSize[25]]" type="text" name="phone" value="<?php if(isset($shipper)) echo $shipper['phone']?>" placeholder="">
                </td>
            </tr>
            <tr>
                <td class="w-100">Fax</td>
                <td>
                    <input class="w-100pr validate[minSize[12],maxSize[25]]" type="text" name="fax" value="<?php if(isset($shipper)) echo $shipper['fax']?>" placeholder="">
                </td>
            </tr>
            <tr>
                <td class="w-100">Description</td>
                <td>
                    <textarea name="description" class="w-100pr h-150 mb-0 validate[maxSize[1000]]"><?php echo $shipper['description'];?></textarea>
					<p class="fs-10">The recommended length of the text is about 200-300 symbols.</p>
                </td>
            </tr>
            <tr>
                <td class="w-100">Video (input the iframe code of video)</td>
                <td>
                    <textarea name="video" placeholder="Video" class="w-100pr h-150 validate[maxSize[500],custom[iframe]]"><?php echo $shipper['video'];?></textarea>
                </td>
            </tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id_shipper" value="<?php echo $shipper['id']; ?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	$(document).ready(function () {
		$selectCity = $(".select-city");
		initSelectCity($selectCity);

		<?php if(!empty($shipper['id_state'])){?>
			selectState = <?php echo $shipper['id_state'];?>;
		<?php }?>
	});

	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>shippers/ajax_shippers_operation/edit_shipper',
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
