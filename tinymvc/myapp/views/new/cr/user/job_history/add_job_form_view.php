<div class="wr-modal-b">
	<form class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<label class="modal-b__label">Place (Company name)</label>
			<div class="required-field__ico"></div>
			<input class="validate[required,custom[validUserName],minSize[2],maxSize[250]]" type="text" name="place" value="<?php echo $history_item['job_place']?>" placeholder=""/>

			<div class="row">
				<div class="col-6">
					<label class="modal-b__label">Date from</label>
					<div class="required-field__ico"></div>
					<input id="from-date-job" class="from-date-job validate[required]" type="text" name="date_from" value="<?php echo ($history_item['date_from'])?formatDate($history_item['date_from'], 'm/d/Y'):'';?>" readonly>
				</div>
				<div class="col-6">
					<label class="modal-b__label">Date to</label>
					<input id="to-date-job" class="to-date-job" type="text" name="date_to" value="<?php echo ($history_item['date_to'])?formatDate($history_item['date_to'], 'm/d/Y'):'';?>" readonly>
				</div>
			</div>

			<label class="modal-b__label">Position</label>
			<div class="required-field__ico"></div>
			<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="position" value="<?php echo $history_item['job_position']?>"/>

			<label class="modal-b__label">Skills and duty performed</label>

			<div class="skills mb-15">
				<?php if(!empty($history_item['job_skills'])){
					$history_item['job_skills'] = json_decode( $history_item['job_skills'], true);
					foreach($history_item['job_skills'] as $skill_key => $skill_item){
						if($skill_key > 0){?>
							<div class="input-group input-group-lg mb-10">
								<div class="required-field__ico"></div>
								<input class="form-control validate[required,minSize[2],maxSize[50]]" type="text" name="skills[]" value="<?php echo $skill_item?>"/>
								<span class="input-group-btn">
									<a class="btn btn-default h-35 confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="removeJobSkill"><i class="ep-icon ep-icon_remove fs-14 txt-gray"></i></a>
								</span>
							</div>
						<?php }else{?>
							<div class="required-field__ico"></div>
							<input class="validate[required,minSize[2],maxSize[50]] mb-10" type="text" name="skills[]" value="<?php echo $skill_item?>"/>
						<?php }?>
					<?php }?>
				<?php }else{?>
					<div class="required-field__ico"></div>
					<input class="validate[required,minSize[2],maxSize[50]] mb-10" type="text" name="skills[]" value=""/>
				<?php }?>
			</div>

			<div class="clearfix">
				<a class="pull-right call-function" data-group="0" data-callback="addJobSkill" href="#"><i class="ep-icon ep-icon_plus"></i> add skill</a>
			</div>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" name="id_job" value="<?php echo $history_item['id_job']?>"/>
			<button class="btn btn-primary pull-right" type="submit">Send</button>
		</div>
	</form>
</div>
<script>
$(function(){
	initDateJob();
});

function initDateJob(){
	var dateFormat = "mm/dd/yy",
	from = $( "#from-date-job" ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: dateFormat
 	}).on( "change", function() {
		to.datepicker( "option", "minDate", getDate( this ) );
	}),

	to = $( "#to-date-job" ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: dateFormat
 	}).on( "change", function() {
		from.datepicker( "option", "maxDate", getDate( this ) );
	});

	function getDate( element ) {
		var date;
		try {
			date = $.datepicker.parseDate( dateFormat, element.value );
		} catch( error ) {
			date = null;
		}

		return date;
	}
}

var addJobSkill = function(obj){
	var $thisBtn = $(obj);

	var variantGroupNew = '<div class="input-group input-group-lg mb-10">\
							<div class="required-field__ico"></div>\
							<input class="form-control mb-0 validate[required,minSize[2],maxSize[50]]" type="text" name="skills[]" value="" placeholder=""/>\
							<span class="input-group-btn">\
								<a class="btn btn-default h-35 confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="removeJobSkill"><i class="ep-icon ep-icon_remove fs-14 txt-gray"></i></a>\
							</span>\
						</div>';

	$thisBtn.closest('.modal-b__form').find('.skills').append(variantGroupNew);

	validateReInit();
}

var removeJobSkill = function(obj){
	var $this = $(obj);

	$this.closest('.input-group').fadeOut('normal', function(){
		$(this).remove();
	});
}

function validateReInit(){
	$(".validengine").validationEngine('detach');
	$(".validengine").validationEngine(
		'attach',
		{
			promptPosition : "topLeft",
			autoPositionUpdate : true,
			onValidationComplete: function(form, status){
				if(status){
					if ($(form).data("callback") != undefined) {
						window[$(form).data("callback")](form);
					} else {
						$(form).validationEngine('detach');
						$(form).submit();
					}
				}
			}
		}
	);
}

function modalFormCallBack(form){
	var $form = $(form);
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		<?php if(!empty($history_item)){?>
		url: 'cr_job_history/ajax_job_operation/edit_job',
		<?php }else{?>
		url: 'cr_job_history/ajax_job_operation/add_job',
		<?php }?>
		dataType: 'JSON',
		data: fdata,
		beforeSend: function(){
			showLoader('.wr-modal-b');
		},
		success: function(resp){
			systemMessages(resp.message, 'message-' + resp.mess_type);
			hideLoader('.wr-modal-b');
			if(resp.mess_type == 'success'){
				closeFancyBox();
				callbackAddJob();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
