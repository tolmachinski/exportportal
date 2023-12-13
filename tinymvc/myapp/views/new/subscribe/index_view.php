<?php if(!isset($webpackData)){?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/subscribe/index.js'); ?>"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/subscribe_page_styles.css');?>" />
<?php }?>

<h1 class="subscribe-view__headline"><?php echo translate('subscribe_to_our_newsletter');?></h1>

<div class="subscribe-view footer-connect">
	<div class="subscribe-view__backdrop">
		<div class="subscribe-view__form">
			<div class="subscribe-view__info"><?php echo translate('subscribe_to_our_monthly_newsletter');?></div>
			<form
                class="unsubscribe-form validengine"
                data-callback="subscribeFormCallBack"
                data-js-action="form:submit_form_subscribe"
                action="<?php echo __SITE_URL;?>"
                method="post">
                <div class="relative-b">
                    <input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
                           name="email"
                           maxlength="50"
                           placeholder="<?php echo translate('learn_more_newslatter_input_placeholder');?>"
                           type="text"
                           <?php echo addQaUniqueIdentifier('page__subscribe__email-input'); ?>
                    >
                    <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">
                </div>
				<label class="subscribe-view__label custom-checkbox" <?php echo addQaUniqueIdentifier('page__subscribe__t&c-label'); ?>>

					<input class="validate[required]" type="checkbox" name="terms_cond" <?php echo addQaUniqueIdentifier('page__subscribe__t&c-checkbox'); ?>>

					<span class="custom-checkbox__text-agreement txt-white">
						<?php echo translate('label_i_agree_with');?>
						<a class="subscribe-view__terms fancybox fancybox.ajax link-white"
						   data-w="1040"
						   data-mw="1040"
						   data-h="400"
						   data-title="<?php echo translate('ep_general_terms_and_conditions');?>"
						   href="<?php echo __SITE_URL;?>terms_and_conditions/tc_register_seller"><?php echo translate('label_terms_and_conditions');?></a>
					</span>
				</label>
				<button class="btn btn-primary btn-block cur-pointer tt-uppercase" type="submit" <?php echo addQaUniqueIdentifier('page__subscribe__submit-btn'); ?>>
                    <?php echo translate('general_button_subscribe_text');?>
                </button>
			</form>
		</div>
	</div>
</div>
