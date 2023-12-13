<?php $is_logged_in = logged_in(); ?>

<?php // ===== Featured Items ===== //?>
<?php views('new/home/components/featured_items_view');?>

<?php // ===== Shop by Category ===== // ?>
<?php if (!empty($goldenCategories)) {?>
    <?php views('new/home/components/golden_categories_view');?>
<?php }?>

<?php // ===== Updates from Export portal ===== // ?>
<?php views('new/home/components/updates_from_ep_view');?>

<?php // ===== Buyer benefits ===== // ?>
<?php views('new/home/components/buyer_benefits_view');?>

<?php // ===== Picks of the month ===== // ?>
<?php views('new/home/components/picks_of_the_month_view');?>

<?php // ===== Latest products ===== // ?>
<?php views('new/home/components/latest_items_view');?>

<?php // ===== Seller benefits ===== // ?>
<?php views('new/home/components/seller_benefits_view');?>

<?php // ===== Top 50 products ===== // ?>
<?php views('new/home/components/top_products_view');?>

<?php // ===== Manufacturer benefits ===== // ?>
<?php views('new/home/components/manufacturer_benefits_view');?>

<?php // ===== Exclusive deals ===== // ?>
<?php views('new/home/components/exclusive_deals_view');?>

<?php // ===== Blogs ===== // ?>
<?php views('new/home/components/blogs_view');?>

<?php // ===== Shipper benefits ===== // ?>
<?php views('new/home/components/shipper_benefits_view');?>

<?php // ===== EP Security ===== // ?>
<?php views('new/home/components/security_view');?>
