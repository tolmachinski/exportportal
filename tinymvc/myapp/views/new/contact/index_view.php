<?php views()->display('new/two_mobile_buttons_view'); ?>

<div class="title-public">
    <h2 class="title-public__txt title-public__txt--26"><?php echo translate('contact_page_title'); ?></h2>
</div>

<?php views()->display('new/contact/contact_us_view', array('contact_us_not_modal' => 1)); ?>

<?php if(!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/webinar_requests/schedule-demo-popup.js');?>"></script>
<?php } ?>

