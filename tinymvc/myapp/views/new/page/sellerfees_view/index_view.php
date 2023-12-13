<script>
    $(function() {
        if(($('.js-sometable').length > 0) && ($(window).width() < 768)) {
            $('.js-sometable').addClass('main-data-table--mobile');
        }

        mobileDataTable($('.js-sometable'));
    });

    jQuery(window).on('resizestop', function () {
        if($('.js-sometable').length > 0) {
            if($(window).width() < 768) {
                $('.js-sometable').addClass('main-data-table--mobile');
            } else {
                $('.js-sometable').removeClass('main-data-table--mobile');
            }
        }
    });
</script>

<h2 class="fees__title">Standard Export Fees for Sellers & Manufacturers</h2>

<div class="ep-large-text">
    <p>Both Verified and <strong class="txt-bold">Certified Sellers and Manufacturers</strong> register for free, with Certified Sellers and Manufacturers receiving the same benefits as Verified Sellers and Manufacturers and other prime, exclusive benefits. However, both accounts are still required to pay the export transaction fee.</p>
    <p>Your transaction fee depends on the Total Sale Price (TSP) of your purchase. The percentage of the <strong class="txt-bold">export fee ranges</strong> from 4% - 16%. For example, if your TSP is $4,500, your transaction fee would be 12% of $4,500, which is $540.</p>
</div>

<table class="js-sometable main-data-table">
    <thead>
        <tr>
            <th>Total Sale Price (TSP) in USD</th>
            <th>TSP Export Fee Percentage for Sellers & Manufacturers</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$0 - $200.00</td>
            <td>16% of the TSP, with a minimum fee of $2.00 per item</td>
        </tr>
        <tr>
            <td>$200.01 - $800.00</td>
            <td>15% of the TSP</td>
        </tr>
        <tr>
            <td>$800.01 - $5,000.00</td>
            <td>12% of the TSP</td>
        </tr>
        <tr>
            <td>$5,000.01 - $10,000.00</td>
            <td>9% of the TSP</td>
        </tr>
        <tr>
            <td>$10,000.01 - $25,000.00</td>
            <td>6% of the TSP</td>
        </tr>
        <tr>
            <td>$25,000.01 - $100,000.00</td>
            <td>4% of the TSP</td>
        </tr>
        <tr>
            <td>More than $100,000</td>
            <td><span>Orders over <strong>$100,000</strong> will be processed under a separate pre-negotiated escrow and brokerage agreement. If an order is placed, Export Portal will draft a separate agreement and will contact the buyer and seller with this special agreement within 48 business hours.</span></td>
        </tr>
    </tbody>
</table>

<div class="ep-large-text pt-30">
    <p>
        If you have any questions about how our seller and manufacturer import/export fees are calculated, <a href="<?php echo __SITE_URL;?>contact">contact our support team</a>.
    </p>
</div>
