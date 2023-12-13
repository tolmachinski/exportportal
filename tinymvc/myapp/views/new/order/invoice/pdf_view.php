<link rel="stylesheet" type="text/css" media="all" href="<?php echo fileModificationTime('public/css/style_invoices_pdf.css');?>" />

<body>
    <div style="width:1024px;padding:0 50px;margin:0 auto;font: 14px Arial,sans-serif;color: #000000;">
		<?php app()->view->display('new/order/invoice/content_view');?>
    </div>
</body>
