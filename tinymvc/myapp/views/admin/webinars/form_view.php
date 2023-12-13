<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-600">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tbody>
				<tr>
					<td>Title</td>
					<td>
						<input type="text" name="title" class="form-control validate[required]" value="<?php if(isset($one['title'])) echo $one['title'] ?>">
					</td>
				</tr>
                <tr>
					<td>Date</td>
					<td>
                        <input class="form-control validate[required]" type="text" data-title="Date start" name="start_date" id="webinarStartDate" placeholder="Date start" value="<?php echo getDateFormat($one['start_date'] ?? null, null, 'm/d/Y H:i');?>" readonly autocomplete="off">
					</td>
				</tr>
				<tr>
					<td>Link</td>
					<td>
						<input type="text" name="link" class="form-control validate[custom[url]]" value="<?php if(isset($one['link'])) echo $one['link'] ?>">
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($one)){?><input type="hidden" name="id" value="<?php echo $one['id'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js';?>"></script>

<script>

    $(function(){
        $("#webinarStartDate").datetimepicker({
            timeFormat: "HH:mm",
            stepMinute: 5,
            controlType: 'select',
            oneLine: true
        });
    });

    function modalFormCallBack(form)
    {
        var formData = form.serialize();
        postRequest(__site_url + '<?php echo $url; ?>', form.serialize(), "json")
            .then(function (response) { systemMessages(response.message, response.mess_type); closeFancyboxPopup(); })
            .catch(onRequestError)
            .finally(function () {
                if(dtWebinar != undefined){
                    dtWebinar.fnDraw();
                }
            })
    }
</script>
