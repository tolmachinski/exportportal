<form method="post" action="<?php echo $action; ?>" class="validateModal relative-b" id="send-email--form">
    <input type="hidden" name="user" value="<?php echo !empty($user['idu']) ? $user['idu'] : ''; ?>">

    <div class="wr-form-content w-900 mh-500 mt-10">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-200">
                        Email template
					</td>
					<td>
						<select name="type" class="w-100pr validate[required]" id="send-email--form-input-type">
							<option value disabled selected>Select email type</option>
							<?php foreach($templates as $template){?>
								<option value="<?php echo $template['template_name']?>"><?php echo $template['title'];?></option>
							<?php }?>
						</select>
					</td>
					<td class="w-150 tac">
						<div class="btn-group">
							<button class="btn btn-primary call-function"
                                title="View template"
                                data-url="<?php echo __SITE_URL . 'cr_users/preview_email_template'; ?>"
                                data-user="<?php echo !empty($user['idu']) ? $user['idu'] : ''; ?>"
                                data-callback="viewTemplate">
                                <i class="ep-icon ep-icon_visible txt-white m-0"></i>
                            </button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<iframe id="view-template" src="" width="100%"></iframe>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_envelope"></span> Send email</button>
	</div>
</form>
<script>
	var modalFormCallBack = function (formElement, dataGrid) {
        var form = $(formElement);
        var url = form.attr('action');
        var data = form.serializeArray();
        var onRequestSuccess = function(response){
            systemMessages( response.message, 'message-' + response.mess_type );
            if(response.mess_type === 'success'){
                closeFancyBox();
                if(dataGrid !== undefined) {
                    dataGrid.fnDraw(false);
                }
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
	}

	var viewTemplate = function (button) {
		var url = button.data('url') || null;
		var user = button.data('user') || null;
		var type = $("#send-email--form-input-type").val() || null;
        if(null === url || null === user || null === type) {
            return;
        }

        $("#view-template").attr("src", url + "?" + $.param({user: user, type: type})).css({'height': 400});
	}
</script>
