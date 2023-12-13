<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__content">
        <table class="table table-hover mt-15">
            <tbody>
                <tr>
                    <td class="w-50"><i class="icon-files-pdf-small w-25 h-25 display-b m-auto"></i></td>
                    <td class="vam">
                        <a href="<?php echo __SITE_URL.'invoices/view_invoice/'.$order['id'].'/full/'.orderNumberOnly($order['id']);?>" target="_blank">Full invoice</a>
                    </td>
                </tr>
                <tr>
                    <td class="w-50"><i class="icon-files-pdf-small w-25 h-25 display-b m-auto"></i></td>
                    <td class="vam">
                        <a href="<?php echo __SITE_URL.'invoices/view_invoice/'.$order['id'].'/ship/'.orderNumberOnly($order['id']);?>" target="_blank">Shipping invoice</a>
                    </td>
                </tr>
                    <?php if($order['order_type'] == 'po'){?>
                        <?php
                            $invoice_map_items = json_decode($invoice['invoice_map'], true);
                        ?>
                        <?php foreach($invoice_map_items as $key=>$map_item){?>
                        <tr>
                            <td class="w-50"><i class="icon-files-pdf-small w-25 h-25 display-b m-auto"></i></td>
                            <td class="vam">
                                <a href="<?php echo __SITE_URL.'invoices/view_invoice/'.$order['id'].'/percent/'.$key.'_'.orderNumberOnly($order['id']);?>" target="_blank"><?php echo $map_item['percent'];?>% of the Full invoice</a>
                            </td>
                        </tr>
                        <?php }?>
                    <?php }?>
            </tbody>
        </table>
    </div>
</div>
