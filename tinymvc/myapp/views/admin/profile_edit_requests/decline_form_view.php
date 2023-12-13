<div class="wr-modal-b">
    <form method="post" id="profile-edit-request-decline--form" class="validateModal relative-b">
		<div class="modal-b__content pb-0 w-900">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Reason</label>
                    <textarea
                        id="profile-edit-request-decline--formfield--reason"
                        name="reason"
                        class="validate[required,maxSize[500]] textcounter-document_comment"
                        placeholder="Enter your reason"
                        data-max="500"
                    ></textarea>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix mnh-60">
            <button
                id="profile-edit-request-decline--action--back"
                type="button"
                class="btn btn-primary fancyboxValidateModalDT fancybox.ajax pull-left"
                title="Go back"
                data-title="#<?php echo cleanOutput($request); ?> request details"
                data-fancybox-href="<?php echo cleanOutput($backUrl); ?>"
            >
                <span class="ep-icon ep-icon_arrow-left"></span> Back
            </button>

            <button
                id="profile-edit-request-decline--action--send"
                type="submit"
                class="btn btn-success pull-right"
                title="Decline request"
            >
                <span class="ep-icon ep-icon_ok"></span> Decline
            </button>
        </div>
    </form>
</div>

<script><?php echo getPublicScriptContent('plug_admin/js/profile_edit_requests/decline.js', true); ?></script>
<script>
    $(function () {
		if (!('ProfileEditRequestDeclineModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'ProfileEditRequestDeclineModule' must be defined"))
			}

			return;
        }

        ProfileEditRequestDeclineModule.default(
            <?php echo json_encode([
                'declineUrl'     => $declineUrl,
                'reasonField'    => '#profile-edit-request-decline--formfield--reason',
                'declineButton'  => '#profile-edit-request-decline--action--send',
                'declineWrapper' => '#profile-edit-request-decline--form',
            ]); ?>
        );
	});
</script>
