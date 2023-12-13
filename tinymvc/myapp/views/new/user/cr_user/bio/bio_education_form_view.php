<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<label class="input-label input-label--required">School</label>
				<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="school" value="<?php if(!empty($user_education)){echo $user_education['school'];}?>" placeholder="e.g. Boston University"/>

				<label class="input-label input-label--required">Degree</label>
				<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="degree" value="<?php if(!empty($user_education)){echo $user_education['degree'];}?>" placeholder="e.g. Bachelor's"/>

				<label class="input-label input-label--required">Field of study</label>
				<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="field_of_study" value="<?php if(!empty($user_education)){echo $user_education['field_of_study'];}?>" placeholder="e.g. Business"/>

				<label class="input-label">Grade</label>
				<input class="validate[maxSize[250]]" type="text" name="grade" value="<?php if(!empty($user_education)){echo $user_education['grade'];}?>" placeholder=""/>

				<div class="row">
					<div class="col-6">
						<label class="input-label input-label--required">From year</label>
						<select name="year_from" class="validate[required]">
							<option value="">Year</option>
							<?php $from_year = (int)date('Y');?>
							<?php $min_year = $from_year - 60;?>
							<?php for($from_year; $from_year >= $min_year; $from_year--){?>
								<option value="<?php echo $from_year;?>" <?php if(!empty($user_education['year_from'])){echo selected($user_education['year_from'], $from_year);}?>><?php echo $from_year;?></option>
							<?php }?>
						</select>
					</div>
					<div class="col-6">
						<label class="input-label input-label--required">To year <small>(or expected)</small></label>
						<select name="year_to" class="validate[required]">
							<option value="">Year</option>
							<?php $to_year = (int)date('Y') + 10;?>
							<?php $min_year = $to_year - 70;?>
							<?php for($to_year; $to_year >= $min_year; $to_year--){?>
								<option value="<?php echo $to_year;?>" <?php if(!empty($user_education['year_to'])){echo selected($user_education['year_to'], $to_year);}?>><?php echo $to_year;?></option>
							<?php }?>
						</select>
					</div>
				</div>

				<label class="input-label">Description</label>
				<textarea name="description"><?php if(!empty($user_education)){echo $user_education['description'];}?></textarea>
			</div>
            <?php if(!empty($user_education)){?>
                <input type="hidden" name="id_education" value="<?php echo $user_education['id_education']?>"/>
            <?php }?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script>
function modalFormCallBack(form){
	var $form = $(form);
    var fdata = $form.serialize();
    var $wrapper = $form.closest('.js-modal-flex');

	$.ajax({
		type: 'POST',
		url: 'cr_user/ajax_operations/<?php if(!empty($user_education)){?>edit<?php }else{?>add<?php }?>_education',
		dataType: 'JSON',
		data: fdata,
		beforeSend: function(){
			showLoader($wrapper);
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type);

			if(resp.mess_type == 'success'){

				<?php if(!empty($user_education)){?>
					manage_educations_callback(resp, '<?php echo $user_education['id_education']?>');
				<?php }else{?>
					manage_educations_callback(resp);
				<?php }?>

				closeFancyBox();
			} else{
				$form.find('button[type=submit]').removeClass('disabled');
			    hideLoader($wrapper);
			}
		}
	});
}
</script>
