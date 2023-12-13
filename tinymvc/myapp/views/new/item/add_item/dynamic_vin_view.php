<div class="js-add-item-wrapper-vin">
	<div class="form-group">
		<label class="input-label <?php echo form_validation_label($validation, 'vin', 'required'); ?>">Vin Decoder</label>

		<div class="input-group">
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__vincode-input")?>
                id="js-add-item-vindecoder"
                class="form-control"
                data-template="validate[<?php echo form_validation_rules($validation, 'vin', 'required'); ?>]"
				type="text"
				name="vin_code"
				placeholder="VIN code"
				value="<?php if (isset($item['vin']['vin_numb'])) { echo $item['vin']['vin_numb']; } ?>"/>
			<?php if (isset($item['vin']['vin_numb'])) { ?>
				<input type="hidden" name="old_vin_code"  value="<?php echo $item['vin']['vin_numb']; ?>"/>
			<?php } ?>
			<div class="input-group-append">
				<button <?php echo addQaUniqueIdentifier("items-my-add__vincode-view-button")?> class="js-add-item-view-vin btn btn-primary call-function<?php echo (isset($vin_info) && !empty($vin_info))?'':' display-n';?>" data-callback="openVinDecode" type="button">View</button>
				<button <?php echo addQaUniqueIdentifier("items-my-add__vincode-decode-button")?> class="btn btn-dark call-function" data-callback="vinDecode" type="button">Decode</button>
			</div>
		</div>
	</div>

	<div id="js-add-item-vinload" class="display-n">
		<?php if (isset($vin_info) && !empty($vin_info)) { ?>
			<table class="vin-table table table--bordered">
				<tbody>
				<?php foreach ($vin_info as $vin_attr) { ?>
					<tr>
						<td class="vin-name"><?php echo $vin_attr['name']; ?></td>
						<td class="vin-value"><?php echo $vin_attr['value']; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
        <?php } ?>

		<?php if(isset($item['vin']['decoded_info'])) { ?>
			<textarea class="display-n" name="vin[decoded_info]"><?php echo $item['vin']['decoded_info']; ?></textarea>
		<?php } ?>
	</div>
</div>

<script>
    function vinDecode() {
        var code = $("#js-add-item-vindecoder").val();
        var $wr = $("#js-add-item-wrapper-vin");
        var $decoded = $("#js-add-item-vinload");
        var $btnView = $(".js-add-item-view-vin");

        $decoded.html('');

        if(code == ''){
            systemMessages("Error: Please enter VIN number.", "error");
            return false;
        }

        if(code.length != 17){
            systemMessages("Error: VIN Number has 17 characters.", "error");
            return false;
        }

        $.ajax({
            type: "POST",
            url: "<?php echo __SITE_URL?>items/vin_decode",
            data: {code:code},
            dataType: 'JSON',
            beforeSend: function(){
                showLoader($wr);
            },
            success: function(resp) {
                hideLoader($wr);

                if(resp.mess_type == 'success'){
                    $decoded.html(resp.html);
                    open_result_modal({
                        title: 'VIN information',
                        content: resp.html,
                        type: 'info',
                        closable: true,
                        buttons: [
                            {
                                label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                                cssClass: "btn btn-light",
                                action: function (dialog) {
                                    dialog.close();
                                },
                            }
                        ]
                    });
                    $btnView.show();
                }else{
                    $btnView.hide();
                    systemMessages( resp.message, resp.mess_type );
                }
            }
        });

        return false;
    };

    function openVinDecode() {
        var $decoded = $("#js-add-item-vinload");

        open_result_modal({
            title: 'VIN information',
            content: $decoded.html(),
            type: 'info',
            closable: true,
            buttons: [
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action: function (dialog) {
                        dialog.close();
                    },
                }
            ]
        });
    }
</script>
