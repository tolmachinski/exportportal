<?php tmvc::instance()->controller->view->display('new/header_view'); ?>

<div class="container-center-sm">
<?php
if(!empty($header_content))
	tmvc::instance()->controller->view->display($header_content);
?>

<?php
if(!empty($header_content2))
	tmvc::instance()->controller->view->display($header_content2);
?>


<div class="product__flex-card" itemscope itemtype="http://schema.org/Product">
	<?php if(!empty($sidebar_left_content)){?>
    <div class="product__flex-card-fixed" id="product__flex-card-fixed">
        <?php tmvc::instance()->controller->view->display($sidebar_left_content); ?>
    </div>
	<?php }?>

    <div class="product__flex-card-float">
        <?php tmvc::instance()->controller->view->display($main_content); ?>
    </div>
</div>
</div>
<?php views()->display('new/item/detail_footer_content_view'); ?>
<?php tmvc::instance()->controller->view->display('new/footer_view'); ?>
