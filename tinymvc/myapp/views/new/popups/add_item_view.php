<div
    id="js-add-item-popup"
    class="add-item-popup"
>
    <a
        class="add-item-popup__close ep-icon ep-icon_remove-stroke call-action"
        data-js-action="popup:close-add-item-popup"
        href="#"></a>
    <picture class="add-item-popup__image">
        <source media="(max-width: 767px)" srcset="<?php echo asset("public/build/images/popups/popup_add_item_mobile.gif");?>">
        <img class="image" width="412" height="161" src="<?php echo asset("public/build/images/popups/popup_add_item_desktop.gif");?>" alt="Export Portal Add Item">
    </picture>
    <div class="add-item-popup__info">
        <h2 class="title-public__txt">READY TO SELL?</h2>
        <p class="mt-5">Add your items for sale today using our new improved Add Item Process</p>
        <button
            class="btn btn-primary mt-10 mr-auto ml-auto w-170 call-action"
            data-js-action="popup:submit-add-item-popup"
        >Add item now</button>
    </div>

    <?php
        echo dispatchDynamicFragment(
            "popup:add_item",
            null,
            true
        );
    ?>
</div>


