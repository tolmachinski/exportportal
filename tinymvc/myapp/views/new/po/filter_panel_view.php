<form class="validengine" data-callback="search_po" id="search_po_form">

	<select class="mb-15" name="search_filter" >
		<option value="">Select filter</option>
		<optgroup label="By number">
			<option value="po_number" <?php if(!empty($id_po)) echo 'selected = "selected"';?>>Producing Requests number</option>
		</optgroup>
		<optgroup label="By status">
			<option value="">All statuses</option>
			<option value="initiated">New Requests</option>
			<option value="po_processing">In process</option>
			<option value="prototype_confirmed">Prototype confirmed</option>
			<option value="order_initiated">Order initiated</option>
			<option value="declined">Declined</option>
			<option value="archived">Archived</option>
		</optgroup>
	</select>

	<input class="validate[required,minSize[3]]" type="text" name="keywords" maxlength="50" value="<?php if(!empty($id_po)) echo orderNumber($id_po);?>" placeholder="Search for po"/>
	
	<div class="flex-display">
		<button class="btn btn-light btn-block mt-15 display-n call-function" data-callback="resetSearchForm" type="reset" <?php if(!empty($keywords) || !empty($id_po)) echo 'style="display:inline-block;"';?>>Clear</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Submit</button>
	</div>
</form>