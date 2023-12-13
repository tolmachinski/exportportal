<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content">
            <ul class="nav nav-tabs nav--borders" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#general_dispute-info" aria-controls="title" role="tab" data-toggle="tab">Main information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#attachments_dispute-info" aria-controls="title" role="tab" data-toggle="tab">Attachments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#timeline_dispute-info" aria-controls="title" role="tab" data-toggle="tab">Timeline</a>
                </li>
            </ul>

            <div class="tab-content tab-content--borders">
                <div role="tabpanel" class="tab-pane fade show active" id="general_dispute-info">
                    <div class="container-fluid-modal">
                        <div class="row">
                            <div class="col-12 mb-15">
                                <label class="input-label mt-0">Order</label>
                                <a href="<?php __SITE_URL;?>order/my/order_number/<?php echo orderNumberOnly($dispute['id_order']);?>" target="_blank">
                                    <?php echo orderNumber($dispute['id_order']);?>
                                </a>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="input-label mt-0">Created</label>
                                <?php echo getDateFormatIfNotEmpty($dispute['date_time']); ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="input-label mt-0">Updated</label>
                                <?php if($dispute['change_date'] !== $dispute['date_time']) { ?>
                                    <?php echo getDateFormatIfNotEmpty($dispute['change_date']); ?>
                                <?php } else { ?>
                                    &mdash;
                                <?php } ?>
                            </div>

                            <?php if (isset($ordered_item)) { ?>
                                <div class="col-12">
                                    <label class="input-label">Item</label>
                                    <a href="<?php echo __SITE_URL . "items/ordered/" . strForURL($ordered_item['title']) . '-' . $ordered_item['id_ordered_item']; ?>" target="_blank">
                                        <?php echo cleanOutput($ordered_item['title']); ?>
                                    </a>
                                </div>
                            <?php } ?>

                            <div class="col-12 col-md-6">
                                <label class="input-label">Requested refund amount</label>
                                <?php $request_amount = priceToUsdMoney($dispute['money_back_request']); ?>
                                <?php if ($request_amount->greaterThan(\Money\Money::USD(0))) { ?>
                                    $<?php echo get_price($request_amount, false); ?>
                                <?php } else { ?>
                                    &mdash;
                                <?php } ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="input-label">Confirmed refund amount</label>
                                <?php $refund_amount = priceToUsdMoney($dispute['money_back']); ?>
                                <?php if ($refund_amount->greaterThan(\Money\Money::USD(0))) { ?>
                                    $<?php echo get_price($refund_amount, false); ?>
                                <?php } else { ?>
                                    &mdash;
                                <?php } ?>
                            </div>

                            <div class="col-12">
                                <label class="input-label">Reason</label>
                                <?php echo cleanOutput($dispute['comment']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="attachments_dispute-info">
                    <div class="container-fluid-modal">
                        <div class="row">
                            <?php if(!empty($users)){?>
                                <?php foreach($users as $id_user => $user){?>
                                    <?php if(!empty($dispute['photos'][$id_user]) || !empty($dispute['videos'][$id_user])){?>
                                        <div class="col-12">
                                            <label class="input-label mt-0"><?php echo $user['user_name'];?></label>
                                            <div class="fileupload mt-10">
                                                <?php if (!empty($dispute['photos'][$id_user])) { ?>
                                                    <?php foreach($dispute['photos'][$id_user] as $photo) { ?>
                                                        <div class="fileupload-item image-dialog-wr">
                                                            <a class="fileupload-item__image image-dialog" data-img="<?php echo $photo['imageLink'];?>">
                                                                <span class="link">
                                                                    <img class="image" src="<?php echo $photo['imageThumb'];?>">
                                                                </span>
                                                            </a>
                                                            <?php if (!in_array($dispute['status'], array('closed', 'canceled'))) { ?>
                                                                <div class="fileupload-item__actions">
                                                                    <a class="btn btn-dark call-function"
                                                                        href="<?php echo __SITE_URL . "dispute/download_image/{$dispute['id']}/{$photo['name']}";?>"
                                                                        data-callback="onDownloadFile"
                                                                        title="Download">
                                                                        Download
                                                                    </a>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                <?php } ?>

                                                <?php if (!empty($dispute['videos'][$id_user])) { ?>
                                                    <?php foreach($dispute['videos'][$id_user] as $video) {?>
                                                        <?php $img_url = $video['imageLink']; ?>
                                                        <div class="fileupload-item">
                                                            <div class="fileupload-item__image">
                                                                <a class="link spersonal-pictures__img" href="<?php echo getDirectVideoLink($video['id'], $video['type']); ?>" target="_blank">
                                                                    <div class="video-play">
                                                                        <div class="video-play__circle"></div>
                                                                        <i class="ep-icon ep-icon_videos"></i>
                                                                    </div>
                                                                    <img class="image" src="<?php echo $img_url; ?>">
                                                                </a>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="timeline_dispute-info">
                    <table class="main-data-table dataTable mt-15">
                        <thead>
                            <tr>
                                <th class="w-130">Date</th>
                                <th class="w-100">Member</th>
                                <th class="mnw-100">Activity</th>
                            </tr>
                        </thead>
                        <tbody class="tabMessage">
                            <?php if (!empty($dispute['timeline'])) { ?>
                                <?php foreach($dispute['timeline'] as $notice) { ?>
                                    <tr>
                                        <td><?php echo $notice['add_date']; ?></td>
                                        <td><?php echo $notice['add_by']; ?></td>
                                        <td>
                                            <div class="grid-text">
                                                <div class="grid-text__item">
                                                    <div class="txt-medium"><?php echo $notice['title']; ?></div>
                                                    <?php echo $notice['notice']; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
	</div>
</div>

<script>
    $(function() {
        $('.modal-flex__content a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fancybox.update();
        });
    });
</script>
