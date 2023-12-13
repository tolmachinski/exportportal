<?php $tmvc = tmvc::instance();?>
    <footer>
        <div class="container">
            <nav class="footer-nav">
                <ul>
                    <li>
                        <a target="_blank" href="<?php echo __SITE_URL;?>terms_and_conditions/tc_terms_of_use">Terms</a>
                    </li>
                    <li>
                        <a target="_blank" href="<?php echo __SITE_URL;?>terms_and_conditions/tc_privacy_policy">Privacy policy</a>
                    </li>
                </ul>
            </nav>
            <div class="socials">
                <?php if (isset($tmvc->my_config['social_facebook'])) { ?>
                    <a target="_blank" href="<?php echo $tmvc->my_config['social_facebook'];?>">
                        <img src="<?php echo fileModificationTime('public/img/bloggers/icons/facebook.svg', __IMG_URL); ?>" alt="Facebook">
                    </a>
                <?php } ?>
                <?php if (isset($tmvc->my_config['social_twitter'])) { ?>
                    <a target="_blank" href="<?php echo $tmvc->my_config['social_twitter'];?>">
                        <img src="<?php echo fileModificationTime('public/img/bloggers/icons/twitter.svg', __IMG_URL); ?>" alt="Twitter">
                    </a>
                <?php } ?>
                <?php if (isset($tmvc->my_config['social_instagram'])) { ?>
                    <a target="_blank" href="<?php echo $tmvc->my_config['social_instagram'];?>">
                        <img src="<?php echo fileModificationTime('public/img/bloggers/icons/insta.svg', __IMG_URL); ?>" alt="Instagram">
                    </a>
                <?php } ?>
                <?php if (isset($tmvc->my_config['social_youtube'])) { ?>
                    <a target="_blank" href="<?php echo $tmvc->my_config['social_youtube'];?>">
                        <img src="<?php echo fileModificationTime('public/img/bloggers/icons/youtube.svg', __IMG_URL); ?>" alt="YouTube">
                    </a>
                <?php } ?>
                <?php if (isset($tmvc->my_config['social_linkedin'])) { ?>
                    <a target="_blank" href="<?php echo $tmvc->my_config['social_linkedin']; ?>">
                        <img src="<?php echo fileModificationTime('public/img/bloggers/icons/linkedin.svg', __IMG_URL); ?>" alt="LinkedIn">
                    </a>
                <?php } ?>
            </div>
            <div class="copyright">
                <span>&copy; <?php echo date('Y');?>, Export Portal</span>
                <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', tmvc::instance()->my_config['ep_phone_whatsapp']); ?>">
                    <?php echo tmvc::instance()->my_config['ep_phone_whatsapp']; ?>
                </a>
            </div>
        </div>
    </footer>

    <?php if (DEBUG_MODE) { ?>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/core-js-3-6-5/bundle.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/js/js.cookie.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-validation-engine-2-6-3/js/jquery.validationEngine.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fancybox-2-1-5/js/jquery.fancybox.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/select2-4-0-3/js/select2.min.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/noty-3.2.0-beta/noty.min.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/tinymce-4-8-3/tinymce.min.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.ui.widget.js');?>"></script>
		<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.iframe-transport.js');?>"></script>
		<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload.js');?>"></script>
		<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-process.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-validate.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/icheck-1-0-2/js/icheck.min.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/js/lang_new.js');?>"></script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/js/bloggers.js');?>"></script>
    <?php } else { ?>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/core-js-3-6-5/bundle.min.js'); ?>"></script>
		<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_compiled/all-bloggers-min.js');?>"></script>
	<?php } ?>
</body>

</html>
