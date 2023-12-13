<div class="container-fluid-modal">
	<label class="input-label">Search</label>
	<input class="dt_filter keywords" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_search-input")?> id="keywords" type="text" placeholder="Keywords" data-title="Search for" name="keywords" maxlength="50">

	<label class="input-label">Created</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_create-from-input")?> id="create_from" type="text" placeholder="From" data-title="Created from" name="create_from" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_create-to-input")?> id="create_to" type="text" placeholder="To" data-title="Created to" name="create_to" readonly>
		</div>
	</div>

	<label class="input-label">Replied</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_replied-from-input")?> id="reply_from" type="text" placeholder="From" data-title="Replied from" name="reply_from" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_replied-to-input")?> id="reply_to" type="text" placeholder="To" data-title="Replied to" name="reply_to" readonly>
		</div>
	</div>

	<label class="input-label">Status</label>
	<select class="dt_filter minfo-form__input2 mb-0" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_status-select")?> data-title="Status" name="status">
		<option data-default="true" value="">All</option>
		<option value="new">New</option>
		<option value="moderated">Moderated</option>
	</select>

	<label class="input-label">Replied</label>
	<select class="dt_filter minfo-form__input2 mb-0" <?php echo addQaUniqueIdentifier("items-questions-my__filter-panel_replied-select")?> data-title="Replied" name="replied">
		<option data-default="true" value="" <?php echo isset($replied) ?: 'selected="selected"';?>>All</option>
		<option data-value-text="Yes" value="yes" <?php echo isset($replied) && 'replied' === $replie ? 'selected="selected"' : '';?>>Yes</option>
		<option data-value-text="No" value="no" <?php echo isset($replied) && 'replied' !== $replie ? 'selected="selected"' : '';?>>No</option>
	</select>
</div>
