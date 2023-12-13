<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<?php if(have_right('have_pictures')){?>
	<?php views()->display('new/file_upload_scripts'); ?>

	<script>
		function callbackAddSellerPictures(resp){
			_notifyContentChangeCallback();
		}

		function callbackEditSellerPictures(resp){
			_notifyContentChangeCallback();
		}

		function callbackAddPicturesCategory(resp){
			return true;
		}

		function callbackEditPicturesCategory(resp){
			return true;
		}
	</script>
<?php }?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_pictures_pictures_word');?></h1>

	<?php if(logged_in() && $seller_view && have_right('have_pictures')){?>
		<div class="dropdown">
			<a
                class="dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                href="#"
                <?php echo addQaUniqueIdentifier('page__company-pictures__heading_dropdown-btn'); ?>
            >
				<i class="ep-icon ep-icon_menu-circles"></i>
			</a>

			<div class="dropdown-menu dropdown-menu-right">
				<button
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    data-fancybox-href="<?php echo __SITE_URL . 'seller_pictures/popup_forms/add_picture'; ?>"
                    data-title="<?php echo translate('seller_pictures_add_picture', null, true);?>"
                    type="button"
                    <?php echo addQaUniqueIdentifier('page__company-pictures__heading_dropdown-menu_add-picture-btn'); ?>
                >
					<i class="ep-icon ep-icon_pencil"></i> <?php echo translate('seller_pictures_add_picture');?>
				</button>
			</div>
		</div>
	<?php }?>
</div>

<?php views()->display('new/user/seller/pictures/list_pictures_view'); ?>

<div class="pt-35 flex-display flex-jc--sb flex-ai--c">
	<?php views()->display("new/paginator_view"); ?>
</div>
