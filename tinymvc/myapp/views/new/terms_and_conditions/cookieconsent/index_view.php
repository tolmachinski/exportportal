<?php if(!isset($webpackData)){?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/terms.css');?>" />
<?php }?>

<?php if (!isset($cookie_policy_modal)) { ?>
    <div class="mobile-links">

        <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#main-flex-card__fixed-right">
            <i class="ep-icon ep-icon_items"></i>
            Menu
        </a>

    </div>
<?php } ?>

<div class="terms-tinymce-text <?php if (isset($cookie_policy_modal)) { ?>terms-tinymce-text--modal<?php } ?>">

    <?php echo $terms_info['text_block']; ?>

    <h2 id="js-cookies-explanation-title">Cookies Explanation</h2>

    <table class="table main-data-table" id="js-cookies-explanation-info">
        <thead>
            <tr>
                <th>Name</th>
                <th>Domain</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ep_r</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_keeping_sined'); ?></td>
            </tr>
            <tr>
                <td>viewed_request_</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_saving_information'); ?></td>
            </tr>
            <tr>
                <td>
                    currency_time <br>
                    currency_key <br>
                    currency_suffix <br>
                    currency_value <br>
                    currency_code
                </td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_currency'); ?></td>
            </tr>
            <tr>
                <td>_ep_ref</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_saving_information_referrer'); ?></td>
            </tr>
            <tr>
                <td>
                    _ep_view_estimate_status <br>
                    _ep_view_inquiry_status <br>
                    _ep_view_offer_status <br>
                    _ep_view_order_status <br>
                    _ep_view_producing_request_status
                </td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_saving_information_user'); ?></td>
            </tr>
            <tr>
                <td>ep_compare</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_storing_information'); ?></td>
            </tr>
            <tr>
                <td>_ep_view_notify</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_information_about_unread'); ?></td>
            </tr>
            <tr>
                <td>_ep_popup_</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_identifying_questionnaire'); ?></td>
            </tr>
            <tr>
                <td>_ulang</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_saving_language'); ?></td>
            </tr>
            <tr>
                <td>_ep_activation_popup</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_saving_information_about'); ?></td>
            </tr>
            <tr>
                <td>
                    ANALITICS_CT_SSID <br>
                    ANALITICS_CT_SSIDE <br>
                    ANALITICS_CT_STA <br>
                    ANALITICS_CT_STEA <br>
                    ANALITICS_CT_SUID <br>
                    ANALITICS_CT_TA
                </td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_building_profile'); ?></td>
            </tr>
            <tr>
                <td>_ep_utz</td>
                <td><?php echo __JS_COOKIE_DOMAIN;?></td>
                <td><?php echo translate('cookies_explanation_time_zone'); ?></td>
            </tr>
        </tbody>
    </table>
    <h2 id="js-3rd-party-cookies-explanation-title">Third party services cookies</h2>
    <table class="table main-data-table" id="js-3rd-party-cookies-explanation-info">
        <thead>
            <tr>
                <th>Name</th>
                <th>Domain</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Stripe
                </td>
                <td>stripe.com</td>
                <td>
                    Stripe - Credit Card payment system, uses cookies for the following purposes: To Operate Payment Services(Authentication, Fraud Prevention and Detection), To Analyze and Improve Payment Services, For Better Advertising.
                    To read more about How Stripe use Cookies <a href="https://stripe.com/cookies-policy/legal" target="_blank">click here</a>.
                </td>
            </tr>
            <tr>
                <td><span class="text-nowrap">Google Analytics</span></td>
                <td>google.com</td>
                <td>
                    Statistic cookies help website owners to understand how visitors interact with websites by collecting and reporting information anonymously.
                    <br>
                    Google Analytics (GA) is Google’s SaaS product for collecting, storing and analysing web traffic data to help us understand how people are using our website.
                    <br>
                    To read more about How Google Analytics use Cookies <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage" target="_blank">click here</a>.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
            "terms_and_conditions:cookie-policy",
            asset('public/plug/js/terms-tinymce-nav/cookie_policy.js', 'legacy'),
            null,
            null,
            true
        );
?>
