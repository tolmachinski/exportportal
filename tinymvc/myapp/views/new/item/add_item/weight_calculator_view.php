<div class="js-weight-calculator inputs-40">
    <div class="">
        <label class="input-label mt-0 <?php echo form_validation_label($validation, 'dimensions', 'required'); ?>">Dimension</label>
        <div>
            <label class="inline-label custom-radio">
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__metric")?>
                    type="radio"
                    name="dimmeas"
                    value="metric"
                    class="validate[<?php echo form_validation_rules($validation, 'dimensions', 'required'); ?>]"
                    checked>
                <span class="custom-radio__text">Metric (kg,cm)</span>
            </label>
        </div>

        <div>
            <label class="inline-label custom-radio">
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__imperial")?>
                    type="radio"
                    name="dimmeas"
                    class="validate[<?php echo form_validation_rules($validation, 'dimensions', 'required'); ?>]"
                    value="imperial"/>
                <span class="custom-radio__text">Imperial (lb, inch)</span>
            </label>
        </div>
    </div>

    <div class="container-fluid-modal container--p15">
        <div class="row">
            <div class="col-12">
                <label
                    class="input-label <?php echo form_validation_label($validation, 'size', 'required'); ?>"
                >
                    Size LxWxH, cm/inch
                </label>
                <div class="row">
                    <div class="col-12 col-md-4 pb-10-md">
                        <input
                            <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__length")?>
                            type="number"
                            step="0.01"
                            name="lgth"
                            size="4"
                            maxlength="10"
                            value=""
                            class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>]"
                            placeholder="Length"/>
                    </div>
                    <div class="col-12 col-md-4 pb-10-md">
                        <input
                            <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__width")?>
                            type="number"
                            step="0.01"
                            name="wdth"
                            size="4"
                            maxlength="10"
                            value=""
                            class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>]"
                            placeholder="Width"/>
                    </div>
                    <div class="col-12 col-md-4">
                        <input
                            <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__height")?>
                            type="number"
                            step="0.01"
                            name="hght"
                            size="4"
                            maxlength="10"
                            value=""
                            class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>]"
                            placeholder="Height"/>
                    </div>
                    <div class="col-12">
                        <div class="input-info-sub">Insert sizes for one piece</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="input-label <?php echo form_validation_label($validation, 'weight', 'required'); ?>">Weight, kg</label>
                        <input
                            <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator__weight")?>
                            type="number"
                            step="0.01"
                            name="wght"
                            size="4"
                            maxlength="10"
                            value=""
                            class="validate[<?php echo form_validation_rules($validation, 'weight', 'required'); ?>,<?php echo form_validation_rules($validation, 'weight', 'min'); ?>]"
                            placeholder="e.g. 1000.50"/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <label class="input-label">Freight chargeable weight</label>
    <div class="js-calc-weight-rezultat display-b">
        <div><span class='txt-gray'>Cargo size:</span> 0x0x0</div>
        <div><span class='txt-gray'>Real weight:</span> 0</div>
        <div><span class='txt-gray'>Volume weight:</span> 0</div>
        <div><span class='txt-gray'>Chargeable weight:</span> 0</div>
    </div>
</div>

<script>
    var calculate_mortgage = function(obj){
        var $this = $(obj);
        var $form = $('.modal-dialog .js-weight-calculator'),
            form_dimension_val = $form.find('input[name="dimmeas"]:checked').val(),
            form_lgth = $form.find('input[name="lgth"]'),
            form_wdth = $form.find('input[name="wdth"]'),
            form_hght = $form.find('input[name="hght"]'),
            form_wght = $form.find('input[name="wght"]');
            form_lgth_val = Math.ceil100(floatval(form_lgth.val()), -2);
            form_wdth_val = Math.ceil100(floatval(form_wdth.val()), -2);
            form_hght_val = Math.ceil100(floatval(form_hght.val()), -2);
            form_wght_val = Math.ceil100(floatval(form_wght.val()), -2);
        var form_errors = [];
        if (form_lgth_val <= 0){
            form_errors.push("Weight Calculator <strong>Lenght</strong> is required.");
            form_lgth.val("");
            form_lgth.focus();
        }

        if (form_wdth_val <= 0){
            form_errors.push("Weight Calculator <strong>Width</strong> is required.");
            form_wdth.val("");
            form_wdth.focus();
        }

        if (form_hght_val <= 0){
            form_errors.push("Weight Calculator <strong>Height</strong> is required.");
            form_hght.val("");
            form_hght.focus();
        }

        if (form_wght_val <= 0){
            form_errors.push("Weight Calculator <strong>Weight</strong> is required.");
            form_wght.val("");
            form_wght.focus();
        }

        if(form_errors.length > 0){
            systemMessages(form_errors.reverse(), "error");

            return false;
        }

        form_lgth.val(form_lgth_val);
        form_wdth.val(form_wdth_val);
        form_hght.val(form_hght_val);
        form_wght.val(form_wght_val);

        var aird, airwround, air, oceand, oceanw, ocean, oceane, vol, bigvol, bigreal, txtair, dimunit, mydims;
        var dims = (form_lgth_val * form_wdth_val * form_hght_val);
        var pieces = 1;
        var weight = (form_wght_val);
        var bigvol = ("The charges will be based on the product's Volume weight.");
        var bigreal = ("The charges will be based on the product's Real weight.");
        var airw = (weight * pieces);
        // Air Calculations
        if (form_dimension_val == 'metric') {
            aird = (dims / 6000 * pieces);
            mydims = dims;
            dimunit = "kgs."
        }else {
            aird = (dims / 166 * pieces);
            mydims = dims * 16.38;
            dimunit = "lbs."
        }

        in_db = Math.ceil100(mydims / 6000 * pieces, -2);

        if (aird >= airw) {
            txtair = bigvol
        }else {
            txtair = bigreal;
            in_db = airw;
        }

        air = Math.ceil100(Math.max(aird,airw), -2);
        vol = Math.ceil100(aird, -2);
        airwround = Math.ceil100(airw, -2);

        $form.find('.js-calc-weight-rezultat').html(
                "<div><span class='txt-gray'>Cargo size:</span> " + form_lgth_val + "x" + form_wdth_val + "x" + form_hght_val + "</div>" +
                "<div><span class='txt-gray'>Real weight:</span> " + airwround + " " + dimunit + "</div>" +
                "<div><span class='txt-gray'>Volume weight:</span> " + vol + " " + dimunit + "<br>" + txtair + "</div>" +
                "<div><span class='txt-gray'>Chargeable weight:</span> " + air + " " + dimunit + "</div>"
            );
    }
</script>
