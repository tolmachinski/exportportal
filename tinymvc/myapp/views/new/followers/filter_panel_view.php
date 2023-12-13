<form class="validengine" data-callback="search_follower" id="search_followers_form" autocomplete="off">
	<select class="mb-15" name="search_filter" >
		<?php foreach($followers_statuses as $key_status => $status){?>
			<option value="<?php echo $key_status;?>" <?php if($status_select == $key_status) echo 'selected = "selected"';?>><?php echo $status['title'];?></option>
		<?php }?>
	</select>
	
	<input class="validate[required,minSize[1]]" type="text" name="keywords" maxlength="50" value="<?php if(!empty($keywords)) echo $keywords;?>" placeholder="Search for followers"/>
	
	<div class="flex-display">
		<button class="btn btn-light btn-block mt-15 display-n call-function" data-callback="resetSearchForm" type="reset" <?php if(!empty($keywords)) echo 'style="display:inline-block;"';?>>Clear</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Submit</button>
	</div>
</form>