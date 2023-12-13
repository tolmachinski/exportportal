<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-900 mh-450 mt-10">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td>
                        <label>Title</label>
					</td>
					<td>
						<input type="text" name="title" class="validate[required] w-100pr" placeholder="Standard title" value="<?php echo $standard['standard_title'];?>" />
					</td>
				</tr>
				<tr>
					<td>
                        <label>
                            Country
                        </label>
					</td>
                    <td>
                        <select name="country" class="w-100pr">
                            <option value="">Select country</option>
                            <?php foreach($countries as $country){?>
                                <option value="<?php echo $country['id'];?>" <?php echo selected($country['id'], $standard['standard_country']);?>>
                                    <?php echo $country['country'];?>
                                </option>
                            <?php }?>
                        </select>
                    </td>
				</tr>
				<tr>
					<td>
                        <label>Description</label>
					</td>
					<td>
						<textarea class="w-100pr h-50 validate[required] tinymce" name="description"><?php echo $standard['standard_description'];?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
        <input type="hidden" name="id_standard" value="<?php echo $standard['id_standard'];?>">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
$(document).ready(function(){
    tinymce.init({
        selector:'.tinymce',
        menubar: false,
        statusbar : false,
        tooltip : false,
        height : 250,
        plugins: ["autolink lists link textcolor"],
        dialog_type : "modal",
        toolbar: "bold italic underline forecolor backcolor link | numlist bullist",
		resize: false
    });
});
function modalFormCallBack(form, data_table){
	tinyMCE.triggerSave();
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>international_standards/ajax_operations/edit_standard',
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
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
