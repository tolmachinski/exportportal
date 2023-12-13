<?php views()->display('new/two_mobile_buttons_view'); ?>

<div class="title-public">
    <?php if (empty($keywords)) {?>
        <h2 class="title-public__txt title-public__txt--26">Here is a list of recently discussed topics</h2>
    <?php } else {?>
        <h2 class="title-public__txt title-public__txt--26"><?php echo translate('community_questions_search_params_keywords'); ?>: <?php echo $keywords;?></h2>
        <span class="minfo-title__total">Found <span <?php echo addQaUniqueIdentifier('page__topics__search_counter') ?>><?php echo $count;?></span> Topic(s)</span>
    <?php }?>
</div>

<div class="row row-eq-height">

    <?php views()->display('new/topics/topics_list_view');?>

    <div class="col-12">
        <div class="pt-10 flex-display flex-jc--sb flex-ai--c">
            <?php views()->display("new/paginator_view"); ?>
        </div>
    </div>
</div>
