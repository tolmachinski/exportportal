<div class="container-fluid-modal">
	<label class="input-label">By type</label>
	<select class="dt_filter" data-title="Type" name="type">
		<option value="" >All</option>
		<?php foreach($company_types as $company_types_item){?>
		<option value="<?php echo $company_types_item['id_type'];?>"><?php echo $company_types_item['name_type'];?></option>
		<?php }?>
	</select>

	<label class="input-label">By company visibility</label>
	<select class="dt_filter" data-title="Visibility" name="visibility_company">
		<option value="" >All</option>
		<option value="1">Visible</option>
		<option value="2">Invisible</option>
	</select>

	<label class="input-label">Register date</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init dt_filter start_to" type="text" data-title="Add date from" name="start_date" placeholder="From" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init dt_filter start_from" type="text" data-title="Add date to" name="finish_date" placeholder="To" readonly>
		</div>
	</div>

	<label class="input-label">Search by</label>
	<input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
</div>
