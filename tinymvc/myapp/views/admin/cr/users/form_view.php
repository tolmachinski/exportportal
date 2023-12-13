<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-10 vam-table">
        <tbody>
			<tr>
				<td>Country</td>
				<td>
					<select name="domains[]" class="w-100pr validate[required]" multiple>
						<?php foreach($domains as $domain){?>
							<option value="<?php echo $domain['id_domain'];?>" <?php if(isset($user_domains) && array_key_exists($domain['id_domain'], $user_domains)){echo 'selected';}?>>
								<?php echo $domain['country']?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="w-120">First Name</td>
				<td>
					<input class="w-100pr validate[required,maxSize[50],custom[validUserName]]" type="text" name="fname" value="<?php if(isset($user)){echo $user['fname'];}?>" placeholder="First name"/>
				</td>
			</tr>
			<tr>
				<td>Last Name</td>
				<td>
					<input class="w-100pr validate[required,maxSize[50],custom[validUserName]]" type="text" name="lname" value="<?php if(isset($user)){echo $user['lname'];}?>" placeholder="Last name" />
				</td>
			</tr>
			<tr>
				<td>Email</td>
				<td>
					<input class="w-100pr validate[required, custom[noWhitespaces],custom[emailWithWhitespaces], maxSize[100]]" type="text" name="email" value="<?php if(isset($user)){echo $user['email'];}?>" placeholder="Email"/>
				</td>
			</tr>
			<tr>
				<td>Group</td>
				<td>
					<select name="group" class="w-100pr validate[required]" >
						<option value="">Select group of user</option>
						<?php $selected_group = (!empty($user))?$user['user_group']:35;?>
						<?php foreach($groups as $group){?>
								<option value="<?php echo $group['idgroup'];?>" <?php echo selected($selected_group, $group['idgroup']);?>><?php echo $group['gr_name']?></option>
							<?php } ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($user)){?>
			<input type="hidden" name="id_user" value="<?php echo $user['idu'];?>">
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
	$(function(){
		$('select[name="domains[]"]').select2({
			placeholder: "Select country",
			allowClear: true
		});
	});
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL;?>cr_users/ajax_operations/<?php echo ((isset($user)? 'edit' : 'add'))?>_user",
			data: $form.serialize(),
			beforeSend: function(){
				showLoader($form);
			},
			dataType: 'json',
			success: function(data){

				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw();
				}else{
					hideLoader($form);
				}
			}
		});
	}
</script>
