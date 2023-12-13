<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime("public/css/smef.css"); ?>" />

<section class="smef-about">
    <img class="smef-about__img" src="<?php echo asset("public/build/images/landings/smef/smef-about-bg.jpg"); ?>" alt="SMEF About">
    <div class="smef-container">
        <h1 class="smef-about__title title-white">About SMEF</h1>
        <p class="smef-about__text text-white">
            Are you a financier interested in furthering the growth and development of small and medium-sized enterprises
            (SMEs) all over the world? Export Portal has an exciting new opportunity for you: SME Finance (SMEF).
        </p>
        <a class="btn btn-primary mnw-200 smef__btn" href="https://app.smartsheet.com/b/form/30ef73bc855c409db5ea8279cb1ee4ad">Join SMEF</a>
    </div>
</section>

<div class="smef-info">
    <div class="smef-container">
        <h3 class="smef-info__title title-black">What is SMEF?</h3>
        <p class="smef-info__text text-black">
            SMEF is where Export Portal connects third-party trade financing providers (SMEF Partner) with our
            SME importers and exporters in our ecosystem for the best-possible lending options.
            SMEF is designed to bridge global SME trade finance gap of 1.4 trillion US dollars (WTO)
            with very high loan rejection rate at 50%+ (IFC). Every effort to boost 25% growth in trade
            financing would stimulate the growth of 19% in investment, 20% in job creation, and 30% in
            production for global economy.
        </p>
    </div>
    <div class="smef-info__graphic">
        <div class="smef-container smef-info__container">
            <picture class="smef-info__image lazy-picture-background">
                <source media="(max-width: 767px)" srcset="<?php echo getLazyImage(290, 610); ?>" data-srcset="<?php echo asset("public/build/images/landings/smef/graphic-mobile.png"); ?>">
                <source media="(max-width: 1024px)" srcset="<?php echo getLazyImage(738, 331); ?>" data-srcset="<?php echo asset("public/build/images/landings/smef/graphic-tablet.png"); ?>">
                <img class="image js-lazy" src="<?php echo getLazyImage(720, 331); ?>" data-src="<?php echo asset("public/build/images/landings/smef/graphic-desktop.png"); ?>" alt="Why buy with Export Portal">
            </picture>
            <div class="smef-info__circle text-white">
                <div class="smef-info__circle-text">
                    <div class="smef-info__item-line">
                        <span class="smef-info__item-percent text-white">25%</span><span>growth</span>
                    </div>
                    in Trade Financing would stimulate:
                </div>
            </div>
            <div class="smef-info__items">
                <div class="smef-info__item">
                    <div class="smef-info__item-line">
                    <span class="smef-info__item-percent">19%</span><span>growth</span>
                    </div>
                    in Investment
                </div>
                <div class="smef-info__item">
                    <div class="smef-info__item-line">
                    <span class="smef-info__item-percent">20%</span><span>growth</span>
                    </div>
                    in Job Creation
                </div>
                <div class="smef-info__item">
                    <div class="smef-info__item-line">
                    <span class="smef-info__item-percent">30%</span><span>growth</span>
                    </div>
                    in Production for Global Economy
                </div>
            </div>
        </div>
    </div>
    <div class="smef-container">
        <p class="smef-info__text text-black">
            SMEF partners will be incentivized to have access to the rich pool of qualified trade leads and
            choose to finance transactions with our verified and certified borrowers at lower credit risk and
            greater profit scale. As a result, SMEF partners will gain more competitive advantages and stronger
            market positions. SMEF will play an important role in the mission and benefit from our growing network
            of thousands and potentially millions of international businesses in our strategic markets and industries.
        </p>
    </div>
</div>

<section class="smef-work">
    <img class="smef-work__img" src="<?php echo asset("public/build/images/landings/smef/smef-work.jpg"); ?>" alt="SMEF Work">
    <div class="smef-container">
        <h2 class="smef-work__title title-white">How does it work?</h2>
    </div>
</section>

<section class="smef-features">
    <div class="smef-container">
        <h3 class="smef-features__title title-black">Export Portal Trade Transaction</h3>
        <p class="smef-features__text text-black">
            SMEF provides new opportunities for SMEs to start fulfilling their potential on Export Portal. In the trade
            transaction, members will have the opportunity to seek financier support through SMEF directly on the platform.
            <span class="smef-features__text smef-features__text--wordbreak text-black">Here’s how that works:</span>
        </p>
    </div>
    <div class="smef-features__img">
        <img class="js-device-aware-action" data-href="<?php echo asset("public/build/images/landings/smef/transaction_scheme.jpg"); ?>"  src="<?php echo asset("public/build/images/landings/smef/transaction_scheme.jpg"); ?>" alt="Trade transaction">
    </div>
    <div class="smef-container">
        <h3 class="smef-features__title title-black">EXPORTER FINANCING</h3>
        <p class="smef-features__text text-black">
            Sellers and manufacturers will be offered diverse financing options by Export Portal’s financier partners
            (SMEF partners) who are SME-focused financial institutions, banks, and fintech companies. Once a trade order
            is paid through escrow by buyer and confirmed by EP administrator, the seller can leverage their beneficiary
            status of the escrow as a collateral to seek for export financing. Through our API, SMEF partners will be
            notified the financing order placed by the seller in relation to the confirmed transaction and the seller’s
            information for credit review and bidding. Once bidding requirements (e.g. the principal amount, LTV,
            interest rate, loan term and other provisions) are matched, the seller will accept the loan for preparing
            the products. After the delivery is complete that triggers the release of Escrow account, the funds will
            be directly transferred to the respective SMEF partner for loan repayment, and the balance will be paid to seller.
        </p>
        <h3 class="smef-features__title title-black">IMPORTER FINANCING</h3>
        <p class="smef-features__text text-black">
            Our Importer financing options are currently being developed and will be announced once they are available.
            If you have any questions about our process, please contact us.
        </p>
        <h3 class="smef-features__title title-black">WHO CAN JOIN SMEF?</h3>
        <p class="smef-features__text smef-features__text--reduced text-black">
            Any financial institutions (Banks, Fintechs, Private Equity Firms, etc.) that recognize the significant
            SME trade financing opportunity and currently find effective ways monetize it.
        </p>
    </div>
