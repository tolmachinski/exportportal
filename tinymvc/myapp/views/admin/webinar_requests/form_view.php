<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-600">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tbody>
				<tr>
                    <td>
                        Webinar
                    </td>
                    <td>
                        <select class="w-50pr" name="webinar">
                            <?php foreach($webinars as $webinar){
                                $dateStart = getDateFormat($webinar['start_date']);?>
                                <option value="<?=$webinar['id']?>" <?php echo selected($currentWebinar, $webinar['id']);?>><?="{$webinar['title']} ({$dateStart})";?></option>
                            <?php }?>
                        </select>
                    </td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php echo $id;?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js';?>"></script>

<script>

    function modalFormCallBack(form)
    {
        var formData = form.serialize();
        postRequest(__site_url + 'webinar_requests/ajax_operations/attach_webinar', form.serialize(), "json")
            .then(function (response) { systemMessages(response.message, response.mess_type); closeFancyboxPopup(); })
            .catch(onRequestError)
            .finally(function () {
                if(dtWebinarRequests != undefined){
                    dtWebinarRequests.fnDraw();
                }
            })
    }
</script>
