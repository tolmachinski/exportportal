<script type="text/javascript">
$(document).ready(function(){


    dtStatistic = $('#dtStatistic').dataTable( {
        "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?php echo __SITE_URL?>user_statistic/ajax_get_statistic_dt",
        "sServerMethod": "POST",
        "aoColumnDefs": [
            { "sClass": "w-60 tac", "aTargets": ['dt_name'], "mData": "dt_name" },
            <?php foreach($groups as $group){?>
            { "sClass": "tac", "aTargets": ['dt_group<?php echo $group['idgroup'];?>'], "mData": "dt_group<?php echo $group['idgroup'];?>", bSortable: false },
            <?php }?>
            { "sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", bSortable: false },
        ],
        "sPaginationType": "full_numbers",
        "fnDrawCallback": function( oSettings ) {

        }
    });

    $('#dtStatistic').on('click', '.btn-change', function(e){
        var $this = $(this);
        var type = $this.data('type');
        var column = $this.data('column');
        var group = $this.data('group');
        var op;

        if(type == 'to_en')
            op = 'enable';
        else
            op = 'disable';

        $.ajax({
            type: 'POST',
            url: "user_statistic/ajax_columns_operation/"  + op,
            data: {idgroup : group, column:column},
            dataType: 'JSON',
            success: function(json){
                if(json.mess_type == 'success'){
                    dtStatistic.fnDraw(false);
                }
            }
        });

        e.preventDefault();
     });

    delete_statisctic = function(obj){
        var $this = $(obj);
        $.post(
            'user_statistic/ajax_columns_operation/delete',
            {'column': $this.data('column')},
            function(json){
                if(json.mess_type == 'success'){
                    $this.parents('tr').remove();
                }
                systemMessages(json.message, 'message-' + json.mess_type);
            },
            'JSON'
        );
    }

});
</script>
<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
            Statistics parameters for Users
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" id="add_column" href="<?php echo __SITE_URL;?>user_statistic/popup_forms/add_statistic" title="Add statistic" data-table="dtStatistic" data-title="Add statistic"></a>
        </div>
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" id="dtStatistic">
            <thead>
                <tr>
                    <th class="first dt_name w-300">Parameter/Groups</th>
                    <?php foreach($groups as $group){?>
                    <th class="dt_group<?php echo $group['idgroup'];?>"><?php echo $group['gr_name']?></th>
                    <?php }?>
                    <th class="first dt_actions w-70">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