</section>

<article class="smef-contact">
    <h2 class="smef-contact__title text-white">
        <span class="smef-contact__text"><?php echo translate("landing_smef_join_smef_ttl"); ?></span>
        <a class="btn btn-primary mnw-200 smef__btn" href="https://app.smartsheet.com/b/form/30ef73bc855c409db5ea8279cb1ee4ad"><?php echo translate("landing_smef_join_smef_today_btn"); ?></a>
    </h2>
</article>

<section class="smef-slider">
    <div id="js-smef-slider">
        <div class="smef-slider__item">
            <img class="smef-slider__item-image" src="<?php echo asset("public/build/images/landings/smef/slider/slider_item_1.jpg"); ?>" alt="Join SMEF">
            <div class="smef-slider__info-wr">
                <div class="smef-slider__info">
                    <h2 class="smef-slider__title title-white">Why join SMEF?</h2>
                    <ul class="smef-slider__list">
                        <li class="smef-slider__list-item text-white"><span>Access to a great pool of qualified leads</span></li>
                        <li class="smef-slider__list-item text-white"><span>Customer acquisition cost savings</span></li>
                        <li class="smef-slider__list-item text-white"><span>Credit risk mitigation</span></li>
                        <li class="smef-slider__list-item text-white"><span>Low partnership investment costs</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="smef-slider__item">
            <img class="smef-slider__item-image" src="<?php echo asset("public/build/images/landings/smef/slider/slider_item_2.jpg"); ?>" alt="Join SMEF">
            <div class="smef-slider__info-wr">
                <div class="smef-slider__info smef-slider__info--second">
                    <h2 class="smef-slider__title title-white">Why join SMEF?</h2>
                    <ul class="smef-slider__list">
                        <li class="smef-slider__list-item text-white"><span>Revenue and profit growth at scale</span></li>
                        <li class="smef-slider__list-item text-white"><span>Strengthen your brand</span></li>
                        <li class="smef-slider__list-item text-white"><span>Finance legitimate businesses</span></li>
                        <li class="smef-slider__list-item text-white"><span>Business expansion opportunities in different markets</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="smef-slider__item">
            <img class="smef-slider__item-image" src="<?php echo asset("public/build/images/landings/smef/slider/slider_item_3.jpg"); ?>" alt="Join SMEF">
            <div class="smef-slider__info-wr">
                <div class="smef-slider__info">
                    <h2 class="smef-slider__title title-white">Why join SMEF?</h2>
                    <ul class="smef-slider__list">
                        <li class="smef-slider__list-item text-white"><span>Faster standardized process</span></li>
                        <li class="smef-slider__list-item text-white"><span>Leverage emerging advanced technologies</span></li>
                        <li class="smef-slider__list-item text-white"><span>Increase customer retention</span></li>
                        <li class="smef-slider__list-item text-white"><span>Build a network of global partners</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="smef-ep footer-connect">
    <div class="smef-container">
        <h2 class="smef-ep__title title-black">Who is Export Portal?</h2>
        <p class="smef-ep__text text-black">
            Export Portal is a digital B2B marketplace aiming to be a comprehensive international trade hub for SMEs
            and their counterparts. Empowered by our proprietary blockchain technology, EP prioritizes security,
            transparency, cost-effectiveness, and ease-of-use. Thus, our partners can confidently trade, network,
            and communicate with other verified companies and experts from all over the world.
        </p>
    </div>
</section>

<script>
    $(document).ready(function() {
        $("#js-smef-slider").bxSlider({
            mode: 'horizontal',
            infiniteLoop: true,
            controls: false,
            auto: Boolean(<?php echo !isBackstopEnabled(); ?>),
            autoStart: true,
            speed: 500,
            touchEnabled: true,
            preventDefaultSwipeY: false,
            useCSS: false
        });
    });

    $(".js-device-aware-action").click(function() {

        if (isMobile()) {
            window.open(this.dataset.href);
        } else {
            $.fancybox.open({
                autoSize: true,
                loop: false,
                closeBtn: true,
                helpers: {
                    title: {
                        type: 'inside',
                        position: 'top'
                    },
                    overlay: {
                        locked: true
                    }
                },
                closeBtnWrapper: '.fancybox-skin .fancybox-title',
                title: 'Trade transaction',
                type: 'image',
                href: this.dataset.href,
                padding: fancyP,
            });
        }
    });
</script>
