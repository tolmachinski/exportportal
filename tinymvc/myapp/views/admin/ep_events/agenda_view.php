<form method="post" class="validateModal relative-b" id="agendaForm">
	<div class="wr-form-content w-900 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-bordered w-100pr">
            <tbody>
                <?php if (empty($event['agenda'])) {?>
                    <tr>
                        <td class="col-xs-2">
                            <input class="form-control js-datetimepicker validate[required]" type="text" name="date_start[]" placeholder="Starting on..." value="" readonly="">
                        </td>
                        <td class="col-xs-9">
                            <textarea name="description[]" class="js-agenda-step-description"></textarea>
                        </td>
                        <td class="col-xs-1 tac">
                            <a role="button" class="btn btn-default w-40 txt-red bd-none call-function" data-callback="removeFormFields">
                                <i class="ep-icon ep-icon_remove"></i>
                            </a>
                        </td>
                    </tr>
                <?php } else {?>
                    <?php foreach ($event['agenda'] as $agenda) {?>
                        <tr>
                            <td class="col-xs-2">
                                <input class="form-control js-datetimepicker validate[required]" type="text" name="date_start[]" placeholder="Starting on..." value="<?php echo $agenda['startDate'];?>" readonly="">
                            </td>
                            <td class="col-xs-9">
                                <textarea name="description[]" class="js-agenda-step-description"><?php echo cleanOutput($agenda['description']);?></textarea>
                            </td>
                            <td class="col-xs-1 tac">
                                <a role="button" class="btn btn-default w-40 txt-red bd-none call-function" data-callback="removeFormFields">
                                    <i class="ep-icon ep-icon_remove"></i>
                                </a>
                            </td>
                        </tr>
                    <?php }?>
                <?php }?>
            </tbody>
        </table>
	</div>
    <div class="overflow-y-a pr-30">
        <a role="button" id="js-add-form-fields" class="btn btn-default pull-right w-40 bd-none fs-16 call-function" data-callback="addFormFields">
            <i class="ep-icon ep-icon_plus"></i>
        </a>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js';?>"></script>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/tinymce-4-3-10/tinymce.min.js';?>"></script>
<script type="text/javascript">
    var initDateTimePicker = function (selector) {
        $(selector).datetimepicker({
            timeFormat: "HH:mm",
            stepMinute: 5,
            controlType: 'select',
            oneLine: true,
            minDateTime: new Date('<?php echo $event['start_date'];?>'),
            maxDateTime: new Date('<?php echo $event['end_date'];?>')
        });
    }

    var initTinyMCE = function (selector) {
        tinymce.init({
            selector: selector,
            menubar: false,
            statusbar : false,
            height : 100,
            media_poster: false,
            media_alt_source: false,
            relative_urls: false,
            dialog_type : "modal",
            toolbar: "undo redo | bold italic underline"
        });
    }

    initDateTimePicker('.js-datetimepicker');
    initTinyMCE('.js-agenda-step-description');

    var addFormFields = function (btn) {
        var textareaId = Math.random().toString(36).substring(7);

        $('#agendaForm > .wr-form-content table > tbody').append(
            '<tr>' +
                '<td class="col-xs-2">' +
                    '<input class="form-control js-datetimepicker validate[required]" type="text" name="date_start[]" placeholder="Starting on..." value="" readonly="">' +
                '</td>' +
                '<td class="col-xs-9">' +
                    '<textarea name="description[]" class="js-agenda-step-description" id="' + textareaId + '"></textarea>' +
                '</td>' +
                '<td class="col-xs-1 tac">' +
                    '<a role="button" class="btn btn-default w-40 txt-red bd-none call-function" data-callback="removeFormFields">' +
                        '<i class="ep-icon ep-icon_remove"></i>' +
                    '</a>' +
                '</td>' +
            '</tr>'
        );

        initDateTimePicker('.js-datetimepicker:not(.hasDatepicker)');
        initTinyMCE('#' + textareaId);
    }

    var removeFormFields = function (btn) {
        $(btn).parent().parent().remove();
        $('#js-add-form-fields').show();
    }

    function modalFormCallBack(form, data_table){
		var form = $(form);

		$.ajax({
            type: 'POST',
            url: '<?php echo $submitFormUrl;?>',
			data: form.serialize(),
            beforeSend: function () {
                showLoader(form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if ('success' === data.mess_type){
                    closeFancyBox();

					if (data_table) {
						data_table.fnDraw();
                    }
				} else {
					hideLoader(form);
				}
			}
        });
    }
</script>
