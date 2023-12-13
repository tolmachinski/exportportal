<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-grid-a-licious-3-0-1/jquery.grid-a-licious.js'); ?>"></script>
<script>
    $(document).ready(function() {
        function initGridalicious($block) {
            $block.gridalicious({
                gutter: 1,
                width: 300,
                selector: '.sliding-block',
                animate: false
            })
        }

        initGridalicious($(".sliding-row"));

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            $(".sliding-row").gridalicious({
                gutter: 1,
                width: 300,
                selector: '.sliding-block',
                animate: false
            });
        });

    });
</script>

<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<?php if (empty($keywords)) { ?>
    <div class="title-public">
        <h2 class="title-public__txt title-public__txt--26"><?php echo translate('help_title_faq'); ?></h2>
    </div>
<?php } else { ?>
    <div class="title-public">
        <h2 class="title-public__txt title-public__txt--26"><?php echo translate('community_questions_search_params_keywords'); ?>: <?php if (isset($keywords)) echo $keywords ?></h2>
        <span class="minfo-title__total">Found <?php echo $count_user_guides; ?> document(s)</span>
    </div>
<?php } ?>

<?php if ($count_user_guides > 0) { ?>
    <?php if ('search' == tmvc::instance()->action) { ?>
        <?php echo views()->fetch('new/user_guide/search_view', ['userGuides' => $user_guides]);?>
    <?php }?>
<?php } else { ?>
    <?php tmvc::instance()->controller->view->display('new/help/results_not_found_view'); ?>
<?php } ?>

<div class="col-12">
    <div class="pt-10 flex-display flex-jc--sb flex-ai--c">
        <?php tmvc::instance()->controller->view->display("new/paginator_view"); ?>
    </div>
</div>
