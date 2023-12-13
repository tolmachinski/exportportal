<div class="popover popover-password" role="tooltip" style="display:none;">
	<div class="pass-strength-popup">
		<div class="pb-15">
			<?php app()->view->display('new/register/password_security_view'); ?>
		</div>

		<div class="pass-strength-popup__progress"></div>
		<div class="pass-strength-popup__txt">
			<strong><?php echo translate('form_label_password_strength');?></strong>
			<span class="pass-strength-popup__verdict"></span>
		</div>
		<div class="pass-strength-popup__errors mt-10"></div>
	</div>
</div>
