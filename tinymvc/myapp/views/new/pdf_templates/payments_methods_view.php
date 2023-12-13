<link rel="stylesheet" type="text/css" media="all" href="<?php echo __FILES_URL;?>public/css/style_invoices_pdf.css" />
<body>
    <div style="width:1024px;padding:0 50px;margin:0 auto;">
    	<table style="width: 100%; border-collapse:collapse; font: 14px Arial,sans-serif;color: #555555;" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-top: 70px; font-size: 23px;">
					Bank requisites
				</td>
			</tr>

			<?php foreach($methods as $key => $method){?>
			<tr>
				<td style="padding-top: 50px; font-size: 18px;">
					<strong><?php echo $method['method']?>:</strong>
				</td>
			</tr>
			<tr>
				<td style="padding-top: 10px;">
					<div class="metod-instructions-b">
						<?php echo $method['instructions']?>
					</div>
				</td>
			</tr>
			<?php } ?>
		</table>
    </div>
</body>
