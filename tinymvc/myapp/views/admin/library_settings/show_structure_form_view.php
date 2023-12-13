<div class="wr-form-content w-900 mh-600">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <thead>
            <tr>
                <th>Column</th>
                <th>Description</th>
                <th>Example</th>
                <th>Condition</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($config as $key => $value){?>
            <tr>
                <td><?php echo $value['field'];?></td>
                <td><?php echo $value['column_description'];?></td>
                <td><?php echo $value['sample'];?></td>
                <td>
                    <?php echo (!empty($value['rule']['required'])      ? 'required, ':'');?>
                    <?php echo (!empty($value['rule']['minSize'])       ? 'minimum ' . $value['rule']['minSize'] . ' character, ':'');?>
                    <?php echo (!empty($value['rule']['maxSize'])       ? 'maximum ' . $value['rule']['maxSize'] . ' character, ':'')?>
                    <?php echo (!empty($value['rule']['valid_url'])     ? 'url address, ':'');?>
                    <?php echo (!empty($value['rule']['email'])         ? 'email address, ':'');?>
                    <?php echo (!empty($value['rule']['float'])         ? 'only numeric characters':'');?>
                    <?php echo (!empty($value['rule']['alpha_numeric']) ? 'only alpha numeric characters':'');?>
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<div class="wr-form-btns clearfix">
    <div >Allowed format : *.xls, *.xlsx, *.xml, *.csv</div>
    <div class="cur-pointer">Sample file: <span class="call-function" data-file="<?php echo $config_name;?>" data-record-id="<?php echo $id_record;?>" data-callback="dowload_xls_sample">sample_<?php echo $config_name;?>.xls</span></div>
</div>
<iframe src="" id="downloadSample" style="display:none"></iframe>
<script>
    var dowload_xls_sample = function ($this) {
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>library_setting/ajax_library_setting_operation/download_sample',
            data: {file_name: $this.data('file'), record_id: $this.data('record-id')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                if(data.mess_type == 'success'){
                    $('#downloadSample').prop('src', data.src);
                }else{
                    systemMessages( data.message, 'message-' + data.mess_type );
                }
            }
        });
    };
</script>
