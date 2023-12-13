<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-900 mh-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table" style="margin-top: 30px;">
        <tbody>
            <tr>
				<td>Title</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required,maxSize[255]]" type="text" name="title" value="<?php echo cleanOutput($event['title'] ?? '');?>"/>
                    </div>
				</td>
            </tr>
            <tr>
				<td>Tags</td>
				<td>
                    <div class="form-group">
                        <?php if (!empty($event['tags'])) {?>
                            <select class="event-tags w-100pr" name="tags[]" multiple>
                                <?php foreach ($event['tags'] as $tag) {?>
                                    <option selected="selected"><?php echo cleanOutput($tag);?></option>
                                <?php }?>
                            </select>
                        <?php } else {?>
                            <select class="event-tags w-100pr" name="tags[]" multiple></select>
                        <?php }?>
                    </div>
				</td>
			</tr>
			<tr>
				<td>Category</td>
				<td>
                    <div class="form-group">
                        <select class="w-100pr validate[required]" name="category">
                            <?php $eventIdCategory = $event['id_category'] ?? null;?>
                            <option value="" <?php echo selected(null, $eventIdCategory);?>>Select category</option>

                            <?php foreach ($eventCategories as $eventCategory) {?>
                                <option value="<?php echo $eventCategory['id'];?>" <?php echo selected($eventCategory['id'], $eventIdCategory);?>><?php echo cleanOutput($eventCategory['name']);?></option>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
            <tr>
				<td>Date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control js-datetimepicker validate[required]" type="text" name="date_start" placeholder="Start" value="<?php echo getDateFormat($event['start_date'] ?? null, null, 'm/d/Y H:i');?>" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control js-datetimepicker validate[required]" type="text" name="date_end" placeholder="End" value="<?php echo getDateFormat($event['end_date'] ?? null, null, 'm/d/Y H:i');?>" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td>Link</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required, custom[url]]" type="text" name="link" value="<?php echo cleanOutput($event['url'] ?? '');?>">
                    </div>
				</td>
			</tr>
            <tr>
				<td>Speaker</td>
				<td>
                    <div class="form-group">
                        <select class="form-control validate[required]" id="js-select-speaker" name="speaker">
                            <?php $eventIdSpeaker = $event['id_speaker'] ?? null;?>
                            <option></option>
                            <?php foreach ($eventSpeakers as $eventSpeaker) {?>
                                <option value="<?php echo $eventSpeaker['id'];?>" <?php echo selected($eventSpeaker['id'], $eventIdSpeaker);?>><?php echo cleanOutput($eventSpeaker['name']);?></option>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
            <tr>
				<td>Why Attend</td>
				<td>
                    <div class="form-group">
                        <textarea class="event-why-attend-block w-100pr h-100" name="why_attend"><?php echo cleanOutput($event['why_attend'] ?? '');?></textarea>
                    </div>
				</td>
			</tr>
			<tr>
                <td>Description</td>
				<td>
                    <div class="form-group">
                        <textarea class="event-description-block validate[required]" name="description"><?php echo cleanOutput($event['description'] ?? '');?></textarea>
                    </div>
                </td>
			</tr>
            <tr>
                <td>Short description</td>
				<td>
                    <div class="form-group">
                        <textarea class="validate[required, maxSize[255]]" name="short_description"><?php echo cleanOutput($event['short_description'] ?? '');?></textarea>
                    </div>
                </td>
			</tr>

            <tr>
                <td>Main photo</td>
                <td>
                    <div class="relative-b">
                        <?php views()->display('new/user/photo_cropper2_view'); ?>
                    </div>
                </td>
            </tr>

            <tr>
                <td>Event gallery</td>
                <td>
                    <div class="relative-b">
                        <?php views()->display('new/user/photo_cropper2_multiple_view'); ?>
                    </div>
                </td>
            </tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<label class="lh-30 vam pull-left mr-10">
			<input class="vam" type="checkbox" name="published" <?php echo checked($event['is_published'] ?? null, 1);?>/>
			Published
        </label>

        <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder;?>">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js';?>"></script>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/tinymce-4-3-10/tinymce.min.js';?>"></script>
<script type="text/javascript">
	$(document).ready(function(){
		tinymce.init({
			selector:'.event-description-block, .event-why-attend-block',
			menubar: false,
			statusbar : false,
			height : 260,
			media_poster: false,
			media_alt_source: false,
			relative_urls: false,
			plugins: [
				"autolink lists link media image"
			],
			dialog_type : "modal",
			toolbar: "undo redo | bold italic underline link | alignleft aligncenter alignright alignjustify | numlist bullist"
		});

        $(".event-tags").select2({ width: '100%', tags: true});

        $('#js-select-speaker').select2({
            width: '100%',
            placeholder: "Select speaker",
        });
    });

    $('.js-datetimepicker').datetimepicker({
        timeFormat: "HH:mm",
        stepMinute: 5,
        controlType: 'select',
        oneLine: true,
        onClose: function () {
            $.timepicker.datetimeRange($('input[name=date_start]'), $('input[name=date_end]'), {});
        }
    });

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

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw();
				}else{
					hideLoader(form);
				}
			}
        });
    }
</script>
