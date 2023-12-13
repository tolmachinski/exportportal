<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>

<div class="wr-modal-b">
	<form class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<label class="modal-b__label">Template</label>
			<input type="text" class="validate[required]" name="name" placeholder="Template name" value="<?php echo $banner['name'];?>"/>
			<div class="clearfix"></div>

			<label class="modal-b__label">Type</label>
			<select class="form-control validate[required]" name="type" id="faq-translation--form-input--language">
				<option selected disabled>Select type</option>
				<?php foreach($banner_types ?? [] as $type){?>
					<option value="<?php echo $type['id'];?>" <?php echo $type['id'] === $banner['type'] ? 'selected' : '' ?>><?php echo $type['type_name'];?></option>
				<?php } ?>
			</select>

			<label class="modal-b__label">Link</label>
			<input type="text" class="validate[required,custom[url]]" name="link" placeholder="Banner link" value="<?php echo $banner['link'];?>"/>
			<div class="clearfix"></div>

			<label class="modal-b__label">Html source</label>
			<textarea class="validate[required] h-140" name="html_banner" placeholder="Html code"><?php echo $banner['html_banner'];?></textarea>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" name="id_banner" value="<?php echo $banner['id'] ?>"/>
			<button class="btn btn-primary pull-right" type="submit">Submit</button>
		</div>
	</form>
</div>

<script>

<?php if(!empty($banner)){?>
	var link = 'banner/ajax_operation/edit';
<?php }else{?>
	var link = 'banner/ajax_operation/add';
<?php }?>

function modalFormCallBack(form, data_table){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: link,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
