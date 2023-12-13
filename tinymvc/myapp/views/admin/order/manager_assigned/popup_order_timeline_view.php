<?php
if(!empty($order_info['order_summary'])){
    $order_timeline = array_reverse(json_decode('[' . $order_info['order_summary'] . ']', true));
}
?>

<div class="wr-form-content w-700 mh-600">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
        <thead>
            <tr role="row">
                <th class="w-80">Date</th>
                <th class="w-100">Member</th>
                <th class="mnw-100">Actions</th>
            </tr>
        </thead>
        <tbody class="tabMessage">
            <?php foreach($order_timeline as $order_log){?>
                <tr>
                    <td class="tac"><?php echo formatDate($order_log['date'], 'm/d/Y H:i:s A');?></td>
                    <td><?php echo $order_log['user'];?></td>
                    <td><?php echo $order_log['message'];?></td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>
