<div class="container-fluid-modal">
	<label class="input-label">Category</label>
	<select class="dt_filter minfo-form__input2 mb-0" data-title="Category" level="1" name="parent">
		<option data-categories="" data-default="true" value="0">All</option>
		<?php foreach($counter_categories as $category){?>
			<option data-categories="<?php echo $category['category_id'];?>" value="<?php echo $category['category_id'];?>"><?php echo capitalWord($category['name']); ?> (<?php echo $category['counter']?>)</option>
			<?php if(!empty($category['subcats'])){ recursive_ctegories_product($category['subcats'], ''); }?>
		<?php } ?>
	</select>

	<label class="input-label">Creation date</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="From" data-title="Creation date from" name="start_from" placeholder="From" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="To" data-title="Creation date to" name="start_to" readonly>
		</div>
	</div>

	<label class="input-label">Update date</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init update_from dt_filter" id="update_from" data-title="Update date from" name="update_from" type="text" placeholder="From" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init update_to dt_filter" id="update_to" data-title="Update date to" name="update_to" type="text" placeholder="To" readonly>
		</div>
	</div>

	<label class="input-label">Draft expires date</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init dt_filter" id="js-expiration_date" data-title="Draft expires on" name="expiration_date" type="text" placeholder="On" readonly>
		</div>
	</div>

	<div class="row">
		<div class="col-12 col-lg-6">
			<label class="input-label">Highlighted</label>
			<select class="dt_filter" data-title="Highlighted" name="highlight">
				<option value="" data-default="true">All</option>
				<option value="0">No highlighted items</option>
				<option value="1">Highlighted items</option>
			</select>
		</div>
		<div class="col-12 col-lg-6">
			<label class="input-label">Featured</label>
			<select class="js-dt-filter-featured dt_filter" data-title="Featured" name="featured">
				<option value="" data-default="true">All</option>
				<option value="0">No featured items</option>
				<option value="1">Featured items</option>
			</select>
		</div>

		<div class="col-12 col-lg-6">
			<label class="input-label">Visible</label>
			<select class="dt_filter" data-title="Visible" name="visible">
				<option value="" data-default="true">All</option>
				<option value="0">Invisible items</option>
				<option value="1">Visible items</option>
			</select>
		</div>
		<div class="col-12 col-lg-6">
			<label class="input-label">Locked</label>
			<select class="dt_filter" data-title="Locked" name="blocked">
				<option value="" data-default="true">All</option>
				<option value="0">No locked items</option>
				<option value="1">Locked items</option>
			</select>
		</div>
        <div class="col-12 col-lg-6">
			<label class="input-label">Archived status</label>
			<select class="dt_filter" data-title="Archived" name="archived">
				<option value="" data-default="true">All</option>
				<option value="1">Archived</option>
				<option value="0" selected>Not Archived</option>
			</select>
		</div>
	</div>
</div>
