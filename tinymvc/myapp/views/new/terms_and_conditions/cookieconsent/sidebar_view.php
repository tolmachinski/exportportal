
<?php
    echo dispatchDynamicFragmentInCompatMode(
            "terms_and_conditions:cookie-consent",
            asset('public/plug/js/terms-tinymce-nav/cookieconsent.js', 'legacy'),
            sprintf(
                "function () { scrollTermsInit(%d); }",
                $terms_in_modal
            ),
            array($cookie_policy_modal),
            true
        );
?>

<div class="terms-tinymce-nav <?php if (isset($cookie_policy_modal)) { ?>terms-tinymce-nav--modal<?php } ?>">
    <h2 class="terms-tinymce-nav__tll">Chapters</h2>

    <ul class="js-scroll-terms terms-tinymce-nav__list">
        <li class="terms-tinymce-nav__list-item">
            <i class="ep-icon ep-icon_arrow-line-right"></i>

            <a class="link" href="#js-cookie-policy-introduction">
                <span>Introduction to Export Portal’s Cookie Policy</span>
            </a>
        </li>
        <li class="terms-tinymce-nav__list-item">
            <i class="ep-icon ep-icon_arrow-line-right"></i>

            <a class="link" href="#js-cookie-policy-role">
                <span>Role of cookies for Export Portal and its visitors</span>
            </a>
        </li>
        <li class="terms-tinymce-nav__list-item">
            <i class="ep-icon ep-icon_arrow-line-right"></i>

            <a class="link" href="#js-cookie-policy-restricting">
                <span>Restricting cookies</span>
            </a>
        </li>
        <li class="terms-tinymce-nav__list-item">
            <i class="ep-icon ep-icon_arrow-line-right"></i>

            <a class="link" href="#js-cookies-explanation-title">
                <span>Cookies Explanation</span>
            </a>
        </li>
    </ul>
</div>
