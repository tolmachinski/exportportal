<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<?php foreach($standards as $standard) { ?>
    <div id="<?php echo $standard["standard_link"]?>">
        <div class="title-public pt-0 mt-50">
            <h2 class="title-public__txt  title-public__txt--26"><?php echo $standard['standard_title'];?></h2>
        </div>

        <div class="ep-tinymce-text">
            <?php echo $standard['standard_description'];?>
        </div>
    </div>
<?php } ?>
