<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div class="title-public">
    <h2 class="title-public__txt title-public__txt--26"><?php if(isset($keywords))echo $keywords?></h2>
    <span class="minfo-title__total tar"><?php echo $count_faq_list; ?> <?php echo translate('help_faqs');?></span>
</div>

<?php tmvc::instance()->controller->view->display('new/faq/partial_list_view');?>

<div class="col-12">
    <div class="pt-10 flex-display flex-jc--sb flex-ai--c">
        <?php tmvc::instance()->controller->view->display("new/paginator_view"); ?>
    </div>
</div>