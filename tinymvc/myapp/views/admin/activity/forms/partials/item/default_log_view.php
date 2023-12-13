<?php if(!empty($changes) && !empty($changes['keys'])) { ?>
    <div class="row">
        <div class="col-xs-12 mb-15">
            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered table-fixed w-100pr mt-15 mb-15 vam-table" style="table-layout:fixed;">
                <thead>
                    <tr role="row">
                        <th class="w-20pr" rowspan="2">Field</th>
                        <th colspan="2">Changes</th>
                    </tr>
                    <tr role="row">
                        <th class="w-50pr">Old</th>
                        <th class="w-50pr">Current</th>
                    </tr>
                </thead>
                <tbody class="tabMessage">
                    <?php foreach ($changes['keys'] as $key => $key_name) { ?>
                        <tr>
                            <td class="w-20pr"><?php echo $key_name; ?></td>
                            <?php if(in_array($key, array('final_price', 'item_height', 'item_length', 'item_width', 'price', 'weight'))) { ?>
                                <td class="w-40pr"><?php echo isset($changes['old']) && isset($changes['old'][$key]) ? number_format($changes['old'][$key], 2, '.', '') : ''; ?></td>
                                <td class="w-40pr"><?php echo isset($changes['current']) && isset($changes['current'][$key]) ? number_format($changes['current'][$key], 2, '.', '') : ''; ?></td>
                            <?php } else { ?>
                                <td class="w-40pr"><?php echo isset($changes['old']) && isset($changes['old'][$key]) ? $changes['old'][$key] : ''; ?></td>
                                <td class="w-40pr"><?php echo isset($changes['current']) && isset($changes['current'][$key]) ? $changes['current'][$key] : ''; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
