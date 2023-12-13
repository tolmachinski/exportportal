<div class="col-xs-12">
    <h3 class="titlehdr">Add product fields</h3>
    <form method="post">
        <table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 10px;">
            <tr>
                <td colspan="2">
                    <select name="product">
                        <?php foreach($product as $row) { ?>
                        <option value="<?php echo $row['product_id']?>"><?php echo $row['product_name']?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td width="10%">Field name:</td>
                <td><input type="text" name="attribute" class="w195" /> e.g.: Condition</td>
            </tr>
            <tr>
                <td>Field options:</td>
                <td><input type="text" name="option" class="w195" /> e.g.: Frozen Fresh</td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Add" /></td>
            </tr>
        </table>
    </form>
</div>
