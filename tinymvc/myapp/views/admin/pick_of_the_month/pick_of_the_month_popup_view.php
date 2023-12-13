<div class="wr-modal-flex inputs-40">
    <?php if(!empty($infoMessage)){ ?>
    <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo $infoMessage; ?></span></div>
    <?php } ?>
    <?php if(!empty($warningMessage)){ ?>
    <div class="warning-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo $warningMessage; ?></span></div>
    <?php } ?>
    <form class="modal-flex__form validateModal" autocomplete="off">
        <div class="modal-flex__content">
           <label class="input-label">Date from - to</label>
            <div class="input-group">
                <input class="form-control validate[required]" type="text" data-title="Date from" name="start_date" id="pick_start_date" placeholder="From">
                <div class="input-group-addon">-</div>
                <input class="form-control validate[required]" type="text" data-title="Date to" name="end_date" id="pick_end_date" placeholder="To">
			</div>
            <div class="form-group">
                <label class="input-label">Comment</label>
                <textarea class="" name="comment" placeholder="Comment"></textarea>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">

                <?php if(isset($idItem)){?>
                    <input type="hidden" name="id_item" value="<?php echo $idItem; ?>">
                <?php } if(isset($idCompany)){ ?>
                    <input type="hidden" name="id_company" value="<?php echo $idCompany; ?>">
                <?php } ?>

                <button class="btn btn-primary" type="submit">Submit</button>
            </div>
        </div>
    </form>
</div>
<script>
    var type = '<?php echo $type; ?>';

    $(function(){
        initDateJob();
    });

function initDateJob(){

	var dateFormat = "mm/dd/yy";
    var fromPicker = $("#pick_start_date").datepicker().on("change", function() {
		toPicker.datepicker("option", "minDate", getDate(this));
	});
    var toPicker = $("#pick_end_date").datepicker().on("change", function() {
		fromPicker.datepicker("option", "maxDate", getDate(this));
	});

    function getDate( element ) {
        var date;
        try {
            date = $.datepicker.parseDate(dateFormat, element.value);
        } catch(error) {
            date = null;
        }

        return date;
    }
}

function modalFormCallBack(form)
{
	var formData = form.serialize();
    postRequest(__site_url + 'pick_of_the_month/ajax_operations/add/' + type + '/', form.serialize(), "json")
        .then(function (response) {
            systemMessages(response.message, response.mess_type);
            closeFancyboxPopup();
            callFunction('onPickOfTheMonthEnabled', data);
        })
        .catch(onRequestError)
        .finally(function () { })
}
</script>
