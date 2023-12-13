<form method="post" class="validateModal relative-b">
    <div class="wr-form-content w-700 h-100 pt-20">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
                <tr>
                    <td class="w-100">Users</td>
                    <td>
                        <select name="users[]" class="w-100pr validate[required] select-users"></select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="wr-form-btns clearfix">
        <button class="pull-right btn btn-default" type="submit">
            <span class="ep-icon ep-icon_ok"></span> Save
        </button>
    </div>
</form>

<script>
    $(function () {
        $('.select-users').select2({
            multiple: true,
            data: <?php echo json_encode($select_data); ?>,
            theme: "default ep-select2-h30",
            width: '100%',
            placeholder: 'Select users',
            minimumInputLength: 0
        });
    });

    function modalFormCallBack($form, data_table, submitCallback) {
        var data = $('.select-users').select2('data');
        var dataIds = [];
        var dataObjects = [];

        for (var i = 0; i < data.length; i++) {
            dataIds.push(data[i]['id']);
            dataObjects.push({
                id: data[i]['id'],
                text: data[i]['text']
            });
        }

        submitCallback && submitCallback({
            dataIds: dataIds,
            dataObjects: dataObjects,
            idItem: <?php echo $id_item; ?>,
            type: '<?php echo $type; ?>'
        });
    }
</script>
