<form method="post" class="validateModal relative-b" id="send-emails--form">
	<input type="hidden" name="users[]" value="<?php echo (!empty($user)) ? $user['idu'] : ''; ?>">
    <div class="wr-form-content w-900 mh-450 mt-10">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<?php if (!empty($user)) { ?>
				<thead>
					<tr>
						<th>Notify user</th>
						<th>Last notify date</th>
						<th>Notify count</th>
					</tr>
					<tr>
						<td><?php echo cleanOutput(trim("{$user['fname']} {$user['lname']}")); ?></td>
						<td class="tac vam"><?php echo formatDate($user['resend_email_date']); ?></td>
						<td class="tac vam"><?php echo $user['resend_accreditation_email']; ?></td>
					</tr>
				</thead>
			<?php } ?>
			<tbody>
				<tr>
					<td class="w-200">
                        Email template
					</td>
					<td>
						<select name="email_template" class="w-100pr">
							<option value="">Select email type</option>
							<?php foreach($templates as $key => $template) { ?>
								<option value="<?php echo cleanOutput($key); ?>"><?php echo cleanOutput($template['title']); ?></option>
							<?php } ?>
						</select>
					</td>
					<td class="w-150 tac">
						<div class="btn-group">
                            <button class="btn btn-primary call-function"
                                data-callback="viewTemplate"
                                data-user-type="seller"
                                data-page="accreditation"
                                title="View Seller template">
                                <i class="ep-icon ep-icon_visible txt-white m-0"></i>
                            </button>
                            <button class="btn btn-success call-function"
                                data-callback="viewTemplate"
                                data-user-type="buyer"
                                data-page="accreditation"
                                title="View Buyer template">
                                <i class="ep-icon ep-icon_visible txt-white m-0"></i>
                            </button>
                            <button class="btn btn-warning call-function"
                                data-callback="viewTemplate"
                                data-user-type="shipper"
                                data-page="accreditation"
                                title="View Freight Forwarder template">
                                <i class="ep-icon ep-icon_visible txt-white m-0"></i>
                            </button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<iframe id="send-emails--formfield--preview" src="" width="100%"></iframe>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_envelope"></span> Send email</button>
	</div>
</form>
<script>
    $(function () {
        var form = $("#send-emails--form");
        var preview = $("#send-emails--formfield--preview");
        var hasUser = Boolean(~~parseInt('<?php echo (int) !empty($user); ?>'));
        var viewTemplate = function (button) {
            var page = button.data('page');
            var email = form.find('select[name="email_template"]').val();
            var userType = button.data('user-type');

            if(email.length) {
                preview.attr("src", "users/view_email_template?user_type=" + userType + '&email=' + email + '&page=' + page).css({'height': 400});
            } else {
                systemMessages('Warning: Select email template.', 'message-warning');
            }
        };
        var sendEmail = function (form) {
            var url = __group_site_url + 'verification/ajax_operations/send_emails';
            var data = form.serializeArray();
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json').fail(onRequestError);
            };
            var onRequestEnd = function () {
                hideLoader(form);
            };
            var onRequestStart = function () {
                showLoader(form);
            };
            var onRequestSuccess = function(data){
                systemMessages(data.message, data.mess_type);
                if('success' === data.mess_type){
                    callFunction('updateTable');
                    closeFancyBox();
                }
            };

            if (!hasUser) {
                var checkedUsers = [];
                $.each($('.check-user:checked'), function(){
                    checkedUsers.push($(this).data('user'));
                });

                if(!checkedUsers.length){
                    systemMessages('Error: Please select at least one user.', 'error');
                }
                checkedUsers.forEach(function (user) {
                    form.append(
                        $('<input>').attr({type: 'hidden', name: 'users[]'}).val(user|| null)
                    );
                });
            }

            onRequestStart();
            sendRequest(url, data)
                .done(onRequestSuccess)
                .always(onRequestEnd);
        };

        mix(window, {
            viewTemplate: viewTemplate,
            modalFormCallBack: sendEmail
        }, false);
    });
</script>
