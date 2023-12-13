<form class="validateModal relative-b">
    <div class="wr-form-content w-900 mh-500 mt-10">
        <input type="hidden" name="userId" value="<?php echo $userId;?>">
        <div class="row mt-10">
            <div class="col-xs-4">
                <label class="modal-b__label">Certified sellers</label>
                <select name="certifiedSellers" class="w-250">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-xs-4">
                <label class="modal-b__label">Has B2B Requests</label>
                <select name="hasB2bRequests" class="w-250">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
    </div>
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-success call-function" data-callback="exportSellers" type="button">Export sellers</button>
    </div>
</form>

<script>
var exportSellers = function(btn) {
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
                $('iframe#exportSellers').attr('src', iframeUrl);
                closeFancyBox();
            } else {
                systemMessages( data.message, 'message-' + data.mess_type);
            }
        }
    });
}
</script>
