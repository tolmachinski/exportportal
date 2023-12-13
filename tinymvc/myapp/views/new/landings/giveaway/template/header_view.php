<?php
    views()->display('new/template_views/bad_browser_view');
    views()->display('new/template_views/tag_manager_body_view');
?>

<header class="giveaway-header">
    <div class="giveaway-container">
        <nav class="giveaway-nav">
            <a class="giveaway-nav__logo notranslate" href="<?php echo __SITE_URL;?>">
                <img
                    class="image"
                    width="60"
                    height="72"
                    src="<?php echo asset("public/build/images/logo/ep-logo.png"); ?>"
                    alt="Export portal"
                >
                <span class="giveaway-nav__logo-txt">Export portal</span>
            </a>
        </nav>
    </div>
</header>

<?php encoreLinks(); ?>
