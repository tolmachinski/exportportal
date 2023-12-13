<?php
$my_b2b =  is_my($seller_b2b['id_seller']) && logged_in();
$is_active = true;

$nr_value = 0;
if (!empty($seller_b2b) && is_array($seller_b2b)) {
	foreach($seller_b2b as $item){
		if(!empty($item))
			$nr_value++;
	}
}

if(($nr_value > 1) || $my_b2b){?>
	<ul class="nav nav-tabs nav--borders" role="tablist">
		<?php if(!empty($seller_b2b['about']) && $is_active || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#about-b2b" aria-controls="title" role="tab" data-toggle="tab">About</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['meeting']) || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#meeting-b2b" aria-controls="title" role="tab" data-toggle="tab">Meeting</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['phone']) || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#phone-b2b" aria-controls="title" role="tab" data-toggle="tab">Phone</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['meeting_else']) || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#meeting_else-b2b" aria-controls="title" role="tab" data-toggle="tab">Meeting else</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['purchase_order']) || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#purchase_order-b2b" aria-controls="title" role="tab" data-toggle="tab">Purchase Order</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['special_order']) || $my_b2b){?>
			<li class="nav-item">
				<a class="nav-link <?php echo equals($is_active, true, 'active');?>" href="#special_order-b2b" aria-controls="title" role="tab" data-toggle="tab">Special Order</a>
			</li>
			<?php $is_active = false;?>
		<?php }?>
	</ul>

	<div class="tab-content tab-content--borders">
		<?php
			$is_active = true;
			if(!empty($seller_b2b['about']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="about-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'About'" title="Edit block 'About'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/about"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b" <?php echo addQaUniqueIdentifier("item__b2b-about-description")?>>
				<?php echo $seller_b2b['about'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['meeting']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="meeting-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Meeting'" title="Edit block 'Meeting'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/meeting"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b">
				<?php echo $seller_b2b['meeting'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['phone']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="phone-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Phone'" title="Edit block 'Phone'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/phone"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b">
				<?php echo $seller_b2b['phone'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['meeting_else']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="meeting_else-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Meeting else'" title="Edit block 'Meeting else'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/meeting_else"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b">
				<?php echo $seller_b2b['meeting_else'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['purchase_order']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="purchase_order-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Purchase order'" title="Edit block 'Purchase order'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/purchase_order"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b">
				<?php echo $seller_b2b['purchase_order'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>

		<?php if(!empty($seller_b2b['special_order']) || $my_b2b){?>
		<div role="tabpanel" class="tab-pane fade <?php echo equals($is_active, true, 'show active');?>" id="special_order-b2b">
			<?php if($my_b2b){?>
				<a class="btn btn-default btn-xs fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Special order'" title="Edit block 'Special order'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/special_order"> <i class="ep-icon ep-icon_pencil txt-blue2"></i> Edit</a>
			<?php }?>

			<div class="text-b">
				<?php echo $seller_b2b['special_order'];?>
			</div>
		</div>
		<?php $is_active = false;?>
		<?php }?>
	</div>
<?php }else{?>
	<div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No b2b info.</span></div>
<?php }?>

<?php if($my_b2b){?>
<script>
function callbackEditB2bBlock(resp){
	$('#'+resp.update_block+'-b2b .text-b').html(resp.text_block);
}
</script>
<?php }?>
