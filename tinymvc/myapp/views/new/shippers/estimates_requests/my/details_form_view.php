<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__content">
        <div class="container-fluid-modal">
            <div class="row mt-10 start_request">
                <div class="col-12">
                    <h2><?php echo $estimate['group_title']; ?></h2>
                </div>

                <div class="col-12">
                    <label class="input-label txt-gray"><?php echo translate("shipping_estimates_dashboard_modal_field_shipping_address_label_text"); ?></label>
                    <div class="row">
                        <div class="col-12 col-lg-2">
                            <strong><?php echo translate("shipping_estimates_dashboard_modal_field_shipping_address_from_label_text"); ?></strong>
                        </div>
                        <div class="col-12 col-lg-10"><?php echo cleanOutput($departure); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-lg-2">
                            <strong><?php echo translate("shipping_estimates_dashboard_modal_field_shipping_address_to_label_text"); ?></strong>
                        </div>
                        <div class="col-12 col-lg-10"><?php echo cleanOutput($destination); ?></div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <label class="input-label txt-gray"><?php echo translate("shipping_estimates_dashboard_modal_field_seller_label_text"); ?></label>
                    <?php tmvc::instance()->controller->view->display('new/directory/list_item_view', array('item' => $company)); ?>
                </div>

                <?php if (check_group_type('Shipper')) { ?>
                    <div class="col-12 col-lg-8">
                        <label class="input-label txt-gray"><?php echo translate("shipping_estimates_dashboard_modal_field_buyer_label_text"); ?></label>
                        <a href="<?php echo $buyer['link'] ?>" target="_blank" ><?php echo $buyer['full_name'] ?></a>
                    </div>
                <?php } ?>

                <div class="col-12">
                    <label class="input-label txt-gray"><?php echo translate("shipping_estimates_dashboard_modal_field_items_label_text"); ?></label>
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="w-100">
                                    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_modal_field_quantity_label_text"); ?></label>
                                </th>
                                <th>
                                    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_modal_field_item_label_text"); ?></label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item){?>
                                <tr>
                                    <td class="vam">
                                        <?php echo arrayGet($item, 'quantity', 0); ?>
                                    </td>
                                    <td class="vam">
                                        <a href="<?php echo makeItemUrl($item['id'], $item['title']); ?>" target="_blank">
                                            <?php echo cleanOutput($item['title']); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>

                <div class="col-12">
                    <label class="input-label txt-gray"><?php echo translate("shipping_estimates_dashboard_modal_field_comment_label_text"); ?></label>
                    <div>
                        <?php echo null === arrayGet($estimate, 'comment_buyer') ? '&mdash;' : arrayGet($estimate, 'comment_buyer'); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
