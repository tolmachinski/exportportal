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
			<?php foreach($companies_list as $company){?>
				<tr>
					<td class="td-bordered page-break" style="width: 165px;vertical-align:middle; text-align:center;">
						<img style="width:150px;" src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>" alt="img"/>
					</td>
					<td class="td-bordered page-break" style="vertical-align:top; border-collapse:collapse; font: 12px Arial,sans-serif;vertical-align:top;">
						<table cellspacing="0" cellpadding="0" style="width: 100%; text-align: left;">
							<tr>
								<td style="padding-bottom: 5px; font-size: 16px; font-weight: bold;"><?php echo $company['name_company'];?></td>
							</tr>
							<?php if($company['type_company'] == 'branch'){?>
								<tr>
									<td style="padding-bottom: 5px; font-size: 16px; font-weight: bold;">
										<?php $parent_company_link = getCompanyURL($branch_parents[$company['parent_company']]);?>
										<div>Branch of <a href="<?php echo $parent_company_link;?>" style="color: #16569e; text-decoration:none;"><?php echo $branch_parents[$company['parent_company']]['name_company']?></a></div>
									</td>
								</tr>
							<?php }?>
							<tr>
								<td>
									<table cellspacing="0" style="width: 100%; vertical-align:top; text-align: left;">
										<tr>
											<td style="padding-bottom: 5px; padding-right: 5px;"><span style="color: #c77c11">Email:</span> <?php echo $company['email_company'];?></td>
										</tr>
										<tr>
											<td style="padding-bottom: 5px; word-wrap: break-word; overflow: hidden;"><span style="color: #c77c11">Website:</span> <a href="<?php echo getCompanyURL($company);?>"><?php echo wordwrap(getCompanyURL($company), 25, "\n", true);?></a></td>
										</tr>
										<?php if(!empty($company['phone_company'])){?>
											<tr>
												<td style="padding-bottom: 5px;"><span style="color: #c77c11">Phone:</span> <?php echo $company['phone_company'];?></td>
											</tr>
										<?php }?>
										<?php if(!empty($company['fax_company'])){?>
											<tr>
												<td style="padding-bottom: 5px;"><span style="color: #c77c11">Fax:</span> <?php echo $company['fax_company'];?></td>
											</tr>
										<?php }?>
										<tr>
											<td style="width: 100%; padding-bottom: 5px; vertical-align:middle;">
												<table cellspacing="0" style="width: 100%; text-align: left;">
													<tr>
														<td style="width:70px;">
															<span style="color: #c77c11">Location: </span>
														</td>
														<td style="width:30px;">
                                                            <img
                                                                width="24"
                                                                height="24"
                                                                src="<?php echo getCountryFlag($company['country']); ?>"
                                                                alt="<?php echo $company['country'];?>"
                                                            />
														</td>
														<td>
															<?php echo $company['country'];?>,
															<?php
																if($company['id_state'] != 0){
																	echo $cities_with_states[$company['id_city']];
																} else{
																	echo $cities_without_states[$company['id_city']];
																}
															?>,
															<?php echo $company['address_company'];?>,
															<?php echo $company['zip_company'];?>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<a style="color: #16569e; text-decoration:none;" href="<?php echo getCompanyURL($company);?>"><?php echo wordwrap(getCompanyURL($company));?></a>
								</td>
							</tr>
						</table>
					</td>
					<td class="td-bordered page-break" style="width: 130px; vertical-align:middle;">
						<table cellspacing="0" cellpadding="0" style="width: 100%; border-collapse:collapse; overflow: hidden; font: 12px Arial,sans-serif; text-align:center;">
							<tr>
								<td style="padding-bottom: 5px;">
									<?php if($company['type_company'] != 'branch'){?>
										Rating: <?php echo $company['rating_company'];?>
									<?php }?>
								</td>
							</tr>
							<tr>
								<td style="padding-bottom: 5px;">
									<span><?php if(!isset($count_feedbacks[$company['id_user']])){ echo 'no'; }else{ echo $count_feedbacks[$company['id_user']];}?> feedback<?php if($count_feedbacks[$company['id_user']] != 1) echo 's';?></span>
								</td>
							</tr>
							<tr>
								<td style="padding-bottom: 5px;">

									<img width="50" src="<?php echo __IMG_URL.getImage('public/img/groups/'.$user_group[$company['user_group']]['stamp_pic'], 'public/img/no_image/no-image-80x80.png');?>" alt="<?php echo $user_group[$company['user_group']]['gr_name'];?>"/>
								</td>
							</tr>
							<tr>
								<td style="color: #16569e; padding-bottom: 5px;"><?php echo $user_group[$company['user_group']]['gr_name'];?></td>
							</tr>
							<tr>
								<td style="padding-bottom: 5px; color: #1a5600;">reg: <?php echo formatDate($company['registration_date'],'m/d/Y');?></td>
							</tr>
						</table>
					</td>
				</tr>
			<?php }?>
		</table>
    </div>
</body>
