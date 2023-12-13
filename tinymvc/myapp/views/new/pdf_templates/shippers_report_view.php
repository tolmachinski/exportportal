<link rel="stylesheet" type="text/css" media="all" href="<?php echo fileModificationTime('public/css/style_invoices_pdf.css');?>" />

<body>
    <div style="width:1024px;padding:0 50px;margin:0 auto;">
    	<table style="width: 100%; border-collapse:collapse; color:#555555;" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="width:50%;">
				    <table style="width: 100%; vertical-align: top;">
						<tr>
							<td rowspan="2"><img width="70" height="80" src="<?php echo __IMG_URL;?>public/img/ep-logo/img-logo-header.png" alt="exportportal"/></td>
							<td style="vertical-align: bottom; padding: 0; height: 45px;"><h1 style="font: bold 28px 'Copperplate Gothic Bold',Arial,sans-serif; color: #1d6e0f; margin:0;">EXPORT<span style="color: #0f6c94;">PORTAL</span></h1></td>
						</tr>
						<tr>
							<td style="vertical-align: top; font: 12px 'Copperplate Gothic',Arial,sans-serif; color: #838d96; text-transform: uppercase; padding-left: 5px;">The <strong>Nr.1</strong> Export &amp; Import Source</td>
						</tr>
					</table>
				</td>
				<td style="width:50%;">
					<table style="width: 100%; vertical-align: top;text-align:right;">
						<tr>
							<td style="text-transform: uppercase; font: bold 23px Arial,sans-serif; color: #646464;">Report</td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #b3b3b3;"><strong>Report date:</strong> <?php echo formatDate(date("Y-m-d"));?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<p style="font-size: 16px;">
						Directory of the found companies
						<?php if(isset($search_params)){?>
							by:<br>
							<?php foreach($search_params as $item){?>
								<label style="margin-right:10px;">
									<strong><?php echo $item['param']?>: </strong> <?php echo $item['title']?>;
								</label>
							<?php }?>
						<?php }?>
					</p>
				</td>
			</tr>
		</table>
		<table style="width: 100%; color: #555555;border-collapse:collapse;margin-top: 15px;" cellspacing="0" cellpadding="0">
            <?php foreach($shipper_list as $shipper){?>
				<tr>
					<td class="td-bordered page-break" style="width: 165px;vertical-align:middle; text-align:center;">
						<img
							style="width:150px;"
							src="<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main');?>"
							alt="<?php echo $shipper['co_name']; ?>">
                    </td>
					<td class="td-bordered page-break" style="vertical-align:top; border-collapse:collapse; font: 12px Arial,sans-serif;vertical-align:top;">
						<table cellspacing="0" cellpadding="0" style="width: 100%; text-align: left;">
							<tr>
								<td style="padding-bottom: 5px; font-size: 16px; font-weight: bold;">
                                    <a style="color: #16569e; text-decoration:none;" href="<?php echo __SITE_URL.'shipper/'.strForUrl($shipper['co_name']).'-'.$shipper['id'];?>"><?php echo $shipper['co_name'];?></a>
                                </td>
							</tr>
							<tr>
								<td>
									<table cellspacing="0" style="width: 100%; vertical-align:top; text-align: left;">
										<tr>
											<td style="padding-bottom: 5px; padding-right: 5px;"><span style="color: #c77c11">Email:</span> <?php echo $shipper['email'];?></td>
										</tr>
										<?php if(!empty($shipper['co_website'])){?>
											<tr>
												<td style="padding-bottom: 5px; word-wrap: break-word; overflow: hidden;"><span style="color: #c77c11">Website:</span> <a href="<?php echo $shipper['co_website'];?>"><?php echo wordwrap($company['website_company'], 25, "\n", true);?></a></td>
											</tr>
										<?php }?>
										<?php if(!empty($shipper['phone'])){?>
											<tr>
												<td style="padding-bottom: 5px;"><span style="color: #c77c11">Phone:</span> <?php echo $shipper['phone'];?></td>
											</tr>
										<?php }?>
										<?php if(!empty($shipper['fax'])){?>
											<tr>
												<td style="padding-bottom: 5px;"><span style="color: #c77c11">Fax:</span> <?php echo $shipper['fax'];?></td>
											</tr>
										<?php }?>
										<tr>
											<td style="width: 100%; vertical-align:middle;">
												<table cellspacing="0" style="width: 100%; text-align: left;">
													<tr>
														<td style="width:70px;">
                                                            <span style="color: #c77c11">Location: </span>
                                                        </td>
														<td style="width:30px;">
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="<?php echo getCountryFlag($shipper['country']); ?>"
                                                                alt="<?php echo $shipper['country'];?>"
                                                            />
                                                        </td>
														<td>
															<?php echo $shipper['country'];?>,
                                                            <?php
                                                                if($shipper['id_state'] != 0){
                                                                    echo $cities_with_states[$shipper['id_city']];
                                                                } else{
                                                                    echo $cities_without_states[$shipper['id_city']];
                                                                }
                                                            ?>,
                                                            <?php echo $shipper['address'];?>,
                                                            <?php echo $shipper['zip'];?>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			<?php }?>
		</table>
    </div>
</body>
