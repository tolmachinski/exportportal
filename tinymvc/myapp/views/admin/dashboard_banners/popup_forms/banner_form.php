<script type="text/javascript" src="<?php echo __FILES_URL; ?>public/plug_admin/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js"></script>
<form method="post" class="validateModal relative-b">
    <div class="wr-form-content w-650 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tr>
                <td class="w-100">Subitle</td>
                <td>
                    <input
                        class="w-100pr validate[required, maxSize[255]]"
                        type="text"
                        name="subtitle"
                        value="<?php echo isset($dashboardBanner) ? cleanOutput($dashboardBanner['subtitle']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                <td class="w-100">Title</td>
                <td>
                    <input
                        class="w-100pr validate[required, maxSize[255]]"
                        type="text"
                        name="title"
                        value="<?php echo isset($dashboardBanner) ? cleanOutput($dashboardBanner['title']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                <td>Link</td>
                <td>
                    <input
                        class="w-100pr validate[required, maxSize[255], custom[url]]"
                        type="text"
                        name="link"
                        value="<?php echo isset($dashboardBanner) ? cleanOutput($dashboardBanner['url']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                <td>Text Button</td>
                <td>
                    <input
                        class="w-100pr validate[required, maxSize[50]]"
                        type="text"
                        name="button"
                        value="<?php echo isset($dashboardBanner) ? cleanOutput($dashboardBanner['button_text']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                    <td>Groups :</td>
                    <td>
                        <div class="form-group" >
                            <select name="user_groups[]" data-title="Groups" class="js-select-user-groups-list w-100pr " multiple="multiple">
                                <?php foreach ($userGroups as $userGroup) {?>
                                    <option value="<?php echo $userGroup['idgroup']; ?>" <?php echo in_array($userGroup['idgroup'], $dashboardBanner['user_groups'] ?? []) ? 'selected' : '';?>><?php echo $userGroup['gr_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </td>
                </tr>
            <tr>
                <td>Images</td>
                <td>
                    <div id="js-fileupload-banner-wrapper">
                        <div class="flex-card mb-10" name="image">
                            <?php widgetAdminFileUploader(
                                $uploadOptions,
                                $uploadedImages ?? [],
                                null,
                                'banners',
                                'file',
                                true,
                                true,
                                false,
                                false,
                                true,
                                'image'
                            );
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
       <input type="hidden" name="id" value="<?php echo $dashboardBanner['id'] ?? '';?>">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
    var selectGroups;
    $(document).ready(function() {
		$('.datepicker').datepicker();
        selectGroups = $('.js-select-user-groups-list').multipleSelect({
			width: '100%',
			placeholder: translate_js({plug:'multipleSelect', text: 'placeholder_users'}),
			selectAllText: translate_js({plug:'multipleSelect', text: 'select_all_text'}),
			allSelected: translate_js({plug:'multipleSelect', text: 'all_selected'}),
			countSelected: translate_js({plug:'multipleSelect', text: 'count_selected'}),
			noMatchesFound: translate_js({plug:'multipleSelect', text: 'no_matches_found'})
		});

        selectGroups.next('.ms-parent').addClass('validate[required]')
            .setValHookType('groupsSelect');
        $.valHooks.groupsSelect  = {
            get: function (el) {
                return $(selectGroups[0]).val() || [];
            },
            set: function (el, val) {
                $(selectGroups[0]).val(val);
            }
        };
    })

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL; ?>dashboard_banner/ajax_operations/<?php echo isset($dashboardBanner) ? 'edit' : 'add'; ?>_banner',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                hideLoader($form);

                if(data.mess_type == 'success'){
                    closeFancyBox();

                    if(data_table != undefined)
                        data_table.fnDraw(false);
                }
            }
        });
    }

</script>
