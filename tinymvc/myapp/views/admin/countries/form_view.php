<form method="post" class="validateModal relative-b" action="<?php echo $action;?>">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped temp w-100pr" >
			<tr>
				<td class="w-120">Country name</td>
				<td><input type="text" name="country_name" class="w-100pr validate[required,maxSize[255]]" value="<?php echo empty($country['country']) ? '' : cleanOutput($country['country']);?>" /></td>
			</tr>
			<tr>
				<td>Continent</td>
				<td>
					<select class="w-100pr validate[required]" name="country_continent">
                        <option value="">Choose continent</option>
                        <?php foreach ($continents as $continent) {?>
                            <option value="<?php echo $continent['id_continent'];?>" <?php echo selected($country['id_continent'], $continent['id_continent']);?>><?php echo cleanOutput($continent['name_continent']);?></option>
                        <?php }?>
					</select>
				</td>
            </tr>
            <tr>
				<td class="w-120">Abr</td>
				<td><input type="text" name="abr" class="w-100pr validate[required,maxSize[2],minSize[2]]" value="<?php echo empty($country['abr']) ? '' : cleanOutput($country['abr']);?>" /></td>
			</tr>
            <tr>
				<td class="w-120">Abr3</td>
				<td><input type="text" name="abr3" class="w-100pr validate[required,maxSize[3],minSize[3]]" value="<?php echo empty($country['abr3']) ? '' : cleanOutput($country['abr3']);?>" /></td>
			</tr>
            <tr>
                <td class="w-120">Latitude</td>
                <td><input type="text" name="country_latitude" class="w-100pr validate[required,number,maxSize[20]]" value="<?php echo empty($country['country_latitude']) ? '' : $country['country_latitude'];?>" /></td>
            </tr>
            <tr>
                <td class="w-120">Longitude</td>
                <td><input type="text" name="country_longitude" class="w-100pr validate[required,number,maxSize[20]]" value="<?php echo empty($country['country_longitude']) ? '' : $country['country_longitude'];?>" /></td>
            </tr>
            <tr>
                <td class="w-120">Position in Select</td>
				<td><input type="text" name="position" class="w-100pr validate[integer,min[1],max[500]]" value="<?php echo empty($country['position_on_select']) ? '' : $country['position_on_select'];?>" /></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
        <label class="lh-30 vam pull-left mr-10">
            <input class="vam" type="checkbox" name="is_focus" <?php echo $country['is_focus_country'] ? 'checked="checked"' : '';?>/>
            Focus country
        </label>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
    var modalFormCallBack = function (form, countryTable){
        var countryForm = $(form);
        var data = countryForm.serializeArray();
        var url = countryForm.attr('action');
        var onSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if('success' === response.mess_type) {
                countryTable.DataTable().draw(false);
                $.fancybox.close();
            }
        };

        showLoader(countryForm);
        $.post(url, data, null, 'json').done(onSuccess).fail(onRequestError).always(function(){
            hideLoader(countryForm);
        });
    }
</script>
