<form class="validateModal relative-b">
    <div class="wr-form-content w-900 mh-500 mt-10">
        <input type="hidden" name="userId" value="<?php echo $userId;?>">
        <div class="row mt-10">
            <div class="col-xs-4">
                <label class="modal-b__label">Matching with product requests</label>
                <select name="leftProductRequests" class="w-250">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-xs-4">
                <label class="modal-b__label">Matching with industries of interest</label>
                <select name="industriesOfInterest" class="w-250">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-xs-4">
                <label class="modal-b__label">Matching with last viewed items</label>
                <select name="lastViewedItems" class="w-250">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
    </div>
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-success call-function" data-callback="exportBuyers" type="button">Export buyers</button>
    </div>
</form>

<script>
var exportBuyers = function(btn) {
    var form = btn.closest('form');
    var iframeUrl = '<?php echo __SITE_URL . 'matchmaking/download_matchmaking_records';?>';
    var fdata = form.serialize();

    $.ajax({
        url: '<?php echo __SITE_URL . 'matchmaking/ajax_operations/validate_export_form';?>',
        type: 'POST',
        dataType: 'json',
        data: fdata,
        beforeSend: function () {
            showLoader(form);
            clearSystemMessages();
        },
        success: function(data){
            hideLoader(form);
            if (data.mess_type == 'success') {
                iframeUrl +='?' + data.validFilters;
                $('iframe#exportBuyers').attr('src', iframeUrl);
                closeFancyBox();
            } else {
                systemMessages( data.message, 'message-' + data.mess_type);
            }
        }
    });
}
</script>
