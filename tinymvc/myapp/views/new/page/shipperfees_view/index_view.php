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

<h2 class="fees__title">Standard Fees for Freight Forwarders</h2>

<div class="row">
    <div class="col-12 ep-large-text">
        <p>Freight Forwarders can register for free, but are still required to pay a <strong class="txt-bold">freight forwarder transaction fee</strong> on Export Portal. This page describes the freight forwarder price list and how our fees are calculated.</p>
        <div>
            Your <strong class="txt-bold">transaction</strong> fee depends on the Total Sale Price (TSP) of item you are shipping. The <strong class="txt-bold">percentage</strong> of the freight forwarder fee ranges from 4% - 12.9%. For example, if your TSP is $7,500, your transaction fee would be as follows:
            <ul class="list-disc">
                <li>12.9% of $500, which is <strong>$64.50</strong></li>

                <li>10.9% of $1,000, which is <strong>$109</strong></li>

                <li>8.9% of $1,500, which is <strong>$133.50</strong></li>

                <li>6.9% of $2,000, which is <strong>$138</strong></li>

                <li>4.9% of the remaining TSP, $2,500, which is <strong>$122.50</strong></li>
            </ul>
            That means, for a freight forwarder, <strong>the total transaction fee for an order worth $7,500 would be $567.50</strong>.<br>Here is a table that lays out all the percentages in the freight forwarder price list:
        </div>
    </div>
</div>

<br>

<table class="js-sometable main-data-table">
    <thead>
        <tr>
            <th>Total Sale Price (TSP) in USD</th>
            <th>TSP Fee Percentage for Freight Forwarders</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$0 - $500.00</td>
            <td>12.9% of the TSP with a minimum fee of $19.99</td>
        </tr>
        <tr>
            <td>$500.01 - $1,500.00</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of the remaining TSP</td>
        </tr>
        <tr>
            <td>$1,500.01 - $3,000.00</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of $1,000 of the TSP, plus 8.9% of the remaining TSP</td>
        </tr>
        <tr>
            <td>$3,000.01 - $5,000.00</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of $1,000 of the TSP, plus 8.9% of $1,500 of the TSP, plus 6.9% of the remaining TSP</td>
        </tr>
        <tr>
            <td>$5,000.01 - $7,500.00</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of $1,000 of the TSP, plus 8.9% of $1,500 of the TSP, plus 6.9% of $2,000 of the TSP, plus 4.9% of the remaining TSP</td>
        </tr>
        <tr>
            <td>$7,500.01 - $10,500.00</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of $1,000 of the TSP, plus 8.9% of $1,500 of the TSP, plus 6.9% of $2,000 of the TSP, plus 4.9% of $2,500 of the TSP, plus 2.9% of the remaining TSP</td>
        </tr>
        <tr>
            <td>More that $10,500</td>
            <td>12.9% of $500 of the TSP, plus 10.9% of $1,000 of the TSP, plus 8.9% of $1,500 of the TSP, plus 6.9% of $2,000 of the TSP, plus 4.9% of $2,500 of the TSP, plus 2.9% of $3,000 of the TSP, plus 0.9% of the remaining TSP</td>
        </tr>
    </tbody>
</table>

<div class="ep-large-text pt-30">
    <p>
        If you have any questions about how this freight forwarder fee is calculated, <a href="<?php echo __SITE_URL;?>contact" <?php echo addQaUniqueIdentifier('page__shipperfees__standart-fees_contact-our-team-link') ?>>please contact our support team</a>.
    </p>
</div>
