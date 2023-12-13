<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Short Bio</h1>
    </div>

	<ul class="nav nav-tabs nav--borders nav--480 mt-30" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" href="#speak-languages-tab" aria-controls="title" role="tab" data-toggle="tab">Speak languages</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#skills-tab" aria-controls="title" role="tab" data-toggle="tab">Skills</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#awards-acknowledgements-tab" aria-controls="title" role="tab" data-toggle="tab">Awards/Acknowledgements</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#jobs-experience-tab" aria-controls="title" role="tab" data-toggle="tab">Jobs Experience</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#education-tab" aria-controls="title" role="tab" data-toggle="tab">Education</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#video-tab" aria-controls="title" role="tab" data-toggle="tab">Video</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#certificates-tab" aria-controls="title" role="tab" data-toggle="tab">Certificates</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#contacts-tab" aria-controls="title" role="tab" data-toggle="tab">Contacts</a>
		</li>
	</ul>

	<div class="tab-content tab-content--borders">
		<div role="tabpanel" class="tab-pane fade show active" id="speak-languages-tab">
		<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_speak_langs_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="skills-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_skills_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="awards-acknowledgements-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_awards_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="jobs-experience-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_job_history_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="education-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_education_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="video-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_video_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="certificates-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_certificates_view');?>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="contacts-tab">
			<?php tmvc::instance()->controller->view->display('new/user/cr_user/bio/bio_contacts_view');?>
		</div>
	</div>
</div>

<script>
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
</script>