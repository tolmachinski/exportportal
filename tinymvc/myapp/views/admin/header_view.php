<?php

if(DEBUG_MODE){
    echo "<!--<pre> session - ";print_r(session()->getAll()); echo 'cookies - ';print_r($_COOKIE);echo "</pre>-->";
    echo "<!--<pre>";
    print_r($errors);
    echo "</pre>-->";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <base href="<?php echo __SITE_URL ?>">
		<?php tmvc::instance()->controller->view->display('admin/js_global_vars_view'); ?>
        <title>Export Portal &raquo; <?php echo $title ?></title>

        <?php tmvc::instance()->controller->view->display('admin/favicon_view'); ?>

        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/style_administration_scss.min.css');?>"/>

        <?php if (DEBUG_MODE) { ?>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/core-js-3-6-5/bundle.js'); ?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/js/lang_new.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-validation-engine-2-6-1/js/jquery.validationEngine.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/bootstrap-rating-1-3-1/bootstrap-rating.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/icheck-1-0-2/js/icheck.min__pc.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/bootstrap-3-3-7/js/bootstrap.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/select2-4-0-3/js/select2.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-ui-1-12-1-custom/jquery-ui.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/js/js.cookie.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/bootstrap-dialog/js/bootstrap-dialog.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-fancybox-2-1-5/js/jquery.fancybox.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-qtip-2-2-0/jquery.qtip.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/clipboard-1-5-9/clipboard.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/tooltipster/tooltipster.bundle.min.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/textcounter-0-3-6/textcounter.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/js/scripts_general.js');?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/js/admin.scripts.js');?>"></script>
        <?php } else { ?>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/core-js-3-6-5/bundle.min.js'); ?>"></script>
            <script type="text/javascript" src="<?php echo fileModificationTime('public/plug_compiled/all-admin-min.js');?>" ></script>
        <?php } ?>

        <?php if (logged_in()) {?>
            <meta name="csrf-token" content="<?php echo session()->csrfToken;?>">
        <?php }?>
    </head>
    <body>
        <div class="body-wrapper <?php if(admin_logged_as_user()){?>pt-90<?php } else{?>pt-40<?php }?>">
            <?php tmvc::instance()->controller->view->display('admin/system_messages_view'); ?>
            <!--==============================header=================================-->
            <header>
                <?php tmvc::instance()->controller->view->display('admin/admin_logged_as_user_view'); ?>
                <script type="text/javascript">
                $(document).ready(function(){
                    <?php if(!logged_in()){?>
                        $(".validengine-top").validationEngine('attach', {promptPosition : "topLeft", autoPositionUpdate : true});

                        $('body').on('click', '.header-nav-top > a.btn-login', function(e){
                            e.preventDefault();
                            var $this = $(this);

                            if(!$this.hasClass('active')){
                                headerNavRef();
                                $this.addClass('active');
                                $('.popup-header-nav-top').css({'width':'auto'}).show().find('.main-login-form-b').show();
                                $('.shadow-header-top').show();
                            }else
                                headerNavRef();
                        });
                    <?php }?>
                });
                </script>
                <?php tmvc::instance()->controller->view->display('admin/admin_logged_as_user_view'); ?>
                <div class="header-nav-top">
                    <?php logged_in() ? widgetNavHeader() : '';?>
                </div><!--LINKS -->
                <div class="shadow-header-top"></div>

                <div class="row">
                    <div class="col-xs-6">
                        <h1 class="text-logo-admin">
                            <a href="<?php echo __SITE_URL ?>">
                                <div class="log-bg pull-left">
                                    <img src="<?php echo __IMG_URL;?>public/img/ep-logo/img-logo-header.png" alt="ExportPortal">
                                </div>
                                EXPORT <span>PORTAL</span>
                                <div>The <strong>&#8470;1</strong> Export &amp; Import Source</div>
                            </a>
                        </h1>
                    </div>
                    <div class="col-xs-6"></div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="navbar navbar-inverse clearfix">
                            <ul class="nav navbar-nav nav-administration">
                                <li><a href="admin/">Home</a></li>
                                <?php if(have_right_or('items_categories_administration,items_categories_attr_administration,items_categories_articles_administration')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Categories <span class="caret"></span></a>
                                        <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
                                            <?php if(have_right('items_categories_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>categories/administration">Categories</a></li>
                                            <?php }?>
                                            <?php if(have_right('items_categories_attr_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>catattr/administration">Attributes</a></li>
                                            <?php }?>
                                            <?php if(have_right('items_categories_articles_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>categories_articles/administration">Categories articles</a></li>
                                            <?php }?>
                                            <?php if (have_right('items_compilation_administration')) {?>
                                                <li><a href="<?php echo __SITE_URL . 'items_compilation/administration';?>">Items compilation</a></li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('users_administration,calling_statuses_administration,notification_messages_administration,manage_user_documents,cancellation_requests_administration,ep_staff_administration,manage_grouprights,gr_packages_administration,user_services_administration,user_statistic_administration,manage_cr_domain,cr_events_administration,cr_expense_reports_administration,cr_training_administration,manage_cr_users')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="<?php echo __SITE_URL;?>users/administration">Users <span class="caret"></span></a>
                                        <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
                                            <?php if(have_right_or('users_administration,calling_statuses_administration,notification_messages_administration,manage_user_documents,cancellation_requests_administration')){?>
                                                <li class="dropdown-submenu">
                                                    <a tabindex="-1" href="<?php echo __SITE_URL;?>users/administration">Users</a>
                                                    <ul class="dropdown-menu">
                                                        <?php if(have_right('users_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>users/administration">Users</a></li>
                                                        <?php }?>

                                                        <?php if(have_right('manage_user_documents')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>verification/users">Users' verification</a></li>
                                                        <?php }?>

                                                        <?php if(have_right('manage_upgrade_requests')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>upgrade/requests">Upgrade requests</a></li>
                                                        <?php }?>

                                                        <?php if(have_right('calling_statuses_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>users/calling_statuses">Calling statuses</a></li>
                                                        <?php }?>

                                                        <?php if(have_right('notification_messages_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>users/reason_messages">Reason messages</a></li>
                                                        <?php }?>

                                                        <?php if (have_right('users_administration')) { ?>
                                                            <li>
                                                                <a href="<?php echo getUrlForGroup("/profile_edit_requests/administration"); ?>">
                                                                    Profile edit requests
                                                                </a>
                                                            </li>
                                                        <?php } ?>

                                                        <?php if(have_right('cancellation_requests_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>user_cancel/administration">Cancellation requests</a></li>
                                                        <?php }?>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right_or('manage_cr_domain,cr_events_administration,cr_expense_reports_administration,cr_training_administration,manage_cr_users')){?>
                                                <li class="dropdown-submenu">
                                                    <a tabindex="-1" href="<?php echo __SITE_URL;?>cr_domains/administration">CR Affiliate</a>
                                                    <ul class="dropdown-menu">
                                                        <?php if(have_right('manage_cr_users')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_users/administration">Users</a></li>
                                                        <?php }?>
                                                        <?php if(have_right('manage_cr_users')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_users/requests">Users requests</a></li>
                                                        <?php }?>
                                                        <?php if(have_right('cr_events_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_events/administration">Events</a></li>
                                                        <?php }?>
                                                        <?php if(have_right('cr_expense_reports_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_expense_reports/administration">Expense Reports</a></li>
                                                        <?php }?>
                                                        <?php if(have_right('cr_training_administration')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_training/administration">Trainings</a></li>
                                                        <?php }?>
                                                        <?php if(have_right('manage_cr_domain')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>cr_domains/administration">Countries</a></li>
                                                        <?php }?>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right('ep_staff_administration')){?>
                                                <li>
                                                    <a href="<?php echo __SITE_URL;?>users/ep_staff">Staff &amp; Administrators</a>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right_or('manage_grouprights,gr_packages_administration,user_services_administration,user_statistic_administration')){?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>admin/groupright">Groups settings</a>
                                                    <ul class="dropdown-menu">
                                                        <?php if(have_right('manage_grouprights')){?>
                                                            <li><a href="<?php echo __SITE_URL;?>admin/grouprightad">CRUD Groups and rights</a></li>
                                                            <li class="dropdown-submenu">
                                                                <a>Groups & rights</a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a href="<?php echo __SITE_URL;?>admin/groupright">Buyer, Sellers, Freight Forwarders</a></li>
                                                                    <li><a href="<?php echo __SITE_URL;?>admin/groupright/ba">CR Affiliate</a></li>
                                                                    <li><a href="<?php echo __SITE_URL;?>admin/groupright/ep_staff">EP Staff</a></li>
                                                                </ul>
                                                            </li>
                                                        <?php }?>
                                                        <?php if(have_right_or('gr_packages_administration')){?>
                                                            <li class="dropdown-submenu">
                                                                <a >Packages</a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a href="<?php echo __SITE_URL;?>group_packages/administration">Account upgrade packages</a></li>
                                                                    <li><a href="<?php echo __SITE_URL;?>rights_packages/administration">Rights packages</a></li>
                                                                </ul>
                                                            </li>
                                                        <?php }?>
                                                        <?php if(have_right('user_services_administration')){?>
                                                            <li>
                                                                <a href="<?php echo __SITE_URL;?>admin/user_services">User's services</a>
                                                            </li>
                                                        <?php }?>
                                                        <?php if(have_right('user_statistic_administration')){?>
                                                            <li>
                                                                <a href="<?php echo __SITE_URL;?>user_statistic/administration">User's statistics</a>
                                                            </li>
                                                        <?php }?>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('directory_administration,moderate_content,manage_content')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="<?php echo __SITE_URL;?>directory/administration_type">Directory <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if(have_right_or('moderate_content,manage_content,companies_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>directory/administration">Companies</a></li>
                                                <?php if (have_right('companies_administration')) { ?>
                                                    <li>
                                                        <a href="<?php echo getUrlForGroup("/company_edit_requests/administration"); ?>">
                                                            Company edit requests
                                                        </a>
                                                    </li>
                                                <?php } ?>

                                                <li><a href="<?php echo __SITE_URL;?>b2b/administration/">B2B Requests</a></li>
                                                <li class="dropdown-submenu">
                                                    <a href="#">Companies posts</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>directory/news_administration">News</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>directory/photos_administration">Photos</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>directory/videos_administration">Videos</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>directory/updates_administration">Updates</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>directory/library_administration">Library</a></li>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right('directory_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>directory/administration_types">Companies type</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>directory/administration_industries">Directory industries</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>directory/administration_categories">Directory categories</a></li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('items_administration,moderate_content')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Items <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if(have_right('items_administration')){?>
                                                <li><a href="<?php echo __SITE_URL;?>items/administration">Items</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>items/featured_administration">Featured Items</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>items/highlight_administration">Highlight Items</a></li>
                                            <?php }?>
                                            <?php if(have_right('moderate_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>items_questions/administration">Items Questions</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>items_comments/administration/">Items Comments</a></li>
                                                <li><a href="<?php echo getUrlForGroup('/product_requests/administration'); ?>">Product Requests</a></li>
                                                <li><a href="<?php echo getUrlForGroup('/draft_extend/administration'); ?>">Draft Extend Requests</a></li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('administrate_orders,read_all_orders')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Orders <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if (have_right('read_all_orders')) {?>
                                                <li><a href="<?php echo __SITE_URL . 'orders/all';?>">EP orders</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'order/all';?>">All orders</a></li>
                                            <?php }?>
                                            <li><a href="<?php echo __SITE_URL . 'order/admin_not_assigned';?>">Not assigned orders</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'order/admin_assigned';?>">Assigned Orders</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'sample_orders/administration';?>">Sample orders</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'order_documents/administration';?>">Orders documents</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'order/admin_reasons';?>">Cancel reasons</a></li>
                                            <?php if (have_right('read_all_disputes')) {?>
                                                <li><a href="<?php echo __SITE_URL . 'dispute/all';?>">All disputes</a></li>
                                            <?php }?>
                                            <li><a href="<?php echo __SITE_URL . 'dispute/administration';?>">Disputes</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'shippers/administration';?>">Freight Forwarders</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'offers/administration';?>">Offers</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'estimate/administration';?>">Estimate</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'inquiry/inquiry_administration';?>">Inquiries</a></li>
                                            <li><a href="<?php echo __SITE_URL . 'po/administration';?>">PO</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('manage_bills')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Billing <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if(have_right('manage_bills')){?>
                                                <li><a href="<?php echo __SITE_URL;?>billing/administration">Billing</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>external_bills/administration">External bills</a></li>
                                            <?php }?>
                                            <li><a href="<?php echo __SITE_URL;?>payments/administration">Payments methods</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('manage_content,admin_site')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Site Content <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if(have_right_or('manage_content')){?>
                                                <li class="dropdown-submenu">
                                                    <a>Popups</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL; ?>popups/administration/5">Feedback register</a></li>
                                                        <li><a href="<?php echo __SITE_URL; ?>popups/administration/6">Feedback upgrade</a></li>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right_or('manage_content')){?>
                                                <li class="dropdown-submenu">
                                                    <a>Main content</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>shipping_methods/administration">Shipping Methods</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>dashboard_banner/administration">Menu promo banner</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>promo_banners/administration">Promo banners</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>emails_template/administration">Emails template</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>trade_news/administration">Trade News</a></li>
                                                        <li class="dropdown-submenu">
                                                            <a href="<?php echo __SITE_URL;?>faq/administration">FAQ</a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="<?php echo __SITE_URL;?>faq/tags_administration">Tags</a></li>
                                                            </ul>
                                                        </li>
                                                        <li><a href="<?php echo __SITE_URL;?>topics/administration">Topics</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>user_guide/administration">User Guide</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>text_block/administration">Textual block's</a></li>
                                                        <?php if (have_right('ep_events_administration')) {?>
                                                            <li class="dropdown-submenu">
                                                            <a href="<?php echo __SITE_URL;?>ep_events/administration">EP events</a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="<?php echo __SITE_URL;?>ep_events/administration">EP events</a></li>
                                                                <li><a href="<?php echo __SITE_URL;?>ep_events_speakers/administration">Speakers</a></li>
                                                                <li><a href="<?php echo __SITE_URL;?>ep_events_partners/administration">Partners</a></li>
                                                            </ul>
                                                        </li>
                                                        <?php }?>
                                                        <li><a href="<?php echo __SITE_URL;?>ep_news/administration">EP news</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>ep_updates/administration">EP updates</a></li>
                                                        <li class="dropdown-submenu">
                                                            <a href="<?php echo __SITE_URL;?>mass_media/administration_media">mass media</a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="<?php echo __SITE_URL;?>mass_media/administration_media">Media</a></li>
                                                                <li><a href="<?php echo __SITE_URL;?>mass_media/administration_news">News</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li><a href="<?php echo __SITE_URL;?>verification_document_types/administration">Accreditation documents</a></li>
                                            <?php } ?>

                                            <?php if(have_right('manage_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>systmess/administration">Notifications</a></li>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>country/administration">Countries</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>country/administration">Countries</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>library_country_statistic/manage">Country statistic</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>country_articles/administration">Country articles</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="<?php echo __SITE_URL;?>bad_words/manage">Bad words</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>partners/administration">Our Partners</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>video/">Videos</a></li>
                                            <?php } ?>

                                            <?php if (have_right('comments_administration')) {?>
                                                <li><a href="<?php echo __SITE_URL . 'comments/administration';?>">Comments</a></li>
                                            <?php }?>

                                            <?php if(have_right('manage_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>international_standards/administration">International standards</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>customs_requirements/administration">Customs requirements</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>newsletter/archive_administration">Newsletter archive</a></li>
                                                <?php if(have_right('super_admin')){ ?>
                                                <li>
                                                    <a href="<?php echo __SITE_URL; ?>downloadable_materials/administration">Downloadable materials</a>
                                                </li>
                                                <?php } ?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>our_team/administration">Our team</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>our_team/administration">People</a></li>
                                                        <li class="dropdown-submenu">
                                                            <a href="<?php echo __SITE_URL;?>offices/administration">Office</a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="<?php echo __SITE_URL;?>hiring/administration">Hirings</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>
                                            <?php } ?>
                                            <?php if(have_right('admin_site')){ ?>
                                                <li class="dropdown-submenu">
                                                    <a href="#">Settings</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>pages/configs">Generate configuration</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>ep_modules/administration">EP modules</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>pages/administration">EP pages</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>meta/administration">Meta pages</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>config/administration">configuration</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>cache_config/administration">Cache configuration</a></li>
                                                    </ul>
                                                </li>
                                            <?php } ?>
                                            <?php if(have_right('manage_picks_of_the_month')){ ?>
                                                <li class="dropdown-submenu">
                                                    <a href="#">Pick of the month</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>pick_of_the_month/company">Companies</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>pick_of_the_month/item">Items</a></li>
                                                    </ul>
                                                </li>
                                            <?php } ?>
                                            <?php if(have_right('webinars_administration')){ ?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>webinars/administration">Webinars</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>webinars/administration">Webinars</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>webinar_requests/administration">Webinar Requests</a></li>
                                                    </ul>
                                                </li>
                                            <?php } ?>
                                            <?php if(have_right('manage_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>library_setting/administration">Library</a></li>
                                            <?php } ?>
                                            <li><a href="<?php echo __SITE_URL;?>admin/pages">Pages List</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right('manage_content')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Content templates <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?php echo __SITE_URL;?>links_storage/administration">Links storage</a></li>
                                            <li><a href="<?php echo __SITE_URL;?>email_templates/administration">E-mail template files</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('manage_email_messages,manage_content')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Incoming messages <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php if(have_right('manage_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>contact/administration">Contact us messages</a></li>
                                            <?php }?>
                                            <?php if(have_right('manage_email_messages')){?>
                                                <li><a href="<?php echo __SITE_URL . 'mail_messages/administration';?>">Mail Messages</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'email_message/administration';?>">Messages</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'email_message/my';?>">My messages</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'category_support/administration';?>">EP staff groups</a></li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right('admin_site')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="<?php echo __SITE_URL;?>admin/banner">Banners <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?php echo __SITE_URL;?>banner/administration/">Link to us</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('community_questions_administration,moderate_content,blogs_administration,manage_content,bloggers_articles_administration')){?>
                                    <li class="dropdown ">
                                        <a data-toggle="dropdown" href="#">Community <span class="caret"></span></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <?php if(have_right('community_questions_administration')){?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>community_questions/administration/">Questions</a>
                                                    <ul class="dropdown-menu open-left">
                                                        <li><a href="<?php echo __SITE_URL;?>community_questions/question_categories/">Question's categories</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>community_questions/answers_administration/">Answers</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>community_questions/comments_administration/">Comments</a></li>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right('moderate_content')){?>
                                                <li><a href="<?php echo __SITE_URL;?>reviews/administration/">Reviews</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>feedbacks/administration">Feedback</a></li>
                                            <?php }?>
                                            <?php if (have_right('ep_reviews_administration')) {?>
                                                <li><a href="<?php echo __SITE_URL . 'ep_reviews/administration';?>">User reviews</a></li>
                                            <?php }?>
                                            <?php if(have_right_or('blogs_administration')){?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>blogs/administration">Blogs</a>
                                                    <ul class="dropdown-menu">
                                                        <?php if(have_right('blogs_administration')) {?>
                                                        <li><a href="<?php echo __SITE_URL;?>blogs/administration">Blogs</a></li>
                                                        <?php } ?>
                                                        <?php if(have_right_or('blogs_administration')) { ?>
                                                        <li><a href="<?php echo __SITE_URL;?>blogs/category_administration">Blogs' Categories</a></li>
                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right('bloggers_articles_administration')){?>
                                                <li class="dropdown-submenu">
                                                    <a>Bloggers</a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="<?php echo __SITE_URL;?>bloggers/administration">Articles</a></li>
                                                    </ul>
                                                </li>
                                            <?php }?>
                                            <?php if(have_right('manage_content')){?>
                                                <li class="dropdown-submenu">
                                                    <a href="<?php echo __SITE_URL;?>complains/administration">Reports</a>
                                                    <ul class="dropdown-menu open-left">
                                                        <li><a href="<?php echo __SITE_URL;?>complains/administration">Reports</a></li>
                                                        <li><a href="<?php echo __SITE_URL;?>complains/types_themes_administration">Reports types themes</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="<?php echo __SITE_URL;?>ecb2b/administration">EC B2B Requests</a></li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right('admin_site')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">API <span class="caret"></span></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="<?php echo __SITE_URL;?>api_keys/administration/">Keys</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right('manage_translations')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Translations <span class="caret"></span></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <?php if(have_right('admin_site')) { ?>
                                                <li><a href="<?php echo __SITE_URL;?>translations/languages">Languages</a></li>
                                                <li><a href="<?php echo __SITE_URL;?>translations/routings">Routings</a></li>
                                            <?php } ?>
                                            <li><a href="<?php echo __SITE_URL;?>translations/administration">Static text</a></li>
                                            <li><a href="<?php echo __SITE_URL;?>translations/system_messages">System Messages</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('manage_analytics,manage_analytics_targets,export_db_reports')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" href="#">Analytics <span class="caret"></span></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="<?php echo __SITE_URL . 'share_statistic/administration';?>">Share statistic</a></li>
                                            <?php if(have_right('export_db_reports')) { ?>
                                                <li><a href="<?php echo __SITE_URL . 'reports';?>">Reports</a></li>
                                            <?php } ?>
                                            <?php if(have_right('manage_analytics')) { ?>
                                                <li><a href="<?php echo __SITE_URL . 'analytics/forms_filled';?>">Forms Analytics</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'analytics/pageviews';?>">Pageviews Analytics</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'analytics/ga_pageviews';?>">GA Pageviews</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'analytics/ga_countries';?>">GA Countries</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'analytics/ga_referrals';?>">GA Referrals</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'search_log/administration';?>">Search log</a></li>
                                                <li><a href="<?php echo __SITE_URL . 'search_log/administration/by_query';?>">Search log by query</a></li>
                                            <?php } ?>
                                            <?php if(have_right('manage_analytics_targets')) { ?>
                                                <li><a href="<?php echo __SITE_URL;?>analytics/targets">Targets</a></li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php }?>
                                <?php if(have_right_or('moderate_content')){?>
                                    <li class="dropdown">
                                        <a data-toggle="dropdown">Moderation <span class="caret"></span></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="<?php echo __SITE_URL . "moderation/administration/" . \App\Moderation\Types\TYPE_B2B ;?>">B2B requests</a></li>
                                            <li><a href="<?php echo __SITE_URL . "moderation/administration/" . \App\Moderation\Types\TYPE_COMPANY ;?>">Companies</a></li>
                                            <li><a href="<?php echo __SITE_URL . "moderation/administration/" . \App\Moderation\Types\TYPE_ITEM ;?>">Items</a></li>
                                        </ul>
                                    </li>
                                <?php }?>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            <div id="content-body">
