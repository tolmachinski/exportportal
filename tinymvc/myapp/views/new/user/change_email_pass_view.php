<div class="container-center dashboard-container inputs-40">
	<div class="row">
		<div class="col-12">
            <div class="info-alert-b">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <span><?php echo translate('user_change_email_pass_description'); ?></span>
            </div>
        </div>
		<div class="col-12 col-md-6">
			<form method="post" class="validengine relative-b" data-callback="change_email">
				<div class="dashboard-line">
					<h1 class="dashboard-line__ttl">
						Change Email
						<div class="dashboard-line__ttl-sub txt-normal txt-ws--normal">If you want to change your email, please input your current password to make the changes.</div>
					</h1>
				</div>

				<?php if(!empty($notification_email_change)){?>
				<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>You sent query for change email to <strong><?php echo $notification_email_change['email'];?></strong>. Please verify and confirm this email.</span></div>
				<?php }?>

				<label class="input-label input-label--required">New Email</label>
				<input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]" type="text" name="email_new" placeholder="New Email" />

				<label class="input-label input-label--required">Current Password</label>
				<span class="view-password">
					<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
					<input class="validate[required]" type="password" name="pwd_current" placeholder="Current Password"/>
				</span>

				<button class="btn btn-primary mt-15 mnw-150 pull-right" type="submit">Confirm email</button>
			</form>
		</div>
		<div class="col-12 col-md-6">
			<form method="post" class="validengine relative-b" data-callback="change_passwords">
				<div class="dashboard-line">
					<h1 class="dashboard-line__ttl">
						Change Password
						<div class="dashboard-line__ttl-sub txt-normal txt-ws--normal">If you want to change your password, please input your old and new password to make changes.</div>
					</h1>
				</div>

				<?php if(!empty($notification_password_change)){?>
				<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>You sent query for change password to <strong><?php echo $notification_password_change['email'];?></strong>. Please verify and confirm this email.</span></div>
				<?php }?>

				<div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">Current Password</label>
						<span class="view-password">
							<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
							<input class="validate[required]" type="password" name="pwd_current" placeholder="Current Password"/>
						</span>
                    </div>
					<div class="col-tn-12 col-6">
						<label class="input-label input-label--required">New Password</label>
						<span class="view-password">
							<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
							<input class="validate[required,minSize[6],maxSize[30]]" type="password" name="pwd_new" id="js-password-new" placeholder="New Password"/>
						</span>
						<label class="input-label input-label--required">Confirm New Password</label>
						<span class="view-password">
							<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
							<input class="validate[required,equals[js-password-new]]" type="password" name="pwd_new_confirm" placeholder="Confirm New Password"/>
						</span>
					</div>
					<div class="col-tn-12 col-6">
                        <label class="input-label">&nbsp;</label>
						<div class="pass-strength-popup--full">
							<?php
								tmvc::instance()->controller->view->display('new/register/password_security_view');
							?>
						</div>
					</div>
				</div>

				<button class="btn btn-primary mt-15 mnw-150 pull-right" type="submit">Confirm password</button>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	var change_email = function (form){
		var $this = $(form);
		$.ajax({
			url: __current_sub_domain_url + "user/ajax_preferences_operation/change_email",
			type: 'POST',
			dataType: 'JSON',
			data: $this.serialize(),
			beforeSend: function(){
				showLoader($this);
			},
			success: function(resp){
				hideLoader($this);
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					$this[0].reset();
				}
			}
		});
		return false;
	}

	var change_passwords = function (form){
		var $this = $(form);
		$.ajax({
			url: __current_sub_domain_url + "user/ajax_preferences_operation/change_password",
			type: 'POST',
			dataType: 'JSON',
			data: $this.serialize(),
			beforeSend: function(){
				showLoader($this);
			},
			success: function(resp){
				if(resp.mess_type == 'success'){
					$this[0].reset();
				}
				hideLoader($this);
				systemMessages( resp.message, resp.mess_type );
			}
		});
		return false;
	}
</script>
