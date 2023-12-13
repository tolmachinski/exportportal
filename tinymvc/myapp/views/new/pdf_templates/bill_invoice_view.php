<link rel="stylesheet" type="text/css" media="all" href="<?php echo fileModificationTime('public/css/style_invoices_pdf.css'); ?>" />

<body>
	<div style="width:1024px;padding:0 50px;margin:0 auto;">
		<table style="width: 100%; border-collapse:collapse; font: 14px Arial,sans-serif;color: #555555;" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="width:50%;">
					<table style="width: 100%; vertical-align: top;">
						<tr>
							<td rowspan="2"><img width="70" height="80" src="<?php echo __IMG_URL; ?>public/img/ep-logo/img-logo-header.png" alt="exportportal"/></td>
							<td style="vertical-align: bottom; padding: 0; height: 45px;"><h1 style="font: bold 28px 'Copperplate Gothic Bold',Arial,sans-serif; color: #1d6e0f; margin:0;">EXPORT<span style="color: #0f6c94;">PORTAL</span></h1></td>
						</tr>
						<tr>
							<td style="vertical-align: top; font: 12px 'Copperplate Gothic',Arial,sans-serif; color: #000000; text-transform: uppercase; padding-left: 5px;">The <strong>Nr.1</strong> Export &amp; Import Source</td>
						</tr>
					</table>
				</td>
				<td style="width:50%;">
					<table style="width: 100%; vertical-align: top;text-align:right;">
						<tr>
							<td style="text-transform: uppercase; font: bold 23px Arial,sans-serif; color: #000000;">Bill Invoice <?php echo orderNumber($bill_info['id_bill']); ?></td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;">
								<strong>Issue date:</strong> <?php echo getDateFormat($bill_info['create_date'], "Y-m-d H:i:s", 'j M, Y'); ?>
							</td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;">
								<strong>Due date:</strong> <?php echo getDateFormat($bill_info['due_date'], "Y-m-d H:i:s", 'j M, Y'); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding-top: 50px;padding-right:5px;width:50%; vertical-align: top;">
					<table style="width: 100%; vertical-align: top;">
						<tr>
							<td style="font: bold 22px Arial,sans-serif; color: #000000;">Recipient</td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;">ExportPortal</td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $email_contact_us; ?></td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;">International call: <?php echo $ep_phone_number; ?></td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;">Free call: <?php echo $ep_phone_number_free; ?></td>
						</tr>
						<tr>
							<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $ep_address; ?></td>
						</tr>
					</table>
				</td>
				<td style="padding-top: 50px;padding-left:5px; vertical-align: top;">
					<table style="width: 100%; vertical-align: top; text-align: right;">
						<tr>
							<td style="font: bold 22px Arial,sans-serif; color: #000000;">Invoiced to</td>
						</tr>
						<?php if ($user_info['gr_type'] == 'Seller') { ?>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['legal_name_company']; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['email_company']; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['phone_code_company'] . ' ' . $company_info['phone_company']; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['address_company']; ?></td>
							</tr>
						<?php } else if ($user_info['gr_type'] == 'Buyer' && !empty($company_info)) { ?>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['company_legal_name']; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo (!empty($user_info['email'])) ? $user_info['email'] : '----'; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['company_phone_code'] . ' ' . $company_info['company_phone']; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $company_info['company_address']; ?></td>
							</tr>
						<?php } else { ?>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo !empty($user_info['legal_name']) ? $user_info['legal_name'] : $user_info['fname'] . ' ' . $user_info['lname'];  ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo (!empty($user_info['email'])) ? $user_info['email'] : '----'; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo $user_info['phone_code'] . ' ' . $user_info['phone'] ; ?></td>
							</tr>
							<tr>
								<td style="font: 14px Arial,sans-serif; color: #000000;"><?php echo (!empty($user_info['address'])) ? $user_info['address'] : '----'; ?></td>
							</tr>
						<?php } ?>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top: 30px;">
					<table style="width: 100%; margin-top: 30px; border-collapse:collapse;" border="1" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th align="center" style="padding: 3px;"></th>
								<th align="right" style="padding: 3px;">
									<strong style="font: bold 14px Arial,sans-serif; color: #000000;">
										Amount, in USD
									</strong>
								</th>
							</tr>
						</thead>
						<?php foreach ($bills as $bill) { ?>
							<tr>
								<td align="left" style="padding: 3px;">
									<div style="font: 14px/18px Arial,sans-serif; color: #000000;">
										<?php echo cleanOutput($bill['bill_description']); ?>
									</div>
								</td>
								<td align="right" style="padding: 3px;">
									<div style="font: 14px/18px Arial,sans-serif; color: #000000;">
										<?php echo get_price($bill['pay_amount'], false); ?>
									</div>
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td align="right" style="padding: 3px;">
								<strong style="font: bold 16px Arial,sans-serif; color: #000000;">
									Total
								</strong>
							</td>
							<td align="right" style="padding: 3px;">
								<strong style="font: bold 16px Arial,sans-serif; color: #000000;">
									<?php echo get_price($amount, false); ?>
								</strong>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
    </div>
</body>
