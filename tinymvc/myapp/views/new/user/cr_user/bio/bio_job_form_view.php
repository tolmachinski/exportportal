<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<label class="input-label input-label--required">Place (Company name)</label>
				<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="place" value="<?php if(!empty($user_job)){echo $user_job['place'];}?>" placeholder=""/>

				<div class="row">
					<div class="col-6">
						<label class="input-label input-label--required">Date from</label>
						<input id="from-date-job" class="date-job from-date-job validate[required,custom[dateFormat]]" type="text" name="date_from" value="<?php echo (!empty($user_job['date_from']))?formatDate($user_job['date_from'], 'm/d/Y'):'';?>" readonly>
					</div>
					<div class="col-6">
						<label class="input-label">Date to</label>
						<input id="to-date-job" class="date-job to-date-job" type="text" name="date_to" value="<?php echo (!empty($user_job['date_to']))?formatDate($user_job['date_to'], 'm/d/Y'):'';?>" readonly>
					</div>
				</div>

				<label class="input-label input-label--required">Position</label>
				<input class="validate[required,minSize[2],maxSize[250]]" type="text" name="position" value="<?php echo $user_job['position']?>"/>

				<label class="input-label">Skills and duty performed</label>

				<div class="skills mb-15">
					<?php if(!empty($user_job['skills'])){
						foreach($user_job['skills'] as $skill_key => $skill_item){
							if($skill_key > 0){?>
								<div class="input-group mb-10">
									<input class="form-control mr-8 validate[required,minSize[2],maxSize[50]]" type="text" name="skills[]" value="<?php echo $skill_item?>"/>
									<span class="input-group-btn">
										<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="remove_job_skill"><i class="ep-icon ep-icon_trash-stroke"></i></a>
									</span>
								</div>
							<?php }else{?>
								<input class="validate[required,minSize[2],maxSize[50]] mb-10" type="text" name="skills[]" value="<?php echo $skill_item?>"/>
							<?php }?>
						<?php }?>
					<?php }else{?>
						<input class="validate[required,minSize[2],maxSize[50]] mb-10" type="text" name="skills[]" value=""/>
					<?php }?>
				</div>

				<div class="clearfix">
					<a class="btn btn-dark pull-right call-function" data-group="0" data-callback="add_job_skill" href="#"><i class="ep-icon ep-icon_plus-circle"></i> Add skill</a>
				</div>
			</div>
            <?php if(!empty($user_job)){?>
                <input type="hidden" name="id_job" value="<?php echo $user_job['id_job']?>"/>
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
$(function(){
	init_date_job();
});

function init_date_job(){
	var dateFormat = "mm/dd/yy",
	from = $( ".date-job" ).datepicker({
		changeMonth: true,
		changeYear: true,
		minDate: new Date(1990, 1 - 1, 25),
		maxDate: "+0y",
		yearRange: "1990:" + (new Date()).getFullYear(),
		dateFormat: dateFormat,
		beforeShow: function (input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        },
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

var add_job_skill = function(obj){
	var $thisBtn = $(obj);

	var template = '<div class="input-group mb-10">\
						<input class="form-control mr-8 validate[required,minSize[2],maxSize[50]]" type="text" name="skills[]" value="" placeholder=""/>\
						<span class="input-group-btn">\
							<a class="btn btn-light confirm-dialog" data-message="Are you sure you want to delete this skill?" data-callback="remove_job_skill"><i class="ep-icon ep-icon_trash-stroke"></i></a>\
						</span>\
					</div>';

	$thisBtn.closest('.modal-flex__form').find('.skills').append(template);
	validateReInit();
}

var remove_job_skill = function(obj){
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
				}else{
					systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
				}
			}
		}
	);
}

function modalFormCallBack(form){
	var $form = $(form);
    var fdata = $form.serialize();
    var $wrapper = $form.closest('.js-modal-flex');
	$.ajax({
		type: 'POST',
		url: 'cr_user/ajax_operations/<?php if(!empty($user_job)){?>edit<?php }else{?>add<?php }?>_job',
		dataType: 'JSON',
		data: fdata,
		beforeSend: function(){
			showLoader($wrapper);
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type);

			if(resp.mess_type == 'success'){
				<?php if(!empty($user_job)){?>
					manage_jobs_callback(resp, '<?php echo $user_job['id_job']?>');
				<?php }else{?>
					manage_jobs_callback(resp);
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
