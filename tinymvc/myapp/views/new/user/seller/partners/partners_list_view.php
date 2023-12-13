<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<?php if(logged_in()){?>
<script>
	<?php if($company['id_user'] === id_session()){?>
	var	removePartner = function(obj){
		var $this = $(obj);
		var shipper = $this.data('shipper');
		var partner = $this.data('partner');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL . 'shippers/ajax_shippers_operation/remove_partnership';?>',
			data: { shipper : shipper, partner : partner},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.closest('li').fadeOut(function(){
						$(this).remove();
					});
				}
			}
		});
	}
	<?php }?>


	//remove shipper
	remove_shipper_company = function(opener){
		var $this = $(opener);
		$.ajax({
			url: 'shipper/ajax_shipper_operation/remove_shipper_saved',
			type: 'POST',
			dataType: 'JSON',
			data: {company : $this.data('company')},
			success: function (resp) {
				systemMessages(resp.message, resp.mess_type);
				if(resp.mess_type == 'success'){
					$this.data('callback','add_shipper_company').html('<i class="ep-icon ep-icon_favorite-empty"></i><?php echo translate('freight_forwarder_card_actions_save_btn');?>');
				}
			}
		});
	}

	//save shipper
	add_shipper_company = function(opener){
		var $this = $(opener);
		$.ajax({
			url: 'shipper/ajax_shipper_operation/add_shipper_saved',
			type: 'POST',
			dataType: 'JSON',
			data: {company : $this.data('company')},
			success: function (resp) {
				systemMessages(resp.message, resp.mess_type);
				if(resp.mess_type == 'success'){
					$this.data('callback','remove_shipper_company').html('<i class="ep-icon ep-icon_favorite"></i><?php echo translate('freight_forwarder_card_actions_unsave_btn');?>');
				}
			}
		});
	}
</script>
<?php }?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_partners_business_partners_block_title');?></h1>
</div>
<div class="directory-page">
	<?php views()->display('new/directory/list_view'); ?>
</div>

<div class="title-public">
	<h1 class="title-public__txt"><?php echo translate('seller_partners_shipping_partners_block_title');?></h1>
</div>
<?php views()->display('new/shippers/directory/list_view'); ?>
