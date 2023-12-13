<form class="validengine" data-callback="noopFilterCallback" id="order-samples--form">
	<select class="mb-15 js-types-list js-filter-entry" name="type-filter">
		<option value="">Select filter</option>
		<optgroup label="By number">
			<option value="order" <?php if (!empty($filters['order'])) { ?>selected<?php } ?>>
				Sample order
			</option>
		</optgroup>
		<optgroup label="Status">
			<?php foreach ($statuses as $status) { ?>
				<option value="<?php echo cleanOutput($status['alias']); ?>"
					<?php if (empty($filters['order']) && !empty($filters['status']) && (int) $filters['status'] === (int) $status['id']) { ?>selected<?php } ?>>
					<?php echo cleanOutput($status['name']); ?>
				</option>
			<?php } ?>
		</optgroup>
	</select>

	<?php if (have_right('assign_sample_order')) { ?>
		<select class="mb-15 js-assigned-status js-filter-entry" name="assigned">
			<option value="">All orders</option>
			<option value="1">Assigned to buyer</option>
			<option value="0">Not assigned to buyer</option>
		</select>
	<?php } ?>

	<input type="text"
		name="keywords"
		class="validate[minSize[3],maxSize[250]] js-keywords js-filter-entry"
		value="<?php echo !empty($filters['order']) ? cleanOutput(orderNumber($filters['order'])) : null; ?>"
		maxlength="50"
		placeholder="Search for sample order">

	<div class="flex-display">
		<button type="reset"
			class="btn btn-light btn-block mt-15 display-n js-filters-reset"
			<?php if (!empty($filters['order']) || !empty($filters['status'])) { ?>style="display:inline-block;"<?php } ?>>
			Clear
		</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Filter</button>
	</div>
</form>
