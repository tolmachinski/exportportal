<form method="post" class="validateModal relative-b">
    <div class="wr-form-content w-650 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tr>
                <td class="w-100">Name</td>
                <td>
                    <input
                        class="w-100pr validate[required, maxSize[50]]"
                        type="text"
                        name="name"
                        value="<?php echo isset($shippingType) ? cleanOutput($shippingType['type_name']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                <td class="w-100">Alias</td>
                <td>
                    <input
                        <?php if(!empty($shippingType['type_alias'])){echo 'disabled="disabled"';} ?>
                        class="w-100pr validate[required, minSize[2], maxSize[100]]"
                        type="text"
                        name="alias"
                        value="<?php echo isset($shippingType) ? cleanOutput($shippingType['type_alias']) : '' ;?>"
                    />
                </td>
            </tr>
            <tr>
                <td class="w-100">Short Description</td>
                <td>
                    <textarea
                        class=" validate[required, maxSize[500]]"
                        type="text"
                        name="short_desc"
                    ><?php echo isset($shippingType) ? cleanOutput($shippingType['type_description']) : '' ;?></textarea>
                </td>
            </tr>
            <tr>
                <td>Full Description</td>
                <td>
                    <textarea class="validate[required, maxSize[2500]] article-text-block" type="text" name="full_desc">
                        <?php echo isset($shippingType) ? cleanOutput($shippingType['full_description']) : '' ;?>
                    </textarea>
                </td>
            </tr>
            <tr>
                <td>Images</td>
                <td>
                    <div id="js-fileupload-shipping-method-wrapper">
                        <div class="flex-card mb-10" name="image">
                        <?php widgetAdminFileUploader(
                                $uploadOptions,
                                $uploadedImages ?? [],
                                null,
                                'shipping-method',
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
        <input type="hidden" name="id" value="<?php echo $shippingType['id_type'] ?? '';?>">
	    <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
    tinymce.init({
		selector:'.article-text-block',
		menubar: false,
		statusbar : false,
		height : 250,
		plugins: ["autolink lists link textcolor"],
		dialog_type : "modal",
		toolbar: "bold italic underline forecolor backcolor link | numlist bullist",
		resize: false
	});

    var modalFormCallBack = function (form, data_table) {
        var $form = $(form);
        var data = $form.serialize();
        var onRequestSuccess = function (data) {
            systemMessages(data.message, data.mess_type);
            if ('success' === data . mess_type) {
                closeFancyBox();
                if (data_table != undefined) {
                    data_table . fnDraw(false);
                }
            }
        };

        postRequest(__site_url + 'shipping_methods/ajax_operations/<?php echo isset($shippingType) ? 'edit' : 'add'; ?>', data, "json")
        .then(onRequestSuccess)
        .catch(onRequestError);
    }

</script>
