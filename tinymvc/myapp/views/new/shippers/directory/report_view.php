<page backtop="25mm">
	<page_header>
		<table style="width: 100%; vertical-align: top;">
			<tr>
				<td rowspan="2"><img width="70" height="80" src="<?php echo __IMG_URL;?>public/img/ep-logo/img-logo-header.png" alt="exportportal"/></td>
				<td style="vertical-align: bottom; padding: 0; height: 38px;"><h1 style="font: bold 32px 'Copperplate Gothic Bold',Arial,sans-serif; color: #1d6e0f; margin:0;">EXPORT<span style="color: #0f6c94;">PORTAL</span></h1></td>
			</tr>
			<tr>
				<td style="vertical-align: top; font: 12px 'Copperplate Gothic',Arial,sans-serif; color: #838d96; text-transform: uppercase; padding-left: 5px;">The <strong>Nr.1</strong> Export &amp; Import Source</td>
			</tr>
		</table>
	</page_header>
	<page_footer>
			<div style="text-align: center;">&copy; Generated by ExportPortal.com at <?php echo date('m.d.Y H:i:s');?></div>
	</page_footer>
	<h1 style="text-align: center;">Report</h1>
	<div style="font-size: 16px;">
		Directory of the found freight forwarders
		<?php if(isset($search_params)){?>
			<br/>
			by:
			<table width="730">
				<tr style="width: 730px;">
			<?php foreach($search_params as $item){?>
					<td><strong><?php echo $item['param']?>:</strong></td>
					<td style="padding-right: 10px;"><?php echo $item['title']?></td>
			<?php }?>
				</tr>
			</table>
		<?php }?>
	</div>

	<table border="1" cellpadding="0" cellspacing="0" style="width: 730px; text-align: center; background: #fff; border-collapse:collapse; font: 12px Arial,sans-serif;">
	<?php foreach($shipper_list as $shipper){?>
		<tr style="width: 730px;">
			<td cellspacing="5" style="width: 165px; padding-bottom: 10px; padding-top: 10px; vertical-align:middle;">
				<table cellspacing="0" cellpadding="0" style="width: 165px; border-collapse:collapse;">
					<tr>
						<td>
							<img
								width="165"
								src="<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main', array( 'thumb_size' => 1 ));?>"
								alt="<?php echo $shipper['co_name']; ?>">
						</td>
					</tr>
				</table>
			</td>
			<td style="vertical-align:top; border-collapse:collapse; font: 12px Arial,sans-serif; padding-bottom: 10px; padding-top: 10px; vertical-align:top;">
				<table cellspacing="0" cellpadding="0" style="text-align: left;">
					<tr><td style="padding-bottom: 5px; font-size: 16px; font-weight: bold;"><?php echo $shipper['co_name'];?></td></tr>
					<tr>
						<td>
							<table cellspacing="0" style="vertical-align:top; text-align: left;">
								<tr>
									<td style="width: 315px; padding-bottom: 5px; padding-right: 5px;"><strong>email:</strong> <?php echo $shipper['email'];?></td>
									<td style="width: 215px; padding-bottom: 5px; word-wrap: break-word; overflow: hidden;"><strong>website:</strong> <a href="<?php echo $shipper['co_website'];?>"><?php echo wordwrap($shipper['co_website'], 25, "\n", true);?></a></td>
								</tr>
								<tr>
									<td style="width: 315px; padding-bottom: 5px;"><strong>phone:</strong> <?php echo $shipper['phone'];?></td>
									<td style="width: 215px; padding-bottom: 5px;"><strong>fax:</strong> <?php if(!empty($shipper['fax'])){ echo $shipper['fax']; }else{ echo '-'; }?></td>
								</tr>
								<tr>
									<td style="padding-bottom: 5px;" colspan="2"><strong>location: </strong>
										<img
                                            width="24"
                                            height="24"
                                            src="<?php echo getCountryFlag($shipper['country']);?>"
                                            alt="<?php echo $shipper['country'];?>"
                                        />
										<?php echo $shipper['country'];?>,
										<?php if($shipper['id_state'] != 0){ echo $cities_with_states[$shipper['id_city']];}else{ echo $cities_without_states[$shipper['id_city']];}?>,
										<?php echo $shipper['address'];?>,
										<?php echo $shipper['zip'];?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="width: 430px; padding-bottom: 5px;">
							<a href="<?php echo __SITE_URL.'shipper/'.strForUrl($shipper['co_name']).'-'.$shipper['id'];?>"><?php echo $shipper['co_name'];?></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	<?php }?>
	</table>
</page>
