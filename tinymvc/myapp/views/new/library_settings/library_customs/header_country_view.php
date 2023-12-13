<div class="public-heading">
    <div class="public-heading__container">

        <?php tmvc::instance()->controller->view->display('new/library_settings/header_menu_view'); ?>

        <h1 class="public-heading__ttl"><?php echo $country['country'];?></h1>
    </div>
    <img class="image" src="<?php echo $country['imageUrl'] ?>">
</div>
