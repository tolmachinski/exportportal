<?php
/**
 * application.php
 *
 * application configuration
 *
 * @author AL
 */

/*
,
'!^/seller/([^/]+)/([^/]+)!',
'!^/seller/([^/]+)/([^/]+)/([\w\d\/]+)!'


'/seller/${2}/${1}',
'/seller/${1}/${2}/${3}'
*/
/* URL routing, use preg_replace() compatible syntax */
$config['routing']['search'] =  array(
	'!^/usr/([^/]+)$!',
	'!^/country_representative/([^/]+)$!',
	'!^/job_history/my$!',
	'!^/export_import$!',
	'!^/ec_b2b!',
	'!^/ec_b2b_investor!',
	'!^/shipper_description$!',
	'!^/manufacturer_description$!',
	'!^/buying$!',
	'!^/selling$!',
	'!^/resources$!',
	'!^/learn_more!',
	'!^/cookieconsent!',
	'!^/handmade-crafting!',
	'!^/topics/[^/]+/([0-9]+)$!',
	'!^/item/([^/]+)/?$!',
	'!^/item/([^/]+)/comments/?$!',
	'!^/item/([^/]+)/questions/?$!',
	'!^/item/([^/]+)/reviews/?$!',
	'!^/item/([^/]+)/reviews_ep/?$!',
	'!^/item/([^/]+)/reviews_external/?$!',
	'!^/search/([^/]+)!',
	'!^/maincategories/([^/]+)!',
	'!^/seller/([^/]+)/([^/]+)!',
	'!^/seller/([^/]+)$!',
	'!^/snapshot/([^/]+)!',
	'!^/branch/([^/]+)!',
	'!^/branch/([^/]+)/([^/]+)!',
	'!^/shipper/([^/]+)$!',
    '!^/blocked/([^/]+)$!',
	'!^/analytics/v/([0-9\.-_]+)/analytics.js(\.map)?!',
	'!^/question/([^/]+)$!',
	'!^/ep_updates/page/([^/]+)!',
	'!^/ep_news/page/([^/]+)!',
	'!^/mass_media/(page|channel)/([^/]+)!',
    '!^/library_accreditation_body/(page|country)/([^/]+)!',
    '!^/trade_news/page/([^/]+)!',
    '!^/account/confirm-cancelation/([^/]+)$!',
	'!^/403$!',
);
$config['routing']['replace'] = array(
	'/user/index/${1}',
	'/cr_user/index/${1}',
	'/cr_job_history/my',
	'/default/export_import',
	'/default/ec_b2b',
	'/default/ec_b2b_investor',
	'/default/shipper_description',
	'/default/manufacturer_description',
	'/default/buying',
	'/default/selling',
	'/default/resources',
	'/default/learn_more',
	'/default/cookieconsent',
	'/landing/crafting',
	'/topics/index/${1}',
	'/items/detail/${1}',
	'/items/comments/${1}',
	'/items/questions/${1}',
	'/items/reviews/${1}',
	'/items/reviews_ep/${1}',
	'/items/reviews_external/${1}',
	'/search/index/${1}',
	'/maincategories/index/${1}',
	'/seller/${2}/${1}',
	'/seller/index/${1}',
	'/snapshot/index/${1}',
	'/branch/${1}/detail',
	'/branch/${2}/${1}',
	'/shipper/company/${1}',
    '/blocked/index/${1}',
	'/analytics/file/${1}/${2}',
    '/community/question/${1}',
	'/ep_updates/index/page/${1}',
	'/ep_news/index/page/${1}',
	'/mass_media/index/${1}/${2}',
    '/library_accreditation_body/index/${1}/${2}',
    '/trade_news/index/page/${1}',
    '/user_cancel/request_confirmation/code/${1}',
	'/errors/p403',
);

require \App\Common\CONFIG_PATH . '/translations/routings/all_langs_routes.php';

/* set this to force controller and method instead of using URL params */
$config['root_controller'] = null;
$config['root_action'] = null;

/* name of default controller/method when none is given in the URL */
$config['default_controller'] = 'default';
$config['default_action'] = 'index';
// $config['default_action'] = 'maintenance';

/* name of controller for companies */
$config['company_controller'] = 'seller';

/* name of controller for blog */
$config['blog_controller'] = 'blog';
$config['community_controller'] = 'community';
$config['blog_default_action'] = 'all';

/* name of controller for blog */
$config['cr_controller'] = 'cr';
$config['cr_default_action'] = 'index';
$config['cr_available'] = array();

/* controller for errors page */
$config['errors_controller'] = 'errors';
$config['404_action'] = 'p404';

/* name of PHP function that handles system errors */
$config['error_handler_class'] = 'TinyMVC_ErrorHandler';

/* enable timer. use {TMVC_TIMER} in your view to see it */
$config['timer'] = true;

