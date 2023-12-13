<form  class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr">
		<tr>
			<td>Title</td>
			<td>
				<input value="<?php if(isset($training['training_title'])) echo $training['training_title'] ?>" type="text" name="title" class="w-100pr validate[required, maxSize[255]]"/>
			</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>
                <textarea class="w-100pr h-100 validate[required] text-block" name="description" placeholder="Write your description here"><?php if(isset($training['training_description'])) echo $training['training_description'];?></textarea>
			</td>
		</tr>
		<tr>
			<td class="w-100">Start date</td>
			<td>
                <input class="w-100pr form-control edit-time validate[required]" type="text" id="training_start_date" name="start_date"  placeholder="From" value="<?php if(isset($training['training_start_date'])) echo formatDate($training['training_start_date'], 'm/d/Y H:i:s A');?>" readonly>
			</td>
		</tr>
		<tr>
			<td class="w-100">Finish date</td>
			<td>
                <input class="w-100pr form-control edit-time validate[required]" type="text" name="finish_date"  placeholder="From" value="<?php if(isset($training['training_finish_date'])) echo formatDate($training['training_finish_date'], 'm/d/Y H:i:s A');?>" readonly>
			</td>
		</tr>
		<tr>
			<td class="w-100">Type</td>
			<td>
                <select name="type" class="w-100pr validate[required]">
                    <option value="training" <?php if(isset($training['training_type']) && $training['training_type'] == 'training') echo "selected" ?>>Training</option>
                    <option value="webinar" <?php if(isset($training['training_type']) && $training['training_type'] == 'webinar') echo "selected" ?>>Webinar</option>
                </select>
			</td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
        <?php if(isset($training['id_training'])) { ?>
            <input type="hidden" name="id" value="<?php echo $training['id_training'] ?>"/>
        <?php } ?>
		<button class="pull-right btn btn-default" type="submit" name="update_training"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . ( isset($training['id_training']) ? "cr_training/ajax_trainings_operation/edit_training" : "cr_training/ajax_trainings_operation/add_training")?>',
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

    $(function() {
		tinymce.init({
			selector:'.text-block',
			menubar: false,
			statusbar : false,
			height : 140,
			plugins: ["autolink lists link textcolor"],
			dialog_type : "modal",
            style_formats: [
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
                {title: 'H5', block: 'h5'},
                {title: 'H6', block: 'h6'},
            ],
			toolbar: "styleselect | bold italic underline link | numlist bullist ",
			resize: false
		});

		$('.edit-time').datetimepicker({
			timeFormat: "hh:mm:00 TT",
			minDate: 0,
			millisec_slider: false,
			numberOfMonths: 1
		});
    });
</script>
