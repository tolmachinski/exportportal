<?php if(DEBUG_MODE){?>
	<button
		class="fixed-rigth-block__item fixed-rigth-block__item--new-page call-function"
		data-callback="siteNewPage"
		title="<?php echo translate('accreditation_mes_change_to_old_design'); ?>"
        type="button"
	>
		<span class="fixed-rigth-block__item-icon"><i class="ep-icon <?php echo 'ep-icon_new-stroke';?>"></i></span>
		<span class="fixed-rigth-block__item-text">
			<span class="fixed-rigth-block__item-text-inner">
				<?php echo translate('accreditation_mes_change_to_old_design');?>
			</span>
		</span>
	</button>
	<script>
		var siteNewPage = function () {
			if (__debug_mode) {
				if (existCookie('_ep_legacy_mode')) {
					removeCookie('_ep_legacy_mode');
				} else {
					setCookie('_ep_legacy_mode', 1, 365);
				}

				document.location.reload(true);
			}

			return false;
		};
	</script>
<?php }?>
