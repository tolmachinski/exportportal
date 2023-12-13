<form  method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 mh-500">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table ">
        <tbody>
			<tr>
				<td>Status name</td>
				<td>
					<input class="w-100pr validate[required]" type="text" name="status_title" placeholder="Status name"/>
				</td>
			</tr>
			<tr>
				<td>Status color</td>
				<td>
					<div class="input-group colorpicker-component">
                        <input type="text" name="status_color" value="#000000" class="form-control validate[required]" readonly placeholder="Select status color"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
				</td>
			</tr>
			<tr>
				<td>Status description</td>
				<td>
					<textarea name="status_description" class="w-100pr h-100 validate[required]"></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-success" type="submit">
            <span class="ep-icon ep-icon_ok"></span> Save
        </button>
	</div>
</form>

<script>

$(document).ready(function(){
    $('.colorpicker-component').colorpicker();
});

function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "users/ajax_operations/add_calling_status",
		data: $form.serialize(),
		beforeSend: function(){ 
			showLoader($form); 
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			if(data.mess_type == 'success'){
				if(data_table != undefined)
						data_table.fnDraw();
                        
				closeFancyBox();
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
