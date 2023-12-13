<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table table-striped table-fixed vam-table">
        <tbody>
			<tr>
				<td>First Name</td>
				<td>
					<input class="w-100pr validate[required,maxSize[50],custom[validUserName]]" type="text" name="fname" value="<?php echo $user['fname']?>" placeholder="First name"/>
				</td>
			</tr>
			<tr>
				<td>Last Name</td>
				<td>
					<input class="w-100pr validate[required,maxSize[50],custom[validUserName]]" type="text" name="lname" value="<?php echo $user['lname']?>" placeholder="Last name" />
				</td>
			</tr>
			<tr>
				<td>Email</td>
				<td>
					<input class="w-100pr validate[required, custom[noWhitespaces],custom[emailWithWhitespaces], maxSize[100]]" type="text" name="email" value="<?php echo $user['email']?>" placeholder="Email"/>
				</td>
			</tr>
			<?php if(!isset($user)){?>
				<tr>
					<td>Password</td>
					<td>
						<input class="w-100pr validate[required,maxSize[20],minSize[6]]" type="password" name="pwd" id="pwd" placeholder="Password"/>
					</td>
				</tr>
				<tr>
					<td>Password confirm</td>
					<td>
						<input class="w-100pr validate[required,equals[pwd],maxSize[20],minSize[6]]" type="password" id="pwd_confirm" name="pwd_confirm" placeholder="Password confirm"/>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td>Group</td>
				<td>
					<select name="group" class="w-100pr validate[required]" id="ep-staff-user-group">
						<option value="" selected disabled>Select group of user</option>
						<?php foreach($groups as $group){?>
								<option value="<?php echo $group['idgroup'];?>"
									<?php if(!empty($user['user_group'])) echo selected($user['user_group'], $group['idgroup']);?>
                                    data-lang-restriction="<?php echo $group['gr_lang_restriction_enabled']; ?>">
									<?php echo $group['gr_name']?>
								</option>
							<?php } ?>
					</select>
				</td>
			</tr>
			<tr <?php echo $show_languages ? '' : 'style="display: none"'; ?>>
				<td>Languages</td>
				<td>
                    <div class="form-group">
                        <select name="lang[]"
                            id="ep-staff-user-lang"
                            class="validate[required]"
                            multiple
                            <?php echo $show_languages ? '' : 'disabled'; ?>>
                            <?php foreach($languages as $lang){?>
                                <option value="<?php echo $lang['id_lang']; ?>"
                                    <?php echo in_array($lang['id_lang'], $language_restriction) ? 'selected': ''; ?>>
                                    <?php echo $lang['lang_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="idu" value="<?php echo $user['idu'];?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: "user/ajax_user_operation/<?php echo ((isset($user)? 'update' : 'add'))?>_ep_staff",
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
                } else {
                    hideLoader($form);
                }
            }
        });
    }

    $(document).ready(function(){
        var staffGroup = $('#ep-staff-user-group');
        var staffLang = $('#ep-staff-user-lang');

        staffGroup.on('change', function(e) {
            var self = $(this);
            var option = self.find(':selected');
            var hasLangRestrictions = Boolean(~~(option.data('lang-restriction') || 0));
            if(hasLangRestrictions && staffLang.length) {
                staffLang.prop('disabled', false);
                staffLang.closest('tr').show();
            } else {
                staffLang.prop('disabled', true);
                staffLang.closest('tr').hide();
            }
        });
        staffLang.select2({
            width: '100%',
            multiple: true,
            placeholder: "Select allowed languages",
            minimumResultsForSearch: 2,
        });
    });
</script>
