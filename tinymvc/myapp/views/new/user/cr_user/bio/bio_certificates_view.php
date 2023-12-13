<form class="validengine inputs-40" data-callback="update_certificate">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_certificates_limit;?> certificates.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<button class="btn btn-dark mnw-150 call-function" data-callback="add_certificate"><i class="ep-icon ep-icon_plus-circle"></i> Add</button>
			<button class="btn btn-primary mnw-150" type="submit">Save</button>
		</div>
	</div>
	
	<div id="user_certificates-wr" class="row">
		<?php if(!empty($user_aditional)){?>
			<?php $user_certificates = json_decode($user_aditional['user_certificates'], true);?>
			<?php if(!empty($user_certificates)){?>
				<?php foreach($user_certificates as $user_certificate){?>
				<div class="col-12 col-md-6 user_certificates-item">
					<div class="input-group mb-8">
						<input class="form-control mr-8 validate[required]" type="text" name="user_certificates[]" value="<?php echo $user_certificate;?>" placeholder="Certificate name"/>
						<span class="input-group-btn">
							<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this certificate?" data-callback="delete_certificate"><i class="ep-icon ep-icon_trash-stroke"></i></a>
						</span>
					</div>
				</div>
				<?php }?>
			<?php }?>
		<?php }?>
	</div>
</form>
<script>
    var certificates_limit = intval(<?php echo $cr_certificates_limit;?>);
    var add_certificate = function(btn){
        var $this = $(btn);

        if($('#user_certificates-wr .user_certificates-item').length < certificates_limit){
            var index = uniqid();
            var template = '<div class="col-12 col-md-6 user_certificates-item">\
								<div class="input-group mb-8">\
									<input class="form-control mr-8 validate[required]" type="text" name="user_certificates[]" value="" placeholder="Certificate name"/>\
									<span class="input-group-btn">\
										<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this certificate?" data-callback="delete_certificate"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
									</span>\
								</div>\
							</div>';
            $('#user_certificates-wr').append(template);
            validateReInit();
        }
    }
    var delete_certificate = function(btn){
        var $this = $(btn);
        $this.closest('.user_certificates-item').remove();
    }

    var update_certificate = function(form){
        var $form = $(form);
        var fdata = $form.serialize();
        $.ajax({
            type: 'POST',
            url: 'cr_user/ajax_operations/update_certificate',
            dataType: 'JSON',
            data: fdata,
            beforeSend : function(xhr, opts){},
            success: function(resp){
                systemMessages(resp.message,  resp.mess_type);
            }
        });
    }
</script>