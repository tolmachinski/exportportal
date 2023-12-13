<form class="validateModal relative-b">
	<div class="wr-form-content w-1000 h-400">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
	   <tbody>
		   <tr>
			   <td class="w-130">Company Name</td>
			   <td>
				   <input class="w-100pr validate[required]" type="text" name="name" value="<?php if(isset($company)) echo $company['name']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Personal link</td>
			   <td>
<!--			   validate[required]-->
				   <input class="w-100pr " type="text" name="index_name" id="index_name" value="<?php echo $company['index_name']; ?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Type</td>
			   <td>
				   <select class="w-100pr validate[required]" name="type">
					   <?php foreach ($types as $type){ ?>
						   <option value="<?php echo $type['id_type']?>" <?php echo selected($type['id_type'],$company['type']);?> ><?php echo $type['name_type']?></option>
					   <?php } ?>
				   </select>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Industry</td>
			   <td>
				<div class="row">
					<div class="col-xs-6">
						<label>All industries</label>
						<ul id="industry-list" class="multiple-list-select h-250">
							<?php if(!empty($industries)){
								$array_industries_selected = $company['industry'];?>
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
						<ul id="industry-list-selected" class="multiple-list-select h-250" name="category[]">
							<?php if(!empty($industries_selected)){?>
								<?php foreach ($industries_selected as $industry) {?>
								<li>
									<?php echo $industry['name']?>
									<input type="hidden" value="<?php echo $industry['category_id']?>" name="industriesSelected[]">
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
			   <td class="w-130">Category</td>
			   <td>
			   		<div class="row">
			   			<div class="col-xs-6 pt-10">
							<label>All categories</label>
							<ul id="category-list" class="multiple-list-select__subtree h-250">
								<?php if(!empty($industries_selected)){?>
									<?php foreach ($industries_selected as $industry) {?>
										<li class="group-b" data-value="<?php echo $industry['category_id']?>">
											<div class="ttl-b"><?php echo $industry['name']?>
												<i class="ep-icon ep-icon_arrows-right"></i>
											</div>
											<ul>
										<?php if(!empty($categories)){
												$no_categories = 0;
												$array_categories_selected = $company['category']; ?>
											<?php foreach ($categories[$industry['category_id']] as $category) {
													$no_categories++;?>
													<li data-value="<?php echo $category['category_id']?>"  <?php if(in_array($category['category_id'], $array_categories_selected)){?>style="display: none;"<?php }?>>
														<span><?php echo $category['name']?></span> <i class="ep-icon ep-icon_arrows-right"></i>
													</li>
											<?php } ?>

											<?php if(!$no_categories){ ?>
												<li data-value="<?php echo $industry['category_id']?>"  <?php if(in_array($industry['category_id'], $array_categories_selected)){?>style="display: none;"<?php }?>>
													<span><?php echo $industry['name']?></span> <i class="ep-icon ep-icon_arrows-right"></i>
												</li>
											<?php } ?>
										<?php }else{ ?>
										<?php $array_categories_selected = $company['category']; ?>
											<li data-value="<?php echo $industry['category_id']?>" <?php if(in_array($industry['category_id'], $array_categories_selected)){?>style="display: none;"<?php }?>>
												<span><?php echo $industry['name']?></span> <i class="ep-icon ep-icon_arrows-right"></i>
											</li>
										<?php }?>
										</ul></li>
									<?php }?>
								<?php }?>
							</ul>
						</div>

						<div class="col-xs-6 pt-10">
							<label>Selected categories</label>
							<ul id="category-list-selected" class="multiple-list-select__subtree h-250" name="subcategory[]">
								<?php if(!empty($industries_selected)){?>
									<?php foreach ($industries_selected as $industry) {?>
										<li class="group-b" data-value="<?php echo $industry['category_id']?>">
											<div class="ttl-b"><?php echo $industry['name']?>
												<i class="ep-icon ep-icon_remove"></i>
											</div>
											<ul>
												<?php if(!empty($categories_selected)){ ?>
													<?php foreach($categories_selected[$industry['category_id']] as $category) {?>
														<li>
															<?php echo $category['name']?>
															<input type="hidden" value="<?php echo $category['category_id']?>" name="categoriesSelected[]">
															<i class="ep-icon ep-icon_remove"></i>
														</li>
													<?php } ?>
												<?php }?>
											</ul>
										</li>
									<?php }?>
								<?php }?>
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
					   <?php foreach($port_country as $mconutry){ ?>
							<option value='<?php echo $mconutry['id']?>' <?php if(isset($company)) echo selected($company['country'], $mconutry['id']); ?>>
								<?php echo $mconutry['country']?>
							</option>
					   <?php } ?>
				   </select>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">State</td>
			   <td id="state_td">
				   <span>
				   <select name="states" class="w-100pr validate[required]" id="states">
						<option value="">Select state or province</option>
						<?php if(isset($states) && !empty($states)){ ?>
							<?php foreach($states as $state){?>
							   <option value="<?php echo $state['id'];?>" <?php if(!empty($company['states'])) echo selected($company['states'], $state['id']);?>>
								   <?php echo $state['state'];?>
							   </option>
							<?php } ?>
						<?php } ?>
				   </select>
				   </span>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">City</td>
			   <td>
				    <div id="city_td">
				    <span>
					   <select class="w-100pr validate[required] select-city" id="port_city" name="port_city">
						   <option value="">Select country first</option>
				    		<?php if(isset($city_selected) && !empty($city_selected)){ ?>
								<option value="<?php echo $city_selected['id'];?>" selected>
										<?php echo $city_selected['city'];?>
								</option>
				    		<?php } ?>
					   </select>
				    </span>
				    </div>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Address</td>
			   <td>
				   <input class="w-100pr validate[required]" type="text" name="address" value="<?php if(isset($company)) echo $company['address']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Map coordinates</td>
			   <td>
				   <p class="map-coord" >Please indicate the location of this office on the map, or write the coordinates manually.</p>

				   <div class="pull-left">
					   <span >Latitude:</span> <br/>
<!--validate[required]-->
					   <input class="w-200 " type="text" name="lat" id="lat" value="<?php if(isset($company)) echo $company['lat']?>"/>

				   </div>
				   <div class="pull-right mb-10">
					   <span >Longitude:</span> <br/>
<!--					   validate[required]-->
					   <input class="w-200 " type="text" name="long" id="long" value="<?php if(isset($company)) echo $company['long']?>"/> <br/>

				   </div>

				   <div class="w-100pr h-250" id="google-map-b"></div>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">ZIP</td>
			   <td>
<!--			   validate[required]-->
				   <input class="w-100pr validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php if(isset($company)) echo $company['zip']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Telephone</td>
			   <td>
				   <input class="w-100pr validate[required]" type="text" name="phone" value="<?php if(isset($company)) echo $company['phone']?>" placeholder=""/>

			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Fax</td>
			   <td>
				  <input class="w-100pr" type="text" name="fax" value="<?php if(isset($company)) echo $company['fax']?>" placeholder=""/>

			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Email</td>
			   <td>
				  <input class="w-100pr validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" type="text" name="email" value="<?php if(isset($company)) echo $company['email']?>" placeholder=""/>

			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Number of employees</td>
			   <td>
				  <input class="w-100pr" type="text" name="employees" value="<?php if(isset($company) && $company['employees']>0) echo $company['employees']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Annual Revenue</td>
			   <td>
				  <input class="w-100pr" type="text" name="revenue" value="<?php if(isset($company)) echo $company['revenue']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Website</td>
			   <td>
				 <input class="w-100pr validate[custom[url]]" type="text" name="website" value="<?php if(isset($company)) echo $company['website']?>" placeholder=""/>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Logo</td>
			   <td>
				   <?php if(!empty($company['logo_company'])) {?>
				   <div class="img-list-b pull-left mr-5 mb-5 relative-b">
					   <a class="delete_logo ep-icon ep-icon_remove txt-red absolute-b pos-r0 m-0 bg-white confirm-dialog" data-callback="delete_logo"
						  title="Delete logo" data-id="<?php echo $company['company_id'] ?>" data-message="Are you sure want to delete company photo?"></a>
					   <img src="<?php echo getDisplayImageLink(array('{ID}' => $company['company_id'], '{FILE_NAME}' =>  $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?> ">

				   </div>
				   <?php } else { ?>
				   There is no logo
				   <?php } ?>
			   </td>
		   </tr>
		   <tr>
			   <td class="w-130">Description</td>
			   <td>
				 <textarea class="w-100pr h-100" name="description"><?php if(isset($company)) echo $company['description']?></textarea>
			   </td>
		   </tr>
		   <tr>
				<td class="w-130">Video (youtube or vimeo link)</td>
				<td>
					<input class="validate[maxSize[200]]" type="text" name="video" placeholder="URL of company video" value="<?php if(isset($company)) echo $company['video']?>"/>
				</td>
		   </tr>
	   </tbody>
	   </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" value="<?php if(isset($company)) echo $company['company_id']?>" name="id_company">
		<button class="pull-right btn btn-default" type="submit" name="edit_company"><span class="ep-icon ep-icon_ok"></span> Edit Company</button>
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function(){
		selectState = <?php echo $company['states'];?>;
		$selectCity = $(".select-city");
		initSelectCity($selectCity);

		<?php if(!empty($industries_selected)){?>
		categorySelectActualization();
		<?php }?>
	});

	init();

function modalFormCallBack(form, data_table){
	var $form = $(form);

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>directory/ajax_company_administration/edit',
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

function categoryActualization(idIndustries){
	$.ajax({
		type: "POST",
		url: "directory/ajax_company_category",
		dataType: "json",
		data: { industries: idIndustries},
		success: function(data) { //alert(data);
			if(data.mess_type == 'success'){
				$('#category-list').append(data.categories);
			}else{
				systemMessages( data.message, 'message-'+data.mess_type );
			}
		}
	});
}

function categorySelectActualization(){
	//category group li ALL
	$('#category-list > li').each(function(){
		//category group li ALL
		var $groupLi = $(this);
		//category group ul ALL
		var $groupUl = $(this).find('ul');

		//if not exist visible category li ALL
		if(!$groupUl.find('li:visible').length){
			//hide category group li SELECTED
			$groupLi.hide();
		}
	});

	//category group li SELECTED
	$('#category-list-selected > li').each(function(){
		//category group li SELECTED
		var $groupLi = $(this);
		//category group ul SELECTED
		var $groupUl = $(this).find('ul');

		//if not exist category li SELECTED
		if(!$groupUl.find('li').length){
			//hide category group li SELECTED
			$groupLi.hide();
		}
	});
}

</script>
