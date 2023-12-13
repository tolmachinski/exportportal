<form class="validengine inputs-40" data-callback="update_contacts">
	<div class="row">
		<div class="col-12 col-lg-6 mb-30">
			<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Add Maximum <?php echo $cr_contacts_limit;?> contacts.</span></div>
		</div>
		<div class="col-12 col-lg-6 mb-30">
			<button class="btn btn-dark mnw-150 call-function" data-callback="add_contacts"><i class="ep-icon ep-icon_plus-circle"></i> Add</button>
			<button class="btn btn-primary mnw-150" type="submit">Save</button>
		</div>
	</div>
	
	<div id="user_contacts-wr" class="row">
	<?php if(!empty($user_aditional)){?>
		<?php $user_contacts = json_decode($user_aditional['user_contacts'], true);?>
		<?php if(!empty($user_contacts)){?>
			<?php foreach($user_contacts as $contact_key => $contact_info){?>
			<div class="col-12 col-md-6 user_contacts-item">
				<div class="input-group mb-8">
					<input class="form-control mr-8" type="text" name="contacts[<?php echo $contact_key;?>][name]" value="<?php echo $contact_info['name'];?>" placeholder="Name (Ex: Skype or Viber or Email)">
					<input class="form-control mr-8" type="text" name="contacts[<?php echo $contact_key;?>][value]" value="<?php echo $contact_info['value'];?>" placeholder="Value (Ex: mySkype or myViber or my@email.com)">
					<span class="input-group-btn">
						<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="delete_contact"><i class="ep-icon ep-icon_trash-stroke"></i></a>
					</span>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	</div>
</form>
<script>
    var contacts_limit = intval(<?php echo $cr_contacts_limit;?>);
    var add_contacts = function(btn){
        var $this = $(btn);

        if ($('#user_contacts-wr .user_contacts-item').length < contacts_limit) {
            var index = uniqid();
            var template = '<div class="col-12 col-md-6 user_contacts-item">\
							<div class="input-group mb-8">\
                                <input class="form-control mr-8" type="text" name="contacts[' + index + '][name]" placeholder="Name (Ex: Skype or Viber or Email)">\
                                <input class="form-control mr-8" type="text" name="contacts[' + index + '][value]" placeholder="Value (Ex: mySkype or myViber or my@email.com)">\
                                <span class="input-group-btn">\
                                    <a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this contact?" data-callback="delete_contact"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
                                </span>\
                            </div>\
						</div>';
            $('#user_contacts-wr').append(template);
            validateReInit();
        }
    }
	
    var delete_contact = function(btn){
        var $this = $(btn);
        $this.closest('.user_contacts-item').remove();
    }

    var update_contacts = function(form){
        var $form = $(form);
        var fdata = $form.serialize();
        $.ajax({
            type: 'POST',
            url: 'cr_user/ajax_operations/update_contacts',
            dataType: 'JSON',
            data: fdata,
            beforeSend : function(xhr, opts){},
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);
            }
        });
    }
</script>