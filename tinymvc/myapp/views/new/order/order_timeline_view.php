
<?php
if(!empty($order['order_summary'])){
    $order_summary = explode('||',$order['order_summary']);
    $order_timeline = array_reverse(json_decode('[' . $order['order_summary'] . ']', true));
}
?>
<table class="main-data-table dataTable">
    <thead>
        <tr>
            <th class="w-130">Date</th>
            <th class="w-100">Member</th>
            <th class="mnw-100">Activity</th>
        </tr>
    </thead>
    <tbody class="tabMessage">
        <?php foreach($order_timeline as $order_log){?>
            <tr>
                <td><?php echo formatDate($order_log['date'], 'm/d/Y H:i:s A');?></td>
                <td><?php echo $order_log['user'];?></td>
                <td>
                    <div class="grid-text">
                        <div class="grid-text__item">
                            <?php echo $order_log['message'];?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php }?>
    </tbody>
</table>