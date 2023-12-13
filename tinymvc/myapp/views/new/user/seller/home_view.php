<div class="display-n" itemscope itemtype="http://schema.org/Organization">
	<a itemprop="url" href="<?php echo __SITE_URL . 'seller/' . strForUrl($company['name_company']) . '-' . $company['id_company']; ?>"><?php echo $company['name_company'];?></a>
	<h2 itemprop="name"><?php echo $company['name_company'];?></h2>
    <img
        itemprop="logo"
        src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main'); ?>"
        alt="<?php echo $user_main['name_company']; ?>" />

	<div itemprop="description">
		<?php echo strip_tags(truncWords($company['description_company'])); ?>
	</div>

	<?php if($company['rating_count_company'] > 0){ ?>
        <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/aggregateRating">
            <span itemprop="ratingValue"><?php echo $company['rating_company'];?></span>
            <span itemprop="reviewCount"><?php echo $company['rating_count_company']?></span>
        </div>
	<?php } ?>

	<div itemprop="member" itemscope itemtype="http://schema.org/OrganizationRole">
		<div itemprop="member" itemscope itemtype="http://schema.org/Person">
			<span itemprop="name"><?php echo $user_main['fname'].' '.$user_main['lname']; ?></span>
		</div>
		<span itemprop="roleName"><?php echo translate('seller_home_page_seller_role');?></span>
	</div>

	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<span itemprop="addressLocality">
            <?php echo $company['country']?>
            <?php if(!empty($company['state'])){?>
                , <?php echo $company['state'];?>
            <?php }?>
            <?php if(!empty($company['city'])){?>
                , <?php echo $company['city'];?>
            <?php }?>
        </span>
		<span itemprop="streetAddress"><?php echo $company['address_company'];?></span>
		<span itemprop="postalCode"><?php echo $company['zip_company'];?></span>
	</div>
</div>
<div class="row">
    <div class="col-lt-12 col-lg-8">
        <?php views()->display('new/user/seller/company/company_menu_block'); ?>

        <div class="seller-detail-info">
            <?php
                if (!empty($wall_items)) {
                    views()->display('new/user/seller/wall/list_view');
                } else {
                    views()->display('new/user/seller/detail_view');
                }
            ?>
        </div>
    </div>

    <div class="col-md-4 seller-sidebar-info hide-1024">
        <?php views()->display('new/user/seller/sidebar_seller_info_view');?>
    </div>
</div>

