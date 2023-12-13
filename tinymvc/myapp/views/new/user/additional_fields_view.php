<?php if(!empty($simple_fields) || !empty($social_fields)){ ?>
<script>
var save_user_information = function (form){
	var $this = $(form);
	$.ajax({
		url: "<?php echo __SITE_URL;?>user/ajax_preferences_operation/save_right_field",
		type: 'POST',
		dataType: 'JSON',
		data: $this.serialize(),
		beforeSend: function(){
			showLoader('form[data-callback="save_user_information"]', 'default', 'fixed');
		},
		success: function(data){
			hideLoader('form[data-callback="save_user_information"]');
			systemMessages( data.message, data.mess_type );
		}
	});
	return false;
};
</script>
<?php } ?>

<div class="container-center dashboard-container inputs-40">
	<?php if(!empty($simple_fields) || !empty($social_fields)){ ?>
		<form class="validengine relative-b" method="post" data-callback="save_user_information">
		<?php if(!empty($simple_fields)){ ?>
			<div class="dashboard-line">
				<h1 class="dashboard-line__ttl">
					Additional information
					<div class="dashboard-line__ttl-sub">Your additional fields by your group.</div>
				</h1>
			</div>

			<div class="row pb-20">
				<?php foreach($simple_fields as $key => $field_s){?>
					<div class="col-12 col-md-6">
						<label class="input-label"><?php echo $field_s['name_field']?></label>
						<input class="validate[<?php echo createCustomValidationRule($field_s['valid_rule']); ?>]" type="text" placeholder="<?php echo $field_s['sample_field']?>" name="field_<?php echo $field_s['id_right']?>" value="<?php if(isset($fields_values[$field_s['id_right']]))echo $fields_values[$field_s['id_right']];?>"/>
					</div>
				<?php }	?>
			</div>
		<?php } ?>

		<?php if(!empty($social_fields)){ ?>
			<div class="dashboard-line">
				<h1 class="dashboard-line__ttl">
					Social additional information
					<div class="dashboard-line__ttl-sub">Your social additional fields by your group.</div>
				</h1>
			</div>

			<div class="info-alert-b">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span><?php echo translate('user_adidional_fields_description'); ?></span>
			</div>

			<div class="row">
				<?php foreach($social_fields as $key => $field_social){ ?>
					<div class="col-12 col-md-6">
						<?php if(!empty($field_social['icon'])){ ?>
							<label class="input-label">
								<i class="fs-20 ep-icon ep-icon_<?php echo $field_social['icon']?>"></i>
								<?php echo $field_social['name_field']?>
							</label>
						<?php }	?>
						<input class="validate[<?php echo createCustomValidationRule($field_social['valid_rule']); ?>]" type="text" placeholder="<?php echo $field_social['sample_field']?>" name="field_<?php echo $field_social['id_right']?>" value="<?php if(isset($fields_values[$field_social['id_right']]))echo $fields_values[$field_social['id_right']];?>"/>
					</div>
				<?php }	?>
			</div>
		<?php } ?>

		<div class="row">
			<div class="col-12 pt-15">
				<button class="btn btn-primary w-150 pull-right" type="submit">Save</button>
			</div>
		</div>
		</form>
	<?php }else{ ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>You do not have additional fields.</span></div>
	<?php } ?>
</div>
