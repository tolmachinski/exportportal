<form class="validengine" data-callback="search_order" id="search_order_form">
	<select class="mb-15" name="search_filter">
		<option value="">Select filter</option>
		<optgroup label="By number">
			<option value="order_number" <?php if(!empty($id_order)) echo 'selected = "selected"';?>>Order number</option>
		</optgroup>
		<optgroup label="Processing">
			<?php foreach($statuses_process as $status_process){?>
				<option value="<?php echo $status_process['alias'];?>"><?php echo $status_process['status'];?></option>
			<?php }?>
		</optgroup>
		<optgroup label="Finished">
			<?php foreach($statuses_finished as $status_finished){?>
				<option value="<?php echo $status_finished['alias'];?>"><?php echo $status_finished['status'];?></option>
			<?php }?>
		</optgroup>
		<!-- <optgroup label="Archived">
			<option value="archived">Archived</option>
		</optgroup> -->
	</select>
	
	<input class="validate[required,minSize[3]]" type="text" name="keywords" maxlength="50" value="<?php if(!empty($id_order)) echo orderNumber($id_order);?>" placeholder="Search for order"/>
	
	<div class="flex-display">
		<button class="btn btn-light btn-block mt-15 display-n call-function" data-callback="resetSearchForm" type="reset" <?php if(!empty($id_order)) echo 'style="display:inline-block;"';?>>Clear</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Submit</button>
	</div>
</form>