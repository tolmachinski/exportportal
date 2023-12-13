<style>
    body {
        font-family: Roboto, sans-serif;
        font-weight: normal;
    }

    body a{
        text-decoration: none;
    }

    .white-link a{
        color: #ffffff;
    }
    .gray-link a{
        color: #333333;
    }

    .main-wrapper-dn{
            display: none;
     }

    .cards-wrapper {
        width: 789px;
    }
    @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
        .border-ie-fix{
            margin-left: 1px;
            width: 337px!important;
        }


    }

    <?php if(isset($is_pdf) && $is_pdf){?>
        .main-wrapper{
            margin: 0 auto;
            float:left;
            border: 1px solid #707070;
        }

        .main-wrapper iframe {
            border: 1px solid #E0E0E0;
        }
        .main-wrapper-dn{
         display: block !important;
        }
        .cards-wrapper {
        width: 1628px !important;
    }


    <?php }else{?>
        @font-face{font-family:Roboto;font-style:normal;font-weight:300;font-display:swap;src:url(/assets/fonts/roboto/300/KFOlCnqEu92Fr1MmSU5vAA.woff) format("woff")}
        @font-face{font-family:Roboto;font-style:normal;font-weight:300;font-display:swap;src:url(/assets/fonts/roboto/300/KFOlCnqEu92Fr1MmSU5fBBc4.woff2) format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}
        @font-face{font-family:Roboto;font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/roboto/400/KFOmCnqEu92Fr1Me5g.woff) format("woff")}
        @font-face{font-family:Roboto;font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/roboto/400/KFOmCnqEu92Fr1Mu4mxK.woff2) format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}
        @font-face{font-family:Roboto;font-style:normal;font-weight:500;font-display:swap;src:url(/assets/fonts/roboto/500/KFOlCnqEu92Fr1MmEU9vAA.woff) format("woff")}
        @font-face{font-family:Roboto;font-style:normal;font-weight:500;font-display:swap;src:url(/assets/fonts/roboto/500/KFOlCnqEu92Fr1MmEU9fBBc4.woff2) format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}

        body {
            margin: 0;
            overflow: hidden;
        }
    <?php }?>
</style>
 <div  class="cards-wrapper" style="height: 1164px; margin: 0px auto;">
     <div style="width: 789px; float:left;">
        <div class="main-wrapper" style="max-width: 789px; width: 789px; height: 1164px; margin: 0 auto 0 auto; font-family: Roboto, sans-serif; background-image: url(<?php echo asset('public/build/images/promo_materials/certified/cert-bg-front.png'); ?>); background-repeat: no-repeat; background-size: contain; background-position: bottom left;  background-color: #ffffff;">
            <table style="width: 100%; height: 1164px; padding: 78px 82px 78px 78px; color: #ffffff;  font-family: Roboto" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td style="height: 109px; vertical-align: top; text-align: center;">
                        <img width="300" height="109" src="<?php echo asset("public/build/images/promo_materials/logo.png"); ?>" alt="logo" />
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 450px; width: 450px; padding-top: 39px; vertical-align: top; text-align: center;">
                            <img width="450" height="450" src="<?php echo asset("public/build/images/promo_materials/certified/cert-user-foto.png"); ?>" alt="logo" />
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 38px; font-size: 38px; font-weight: 500; vertical-align: top; text-align: center; padding-top: 27px;">REBECCA JHONSON</td>
                    </tr>
                    <tr>
                        <td style="height: 30px; font-size: 30px; vertical-align: top; text-align: center; padding-top: 10px;">Official of: Quality Conscious Enterprises</td>
                    </tr>
                    <tr>
                        <td style="height: 34px; font-size: 34px; font-weight: 500; vertical-align: top; text-align: center; padding-top: 18px; color: #FBB032;">Certified Seller</td>
                    </tr>
                    <tr>
                        <td style="height: 190px; vertical-align: top; text-align: center; padding-top: 34px;">
                            <img width="190" height="190" src="<?php echo asset("public/build/images/promo_materials/user-qr.png"); ?>" alt="qr-code" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="main-wrapper-dn" style="width: 789px; float:left; margin-left: 50px">
        <div class="main-wrapper" style="max-width: 789px; width: 789px; height: 1164px; margin: 0 auto 0 auto; font-family: Roboto, sans-serif; background-image: url(<?php echo asset('public/build/images/promo_materials/certified/cert-bg-back.png'); ?>); background-repeat: no-repeat; background-size: contain; background-position: bottom left;  background-color: #ffffff;">
            <table style="width: 100%; height: 1164px; padding: 80px; color: #000000;  font-family: Roboto" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td style="height: 34px; font-size: 34px; font-weight: 500; vertical-align: top; text-transform: uppercase; padding-bottom: 21px;" colspan="2">Contact me:</td>
                    </tr>
                    <tr>
                        <td style="height: 60px; width: 60px; vertical-align: top; padding-top: 8px;">
                            <table style="width: 100%; border-collapse:collapse;">
                                <tr>
                                    <td style="width: 75px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/phone.png"); ?>" alt="phone" />
                                    </td>
                                    <td style="width: 549px; font-size: 28px;">
                                        +37360 000 000
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 75px; padding-top: 18px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/mail.png"); ?>" alt="mail" />
                                    </td>
                                    <td style="width: 549px; font-size: 28px; padding-top: 22px;">
                                    epseller@exportportal.com
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 75px; padding-top: 18px;">
                                    <img width="60" height="60" src="<?php echo asset("public/build/images/promo_materials/placeholder.png"); ?>" alt="placeholder" />
                                    </td>
                                    <td style="width: 549px; font-size: 28px; padding-top: 21px">
                                    United States of America, California, Burbank, 9150
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 681px; font-size: 20px; vertical-align: bottom; text-align: center; padding-bottom: 17px; line-height: 28px; color: #ffffff;">
                        The Export Portal membership provides benefits on <span style="color: #2181F8;">ExportPortal.com</span>. The use of this card constitutes as acceptance of all published Terms and Conditions, viewable at <span style="color: #2181F8;">ExportPortal.com/terms_and_conditions</span>. This card is valid for this account only and is non-transferrable. For questions or concerns, please contact us at <span style="color: #2181F8;">support@exportportal.com</span>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

