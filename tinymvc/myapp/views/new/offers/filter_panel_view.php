<form class="validengine" data-callback="search_offer" id="search_offer_form">
	
	<select class="mb-15" name="search_filter" >
		<option value="">Select filter</option>
		<optgroup label="By status">
			<option value="">All statuses</option>
			<?php foreach($offers_statuses as $key_status => $status){?>
				<option value="<?php echo $key_status;?>" <?php if($status_select == $key_status) echo 'selected = "selected"';?>><?php echo $status['title'];?></option>
			<?php }?>
		</optgroup>
		<optgroup label="Other">
			<option value="offer_number" <?php if(!empty($id_offer)) echo 'selected = "selected"';?>>Offer number</option>
			<option value="expire_soon" <?php if($status_select == 'expire_soon') echo 'selected = "selected"';?>>Expire soon</option>
		</optgroup>
	</select>

	<input class="validate[required,minSize[1]]" type="text" name="keywords" maxlength="50" value="<?php if(!empty($keywords)) echo $keywords;?>" placeholder="Search for offers"/>
	
	<div class="flex-display">
		<button class="btn btn-light btn-block mt-15 display-n call-function" data-callback="resetSearchForm" type="reset" <?php if(!empty($keywords) || !empty($id_offer)) echo 'style="display:inline-block;"';?>>Clear</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Submit</button>
	</div>
</form>