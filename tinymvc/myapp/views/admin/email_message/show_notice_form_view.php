<div class="wr-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <thead>
            <tr>
                <th class="w-80">Status</th>
                <th class="w-150">Staff sent</th>
                <th class="w-150">Appointed staff</th>
                <th class="w-80">Date</th>
                <th>Notice</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($notice as $item){?>
            <tr>
                <td class="tac"><?php echo $item['status'];?></td>
                <td class="tac"><?php echo $item['add_by'];?></td>
                <td class="tac"><?php echo $item['assign_user'];?></td>
                <td class="tac"><?php echo formatDate($item['add_date']);?></td>
                <td><?php echo $item['notice'];?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
