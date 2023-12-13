<?php
$user_data = json_decode($user['import_data'], true);
?>
<form class="relative-b validateModal">
	<div class="wr-form-content w-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
                <tr>
                    <td class="w-100">First name</td>
                    <td colspan="2">
                        <input type="text" name="fname" class="validate[required,custom[validUserName],maxSize[100]] w-100pr" value="<?php echo $user_data['user_fname'];?>" placeholder="First name"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Last name</td>
                    <td colspan="2">
                        <input type="text" name="lname" class="validate[required,custom[validUserName],maxSize[100]] w-100pr" value="<?php echo $user_data['user_lname'];?>" placeholder="Last name"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Email</td>
                    <td colspan="2">
                        <input type="text" name="email" class="validate[required,maxSize[100],custom[noWhitespaces],custom[emailWithWhitespaces]] w-100pr" value="<?php echo $user_data['email'];?>" placeholder="Email"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">User group</td>
                    <td colspan="2">
                        <select name="group" class="w-100pr validate[required]">
                            <option value="">Select user group</option>
                            <?php $group_name = '';?>
                            <?php foreach($groups as $group){?>
                                <option
                                    value="<?php echo $group['idgroup'];?>"
                                    <?php if($user['status'] != 'new'){?>
                                        <?php echo selected($user_data['group'], $group['idgroup']);?>
                                        <?php if($user_data['group'] == $group['idgroup']) $group_name = $group['gr_name'];?>
                                    <?php }?>
                                    data-name="<?php echo $group['gr_name'];?>">
                                    <?php echo $group['gr_name'];?>
                                </option>
                            <?php }?>
                        </select>
                        <input type="hidden" name="group_name" value="<?php echo $group_name;?>">
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Country</td>
                    <td class="w-200">
                        <?php echo $user_data['country'];?>
                    </td>
                    <td>
                        <select name="port_country" id="country" class="w-100pr validate[required]">
                            <option value="">Select country</option>
                            <?php $country_name = $user_data['country'];?>

                            <?php foreach($port_country as $mconutry){ ?>
                                <option value='<?php echo $mconutry['id']?>'
                                <?php if($user['status'] != 'new'){?>
                                    <?php echo selected($user_data['id_country'], $mconutry['id']); ?>
                                    <?php if($user_data['id_country'] == $mconutry['id']) $country_name = $mconutry['country'];?>
                                <?php }?>
                                data-name="<?php echo $mconutry['country']?>">
                                <?php echo $mconutry['country']?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">State</td>
                    <td class="w-200">
                        <?php echo $user_data['state'];?>
                    </td>
                    <td id="user_state">
                        <?php $state_name = $user_data['state'];?>
                        <?php if($user['status'] == 'new'){?>
                            <select name="states" id="states" class="w-100pr">
                                <option value="">Select state</option>
                            </select>
                        <?php } else{?>
                            <select name="states" class="w-100pr validate[required]" id="states">
                                <option value=""><?php echo translate('form_placeholder_select2_state');?></option>
                                <?php foreach($states as $state){?>
                                    <option value="<?php echo $state['id'];?>"
                                        <?php echo selected($user_data['id_state'], $state['id']);?>
                                        <?php if($user_data['id_state'] == $state['id']) $state_name = $state['state'];?>
                                        data-name="<?php echo $state['state']?>">
                                        <?php echo $state['state'];?>
                                    </option>
                                <?php } ?>
                            </select>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">City</td>
                    <td class="w-200">
                        <?php echo $user_data['city'];?>
                    </td>
                    <td id="user_city">
                        <?php $city_name = $company_data['city'];?>
						<select name="port_city" class="w-100pr validate[required] select-city">
							<option value="">Select city</option>
							<?php if(isset($city_selected) && !empty($city_selected)){ ?>
								<option value="<?php echo $city_selected['id'];?>" selected>
									<?php echo $city_selected['city'];?>
								</option>
							<?php } ?>
						</select>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
        <input type="hidden" name="country_name" value="<?php echo $country_name;?>">
        <input type="hidden" name="state_name" value="<?php echo $state_name;?>">
        <input type="hidden" name="city_name" value="<?php echo $city_name;?>">
	    <input type="hidden" name="id_import" value="<?php echo $user['id'];?>">
		<a title="Cancel" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Cancel</a>
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>

	var $selectCity;
	var selectState;

    $(function(){
		$selectCity = $(".select-city");
		initSelectCity($selectCity);

		$('body').on('change', "select#states", function(){
            selectState = this.value;
            $selectCity.empty().trigger("change").prop("disabled", false);

            if(selectState != '' || selectState != 0){
                var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
            } else{
                var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
				$selectCity.prop("disabled", true);
            }
            $selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
        });

		$('body').on('change', "#country", function(){
			selectCountry($(this), 'select#states');
			selectState = 0;
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

        $('select[name=group]').change(function(){
            var group_name = $("option:selected", this).data('name');
            $('input[name=group_name]').val(group_name);
        });
    });

	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>admin_import/ajax_operations/edit_data',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw(false);
				}else{
					hideLoader($form);
				}
			}
        });
	}
</script>
