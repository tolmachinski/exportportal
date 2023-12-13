<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php tmvc::instance()->controller->view->display('new/js_global_vars_view'); ?>

    <link crossorigin="anonymous" rel="stylesheet" href="<?php echo asset("public/build/styles_user_pages_general.css");?>" />
	<link href="<?php echo __FILES_URL;?>public/css/elements.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_user_pages.css");?>" />

	<script src="<?php echo fileModificationTime('public/plug/core-js-3-6-5/bundle.js'); ?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/js/lang_new.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-mousewheel-3-1-12/jquery.mousewheel.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/popper-1-11-0/popper.min.js');?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/util.js');?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tooltip.js');?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/popover.js');?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/modal.js');?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tab.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/ofi/ofi.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/bootstrap-tabdrop-master/js/bootstrap-tabdrop.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-validation-engine-2-6-2/js/jquery.validationEngine.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/textcounter-0-3-6/textcounter.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-jscrollpane-2-0-20/jquery.jscrollpane.min-mod.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/js/js.cookie.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/select2-4-0-3/js/select2.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-ui-1-12-1-custom/jquery-ui.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/bootstrap-dialog-1-35-4/js/bootstrap-dialog.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/bootstrap-rating-1-3-1/bootstrap-rating.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-bxslider-4-2-12/jquery.bxslider.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/resizestop-master/jquery.resizestop.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/tinymce-4-3-10/tinymce.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/jquery-tags-input-master/jquery.tagsinput.min.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/i-observers/i-observers.js');?>"></script>
    <script src="<?php echo fileModificationTime("public/plug/lazyloading/index.js"); ?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/js/scripts_general.js');?>"></script>
	<script src="<?php echo fileModificationTime('public/plug/js/scripts_new.js');?>"></script>

	<title>EP elements</title>
</head>
<body>
<link href="<?php echo __FILES_URL;?>public/plug/highlight/styles/atom-one-light.css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug/highlight/highlight.pack.js"></script>
<script>
$(document).ready(function() {
	$('pre code').each(function(i, block) {
		hljs.highlightBlock(block);
	});
});
</script>

<div class="container-fluid">
<div class="row flex-xl-nowrap">
	<div class="col-12 col-lg-9 col-xl-8 py-md-3 pl-md-5">
		<?php foreach($elements as $element){?>
			<?php foreach($element['elements'] as $element_key => $element_item){?>
				<div class="elements-view" id="<?php echo $element_item['id'];?>">
					<div  class="title-public">
						<h1 class="title-public__txt"><?php echo $element_item['title'];?></h1>
					</div>

					<?php views()->display('new/epicons/blocks/'.$element_item['id']);?>

					<?php if($element_item['show_code'] !== 'none'){
							$code_prefix = '';
							if($element_item['show_code'] == 'other'){
								$code_prefix = '-code';
							}

							$code = file_get_contents('tinymvc/myapp/views/new/epicons/blocks/'.$element_item['id'].$code_prefix.'.php');
						?>

						<pre><code class="html"><?php echo htmlspecialchars($code);?></code></pre>
					<?php }?>
				</div>
			<?php }?>
		<?php }?>
	</div>

	<?php views()->display('new/epicons/nav_elements');?>
</div>
</div>
</body>
</html>

