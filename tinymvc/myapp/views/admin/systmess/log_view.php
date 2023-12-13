
<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
            <?php foreach($logs as $logKey => $log){?>
            <tr>
                <td>
                    <?php echo $logKey;?>
                </td>
                <td>
                    <?php if(is_array($log)){
                        foreach($log as $oneLog){
                            echo $oneLog . '<br/>';
                        }
                    }else{
                        echo $log;
                    }?>
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
