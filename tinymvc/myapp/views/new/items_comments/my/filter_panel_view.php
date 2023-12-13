<div class="container-fluid-modal">
	<label class="input-label">Search</label>
	<input class="dt_filter keywords" id="keywords" type="text" placeholder="Keywords" data-title="Search for" name="keywords" maxlength="50">

    <?php if ($filterByItem) {?>
        <input class="dt_filter" type="hidden" data-title="Item number" name="item" data-value-text="<?php echo orderNumber($filterByItem);?>" value="<?php echo $filterByItem;?>">
    <?php }?>

	<label class="input-label">Created</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" id="create_from" type="text" placeholder="From" data-title="Created from" name="create_from" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" id="crate_to" type="text" placeholder="To" data-title="Created to" name="crate_to" readonly>
		</div>
	</div>

	<label class="input-label">Status</label>
	<select class="dt_filter minfo-form__input2 mb-0" data-title="Status" name="status">
		<option data-default="true" value="">All</option>
		<option value="new">New</option>
		<option value="moderated">Moderated</option>
	</select>

	<label class="input-label">Has reply</label>
	<select class="dt_filter minfo-form__input2 mb-0" data-title="Contains reply" name="has_reply">
		<option data-default="true" value="">All</option>
		<option value="1">Yes</option>
		<option value="0">No</option>
	</select>
</div>
