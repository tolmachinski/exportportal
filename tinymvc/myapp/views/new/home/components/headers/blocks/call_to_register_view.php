<div class="register-types">
    <?php foreach($data as $key => $element) { ?>
        <a class="register-type<?php echo $additionalChildClass; ?>" href="<?php echo $element['link']; ?>" <?php echo addQaUniqueIdentifier("home__header-link--{$key}"); ?>>
            <div class="register-type__info">
                <h4 class="register-type__header"><?php echo $element['title']; ?></h4>
                <p class="register-type__paragraph"><?php echo $element['paragraph']; ?></p>
            </div>
            <span class="register-type__link"><?php echo $element['linkText']; ?><?php echo widgetGetSvgIcon("arrowRight", 25, 16)?></span>
        </a>
    <?php } ?>
</div>
