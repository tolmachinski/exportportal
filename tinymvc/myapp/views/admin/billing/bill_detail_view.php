<div class="wr-form-content w-700 mh-600">
    <?php if(!empty($bill_info['note'])){?>
        <?php $bill_notes = array_reverse(json_decode('['.$bill_info['note'].']', true));?>
        <table class="data table-striped table-bordered mt-15 w-100pr">
            <thead>
                <tr>
                    <th class="w-150">Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bill_notes as $bill_note){?>
                <tr>
                    <td class="w-115"><?php echo formatDate($bill_note['date_note'], 'm/d/Y H:i');?></td>
                    <td><?php echo $bill_note['note'];?></td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    <?php }?>
</div>
<div class="wr-form-btns clearfix">
    <a title="Close" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Close</a>
</div>
