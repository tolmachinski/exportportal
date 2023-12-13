<form class="validengine" data-callback="search_bill" id="search_bills_form">

	<select class="mb-15" name="type" >
		<option value="">Select filter</option>
		<optgroup data-status="by_number" label="By number">
			<option value="bill_number" <?php if($active_status == 'bill_number') echo 'selected = "selected"';?>>Bill number</option>
			<?php if(have_right('buy_item')){?>
				<option value="order_number" <?php if($active_status == 'order_number') echo 'selected = "selected"';?>>Order number</option>
			<?php }?>
			<?php if(have_right('feature_item')){?>
				<option value="featured_number" <?php if($active_status == 'featured_number') echo 'selected = "selected"';?>>Feature number</option>
			<?php }?>
			<?php if(have_right('highlight_item')){?>
				<option value="highlight_number" <?php if($active_status == 'highlight_number') echo 'selected = "selected"';?>>Highlight number</option>
			<?php }?>
			<option value="group_number" <?php if($active_status == 'group_number') echo 'selected = "selected"';?>>Group number</option>
			<option value="right_number" <?php if($active_status == 'right_number') echo 'selected = "selected"';?>>Right number</option>
		</optgroup>
		<?php foreach($status_array as $status_key => $status){?>
			<optgroup data-status="<?php echo $status_key;?>" label="Status <?php echo $status['title'];?>">
				<?php foreach($types_array as $type_key => $type){?>
					<option value="<?php echo $type_key;?>"><?php echo $type['title'];?></option>
				<?php }?>
			</optgroup>
		<?php }?>
	</select>

	<input class="validate[required,minSize[3]]" type="text" name="keywords" maxlength="50" placeholder="Search for bills" value="<?php if(isset($id_bill)) echo orderNumber($id_bill); elseif(isset($id_item)) echo orderNumber($id_item);?>"/>

	<div class="flex-display">
		<input type="hidden" name="status" value="<?php if(!empty($id_bill) || !empty($id_item)) echo 'by_number';?>"/>
		<button class="btn btn-light btn-block mt-15 display-n call-function" data-callback="resetSearchForm" type="reset" <?php //if(!empty($keywords)) echo 'style="display:inline-block;"';?>>Clear</button>
		<button class="btn btn-primary btn-block mt-15" type="submit">Submit</button>
	</div>
</form>
