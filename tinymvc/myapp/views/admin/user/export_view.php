<form class="validateModal relative-b">
    <div class="wr-form-content w-900 mh-500 mt-10">
        <input id="export-filename" type="hidden" name="filename">
        <div class="row pt-20">
            <div class="col-xs-6">
                <div>
                    <label class="modal-b__label">Select user group</label>
                    <select class="validate[required] h-110" multiple name="groups[]" id="user-group-select">
                        <?php foreach ($groups as $group) { ?>
                            <option data-type="<?php echo $group['gr_type']; ?>" value="<?php echo $group['idgroup']; ?>"><?php echo $group['gr_name']; ?></option>
                        <?php } ?>
                    </select>
                    <small>Use CTRL to select multiple</small>
                </div>

                <div class="mt-20">
                    <label class="modal-b__label">Select country</label>
                    <select name="country">
                        <option>All countries</option>
                        <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo $country['id']; ?>"><?php echo $country['country']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mt-20">
                    <label id="select-date-from" class="modal-b__label">Select registration date from-to</label>
                    <div class="input-group">
                        <input class="form-control datepicker" type="text" name="reg_from" placeholder="From" value=""/>
                        <div class="input-group-addon">-</div>
                        <input class="form-control datepicker" type="text" name="reg_to" placeholder="To" value=""/>
                    </div>
                </div>

                <div class="mt-20">
                    <label class="modal-b__label">Select status</label>
                    <select name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statuses as $status) { ?>
                            <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                        <?php } ?>
                    </select>
                </div>

            </div>
            <div class="col-xs-6">
                <div class="mt-20">
                    <label class="modal-b__label">Restricted at</label>
                    <div class="input-group">
                        <input class="form-control datepicker" type="text" name="restricted_from" placeholder="From" value=""/>
                        <div class="input-group-addon">-</div>
                        <input class="form-control datepicker" type="text" name="restricted_to" placeholder="To" value=""/>
                    </div>
                </div>

                <div class="mt-20">
                    <label class="modal-b__label">Blocked at</label>
                    <div class="input-group">
                        <input class="form-control datepicker" type="text" name="blocked_from" placeholder="From" value=""/>
                        <div class="input-group-addon">-</div>
                        <input class="form-control datepicker" type="text" name="blocked_to" placeholder="To" value=""/>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 mt-20">
                <label style="display: none;" id="select-fields-title" class="modal-b__label">Select fields</label>
                <ul class="h-195 overflow-y-a" id="user-fields"></ul>
            </div>
        </div>

    </div>
    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-success call-function" data-callback="export_link" type="button">Export users</button>
    </div>
</form>
<script>
    window.userTypes = <?php echo json_encode($types); ?>;

    $('#user-group-select').on('change', function () {
        var html = '',
            fields = [],
            selectedGroups = $(this).val();

        if (selectedGroups) {
            var filename = [];
            $('#select-fields-title').show();
            for (var i = 0; i < selectedGroups.length; i++) {
                var $option = $('option[value="' + selectedGroups[i] + '"]', $(this)),
                    type = $option.data('type'),
                    typeFields = window.userTypes[type];

                filename.push($option.text().toLowerCase().replace(/\s+/, '-'));

                for (var t = 0; t < typeFields.length; t++) {
                    if (fields.indexOf(typeFields[t]) !== -1) continue;
                    fields.push(typeFields[t]);
                }
            }

            $('#export-filename').val(filename.join('_'));

            for (var j = 0; j < fields.length; j++) {
                html += '<li><label><input name="fields[]" class="mb-5" checked value="' + fields[j] + '" type="checkbox"> ' + fields[j] + '</label></li>';
            }
        } else {
            $('#select-fields-title').hide();
        }

        $('#user-fields').html(html);
    });

    var export_link = function(btn){
        var $form = btn.closest('form');
        var iframe_url = '<?php echo __SITE_URL ?>users/export_action/download';
        var fdata = $form.serialize();
        $.ajax({
            url: '<?php echo __SITE_URL ?>users/export_action/check',
            type: 'POST',
            dataType: 'json',
            data: fdata,
            beforeSend: function () {
                showLoader($form);
                clearSystemMessages();
            },
            success: function(data){
                hideLoader($form);
                if(data.mess_type == 'success'){
                    $('iframe#download_exported_file').attr('src', iframe_url+'?'+fdata);
                    closeFancyBox();
                } else{
                    systemMessages( data.message, 'message-' + data.mess_type );
                }
            }
        });
    }

    $(".datepicker").datepicker();

    $(".datepicker").on('change', function(element){
        switch ($(this).attr('name')) {
            case 'reg_from':
                $('input[name="reg_to"]').datepicker("option", "minDate", $(this).datepicker("getDate"));
                break;
            case 'reg_to':
                $('input[name="reg_from"]').datepicker("option", "maxDate", $(this).datepicker("getDate"));
                break;
            case 'restricted_from':
                $('input[name="restricted_to"]').datepicker("option", "minDate", $(this).datepicker("getDate"));
                break;
            case 'restricted_to':
                $('input[name="restricted_from"]').datepicker("option", "maxDate", $(this).datepicker("getDate"));
                break;
            case 'blocked_from':
                $('input[name="blocked_to"]').datepicker("option", "minDate", $(this).datepicker("getDate"));
                break;
            case 'blocked_to':
                $('input[name="blocked_from"]').datepicker("option", "maxDate", $(this).datepicker("getDate"));
                break;
        }
    });
</script>
