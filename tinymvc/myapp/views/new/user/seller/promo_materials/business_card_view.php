<style>
    body {
        font-family: Roboto, sans-serif;
        font-weight: normal;
    }

    body a {
        text-decoration: none;
    }

    .white-link a {
        color: #ffffff;
    }

    .gray-link a {
        color: #333333;
    }

    .main-wrapper-dn {
        display: none;
    }

    .cards-wrapper {
        width: 1050px;
    }


    @media all and (-ms-high-contrast: none),
    (-ms-high-contrast: active) {
        .border-ie-fix {
            margin-left: 1px;
            width: 337px !important;
        }


    }

    <?php if (isset($is_pdf) && $is_pdf) { ?>.main-wrapper {
        margin: 0 auto;
        float: left;
        border: 1px solid #707070;
    }

    .main-wrapper-dn {
        display: block !important;
    }

    .cards-wrapper {
        width: 2150px !important;
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

    body {
        margin: 0;
        overflow: hidden;
    }

    <?php } ?>
</style>
<div class="cards-wrapper" style="height: 600px; margin: 0px auto; ">
    <div style="width: 1050px; float:left;">
        <div class="main-wrapper" style="max-width: 1050px; width: 1050px; height: 600px; margin: 0 auto 0 auto; font-family: Roboto, sans-serif; background-image: url(<?php echo asset('public/build/images/promo_materials/certified/cert-card-front.png'); ?>); background-repeat: no-repeat; background-size: 53%; background-position: 100% 1px;  background-color: #00244E;">
            <table style="width: 100%; height: 600px; padding: 57px 58px 37px 58px; color: #ffffff;  font-family: Roboto" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td style="width:500px; height: 300px; vertical-align: top; text-align:center; ">
                            <img width="250" src="<?php echo asset("public/build/images/promo_materials/certified/cert-logo-front.png"); ?>" alt="seller logo">
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 30px; font-size: 44px; font-weight: 500; vertical-align: top; padding-top: 20px; text-align:center;" colspan="1">Quality Conscious Enterprises</td>
                    </tr>
                    <tr>
                        <td style="height: 34px; font-size: 30px;  vertical-align: top; padding-top: 18px; color: #FBB032; text-align:center;" colspan="1">Certified Seller</td>
                        <td style="height: 80px; vertical-align: top; text-align:right;" colspan="1">
                            <img style="margin-top: -16px; margin-right: -5px;" width="220" height="80" src="<?php echo asset("public/build/images/promo_materials/logo.png"); ?>" alt="ogo">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="main-wrapper-dn" style="width: 1050px; float:left; margin-left: 50px;">
    <div class="main-wrapper" style="max-width: 1050px; width: 1050px; height: 600px; margin: 0 auto 0 auto; font-family: Roboto, sans-serif; background-image: url(<?php echo asset('public/build/images/promo_materials/certified/cert-card-back.png'); ?>); background-repeat: no-repeat; background-size: contain; background-position: right bottom;  background-color: #ffffff;">
        <table style="width: 100%; padding: 74px 75px 75px 77px; color: #000000; font-family: Roboto" border="0" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td style="width: 600px; vertical-align: top;" colspan="1">
                        <table>
                            <tr>
                                <td style="font-size: 44px; font-weight: 500;">Rebecca Jhonson</td>
                            </tr>
                            <tr>
                                <td style="font-size: 28px; padding-top: 12px">Official Representative</td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align: right; padding-right: 17px;">
                        <img style="margin-top: 13px;" width="170" height="170" src="<?php echo asset("public/build/images/promo_materials/user-qr.png"); ?>" alt="qrcode" />
                    </td>
                </tr>
                <tr>
                    <td style="width:600px; height: 60px; vertical-align: top;" colspan="1">
                        <table style="width: 100%; border-collapse:collapse; margin-top: -30px;">
                            <tr>
                                <td style="width: 80px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/phone.png"); ?>" alt="phone" />
                                </td>
                                <td style="width: 549px; font-size: 26px;">
                                    +37360 000 000
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 80px; padding-top: 30px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/mail.png"); ?>" alt="mail" />
                                </td>
                                <td style="width: 549px; font-size: 26px; padding-top: 26px;">
                                    epseller@exportportal.com
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 80px; padding-top: 29px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/placeholder.png"); ?>" alt="placeholder" />
                                </td>
                                <td style="width: 549px; font-size: 26px; padding-top: 28px">
                                    United States of America, California, Burbank, 9150
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
