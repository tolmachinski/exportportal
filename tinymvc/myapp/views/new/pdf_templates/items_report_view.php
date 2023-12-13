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
						Found items<br>
                        <?php if(isset($crumbs)){?>
                            <label style="margin-right: 10px;">
                                <strong>Category: </strong>
                                <?php
                                    foreach($crumbs as $crumb){

                                        $out[] = $crumb['title'];
                                    }

                                    echo implode(' &gt;&gt; ', $out);
                                ?>
                            </label>
				        <?php }?>
                        <?php if(isset($keywords)){?>
                            <label style="margin-right: 10px;">
                                <strong>Keywords: </strong>
                                <?php echo $keywords;?>
                            </label>
                        <?php }?>
                        <?php if(isset($year_from)){?>
                            <label style="margin-right: 10px;">
                                <strong>Year from: </strong>
                                <?php echo $year_from;?>
                            </label>
                        <?php }?>
                        <?php if(isset($year_to)){?>
                            <label style="margin-right: 10px;">
                                <strong>Year to: </strong>
                                <?php echo $year_to;?>
                            </label>
                        <?php }?>
                        <?php if(isset($price_from)){?>
                            <label style="margin-right: 10px;">
                                <strong>Price from: </strong>
                                <?php echo $price_from;?>
                            </label>
                        <?php }?>
                        <?php if(isset($price_to)){?>
                            <label style="margin-right: 10px;">
                                <strong>Price to: </strong>
                                <?php echo $price_to;?>
                            </label>
                        <?php }?>
					</p>
                    <?php if(isset($attr_values) && !empty($attr_values)){?>
                        <p style="font-size: 16px;">
                            <label style="margin-right: 10px;">
                                <strong>Atributes: </strong>
                                <?php foreach($attr_values as $result){?>
                                    <?php echo $result['attr_name']; ?>: <?php echo $result['attr_values']; ?>;
                                <?php } ?>
                            </label>
                        </p>
                    <?php }?>
				</td>
			</tr>
		</table>
		<table style="width: 100%; color: #555555;border-collapse:collapse;margin-top: 15px;" cellspacing="0" cellpadding="0">
            <?php foreach($items as $item){?>
				<tr>
					<td class="td-bordered page-break" style="width: 165px;vertical-align:middle; text-align:center;">
						<?php if(
                                !empty($item['photo_name'])
                                && file_exists( getImgSrc('items.main', 'original', array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name'])))
                            ){?>
                            <img
                                style="max-width:150px;"
                                src="<?php echo getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main');?>"
                                alt="<?php echo $item['title'] ?>"
                            />
                        <?php }else{ ?>
                            <img style="max-width:150px;" src="<?php echo __IMG_URL?>public/img/no_image/no-image-125x90.png" alt="<?php echo $item['title']?>" />
                        <?php } ?>
                    </td>
					<td class="td-bordered page-break" style="vertical-align:top; border-collapse:collapse; font: 12px Arial,sans-serif;vertical-align:top;">
						<table cellspacing="0" cellpadding="0" style="width: 100%; text-align: left;">
							<tr>
								<td style="padding-bottom: 5px; font-size: 16px; font-weight: bold;">
                                    <a style="color: #16569e; text-decoration:none;" href="<?php echo __SITE_URL?>item/<?php echo strForURL($item['title'])?>-<?php echo $item['id'] ?>"><?php echo $item['title'];?></a>
                                </td>
							</tr>
							<tr>
								<td>
									<table cellspacing="0" style="width: 100%; vertical-align:top; text-align: left;">
										<tr>
											<td style="width: 100%; vertical-align:middle;">
												<table cellspacing="0" style="width: 100%; text-align: left;">
													<tr>
														<td style="width:30px;">
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="<?php echo getCountryFlag($item['country']); ?>"
                                                                alt="<?php echo $item['country'];?>"
                                                            />
                                                        </td>
														<td>
															by <a style="color: #16569e; text-decoration:none;" href="<?php echo __SITE_URL.'usr/'.strForURL($item['seller']['user_name']).'-'.$item['seller']['idu']?>"><?php echo $item['seller']['user_name']?></a>
                                                            <?php echo $item['seller']['gr_name']?>
														</td>
													</tr>
												</table>
											</td>
										</tr>
                                        <tr>
                                            <td style="padding-bottom: 5px;">
                                            <?php if($item['rev_numb'] < 1){ $item['rating'] = 0;} ?>
                                                <span style="color: #c77c11">Rating:</span> <?php echo number_format($item['rating'], 2, '.', '');?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 5px;">
                                                <span style="color: #c77c11">Reviews:</span> <?php echo $item['rev_numb']?>
                                            </td>
                                        </tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
                    <td class="td-bordered page-break" style="width: 130px; vertical-align:middle;">
						<table cellspacing="0" cellpadding="0" style="width: 100%; border-collapse:collapse; overflow: hidden; font: 12px Arial,sans-serif;">
							<tr>
                                <td style="padding-bottom: 5px;">
                                    <?php if($item['discount']){?>
                                        <strong>Price:</strong> <?php echo $item['curr_entity']?><?php echo $item['final_price'];?>
                                        <p style="text-decoration: line-through;"><?php echo $item['curr_entity']?><?php echo $item['price']?></p>
                                        <strong>Discount:</strong> <?php echo $item['discount']?>%
                                    <?php }else{?>
                                        <strong>Price:</strong> <?php echo $item['curr_entity']?><?php echo $item['final_price'];?>
                                    <?php }?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 5px;">
                                    <strong>Quantity:</strong> <?php echo $item['quantity']?>
                                </td>
                            </tr>
						</table>
					</td>
				</tr>
			<?php }?>
		</table>
    </div>
</body>
