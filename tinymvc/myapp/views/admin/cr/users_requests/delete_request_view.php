<form method="post" action="<?php echo $action; ?>" class="validateModal relative-b" id="delete-request--form">
    <input type="hidden" name="request" value="<?php echo !empty($request) ? $request : null; ?>">

    <div class="wr-form-content w-900 mt-10">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-200">
                        Notice
					</td>
					<td>
                        <textarea name="notice"
                            id="delete-request--form-input--notice"
                            class="form-control validate[required,maxSize[5000]]"
                            placeholder="Enter the notice"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-success" type="submit">Proceed</button>
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
</script>
