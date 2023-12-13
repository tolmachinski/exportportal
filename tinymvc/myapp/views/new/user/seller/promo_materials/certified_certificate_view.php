<style>
    * {
        box-sizing: border-box;
    }

    a {
        color: #ffffff !important;
        text-decoration: none;
    }

    body {
        width: 100%;
        height: 100%;
        font-family: Roboto, sans-serif;
        font-weight: normal;
    }

    .sidebar-bottom a {
        color: #ffffff;
        text-decoration: none;
    }

    @media all and (-ms-high-contrast: none),
    (-ms-high-contrast: active) {
        .border-fix {
            height: 3px !important;
        }

        a {
            color: #ffffff !important;
        }
    }

    @-moz-document url-prefix() {
        .border-fix {
            height: 3px !important;
        }

        a {
            color: #ffffff !important;
            text-decoration: none;
        }
    }

    <?php if ($is_pdf ?? false) { ?>.main-wrapper {
        padding-top: 80px;
    }

    a {
        color: #ffffff !important;
        text-decoration: none;
    }

    .main-wrapper {
        border: 1px solid #E0E0E0;
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

    a {
        color: #ffffff !important;
        text-decoration: none;
    }

    <?php } ?>
</style>

<div class="main-wrapper" style="max-width: 2480px; width: 2480px; height: 3503px; margin: 0 auto 0 auto; font-family: Roboto, sans-serif; background-image: url(<?php echo asset('public/build/images/promo_materials/certified/bg-cert-seller.png'); ?>); background-color: #ffffff; background-repeat: no-repeat; background-size: contain; background-position: right bottom;">
    <table style="width: 100%; height: 3508px; border-collapse: collapse; color: #ffffff;  font-family: Roboto" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td style="height: 3508px;  vertical-align: top" colspan="3">
                <table style="width: 100%; height: 560px; border-collapse: collapse; color: #000000; vertical-align: top" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height: 273px; vertical-align: top; padding: 197px 0px 0px 196px;" colspan="3">
                            <img width="750" height="273" src="<?php echo asset("public/build/images/promo_materials/logo.png"); ?>" alt="logo" />
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 563px; padding-top: 199px; vertical-align: top" colspan="3">
                            <img width="2344" height="563" src="<?php echo asset("public/build/images/promo_materials/certified/cert-seller.png"); ?>" alt="certificate" />
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 54px; padding: 250px 0 0 196px; vertical-align: top; font-size: 54px; line-height: 62px" colspan="3">
                            This certificate is presented to
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <table style="width: 100%; border-collapse:collapse; color:#000000;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height: 120px; padding: 100px 0 0 196px; vertical-align: top; font-size: 120px; line-height: 148px; font-weight: 500">
                                        <div>
                                            <?php echo $company['name_company']; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 48px 0 0px 196px;">
                                        <table style="width: 1670px; height: 3px; padding: 48px 0 0 196px; border-collapse:collapse; color:#000000; text-align: center;" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="border-fix" style="width: 1670px; height: 3px; background: #9E9E9E;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height: 80px; padding: 36px 0 0 196px; vertical-align: top;" colspan="3">
                                        <div style="font-size: 80px; line-height: 92px; font-weight: 500;">
                                            <?php echo $user['fname'] . ' ' . $user['lname']; ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 180px; padding: 144px 700px 0px 196px; vertical-align: top; font-size: 60px; line-height: 85px;" colspan="3">
                            And certifies that on <?php echo getDateFormat($user['activation_account_date'], null, 'F jS Y'); ?> they completed the requirements designated by Export Portal to become a<br>
                            <span style="color: #FBB032;"><?php echo $user['gr_name']; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 350px; padding: 197px 0 64px 196px; vertical-align: top" colspan="3">
                            <img style="display: block; padding: 35px; border: 2px solid #000000;" width="350" height="350" src="<?php echo $qrCode; ?>" alt="qr code" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="width: 980px; height: 60px; padding: 145px 260px 0 196px; vertical-align: top; font-size: 36px; line-height: 44px">
                                        Disclaimer: This certificate is non-transferrable and must be returned to Export Portal should your account be suspended, revoked, or invalidated. If your certificate is not renewed, it becomes invalid.
                                        <?php if (!empty($businessLicense['date_latest_version_expires'])) { ?>
                                            Certificate will expire on <?php echo getDateFormat($businessLicense['date_latest_version_expires'], null, 'F jS Y'); ?>.
                                        <?php } ?>
                                    </td>
                                    <td style="width: 700px; padding: 0px 200px 30px 0px; vertical-align: top; font-size: 46px; color: #ffffff !important; text-align: right;">
                                        <table style="width: 100%;vertical-align: top; font-size: 46px; color: #ffffff !important; text-align: right;">
                                            <tr>
                                                <td style="font-size: 44px; color: #fff !important;">
                                                    <div style="font-size: 44px; color: #fff !important;"><?php echo config('ep_phone_number'); ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 44px; color: #ffffff !important; padding-top: 28px;"><?php echo config('ep_phone_number_free'); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 35px; color: #ffffff !important"><?php echo config('email_contact_us'); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 26px; color: #ffffff !important">www.exportportal.com</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
