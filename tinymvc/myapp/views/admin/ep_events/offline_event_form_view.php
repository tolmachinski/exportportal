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
				<td class="w-150">Category</td>
				<td>
                    <div class="form-group">
                        <select class="w-100pr validate[required]" name="category">
                            <?php $eventIdCategory = $event['id_category'] ?? null;?>
                            <option value="" <?php echo selected(null, $eventIdCategory);?>>Select category</option>

                            <?php foreach ($eventCategories as $eventCategory) {?>
                                <option value="<?php echo $eventCategory['id'];?>" <?php echo selected($eventCategory['id'], $eventIdCategory);?>><?php echo $eventCategory['name'];?></option>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td class="w-150">Country</td>
				<td>
                    <div class="form-group">
                        <select class="w-100pr validate[required]" name="country" id="select_country">
                            <option value="">Select country</option>
                            <?php echo getCountrySelectOptions($location['countries'], $event['id_country'] ?? null, ['include_default_option' => false]);?>
                        </select>
                    </div>
				</td>
			</tr>
            <tr>
				<td>State</td>
				<td>
                    <div class="form-group">
                        <select class="validate[required]" name="state" id="select_state">
                            <option value="">Select state / region</option>
                            <?php if (!empty($location['states'])) {?>
                                <?php foreach($location['states'] as $state){?>
                                    <option value="<?php echo $state['id'];?>" <?php echo selected($event['id_state'], $state['id']);?>>
                                        <?php echo $state['state'];?>
                                    </option>
                                <?php } ?>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td>City</td>
				<td class="wr-select2-h35">
                    <div class="form-group">
                        <select name="city" class="validate[required]" id="select_city">
                            <option value="">Select country first</option>
                            <?php if (!empty($location['city'])){ ?>
                                <option value="<?php echo $location['city']['id'];?>" selected>
                                    <?php echo $location['city']['city'];?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td>Address</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required, maxSize[200]]" type="text" name="address" placeholder="Address" value="<?php echo $event['address'] ?? '';?>"/>
                    </div>
                </td>
			</tr>
            <tr>
				<td>Ticket price</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required, custom[number]]" type="text" name="ticket_price" placeholder="Ticket price" value="<?php echo $event['ticket_price'] ?? '';?>"/>
                    </div>
                </td>
			</tr>
			<tr>
				<td>Max nr. of participants</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required, min[1], custom[integer]]" type="text" name="nr_of_participants" placeholder="Nr. of participants" value="<?php echo $event['nr_of_participants'] ?? '';?>"/>
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
				<td>Why Attend</td>
				<td>
                    <div class="form-group">
                        <textarea class="w-100pr h-100 event-why-attend-block" name="why_attend" data-max="500"><?php echo cleanOutput($event['why_attend'] ?? '');?></textarea>
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
				<td>Partners</td>
				<td>
                    <div class="form-group">
                        <select name="partners[]" class="form-control" id="select_partners" multiple>
                            <option></option>
                            <?php if (!empty($eventPartners)) {?>
                                <?php foreach ($eventPartners as $partner) {?>
                                    <option value="<?php echo $partner['id'];?>" <?php echo isset($event['partners'][$partner['id']]) ? 'selected' : '';?>><?php echo cleanOutput($partner['name']);?></option>
                                <?php }?>
                            <?php }?>
                        </select>
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
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js';?>"></script>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/tinymce-4-3-10/tinymce.min.js';?>"></script>
<script type="text/javascript">
    var selectState = <?php echo (int) ($event['id_state'] ?? 0);?>, selectCity = $("#select_city");

	$(document).ready(function(){
        initSelectCity(selectCity);

        $('#select_country').on('change', function(){
            selectCountry($(this), '#select_state');
            selectState = 0;
            selectCity.empty().trigger("change").prop("disabled", true);
        });

        $('#select_state').on('change', function(){
            selectState = this.value;
			selectCity.empty().trigger("change").prop("disabled", false);

			if (selectState != '' || selectState != 0) {
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
			} else {
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
				selectCity.prop("disabled", true);
            }

			selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
        });

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

        $('#select_partners').select2({
            width: '100%',
            multiple: true,
            placeholder: "Selected partners",
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
						data_table.fnDraw(false);
				}else{
					hideLoader(form);
				}
			}
        });
    }
</script>
