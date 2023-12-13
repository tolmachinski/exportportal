<div class="wr-modal-flex inputs-40">
    <div class="modal-flex__form">
        <div class="modal-flex__content" id="purchase-order-details--form--wrapper">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray">
                            Price, in USD
                        </label>
                        <?php echo get_price(arrayGet($response, 'price'), false) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray">
                            From, days
                        </label>
                        <?php echo arrayGet($response, 'delivery_days_from') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray">
                            To, days
                        </label>
                        <?php echo arrayGet($response, 'delivery_days_to') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12">
                        <label class="input-label txt-gray">
                            Comment
                        </label>
                        <?php echo cleanOutput(arrayGet($response, 'comment')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>