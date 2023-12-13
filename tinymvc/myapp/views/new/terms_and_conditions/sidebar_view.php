<?php
    echo dispatchDynamicFragmentInCompatMode(
            "terms_and_conditions:main",
            asset('public/plug/js/terms-tinymce-nav/terms_and_conditions.js', 'legacy'),
            sprintf(
                "function () { scrollTermsInit(%d); }",
                $terms_in_modal
            ),
            array($terms_in_modal),
            true
        );
?>

<div class="terms-tinymce-nav <?php if(isset($terms_in_modal)){?>terms-tinymce-nav--modal<?php }?>">
    <h2 class="terms-tinymce-nav__tll">Chapters</h2>

    <ul class="js-scroll-terms terms-tinymce-nav__list">
        <?php foreach($terms_menu as $key => $terms_menu_item){?>
        <li class="terms-tinymce-nav__list-item">
            <i class="ep-icon ep-icon_arrow-line-right"></i>

            <a class="link" href="<?php echo $key;?>">
                <span><?php echo $terms_menu_item;?></span>
            </a>
        </li>
        <?php }?>
    </ul>
</div>
