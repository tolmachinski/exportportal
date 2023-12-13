    <div id="js-popup-blog" class="js-popup-container popup-blog">
        <div class="popup-blog__heading">
            <h2 class="popup-blog__title">Thank you for reading our blog!</h2>
            <a class="popup-blog__close ep-icon ep-icon_remove-stroke call-action" data-js-action="popup:close-hash-blog" href="#"></a>
        </div>
        <p class="popup-blog__sub-title">Are you a:</p>
        <form
            id="js-popup-blog-appearing-form"
            data-js-action="form:submit-form-blog"
        >
            <div class="popup-blog__list">
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="buyer">
                    <span class="popup-blog__list-name custom-radio__text">Buyer</span>
                </label>
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="exporter">
                    <span class="popup-blog__list-name custom-radio__text">Exporter</span>
                </label>
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="seller">
                    <span class="popup-blog__list-name custom-radio__text">Seller</span>
                </label>
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="importer">
                    <span class="popup-blog__list-name custom-radio__text">Importer</span>
                </label>
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="manufacturer">
                    <span class="popup-blog__list-name custom-radio__text">Manufacturer</span>
                </label>
                <label class="js-popup-blog__list-item popup-blog__list-item custom-radio">
                    <input class="js-radio-popup-blog validate[required]" name="status" type="radio" value="other">
                    <span class="popup-blog__list-name custom-radio__text">Other</span>
                </label>
            </div>
            <div class="popup-blog__actions">
                <button class="btn btn-primary mnw-170" type="submit">Next</button>
            </div>
        </form>
    </div>

    <div
        id="js-become-member"
        class="popup-become-member display-n"
    >
        <div class="popup-blog__heading">
            <h2 class="popup-blog__title">Great! Thank you for your feedback!</h2>
            <a class="popup-blog__close ep-icon ep-icon_remove-stroke call-action" data-js-action="fancy-box:close" href="#"></a>
        </div>
        <p class="popup-become-member__sub-title">Your opinion is important for us</p>
        <div class="popup-become-member__image">
            <img width="349" height="218" src="<?php echo __IMG_URL;?>public/img/popups/popup_placeholder.jpg" alt="Membership">
        </div>
        <a class="btn btn-primary btn-block" href="<?php echo __SITE_URL ?>register">Become an Export Portal member</a>
    </div>

    <?php
        echo dispatchDynamicFragment(
            "popup:hash_blog",
            null,
            true
        );
    ?>
