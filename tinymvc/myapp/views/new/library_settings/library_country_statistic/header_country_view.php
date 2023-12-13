<div class="public-heading">
    <div class="public-heading__container">

        <?php tmvc::instance()->controller->view->display('new/library_settings/header_menu_view'); ?>

        <h1 class="public-heading__ttl"><?php echo $info['h1_text'];?></h1>

    </div>
    <img class="image" src="<?php echo $info['imageUrl'] ?>" alt="<?php echo $info['country'];?>">
</div>

<div class="container-center-sm ei-statistic-txt">
    <?php echo $info['description_text']; ?>
</div>
