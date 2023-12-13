<?php views('new/learn_more/components/learn_more_list_view');?>

<?php views('new/learn_more/components/learn_more_useful_info_view');?>

<?php views('new/learn_more/components/learn_more_check_view');?>

<div class="learnmore-reg-blocks">
    <div class="container-center">
        <div class="learnmore-reg-blocks__titles">
            <h2 class="learnmore-ttl tac">
                <?php echo translate('learn_more_block_register_header'); ?>
            </h2>
            <div class="learnmore-subttl tac"><?php echo translate('learn_more_block_register_header_subtext'); ?></div>
        </div>
    </div>

    <?php views('new/register/register_users_blocks_view'); ?>
</div>

<div class="learnmore-subscribe-block">
    <?php views()->display('new/subscribe/stay_informed_view'); ?>
</div>

<?php views('new/learn_more/components/learn_more_tour_slider_view');?>

<?php views('new/learn_more/components/learn_more_social_block_view');?>

<?php
encoreEntryLinkTags('learn_more');
encoreEntryScriptTags('learn_more');
?>
