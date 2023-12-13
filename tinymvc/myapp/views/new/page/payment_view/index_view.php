<?php if(!empty($methods)){
$count_methods = count($methods);

    if(($count_methods%2) == 0){
        $count_methods = ($count_methods/2) - 1;
    }else{
        $count_methods = intval($count_methods/2);
    }?>

    <div class="row">
        <div class="col-md-6">
        <?php foreach($methods as $key => $method){?>
            <div class="fees__title-block">
                <img class="image" src="<?php echo __IMG_URL;?>public/img/financial_logo/item_<?php echo $method['alias']?>.png" alt="<?php echo $method['method']?>" title="<?php echo $method['method']?>">
                <h2 class="fees__title"><?php echo cleanOutput(payment_method_i18n($method, 'method')); ?></h2>
            </div>

            <div class="fees__tiny">
                <?php echo payment_method_i18n($method, 'instructions'); ?>
            </div>
            <?php if($key == $count_methods){?>
            </div>
            <div class="col-md-6">
            <?php } ?>
        <?php } ?>
        </div>
    </div>
<?php } ?>
