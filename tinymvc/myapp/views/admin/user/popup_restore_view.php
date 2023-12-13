<form method="post" class="validateModal relative">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
			<tbody>
                <tr>
					<td>Email</td>
					<td><input name="email" class="w-100pr validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]" type="text" placeholder="Email" value="<?php echo $user['email']; ?>"></td>
				</tr><tr>
					<td>First Name</td>
					<td><input name="fname" class="w-100pr validate[required,custom[validUserName],minSize[2],maxSize[50]]" type="text" placeholder="First name" value="<?php echo $user['fname']; ?>"></td>
				</tr><tr>
					<td>Last Name</td>
					<td><input name="lname" class="w-100pr validate[required,custom[validUserName],minSize[2],maxSize[50]]" type="text" placeholder="Last name" value="<?php echo $user['lname']; ?>"></td>
				</tr><tr>
                    <td>Status</td>
                    <td>
                        <select class="w-100pr" data-title="Status" name="status" data-type="select" id="statuses">
                            <option value="new">New</option>
                            <option value="pending">Pending</option>
                            <option value="active">Activated</option>
                            <option value="restricted">Restricted</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </td>
                </tr>
                <?php if(isset($companyData) ){ ?>
                <tr>
                    <td><?php echo $companyData['company_name_label']; ?></td>
                    <td><input name="company_name" class="w-100pr validate[required,custom[companyTitle],minSize[3],maxSize[50]]" type="text" placeholder="<?php echo $companyData['company_name_label']; ?>" value="<?php echo $companyData['company_name']; ?>"></td>
                </tr>
                <tr>
                    <td><?php echo $companyData['company_legal_name_label']; ?></td>
                    <td><input name="company_legal_name" class="w-100pr validate[required,custom[companyTitle],minSize[3],maxSize[50]]" type="text" placeholder="<?php echo $companyData['company_legal_name_label']; ?>" value="<?php echo $companyData['company_legal_name']; ?>"></td>
                </tr>
                <?php } ?>
			<tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="user" value="<?php echo $idUser;?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		url: '<?php echo __SITE_URL;?>users/ajax_operations/restore_user',
		type: 'POST',
		data:  $form.serialize(),
		dataType: 'json',
		beforeSend: function(){
			showLoader($form);
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type );
			if(resp.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined){
					data_table.fnDraw(false);
				}
			} else{
				hideLoader($form);
			}
		}
	});
}
</script>
