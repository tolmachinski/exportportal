<?php if (count($items) > 0) { ?>
    <div class="products">
        <?php
            views()->display('new/item/list_item_view', ['items' => array_slice($items, 0, 8), 'removeLazyFirstImg' => true]);

            encoreLinks();

            if (count($items) > 8) {
                views()->display('new/item/list_item_view', ['items' => array_slice($items, 8)]);
            }
        ?>
    </div>
<?php } else { ?>

	<?php if ($items_not_cheerup) { ?>
		<div class="w-100pr doc-info-b mb-10">
			<div class="info-alert-b">
				<i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('items_list_no_items_message'); ?>
			</div>
		</div>
	<?php } else { ?>
		<?php views('new/partials/results_not_found_view'); ?>
	<?php } ?>

    <?php
        if ($addEncoreLinks ?? false) {
            encoreLinks();
        }
    ?>
<?php } ?>

<?php if ($category['is_restricted'] && !cookies()->exist_cookie('ep_age_verification')) { ?>
    <?php if($useLegacyCode) { ?>
        <script src="<?php echo fileModificationTime('public/plug/js/categories/open-age-verification.js'); ?>"></script>
        <script>
            $(function(){
                openAgeVerificationModal(null, true);
            });
        </script>
    <?php } else { ?>
        <?php echo dispatchDynamicFragment("popup:open-age-verification", [ 'detail' => [ 'redirectClose' => true ] ], true); ?>
    <?php } ?>
<?php } ?>
