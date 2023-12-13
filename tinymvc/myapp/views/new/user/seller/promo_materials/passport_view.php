<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
        font-family: Roboto, sans-serif;
        font-weight: normal;
        color: #f5f2f5;
        background-color: #F5F5F5;
    }

    .passport__wrapper {
        width: 654px;
        height: 470px;
        padding-top: 28px;
        padding-bottom: 31px;
        padding-left: 84px;
        padding-right: 88px;
        border: 0;
    }

    .passport {
        float: left;
        width: 619px;
        height: 398px;
        overflow: hidden;
        margin-bottom: 50px;
        padding: 13px 20px 0px 17px;
        background-image: url(<?php echo asset('public/build/images/promo_materials/passport-background-front.png'); ?>);
        background-repeat: no-repeat;
        background-position: 1px 1px;
        background-size: 110%;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
        border-radius: 10px;
    }

    .passport-dn {
        display: none;
    }

    .passport__header {
        border-bottom: 1px solid #C4C6C7;
        padding-bottom: 17px;
    }

    .passport__header-left {
        display: inline-block;
    }

    .passport__header-right {
        display: inline-block;
    }

    .passport__wrapper {
        box-shadow: none !important;
    }

    .passport__header {
        box-shadow: none !important;
        border-radius: 0 !important;
    }



    <?php if (isset($is_pdf) && $is_pdf) { ?>.passport {
        box-shadow: 0px 0px 0px rgba(0, 0, 0, 1);
    }

    <?php } else { ?>@font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(/assets/fonts/roboto/300/KFOlCnqEu92Fr1MmSU5vAA.woff) format("woff")
    }

    @font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(/assets/fonts/roboto/300/KFOlCnqEu92Fr1MmSU5fBBc4.woff2) format("woff2");
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
    }

    @font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(/assets/fonts/roboto/400/KFOmCnqEu92Fr1Me5g.woff) format("woff")
    }

    @font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(/assets/fonts/roboto/400/KFOmCnqEu92Fr1Mu4mxK.woff2) format("woff2");
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
    }

    @font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(/assets/fonts/roboto/500/KFOlCnqEu92Fr1MmEU9vAA.woff) format("woff")
    }

    @font-face {
        font-family: Roboto;
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(/assets/fonts/roboto/500/KFOlCnqEu92Fr1MmEU9fBBc4.woff2) format("woff2");
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD
    }

    <?php } ?>@media all and (-ms-high-contrast: none),
    (-ms-high-contrast: active) {
        .passport {
            box-shadow: none;
            border: 1px solid #ffffff;
            border-color: #C4C6C7;
        }

        .passport-dn {
            display: block;
        }
    }

    .passport__header-logo {
        width: 41px;
        height: 49px;
        float: left;
        margin-right: 12px;
        padding-top: 1px;
    }

    .passport__header-title {
        font-size: 19px;
        line-height: 25px;
        font-weight: 500;
        text-transform: uppercase;
        color: #000000;
    }

    .passport__header-number {
        padding-top: 3px;
        font-size: 15px;
        line-height: 19px;
        font-weight: 500;
        text-transform: uppercase;
        color: #16294E;
    }

    .passport__info-row {
        height: 36px;
        margin-bottom: 6px;
    }



    .passport__front {
        padding-top: 38px;
        padding-left: 3px;
    }


    .certified .passport__front-badge {
        background-image: url(<?php echo asset('public/build/images/promo_materials/badge.png'); ?>);
        width: 242px;
        background-position: 101% 101%;
        background-size: 37%;
        height: 242px;
        background-repeat: no-repeat;
    }

    .passport__info-title {
        font-size: 12px;
        color: #353535;
    }

    .passport__info-value {
        font-size: 14px;
        line-height: 20px;
        font-weight: 500;
        text-transform: uppercase;
        color: #000000;
    }

    .passport__front-info {
        padding-left: 266px;
    }

    .passport__front-image {
        float: left;
        width: 242px;
        height: 242px;
        border: 2px solid #000000;
        overflow: hidden;
        margin-right: 20px;
        margin-top: 2px;
    }

    .certified .passport__front-image {
        border-color: #FBB032;
    }

    .verified .passport__front-image {
        border-color: #00BE53;
    }

    .passport__front-photo {
        width: 242px;
        height: 242px;
        background: url(<?php echo getUserAvatar($user['idu'], $user['user_photo'], null, 1); ?>);
        background-position: center center;
        background-size: cover;
        background-repeat: no-repeat;
    }

    .passport__back-text {
        padding: 55px 55px 52px 55px;
        font-size: 18px;
        line-height: 26px;
        font-weight: normal;
        text-align: center;
        color: #000000;
    }

    .passport__back-text strong {
        font-weight: 500;
    }

    .passport__back-footer {
        font-size: 18px;
        font-weight: 500;
        line-height: 22px;
        text-transform: uppercase;
        color: #000000;
        background-color: #ffffff;
    }

    table tr td img {
        width: 18px;
        height: 18px;
    }

    @media (max-width: 991px) {
        .passport__wrapper {
            height: 557px;
            margin-top: 18px;
            padding-top: 50px;
            padding-bottom: 0px;
            padding-left: 33px;
            padding-right: 0px;
        }

        .tablet {
            display: block;
        }

        .passport {
            width: 582px;
            height: 375px;
            padding: 11px 20px 0 17px;
        }

        .passport__header {
            padding-bottom: 15px;
        }

        .passport__header-logo {
            width: 38px;
            height: 43px;
            margin-right: 10px;
        }

        .passport__header-title {
            padding-top: 1px;
            font-size: 18px;
            line-height: 24px;
        }

        .passport__header-number {
            font-size: 14px;
        }

        .passport__front {
            padding-top: 33px;
        }

        .passport__front-image {
            width: 228px;
            height: 228px;
            margin-right: 16px;
        }

        .passport__front-photo {
            width: 228px;
            height: 228px;
        }

        .certified .passport__front-badge {
            width: 228px;
            height: 228px;
        }

        .passport__front-info {
            padding-left: 246px;
            padding-top: 1px;
        }

        .passport__info-row {
            height: 34px;
        }

        .passport__info-value {
            line-height: 22px;
        }

    }

    @media(max-width: 767px) {
        .passport__wrapper {
            width: 654px;
            height: 470px;
            padding-top: 28px;
            padding-bottom: 31px;
            padding-left: 84px;
            padding-right: 88px;
            margin-top: 0;
        }

        .passport {
            width: 619px;
            height: 398px;
            padding: 13px 20px 0px 17px;
        }

        .passport__header {
            padding-bottom: 17px;
            border-bottom: 1px solid #C4C6C7;
        }

        .passport__header-logo {
            width: 41px;
            height: 49px;
            margin-right: 12px;
        }

        .passport__header-title {
            font-size: 19px;
            line-height: 25px;
        }

        .passport__header-number {
            font-size: 14px;
        }

        .passport__front {
            padding-top: 33px;
        }

        .passport__front-image {
            width: 242px;
            height: 242px;
            margin-right: 16px;
        }

        .passport__front-photo {
            width: 242px;
            height: 242px;
        }

        .certified .passport__front-badge {
            width: 242px;
            height: 242px;
        }

        .passport__front-info {
            padding-left: 266px;
        }

        .passport__info-row {
            height: 36px;
        }

        .passport__info-value {
            line-height: 20px;
        }
    }


    @media(max-device-width: 991px) {
        .passport__wrapper {
            height: 557px;
            margin-top: 18px;
            padding-top: 50px;
            padding-bottom: 0px;
            padding-left: 33px;
            padding-right: 0px;
        }

        .tablet {
            display: block;
        }

        .passport {
            width: 582px;
            height: 375px;
            padding: 11px 20px 0 17px;
        }

        .passport__header {
            padding-bottom: 15px;
        }

        .passport__header-logo {
            width: 38px;
            height: 43px;
            margin-right: 10px;
        }

        .passport__header-title {
            padding-top: 1px;
            font-size: 18px;
            line-height: 24px;
        }

        .passport__header-number {
            font-size: 14px;
        }

        .passport__front {
            padding-top: 33px;
        }

        .passport__front-image {
            width: 228px;
            height: 228px;
            margin-right: 16px;
        }

        .passport__front-photo {
            width: 228px;
            height: 228px;
        }

        .certified .passport__front-badge {
            width: 228px;
            height: 228px;
        }

        .passport__front-info {
            padding-left: 246px;
            padding-top: 1px;
        }

        .passport__info-row {
            height: 34px;
        }

        .passport__info-value {
            line-height: 22px;
        }

    }

    @media(max-device-width: 767px) {
        .passport__wrapper {
            width: 654px;
            height: 470px;
            padding-top: 28px;
            padding-bottom: 31px;
            padding-left: 84px;
            padding-right: 88px;
            margin-top: 0;
        }

        .passport {
            width: 619px;
            height: 398px;
            padding: 13px 20px 0px 17px;
        }

        .passport__header {
            border-bottom: 1px solid #C4C6C7;
            padding-bottom: 17px;
        }

        .passport__header-logo {
            width: 41px;
            height: 49px;
            margin-right: 12px;
        }

        .passport__header-title {
            font-size: 19px;
            line-height: 25px;
        }

        .passport__header-number {
            font-size: 14px;
        }

        .passport__front {
            padding-top: 33px;
        }

        .passport__front-image {
            width: 242px;
            height: 242px;
            margin-right: 16px;
        }

        .passport__front-photo {
            width: 242px;
            height: 242px;
        }

        .certified .passport__front-badge {
            width: 242px;
            height: 242px;
        }

        .passport__front-info {
            padding-left: 266px;
        }

        .passport__info-row {
            height: 36px;
        }

        .passport__info-value {
            line-height: 20px;
        }
    }
