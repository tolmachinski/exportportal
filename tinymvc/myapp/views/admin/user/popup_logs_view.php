<div class="wr-form-content w-700">
    <div class="row mh-400 overflow-y-a pt-15 pb-15">
        <div class="col-xs-12">
            <table class="data table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="w-100">Date</th>
                        <th class="w-100 tac">Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)){?>
                        <?php foreach($logs as $log){?>
                            <tr>
                                <td class="w-100"><?php echo getDateFormat($log['date_log']); ?></td>
                                <td class="w-100 tac"><?php echo ucfirst($log['type']); ?></td>
                                <td><?php echo $log['message']; ?></td>
                            </tr>
                        <?php }?>
                    <?php } else{?>
                        <tr>
                            <td colspan="3">No logs for this date</td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="wr-form-btns clearfix">
    <button class="pull-right btn btn-primary call-function" data-callback="exportActivity" type="button">Download logs</button>
    <iframe src="" id="js-download-report" class="display-n"></iframe>
</div>
<script type="text/template" id="templates--table-rows">
    <tr>
        <td>{{date_log}}</td>
        <td>{{type}}</td>
        <td>{{message}}</td>
    </tr>
</script>

<script>
    var exportActivity = function(){
        var url = '<?php echo __SITE_URL . "users/download_activity_report/{$idUser}";?>';
        $('#js-download-report').attr('src', url);
    }
</script>
