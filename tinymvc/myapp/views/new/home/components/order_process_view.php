<section class="home-section order-process container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo translate('home_order_process_header_title'); ?></h2>
        <p class="section-header__subtitle"><?php echo translate('home_order_process_header_subtitle'); ?></p>
    </div>

    <div class="order-process__content">
        <?php foreach ($orderProcessData as $key => $order) { ?>
            <?php $orderNumber = $key + 1; ?>
            <div class="order-process__item">
                <img
                    class="order-process__icon js-lazy"
                    src="<?php echo getLazyImage(50, 50); ?>"
                    data-src="<?php echo $order['icon']; ?>"
                    alt="<?php echo $order['title']; ?>"
                >
                <div class="order-process__number"><?php echo "0{$orderNumber}"; ?></div>
                <h3 class="order-process__title"><?php echo $order['title']; ?></h3>
                <p class="order-process__description"><?php echo $order['description']; ?></p>
            </div>
        <?php } ?>
    </div>
</section>
