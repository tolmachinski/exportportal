<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form pb-15">
		<div class="modal-flex__content">
			<ul class="nav nav-tabs nav--borders" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#general_ereport-info" aria-controls="title" role="tab" data-toggle="tab">Main information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#attachments_ereport-info" aria-controls="title" role="tab" data-toggle="tab">Attachments</a>
                </li>
            </ul>

			<div class="tab-content tab-content--borders pt-20">
                <div role="tabpanel" class="tab-pane fade show active" id="general_ereport-info">
					<div class="container-fluid-modal">
						<div class="row">
							<div class="col-12 col-md-6">
								<label class="input-label">Title</label>
								<?php echo $ereport['ereport_title'];?>
							</div>

							<div class="col-12 col-md-6">
								<label class="input-label">Refund amount, (USD)</label>
								<?php echo $ereport['ereport_refund_amount'];?>
							</div>

							<div class="col-12">
								<label class="input-label">Description</label>
								<?php echo $ereport['ereport_description'];?>
							</div>
						</div>
					</div>
				</div>
                <div role="tabpanel" class="tab-pane fade show" id="attachments_ereport-info">
					<div class="container-fluid-modal">
						<div class="row">
							<div class="col-12">
								<!-- The container for the uploaded files -->
								<div class="fileupload">
									<?php if(!empty($ereport)){?>
										<?php $ereport_files = json_decode($ereport['ereport_photos'], true);?>
										<?php if(!empty($ereport_files)){?>
											<?php foreach($ereport_files as $file_key => $ereport_file){?>
												<div class="w-100pr pull-left tac">
													<img class="mw-100pr mt-10" src="<?php echo __IMG_URL . "public/expense_reports/{$ereport['id_ereport']}/{$ereport_file['name']}";?>">
												</div>
											<?php }?>
										<?php }?>
									<?php }?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    $('.modal-flex__content a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fancybox.update();
    });
</script>