</style>
<div class="passport__wrapper">

    <div class="passport <?php echo (is_buyer((int) $user['user_group']) || is_shipper((int) $user['user_group'])) ? '' : (is_certified((int) $user['user_group']) ? 'certified' : 'verified'); ?>">
        <div class="passport__header">
            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="">
                        <table style="width: 100%; " border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="width: 50%; padding-top: 3px">
                                    <table>
                                        <tr>
                                            <td>
                                                <img class="passport__header-logo" src="<?php echo asset("public/build/images/logo/ep_logo.png"); ?>" alt="Passport logo">
                                            </td>
                                            <td>
                                                <span class="passport__header-title" style="display: block;">EXPORT PORTAL <br> TRADE PASSPORT</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="padding-top: 3px; padding-left: 5px;">
                                    <div class="passport__info-title">
                                        Document No.
                                    </div>
                                    <div class="passport__header-number">
                                        <?php echo $documentNr; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="passport__front">
            <div class="passport__front-image ">
                <div class="passport__front-photo">
                    <div class="passport__front-badge">
                    </div>
                </div>
            </div>
            <div class="passport__front-info">
                <div class="passport__info-row">
                    <div class="passport__info-title">
                        Name
                    </div>
                    <div class="passport__info-value">
                        <?php echo $user['fname']; ?>
                    </div>
                </div>
                <div class="passport__info-row">
                    <div class="passport__info-title">
                        Surname
                    </div>
                    <div class="passport__info-value">
                        <?php echo $user['lname']; ?>
                    </div>
                </div>
                <div class="passport__info-row" style="margin-bottom: 7px;">
                    <div class="passport__info-title">
                        Country of origin
                    </div>
                    <div class="passport__info-value">
                        <?php echo $country['country']; ?>
                    </div>
                </div>
                <?php if (!empty($companyName)) { ?>
                    <div class="passport__info-row">
                        <div class="passport__info-title">
                            Company Issued
                        </div>
                        <div class="passport__info-value">
                            <?php echo $companyName; ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="passport__info-row">
                    <div class="passport__info-title">
                        Identification
                    </div>
                    <div class="passport__info-value">
                        <?php echo $user['gr_name']; ?>
                    </div>
                </div>
                <div class="passport__info-row">
                    <div class="passport__info-col" style="float: left; width: 69%;">
                        <div class="passport__info-title">
                            Issued date
                        </div>
                        <div class="passport__info-value">
                            <?php echo getDateFormat($user['registration_date'], null, 'm/d/Y'); ?>
                        </div>
                    </div>
                    <div class="passport__info-col" style="float: right;">
                        <div class="passport__info-title">
                            Date of expiration
                        </div>
                        <div class="passport__info-value">
                            <?php echo $expirationDate; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="passport passport-dn">
        <div class="passport__header">
            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <table style="width: 100%; " border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="width: 50%; padding-top: 3px">
                                    <table>
                                        <tr>
                                            <td>
                                                <img class="passport__header-logo" src="<?php echo asset("public/build/images/logo/ep_logo.png"); ?>" alt="Passport logo">
                                            </td>
                                            <td>
                                                <span class="passport__header-title" style="display: block;">EXPORT PORTAL <br> TRADE PASSPORT</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="padding-top: 3px; padding-left: 5px;">
                                    <div class="passport__info-title">
                                        Document No.
                                    </div>
                                    <div class="passport__header-number">
                                        <?php echo $documentNr; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="passport__back">
            <div class="passport__back-text">
                <strong style="font-weight: bold;">EXPORT PORTAL TRADE PASSPORT</strong> is a user’s unique digital identity on Export Portal, on which users can market products, post updates, and make connections. This trade passport will make our website more exclusive and will help our users to feel even more secure.
            </div>
        </div>
    </div>
</div>
