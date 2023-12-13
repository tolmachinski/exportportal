<div class="wr-form-content w-1000 mh-700">
    <?php if(!empty($logs)){ ?>
        <table class="table table-striped vam-table">
            <?php foreach($logs as $logElement){ ?>
                <tr>
                    <td class="w-150">
                        <div>
                            <?php echo $usersInfo[$logElement['user_id']]['user_name']; ?>
                        </div>
                        <div class="txt-gray">
                            <?php echo getDateFormat($logElement['log_date']); ?>
                        </div>
                    </td>
                    <td>
                        <table class="table mb-0">
                            <?php foreach($logElement['updated'] as $updatedKey => $updatedItem){ ?>
                                <tr>
                                    <?php if($updatedKey == 'preview_template_data'){ ?>
                                        <td class="w-100">
                                            <?php echo $updatedKey; ?>
                                        </td>
                                        <td>
                                        <table class="table mb-0">
                                            <?php foreach($updatedItem as $updatedItemElement){ ?>
                                                <tr>
                                                    <td>
                                                        <?php echo nl2br(htmlspecialchars($updatedItemElement)); ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                        </td>
                                    <?php }else{ ?>
                                        <td class="w-100">
                                            <?php echo $updatedKey; ?>
                                        </td>
                                        <td class="pl-15">
                                            <?php echo nl2br(htmlspecialchars($updatedItem)); ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php }else{?>
        <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>No logs.</span></div>
    <?php }?>
</div>
