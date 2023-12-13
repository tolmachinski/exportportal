<?php
/**
 * cron.php
 *
 * cron job
 *
 * @author Litra Andrei
 */

use App\Common\Contracts\Calendar\EventType;
use App\Common\Contracts\Calendar\NotificationType;
use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Contracts\User\RestrictionType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Transformers\Analytics\ListTransformer;
use App\Common\Transformers\Analytics\TargetsTransformer;
use App\Email\AccountIsNowBlocked;
use App\Email\AccountRestriction;
use App\Email\CalendarEpEventReminder;
use App\Email\CheckYourAccountOnEp;
use App\Email\DemoWebinarEp2WeeksAfter;
use App\Email\DemoWebinarEpComingSoon;
use App\Email\DemoWebinarEpThanksForParticipatedBuyers;
use App\Email\DemoWebinarEpThanksForParticipatedSellers;
use App\Email\DemoWebinarEpTomorrow;
use App\Email\DraftItemExpirationFirstEmail;
use App\Email\DraftItemIncentiveOfferEmail;
use App\Email\DraftItemWarningDeleteEmail;
use App\Email\EnvelopeExpiresSoonForSender;
use App\Email\EnvelopeExpiresSoonForSigner;
use App\Email\GroupEmailTemplates;
use App\Email\LastViewedMonthly;
use App\Email\SellerItemsViews;
use App\Email\StayActiveOnEp;
use App\Email\UnreadNotifications;
use App\Filesystem\ItemPathGenerator;
use App\Messenger\Message\Event\Lifecycle\UserGroupChangedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasBlockedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasRestrictedEvent;
use App\Optimization\CustomCwebp;
use App\Services\MatchmakingService;
use App\Session_logs\Messages as SessionLogsMessages;
use App\Session_logs\Types as SessionLogsTypes;
use App\Sitemap\SitemapAdapter;
use App\Sitemap\SitemapGenerator;
use App\Sitemap\SitemapIndexAdapter;
use App\Sitemap\UrlsGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Fnash\GraphQL\Query;
use GuzzleHttp\Client;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;
use Psr\Log\LoggerInterface;

class Cron_Controller extends TinyMVC_Controller
{
    var $cron_params = array(
        'action' => '',
        'status' => 'success',
        'messages' => array()
    );
    var $cron_log_file;

    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $hash = tmvc::instance()->url_segments[3];
        if ($hash !== 'cronaction') {
            exit();
        }

        $this->notifier = $container->get(NotifierInterface::class);
        $this->cron_log_file = 'crons/logs/' . date('Y-m-d') . '_cron_logs.log';
    }

    public function __destruct()
    {
        $this->cron_params['action'] = tmvc::instance()->action;
        $this->_cron_log();
    }

    // COLLECT GOOGLE ANALYTICS PAGE VIEWS
    // EVERY DAY
    public function get_google_analytics(){
        if(have_right('manage_analytics')){
            if(isset($_GET['date']) && validateDate($_GET['date'], 'Y-m-d')){
                $analytic_date = $_GET['date'];
            }
        }

        if(!isset($analytic_date)){
            $analytic_date = date("Y-m-d", strtotime("yesterday"));
        }

        $params = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                ),
                array(
                    'name' => 'ga:newUsers',
                    'alias' => 'new_users'
                ),
                array(
                    'name' => 'ga:bounces',
                    'alias' => 'bounces'
                ),
                array(
                    'name' => 'ga:sessions',
                    'alias' => 'sessions'
                ),
                array(
                    'name' => 'ga:pageviews',
                    'alias' => 'pageviews'
                ),
                array(
                    'name' => 'ga:avgTimeOnPage',
                    'alias' => 'avg_time_on_page'
                ),
                array(
                    'name' => 'ga:entrances',
                    'alias' => 'entrances'
                ),
                array(
                    'name' => 'ga:exits',
                    'alias' => 'exits'
                ),
                array(
                    'name' => 'ga:exitRate',
                    'alias' => 'exit_rate'
                ),
            ),
            'dimensions' => array(
                'ga:pagePath'
            )
        );

        $params_usertype = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                )
            ),
            'dimensions' => array(
                'ga:userType',
                'ga:pagePath'
            )
        );

        $targets = model('analytics')->get_targets(array(
            'target_type' => 'page',
            'target_active_ga' => 1
        ));

        $target_patterns = array();
        if (!empty($targets)) {
            foreach ($targets as $target) {
                $target_aliases = json_decode($target['target_aliases'], true);
                if(empty($target_aliases)){
                    continue;
                }

                foreach ($target_aliases as $target_alias) {
                    $pattern_key = str_replace('\\\\', '\\', $target_alias['value']);
                    $params['dimension_filters'][] = array(
                        'name' => "ga:pagePath",
                        'operator' => $target['target_operator'],
                        'expressions' => $pattern_key
                    );

                    $target_patterns[$pattern_key] = $target['id_target'];
                }
            }
        }

        $responce = library('google_analytics')->get_report($params);
        $responce_usertype = library('google_analytics')->get_report($params_usertype);
        $records_usertype = array();
        foreach ($responce_usertype['targets'] as $record) {
            if(!isset($records_usertype[$record['dimensions']['ga:pagePath']])){
                $temp_data = array(
                    'new_visitors' => 0,
                    'returning_visitors' => 0
                );
            } else{
                $temp_data = $records_usertype[$record['dimensions']['ga:pagePath']];
            }

            if($record['dimensions']['ga:userType'] == 'New Visitor'){
                $temp_data['new_visitors'] = (int) $record['metrics']['users'];
            } elseif($record['dimensions']['ga:userType'] == 'Returning Visitor'){
                $temp_data['returning_visitors'] = (int) $record['metrics']['users'];
            }

            $records_usertype[$record['dimensions']['ga:pagePath']] = $temp_data;
        }
        $insert = array();
        if(!empty($responce['targets'])){
            foreach ($responce['targets'] as $ga_target) {
                $id_target = 0;
                $ga_page_path = '';
                foreach ($target_patterns as $pattern_key => $target_id) {
                    if(preg_match("/{$pattern_key}/", $ga_target['dimensions']['ga:pagePath'])){
                        $id_target = $target_id;
                        $ga_page_path = $ga_target['dimensions']['ga:pagePath'];
                        break;
                    }
                }

                if($id_target == 0){
                    continue;
                }

                // ga:bounceRate = ga:bounces / ga:sessions
                $ga_target['metrics']['bounce_rate'] = $ga_target['metrics']['sessions'] > 0 ? $ga_target['metrics']['bounces'] / $ga_target['metrics']['sessions'] : 0;
                // ga:entranceRate = ga:entrances / ga:pageviews
                $ga_target['metrics']['entrance_rate'] = $ga_target['metrics']['pageviews'] > 0 ? $ga_target['metrics']['entrances'] / $ga_target['metrics']['pageviews'] : 0;
                // new visitors
                $ga_target['metrics']['new_visitors'] = isset($records_usertype[$ga_target['dimensions']['ga:pagePath']]) ? $records_usertype[$ga_target['dimensions']['ga:pagePath']]['new_visitors'] : 0;
                // returning visitors
                $ga_target['metrics']['returning_visitors'] = isset($records_usertype[$ga_target['dimensions']['ga:pagePath']]) ? $records_usertype[$ga_target['dimensions']['ga:pagePath']]['returning_visitors'] : 0;

                $insert[] = array_merge(
                    array(
                        'id_target' => $id_target,
                        'target_path' => $ga_page_path,
                        'analytic_date' => $analytic_date
                    ),
                    $ga_target['metrics']
                );
            }

            if (!empty($insert)) {
                model('analytics')->insert_ga($insert);
            }
        }

        $this->cron_params['messages'][] = 'The Google Analytics data for ' . $analytic_date . ' has been collected on ' . date('m/d/Y H:i:s') . '.';
    }

    // COLLECT GOOGLE ANALYTICS USERS BY COUNTRIES
    // EVERY DAY
    public function get_google_analytics_countries(){
        if(have_right('manage_analytics')){
            if(isset($_GET['date']) && validateDate($_GET['date'], 'Y-m-d')){
                $analytic_date = $_GET['date'];
            }
        }

        if(!isset($analytic_date)){
            $analytic_date = date("Y-m-d", strtotime("yesterday"));
        }

        $params = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                ),
                array(
                    'name' => 'ga:newUsers',
                    'alias' => 'new_users'
                ),
                array(
                    'name' => 'ga:bounces',
                    'alias' => 'bounces'
                ),
                array(
                    'name' => 'ga:sessions',
                    'alias' => 'sessions'
                ),
                array(
                    'name' => 'ga:pageviews',
                    'alias' => 'pageviews'
                ),
                array(
                    'name' => 'ga:avgTimeOnPage',
                    'alias' => 'avg_time_on_page'
                ),
                array(
                    'name' => 'ga:entrances',
                    'alias' => 'entrances'
                ),
                array(
                    'name' => 'ga:exits',
                    'alias' => 'exits'
                ),
                array(
                    'name' => 'ga:exitRate',
                    'alias' => 'exit_rate'
                ),
            ),
            'dimensions' => array(
                'ga:country'
            )
        );

        $params_usertype = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                )
            ),
            'dimensions' => array(
                'ga:userType',
                'ga:country'
            )
        );

        $responce = library('google_analytics')->get_report($params);
        $responce_usertype = library('google_analytics')->get_report($params_usertype);

        $records_usertype = array();
        foreach ($responce_usertype['targets'] as $record) {
            if(!isset($records_usertype[$record['dimensions']['ga:country']])){
                $temp_data = array(
                    'new_visitors' => 0,
                    'returning_visitors' => 0
                );
            } else{
                $temp_data = $records_usertype[$record['dimensions']['ga:country']];
            }

            if($record['dimensions']['ga:userType'] == 'New Visitor'){
                $temp_data['new_visitors'] = (int) $record['metrics']['users'];
            } elseif($record['dimensions']['ga:userType'] == 'Returning Visitor'){
                $temp_data['returning_visitors'] = (int) $record['metrics']['users'];
            }

            $records_usertype[$record['dimensions']['ga:country']] = $temp_data;
        }
        $insert = array();
        if(!empty($responce['targets'])){
            $countries = array_column(model('analytics')->get_countries(), 'id', 'country');

            foreach ($responce['targets'] as $ga_target) {
                $ga_page_path = $ga_target['dimensions']['ga:country'];

                // ga:bounceRate = ga:bounces / ga:sessions
                $ga_target['metrics']['bounce_rate'] = $ga_target['metrics']['sessions'] > 0 ? $ga_target['metrics']['bounces'] / $ga_target['metrics']['sessions'] : 0;
                // ga:entranceRate = ga:entrances / ga:pageviews
                $ga_target['metrics']['entrance_rate'] = $ga_target['metrics']['pageviews'] > 0 ? $ga_target['metrics']['entrances'] / $ga_target['metrics']['pageviews'] : 0;
                // new visitors
                $ga_target['metrics']['new_visitors'] = isset($records_usertype[$ga_target['dimensions']['ga:country']]) ? $records_usertype[$ga_target['dimensions']['ga:country']]['new_visitors'] : 0;
                // returning visitors
                $ga_target['metrics']['returning_visitors'] = isset($records_usertype[$ga_target['dimensions']['ga:country']]) ? $records_usertype[$ga_target['dimensions']['ga:country']]['returning_visitors'] : 0;

                $insert[] = array_merge(
                    array(
                        'id_country' => isset($countries[$ga_page_path])? $countries[$ga_page_path] : 0,
                        'ga_country' => $ga_page_path,
                        'analytic_date' => $analytic_date
                    ),
                    $ga_target['metrics']
                );
            }

            if (!empty($insert)) {
                model('analytics')->insert_ga_countries($insert);
            }
        }

        $this->cron_params['messages'][] = 'The Google Analytics Countries data for '.$analytic_date.' has been collected on '.date('m/d/Y H:i:s').'.';
    }

    // COLLECT GOOGLE ANALYTICS REFERRALS
    // EVERY DAY
    public function get_google_analytics_referrals(){
        if(have_right('manage_analytics')){
            if(isset($_GET['date']) && validateDate($_GET['date'], 'Y-m-d')){
                $analytic_date = $_GET['date'];
            }
        }

        if(!isset($analytic_date)){
            $analytic_date = date("Y-m-d", strtotime("yesterday"));
        }

        $params = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                ),
                array(
                    'name' => 'ga:newUsers',
                    'alias' => 'new_users'
                ),
                array(
                    'name' => 'ga:bounces',
                    'alias' => 'bounces'
                ),
                array(
                    'name' => 'ga:sessions',
                    'alias' => 'sessions'
                ),
                array(
                    'name' => 'ga:pageviews',
                    'alias' => 'pageviews'
                ),
                array(
                    'name' => 'ga:avgTimeOnPage',
                    'alias' => 'avg_time_on_page'
                ),
                array(
                    'name' => 'ga:entrances',
                    'alias' => 'entrances'
                ),
                array(
                    'name' => 'ga:exits',
                    'alias' => 'exits'
                ),
                array(
                    'name' => 'ga:exitRate',
                    'alias' => 'exit_rate'
                ),
            ),
            'dimensions' => array(
                'ga:source',
                'ga:fullReferrer'
            )
        );

        $params_usertype = array(
            'date' => array(
                'start' => $analytic_date,
                'end' => $analytic_date
            ),
            'metrics' => array(
                array(
                    'name' => 'ga:users',
                    'alias' => 'users'
                )
            ),
            'dimensions' => array(
                'ga:userType',
                'ga:source',
                'ga:fullReferrer'
            )
        );

        $responce = library('google_analytics')->get_report($params);
        $responce_usertype = library('google_analytics')->get_report($params_usertype);

        $records_usertype = array();
        foreach ($responce_usertype['targets'] as $record) {
            $domension_key = sha1($record['dimensions']['ga:source'].$record['dimensions']['ga:fullReferrer']);
            if(!isset($records_usertype[$domension_key])){
                $temp_data = array(
                    'new_visitors' => 0,
                    'returning_visitors' => 0
                );
            } else{
                $temp_data = $records_usertype[$domension_key];
            }

            if($record['dimensions']['ga:userType'] == 'New Visitor'){
                $temp_data['new_visitors'] = (int) $record['metrics']['users'];
            } elseif($record['dimensions']['ga:userType'] == 'Returning Visitor'){
                $temp_data['returning_visitors'] = (int) $record['metrics']['users'];
            }

            $records_usertype[$domension_key] = $temp_data;
        }

        $insert = array();
        if(!empty($responce['targets'])){
            foreach ($responce['targets'] as $ga_target) {
                $domension_key = sha1($ga_target['dimensions']['ga:source'].$ga_target['dimensions']['ga:fullReferrer']);
                $ga_page_path = $ga_target['dimensions']['ga:country'];

                // ga:bounceRate = ga:bounces / ga:sessions
                $ga_target['metrics']['bounce_rate'] = $ga_target['metrics']['sessions'] > 0 ? $ga_target['metrics']['bounces'] / $ga_target['metrics']['sessions'] : 0;
                // ga:entranceRate = ga:entrances / ga:pageviews
                $ga_target['metrics']['entrance_rate'] = $ga_target['metrics']['pageviews'] > 0 ? $ga_target['metrics']['entrances'] / $ga_target['metrics']['pageviews'] : 0;
                // new visitors
                $ga_target['metrics']['new_visitors'] = isset($records_usertype[$domension_key]) ? $records_usertype[$domension_key]['new_visitors'] : 0;
                // returning visitors
                $ga_target['metrics']['returning_visitors'] = isset($records_usertype[$domension_key]) ? $records_usertype[$domension_key]['returning_visitors'] : 0;

                $insert[] = array_merge(
                    array(
                        'referrer_source' => $ga_target['dimensions']['ga:source'],
                        'referrer_full_path' => $ga_target['dimensions']['ga:fullReferrer'],
                        'analytic_date' => $analytic_date
                    ),
                    $ga_target['metrics']
                );
            }

            if (!empty($insert)) {
                model('analytics')->insert_ga_referrals($insert);
            }
        }

        $this->cron_params['messages'][] = 'The Google Analytics Referrals data for '.$analytic_date.' has been collected on '.date('m/d/Y H:i:s').'.';
    }

    // COLLECT PAGEVIEWS ANALYTICS
    // EVERY DAY
    public function get_pageviews_analytics(){
        if(have_right('manage_analytics')){
            if(isset($_GET['date']) && validateDate($_GET['date'], 'Y-m-d')){
                $analytic_date = $_GET['date'];
            }
        }

        if(!isset($analytic_date)){
            $analytic_date = date("Y-m-d", strtotime("yesterday"));
        }

        $targets = model('analytics')->get_targets(array(
            'target_type' => 'page',
            'target_active_oa' => 1
        ));

        $target_patterns = array();
        $query_targets = array();
        if (!empty($targets)) {
            foreach ($targets as $target) {
                $target_aliases = json_decode($target['target_aliases'], true);
                if(empty($target_aliases)){
                    continue;
                }

                foreach ($target_aliases as $target_alias) {
                    $query_targets[] = array(
                        'type' => $target['target_operator'],
                        'value' => $target_alias['value']
                    );

                    $target_patterns[$target_alias['value']] = $target['id_target'];
                }
            }
        }

        $query = Query::create('pageviews_analytics')
            ->arguments([
                'code' => config('env.GLOBAL_CUSTOM_TRACKING_CODE'),
                'type' => 'pageview',
                'dateFrom' => $analytic_date,
                'dateTo' => $analytic_date,
                'targets' => new TargetsTransformer($query_targets),
            ])
            ->fields([
                'metrics' => Query::create()->fields([
                    'target',
                    'users',
                    'sessions',
                    'visits'
                ]),
                'visits' => Query::create()->fields([
                    'target',
                    'users' => Query::create()->fields([
                        'user',
                        'sessions',
                        'visits'
                    ])
                ])
            ])
        ;

        $client = new Client(['base_uri' => __ANALYTIC_API_URL]);
        $response = $client->post('/', [GuzzleHttp\RequestOptions::BODY => $query]);
        $results = \GuzzleHttp\json_decode($response->getBody(), true);

        $insert = array();
        if(!empty($results['data']['pageviews_analytics']['metrics'])){
            $visits = arrayByKey($results['data']['pageviews_analytics']['visits'], 'target');
            foreach ($results['data']['pageviews_analytics']['metrics'] as $record) {
                $id_target = 0;
                $target_path = '';
                foreach ($target_patterns as $pattern_key => $target_id) {
                    if(preg_match("/{$pattern_key}/", $record['target'])){
                        $id_target = $target_id;
                        $target_path = $record['target'];
                        break;
                    }
                }

                if($id_target == 0){
                    continue;
                }

                unset($record['target']);

                $insert[] = array_merge(
                    array(
                        'id_target' => $id_target,
                        'target_path' => $target_path,
                        'analytic_date' => $analytic_date
                    ),
                    $record,
                    array('visits_details' => json_encode($visits[$target_path]))
                );
            }

            if (!empty($insert)) {
                model('analytics')->insert_pageview($insert);
            }
        }

        $this->cron_params['messages'][] = 'The PageViews Analytics data for '.$analytic_date.' has been collected on '.date('m/d/Y H:i:s').'.';
    }

    // COLLECT FORMS ANALYTICS
    // EVERY DAY
    public function get_forms_analytics(){
        if(have_right('manage_analytics')){
            if(isset($_GET['date']) && validateDate($_GET['date'], 'Y-m-d')){
                $analytic_date = $_GET['date'];
            }
        }

        if(!isset($analytic_date)){
            $analytic_date = date("Y-m-d", strtotime("yesterday"));
        }

        $targets = model('analytics')->get_targets(array(
            'target_type' => 'form',
            'target_active_oa' => 1
        ));

        $target_patterns = array();
        $query_targets = array();
        if (!empty($targets)) {
            foreach ($targets as $target) {
                $target_aliases = json_decode($target['target_aliases'], true);
                if(empty($target_aliases)){
                    continue;
                }

                foreach ($target_aliases as $target_alias) {
                    $query_targets[] = $target_alias['value'];
                    $target_patterns[$target_alias['value']] = $target['id_target'];
                }
            }
        }

        $query = Query::create('forms_analytics')
            ->arguments([
                'code' => config('env.GLOBAL_CUSTOM_TRACKING_CODE'),
                'type' => new ListTransformer(['element_change', 'form_submission']),
                'dateFrom' => $analytic_date,
                'dateTo' => $analytic_date,
                'targets' => new ListTransformer($query_targets)
            ])
            ->fields([
                'metrics' => Query::create()->fields([
                    'target',
                    'submits_users',
                    'submits_sessions',
                    'filled_users',
                    'filled_sessions',
                    'submits',
                    'success_submits'
                ])
            ])
        ;

        $client = new Client(['base_uri' => __ANALYTIC_API_URL]);
        $response = $client->post('/', [GuzzleHttp\RequestOptions::BODY => $query]);
        $results = \GuzzleHttp\json_decode($response->getBody(), true);
        $insert = array();
        if(!empty($results['data']['forms_analytics']['metrics'])){
            foreach ($results['data']['forms_analytics']['metrics'] as $record) {
                if(!isset($target_patterns[$record['target']])){
                    continue;
                }

                $id_target = $target_patterns[$record['target']];
                $target_path = $record['target'];
                unset($record['target']);

                $insert[] = array_merge(
                    array(
                        'id_target' => $id_target,
                        'target_path' => $target_path,
                        'analytic_date' => $analytic_date
                    ),
                    $record
                );
            }

            if (!empty($insert)) {
                model('analytics')->insert_forms($insert);
            }
        }

        $this->cron_params['messages'][] = 'The Forms Analytics data for '.$analytic_date.' has been collected on '.date('m/d/Y H:i:s').'.';
    }

    function get_cities_lat_lng(){
        ini_set('max_execution_time', 0);

        $locations = model('country')->get_countries_states_cities(array('lat_lng_need_complet' => 1, 'limit' => 50));

        if(empty($locations)){
            return;
        }

        foreach ($locations as $location) {
            $gmap_config = array(
                'country' => $location['country_name'],
                'state' => $location['state_name'],
                'city' => $location['city']
            );
            $gmap_geodata = library('gmap')->get_geocode($gmap_config);
            if($gmap_geodata['status'] === 'OK'){
                $update = array(
                    'lat_lng_need_complet' => 2,
                    'city_lat' => $gmap_geodata['results'][0]['geometry']['location']['lat'],
                    'city_lng' => $gmap_geodata['results'][0]['geometry']['location']['lng']
                );
                model('country')->update_city($location['id'], $update);
            } else{
                $gmap_config = array(
                    'country' => $location['country_name'],
                    'city' => $location['city']
                );
                $gmap_geodata = library('gmap')->get_geocode($gmap_config);
                if($gmap_geodata['status'] === 'OK'){
                    $update = array(
                        'lat_lng_need_complet' => 2,
                        'city_lat' => $gmap_geodata['results'][0]['geometry']['location']['lat'],
                        'city_lng' => $gmap_geodata['results'][0]['geometry']['location']['lng']
                    );
                    model('country')->update_city($location['id'], $update);
                } else{
                    $update = array(
                        'lat_lng_need_complet' => 3
                    );
                    model('country')->update_city($location['id'], $update);
                }
            }
        }

        model('user')->update_users_lat_lng();
    }

    private function _sendgrid_send_emails_in_queue(){

        /** @var Mail_Messages_Model $mailMessagesModel */
        $mailMessagesModel = model(Mail_Messages_Model::class);

        $emails = $mailMessagesModel->findAllBy([
            'conditions' => [
                'isVerified'    => 1,
                'isSent'        => 0,
            ],
            'with'  => ['content'],
            'limit' => 50,
        ]);

        if(empty($emails)){
            return false;
        }

        //get all unique emails to send to (including multiple)
        $all_emails = array_column($emails, 'to');
        $all_unique_emails = array_unique(explode(\App\Common\EMAIL_DELIMITER, implode(\App\Common\EMAIL_DELIMITER, $all_emails)));

        //get bad status code emails from users and flip them as keys
        $bad_emails = array_flip(model('email_hash')->get_bad_emails($all_unique_emails));

        $config = array(
            'from_email' => config('noreply_email'),
            'from_name' => 'ExportPortal.com',
            'reply_to_email' => config('noreply_email'),
            'mail_subject' => 'Exportportal'
        );

        library('sendgrid')->initialize($config);

        $messages_ids = array();
        foreach($emails as $email)
        {
            $error_messages = array();
            $messages_ids[] = $email['id'];
            if(strpos($email['from'], '@exportportal.com') !== false){
                library('sendgrid')->from_email = $email['from'];
                library('sendgrid')->reply_to_email = $email['from'];
            } else{
                library('sendgrid')->from_email = $_ENV['EMAIL_NO_REPLY'];
                library('sendgrid')->reply_to_email = $_ENV['EMAIL_SUPPORT'];
            }

            $emails_list = explode(\App\Common\EMAIL_DELIMITER, $email['to']);
            //generate x-smtpapi header
            foreach($emails_list as $one_email)
            {
                //check if email is bad then add error
                if(isset($bad_emails[$one_email]))
                {
                    $error_messages[$one_email] = "The email {$one_email} is bad.";
                    continue;
                }

                $email_data = array(
                    'to' => array('email' => $one_email),
                    'subject' => $email['subject'],
                    'headers' => array(
                        'List-Unsubscribe' => '<mailto:unsubscribe@exportportal.com>, <'.__SITE_URL.'user/unsubscribe>'
                    )
                );

                if(!empty($email['cc'])){
                    $email_data['cc'] = $email['cc'];
                }

                if(!empty($email['bcc'])){
                    $email_data['bcc'] = $email['bcc'];
                }

                library('sendgrid')->_reset();

                $text_plain = library('Html2Text')->convert($email['content']['message'])->get_text();
                library('sendgrid')->add_content($text_plain, 'text/plain');
                library('sendgrid')->add_content($email['content']['message'], 'text/html');
                library('sendgrid')->send_personilization($email_data);
                $result = library('sendgrid')->send();

                if(!$result){
                    $error_messages[$one_email . '_sendgrid'] = (library('sendgrid')->get_errors());
                }
            }
            if (!empty($error_messages)) {
                model('notify')->update_sent_emails(array($email['id']), array('failure_log' => json_encode($error_messages)));
            }
        }

        if($messages_ids){
            model('notify')->update_sent_emails($messages_ids, array('is_sent' => 1));
        }
        $this->cron_params['messages'][] = 'There has been sent '.count($messages_ids).' emails.';
    }

    function send_emails_in_queue(){
        switch(config('env.APP_MODE')){
            case 'dev':
                $this->_smtp_send_emails_in_queue();
                break;
            case 'prod':
                $this->_amazon_send_emails_in_queue();
                break;
            case 'staging':
                $this->_sendgrid_send_emails_in_queue();
                break;
        }
        return;
    }

    private function _amazon_send_emails_in_queue() {
        /** @var Mail_Messages_Model $mailMessagesModel */
        $mailMessagesModel = model(Mail_Messages_Model::class);

        $mailMessages = $mailMessagesModel->findAllBy([
            'conditions' => [
                'isVerified'    => 1,
                'isSent'        => 0,
            ],
            'with'  => ['content'],
            'limit' => 50,
        ]);


        if (empty($mailMessages)) {
            return false;
        }

        $recipientsEmails = array_column($mailMessages, 'to');

        $uniqueEmailsWithHashes = [];
        foreach (array_unique($recipientsEmails) as $email) {
            $uniqueEmailsWithHashes[$email] = getEncryptedEmail($email);
        }

        /** @var Email_Hash_Model $emailHashModel */
        $emailHashModel = model(Email_Hash_Model::class);

        $emailsHashesInfo = $emailHashModel->findAllBy([
            'conditions' => [
                'email_hashes' => array_values($uniqueEmailsWithHashes),
            ],
            'columns' => [
                'email_hash AS hash',
                'email_status AS status',
            ],
        ]);

        $emailHashesByKey = array_column($emailsHashesInfo, 'status', 'hash');

        /** @var TinyMVC_Library_Amazon $amazonLibrary */
        $amazonLibrary = library(TinyMVC_Library_Amazon::class);

        $amazonLibrary->from_name = 'ExportPortal.com';
        $messagesIds = [];
        foreach ($mailMessages as $mailMessage) {
            $recipientEmailStatus = $emailHashesByKey[$uniqueEmailsWithHashes[$mailMessage['to']]] ?? null;

            if (null === $recipientEmailStatus) {
                $mailMessagesModel->updateOne($mailMessage['id'], ['is_verified' => 0]);

                continue;
            }

            $messagesIds[] = $mailMessage['id'];

            if ('bad' === strtolower($recipientEmailStatus)) {
                $mailMessagesModel->updateOne(
                    $mailMessage['id'],
                    [
                        'failure_log' => [
                            $mailMessage['to'] => "The email {$mailMessage['to']} is bad.",
                        ],
                    ]
                );

                continue;
            }

            $amazonLibrary->to = $mailMessage['to'];
            $amazonLibrary->from_email = $mailMessage['from'] ?: config('env.EMAIL_NO_REPLY');
            $amazonLibrary->reply_to_email = $mailMessage['reply_to'] ?: config('env.EMAIL_SUPPORT');

            $amazonLibrary->html_content = $mailMessage['content']['message'];
            $amazonLibrary->mail_subject = $mailMessage['subject'];

            if (!empty($mailMessage['bcc'])) {
                $amazonLibrary->bcc = $mailMessage['bcc'];
            }

            if (!empty($mailMessage['cc'])) {
                $amazonLibrary->cc = $mailMessage['cc'];
            }

            if (!$amazonLibrary->send_email()) {
                $mailMessagesModel->updateOne(
                    $mailMessage['id'],
                    [
                        'failure_log' => [
                            $mailMessage['to'] => $amazonLibrary->errors,
                        ],
                    ]
                );
            }

            $amazonLibrary->reset();
        }

        if (!empty($messagesIds)) {
            $mailMessagesModel->updateMany(
                ['is_sent' => 1],
                [
                    'conditions' => [
                        'emailsIds' => $messagesIds
                    ]
                ],
            );
        }

        $this->cron_params['messages'][] = 'There has been sent ' . count($messagesIds) . ' emails.';
    }

    public function send_user_systmess(){
        $users = model('systmess')->get_users_with_unreaded_mess(50, (int) config('systmess_to_email_time_range_send', 86400));
        if (empty($users)) {
            return;
        }

        $users = arrayByKey($users, 'idu');
        $users_ids = array_keys($users);
        $storage_messages = model('systmess')->get_unsended_users_systmess($users_ids, (int) config('systmess_to_email_time_range_send', 86400));
        $messages = arrayByKey($storage_messages, 'idu', true);

        $updated_users_ids = array();
        foreach($users as $id_user => $user){
            if (empty($messages[$id_user])) {
                continue;
            }

            $updated_users_ids[] = $id_user;
            $email_notifications_limit = config('systmess_to_email_limit_by_type');
            $user['messages_count'] = count($messages[$id_user]);
            $user_messages = arrayByKey($messages[$id_user], 'mess_type', true);

            if (!empty($user_messages['notice'])) {
                $user['messages']['count_notices'] = count($user_messages['notice']);
                $user['messages']['has_more_notice_messages'] = $user['messages']['count_notices'] > $email_notifications_limit;
                $user['messages']['notice'] = array_slice($user_messages['notice'], 0, $email_notifications_limit);
            }

            if (!empty($user_messages['warning'])) {
                $user['messages']['count_warnings'] = count($user_messages['warning']);
                $user['messages']['has_more_warning_messages'] = $user['messages']['count_warnings'] > $email_notifications_limit;
                $user['messages']['warning'] = array_slice($user_messages['warning'], 0, $email_notifications_limit);
            }

            $message = views()->fetch('emails/en/user/message_detail_view');
            $warningMessages = '';
            $noticeMessages = '';

            if (!empty($user['messages']['warning'])) {
                $messageContent = '';

                foreach ($user['messages']['warning'] as $mess) {
                    $messageDetail = $message;
                    $messageDetail = str_replace('[initDate]', formatDate($mess['init_date']), $messageDetail);
                    $messageDetail = str_replace('[title]', $mess['title'], $messageDetail);
                    $messageContent .= $messageDetail;
                }

                $replaceParams = [
                    '[type]'            => 'Important',
                    '[count]'           => $user['messages']['count_warnings'],
                    '[warningMessages]' => $messageContent,
                    '[moreWarnings]'    => $user['messages']['has_more_warning_messages'] ? views()->fetch('emails/en/user/more_messages_view') : '',
                    '[noticeMessages]'  => '',
                    '[moreNotices]'     => ''
                ];

                $warningMessages = views()->fetch('emails/en/user/messages_view');
                $warningMessages = str_replace(array_keys($replaceParams), array_values($replaceParams), $warningMessages);
            }

            if (!empty($user['messages']['notice'])) {
                $messageContent = '';

                foreach ($user['messages']['notice'] as $mess) {
                    $messageDetail = $message;
                    $messageDetail = str_replace('[initDate]', formatDate($mess['init_date']), $messageDetail);
                    $messageDetail = str_replace('[title]', $mess['title'], $messageDetail);
                    $messageContent .= $messageDetail;
                }

                $replaceParams = [
                    '[type]'            => 'Info',
                    '[count]'           => $user['messages']['count_notices'],
                    '[warningMessages]' => '',
                    '[moreWarnings]'    => '',
                    '[noticeMessages]'  => $messageContent,
                    '[moreNotices]'     => $user['messages']['has_more_notice_messages'] ? views()->fetch('emails/en/user/more_messages_view') : '',
                ];

                $noticeMessages = views()->fetch('emails/en/user/messages_view');
                $noticeMessages = str_replace(array_keys($replaceParams), array_values($replaceParams), $noticeMessages);
            }

            try {
                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new UnreadNotifications($user['user_name'], $user['messages_count'], $warningMessages, $noticeMessages))
                        ->to(new RefAddress((string) $id_user, new Address($user['email'])))
                );
            } catch (\Throwable $th) {
                jsonResponse(translate('email_has_not_been_sent'));
            }
        }

        if (!empty($updated_users_ids)) {
            model('systmess')->set_sended_by_user_ids($updated_users_ids);
            model('user')->update_users_sent_systmess_date($updated_users_ids);
        }

    }

    // DELETE SENT EMAILS FROM QUEUE
    // EVERY 3 DAYS
    function delete_sent_emails(){
        model('notify')->delete_emails(config('delete_sent_emails_days'));

        $this->cron_params['messages'][] = 'Old emails has been deleted.';
    }

    // DELOG ALL INACTIVE USERS
    // EVERY 10 MIN {0,10,20,30,40,50 * * * *}
    function delogAll(){
        $user_model = model('user');
        $users = $user_model->old_session_users();
        if(!empty($users)){
            $id_users = array_column($users, 'idu', 'idu');
            $user_model->logout_old_session_users($id_users);

            $users_logs = array_map(function($user){
                @unlink( rtrim(session_save_path(), '/') . "/sess_{$user['ssid']}" );

                return array(
                    'id_user' => $user['idu'],
                    'log_type' => SessionLogsTypes\LOGGED_OUT_CRON,
                    'log_message' => SessionLogsMessages\LOGGED_OUT_CRON
                );
            }, $users);

            model('session_logs')->handler_insert_batch($users_logs);
        }

        $this->cron_params['messages'][] = "All users with last_activity > 15 min has been logged out.";
    }

    // DELETE OLD FILES
    // EVERY DAY AT 00:00 {0 0 * * *}
    function clear_temp_files(){
        $path = 'temp';
        $dirs = scandir($path);

        unset($dirs[array_search('.', $dirs)]);
        unset($dirs[array_search('..', $dirs)]);

        foreach($dirs as $dir){
            if(is_dir($path . DS . $dir)){
                if($this->_delete_recursive($path . DS . $dir))
                    mkdir($path . DS . $dir);
            }
        }

        $this->cron_params['messages'][] = 'The temp directory has been cleared.';
    }

    // UPDATE CURRENCY RATE FROM 'http://apilayer.net/'
    // EVERY DAY AT 00:10 {10 0 * * *}
    public function renew_exchange_file(){
        $typeValute  = model('currency')->get_all_cur();
        $activeValute= model('currency')->get_main_cur();

        if(empty($typeValute)){
            $this->cron_params['status'] = 'error';
            $this->cron_params['messages'][] = 'There are no active currencies in the database.';

            return;
        }

        if(empty($activeValute)){
            $this->cron_params['status'] = 'error';
            $this->cron_params['messages'][] = 'There are no main currency setup in the database.';

            return;
        }

        $valute_by_code = array();
        $list_valute    = array();

        foreach($typeValute as $item){
            if($item['code'] != $activeValute){
                $valute_by_code[$item['code']] = $item;
                $list_valute[] = $item['code'];
            }
        }

        $content = array();

        // live         - get current currencies
        // list         - get list with support country
        // historical
        // convert
        // timeframe
        // change

        $url = 'http://apilayer.net/api/live';

        $params = array(
            'access_key' => '9bb8e21162a07ecfe401d5521f39a3a8', // api key
            //'currencies' => implode(',', $list_valute),         // list of currencies (EUR, GBP)
            'source'     => $activeValute,                      // currency active    (USD)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . arrayToGET($params));
        $data = curl_exec($ch);
        curl_close($ch);
        $content = json_decode($data, true);

        if(!$content['success']){
            $this->cron_params['status'] = 'error';
            $this->cron_params['messages'][] = $content['error']['info'];

            return;
        }

        $date = date('Y-m-d', $content['timestamp']);
        $to_json = array();
        foreach($content['quotes'] as $code => $rate){
            $codeValute= str_replace($activeValute, '', $code);
            $insert[]  = array(
                'ccode' => $codeValute,
                'nomin' => 1,
                'name'  => $valute_by_code[$codeValute]['curr_name'],
                'date'  => $date,
                'val'   => $rate
            );

            $to_json[$codeValute] = $rate;
        }

        file_put_contents('current_exchange_rate.json', json_encode($to_json));

        model('exchange_rate')->insert_ex_rate($insert);

        $this->cron_params['messages'][] = 'The currencies rates has been updated.';
    }

    // UPDATE ITEMS THUMBS
    // EVERY 5 MIN {0,5,10,15,20,25,30,35,40,45,50,55 * * * *}
    // function actualize_items_thumbs(){
    //     //CHECK THE CONFIG
    //     if(!config('actualize_items_thumbs')){
    //         return false;
    //     }

    //     // GET ITEMS
    //     $params = array(
    //         'columns' => 'id',
    //         'thumbs_actualized' => 0,
    //         'order_by' => ' update_date DESC ',
    //         'limit' => config('actualize_items_thumbs_limit')
    //     );
    //     $items = model('items')->get_items_for_thumbs_actualize($params);

    //     // IF ALL THE ITEMS THUMBS HAS BEEN ACTUALIZED CHANGE CONFIG
    //     if(empty($items)){
    //         $update = array(
    //             'value' => 0
    //         );
    //         model('config')->update_config('actualize_items_thumbs', $update);

    //         $all_configs = model('config')->get_configs();
    //         $conf_assoc = array();
    //         foreach($all_configs as $conf){
    //             $conf_assoc[$conf['key_config']] = $conf['value'];
    //         }

    //         tmvc::instance()->my_config = $conf_assoc;
    //         model('config')->save_in_file();
    //         file_get_contents(__SHIPPER_URL . 'configs/regenerate/cronaction');

    //         return false;
    //     }

    //     $items_list = array();
    //     foreach($items as $item){
    //         $items_list[$item['id']] = $item['id'];
    //     }

    //     // GET ITEMS PHOTOS
    //     $params = array(
    //         'items_list' => implode(',', $items_list)
    //     );
    //     $items_photos = model('items')->get_items_photos($params);
    //     if(empty($items_photos)){
    //         return false;
    //     }

    //     $photos_by_items = array();
    //     foreach($items_photos as $photo){
    //         $photos_by_items[$photo['sale_id']][] = $photo;
    //     }

    //     // ACTUALIZE THUMBS
    //     $thumbs_sizes = explode(',', config('item_thumbs_size'));
    //     if(!file_exists('public/items/thumbs_actualize_log.txt')){
    //         $file = fopen('public/items/thumbs_actualize_log.txt', 'a');
    //         fclose($file);
    //     }

    //     $log_content = '';
    //     foreach($photos_by_items as $item_id => $photos_by_item){
    //         $errors = array();
    //         foreach($photos_by_item as $items_photo){
    //             $thumbs = unserialize($items_photo['photo_thumbs']);
    //             $photo_thumbs_sizes = array_keys($thumbs);
    //             $create_thumbs = array_diff($thumbs_sizes, $photo_thumbs_sizes);
    //             $path = getImgPath('items.main', array('{ID}' => $items_photo['sale_id']));
    //             $thumbs_changed = false;

    //             if(!empty($create_thumbs)){
    //                 $conditions = array(
    //                     'destination' => $path,
    //                     'thumbs' => implode(',', $create_thumbs)
    //                 );
    //                 $result = $this->upload->create_thumbs($path.'/'.$items_photo['photo_name'], '', $conditions);

    //                 if(!empty($result['errors'])){
    //                     $errors[] = 'Item '.$items_photo['sale_id'].' -> Photo: '.$items_photo['photo_name'].' - '.implode('; ',$result['errors']);
    //                 } else{
    //                     foreach($result as $thumb) {
    //                         $thumbs[$thumb['thumb_key']] = $thumb['thumb_name'];
    //                     }
    //                     $thumbs_changed = true;
    //                 }
    //             }

    //             $remove_thumbs = array_diff($photo_thumbs_sizes, $thumbs_sizes);
    //             if(!empty($remove_thumbs) && empty($result['errors'])){
    //                 foreach($remove_thumbs as $remove_thumb){
    //                     unset($thumbs[$remove_thumb]);
    //                     @unlink($path.'/thumb_'.$remove_thumb.'_'.$items_photo['photo_name']);
    //                 }
    //                 $thumbs_changed = true;
    //             }

    //             if($thumbs_changed){
    //                 $update = array(
    //                     'photo_thumbs' => serialize($thumbs)
    //                 );
    //                 model('items')->update_photo_thumbs($items_photo['id'], $update);
    //             }
    //         }

    //         if($thumbs_changed && empty($errors)){
    //             $update_item = array(
    //                 'id' => $item_id,
    //                 'thumbs_actualized' => 1
    //             );
    //             model('items')->update_item($update_item);
    //         }

    //         // WRITE CRON ACTION LOG TO FILE
    //         $log_content .= '<tr>';
    //         $log_content .= '<td>';
    //         $log_content .= date('m/d/Y H:i:s');
    //         $log_content .= '</td>';
    //         $log_content .= '<td>';
    //         if(!empty($errors)){
    //             $this->cron_params['status'] = 'error';
    //             $this->cron_params['messages'] = $errors;
    //             $log_content .= '<strong class="txt-red">ERROR: </strong>'. implode('<br><strong class="txt-red">ERROR: </strong>',$errors);
    //         } else{
    //             $this->cron_params['messages'][] = 'The thumbs for item '.$item_id.' has been actualized.';
    //             $log_content .= '<strong class="txt-green">Success: </strong>Item ' . $item_id;
    //         }
    //         $log_content .= '</td>';
    //         $log_content .= '</tr>';
    //     }

    //     $log_file = fopen('public/items/thumbs_actualize_log.txt', "a");
    //     fwrite($log_file, $log_content);
    //     fclose($log_file);
    // }

    // CLEAR SYSTEM MESSAGES WITH
    // (STATUS 'deleted' && date_change > 10 days) || (STATUS 'seen' && date_change > 30 days)
    // EVERY DAY AT 00:20 {20 0 * * *}
    function clear_syst_mess(){
        model('SystMess')->clear_syst_mess();

        $this->cron_params['messages'][] = 'The users old system messages has been deleted.';
    }

    // DELETE COMPLAINS WITH
    // STATUS 'declined', 'confirmed' AND date_change > 90 days
    // EVERY DAY AT 00:40 {40 0 * * *}
    function clean_complains() {
        //DELETE COMPLAINS OLDEST THAN 90 DAYS
        model('complains')->deleteExpiredComplains(90);
        $this->cron_params['messages'][] = 'The old complains has been deleted.';
    }

    // DELETE ALL STICKERS WITH
    // STATUS trash AND create_date > 30 DAYS
    // EVERY DAY AT 00:50 {50 0 * * *}
    function clear_stickers(){
        model('stickers')->clear_stickers();

        $this->cron_params['messages'][] = 'The old stickers has been deleted.';
    }

    // DELETE USERS WITH
    // STATUS 'new' AND last_active > 90 DAYS
    // EVERY DAY AT 01:00 {0 1 * * *}
	// REVIEW
    function clear_users(){
        model('user')->clear_users(90);

        $this->cron_params['messages'][] = 'The inactive users has been deleted.';
    }

    // DELETE USER INFO CHANGES WITH date_change > 10 days
    // EVERY DAY AT 01:20 {20 1 * * *}
    function clear_user_info_changes(){
        model('user')->clear_user_info_changes();

        $this->cron_params['messages'][] = 'The old users info changes has been deleted.';
    }

    // NOTIFY USERS ABOUT EXPIRE SOON AND EXPIRED OFFERS,
    // SET OFFERS EXPIRED AND ARCHIVED
    // CLEAN DELETED BY BOTH USERS
    // EVERY DAY AT 01:40 {40 1 * * *}
    function item_offers(){
        $expire_offers = array();
        $expire_soon = model('offers')->get_soon_expire_offers(2);

        $users_list = array();
        $time = time();
        foreach($expire_soon as $offer){
            $users_list[$offer['id_seller']] = $offer['id_seller'];
            $users_list[$offer['id_buyer']] = $offer['id_buyer'];
            $offer_time = $offer['date_offer'] + $offer['days']*86400;
            if($offer_time <= $time){
                $expire_offers[$offer['id_offer']] = $offer['id_offer'];
            }
        }

        //ADD NOTIFICATIONS
        if(!empty($users_list)){

			$data_systmess = [
				'mess_code' => 'offer_expire',
				'id_users'  => $users_list,
				'replace'   => [
					'[EXPIRE_LINK]'      => __SITE_URL . 'offers/my/status/expired',
					'[EXPIRE_SOON_LINK]' => __SITE_URL . 'offers/my/expire/3',
					'[LINK]'             => __SITE_URL . 'offers/my'
				],
				'systmess' => true,
			];

            model('notify')->send_notify($data_systmess);
        }

        //CHANGE TO EXPIRED OFFERS
        if (!empty($expire_offers)) {
            model('offers')->set_offers_expired_by_list($expire_offers);
        }

        // SET OFFERS ARCHIVED
        model('offers')->set_offers_archived();

        //CLEAR OFFERS WITH STATE 2 (deleted from archive)
        model('offers')->clear_offers();

        $this->cron_params['messages'][] = 'The offers statuses has been actualized. The archived offers with state 2 has been deleted.';
    }

    // NOTIFY USERS ABOUT EXPIRE SOON AND EXPIRED ESTIMATES,
    // SET ESTIMATES EXPIRED AND ARCHIVED
    // CLEAN DELETED BY BOTH USERS
    // EVERY DAY AT 01:50 {50 1 * * *}
    function item_estimates(){
        $expire_estimate = array();
        $expire_soon = model('estimate')->get_soon_expire_estimates(2);

        $users_list = array();
        $time = time();
        foreach($expire_soon as $estimate){
            $users_list[$estimate['id_seller']] = $estimate['id_seller'];
            $users_list[$estimate['id_buyer']] = $estimate['id_buyer'];
            $estimate_time = strtotime($estimate['expire_date']);
            if($estimate_time <= $time){
                $expire_estimate[$estimate['id_request_estimate']] = $estimate['id_request_estimate'];
            }
        }

        //ADD NOTIFICATIONS
        if(!empty($users_list)){

			$data_systmess = [
				'mess_code' => 'estimate_item_expire',
				'id_users'  => $users_list,
				'replace'   => [
					'[EXPIRE_LINK]'      => __SITE_URL . 'estimate/my/status/expired',
					'[EXPIRE_SOON_LINK]' => __SITE_URL . 'estimate/my/expire/3',
					'[LINK]'             => __SITE_URL . 'estimate/my'
				],
				'systmess' => true
			];

            model('notify')->send_notify($data_systmess);
        }

        //CHANGE TO EXPIRED ESTIMATE REQUESTS
        if (!empty($expire_estimate)) {
            model('estimate')->set_estimates_expire_by_list($expire_estimate);
        }

        // SET ESTIMATES ARCHIVED
        model('estimate')->set_estimates_archived();

        //CLEAR ESTIMATES WITH STATE 2 (deleted from archive)
        model('estimate')->clear_estimates();

        $this->cron_params['messages'][] = 'The estimates statuses has been actualized. The archived estimates with state 2 has been deleted.';
    }

    // SET Producing Requests ARCHIVED
    // CLEAN DELETED BY BOTH USERS AND DECLINED Producing Requests
    // EVERY DAY AT 02:00 {0 2 * * *}
    function item_po(){
        //GET DELETED OR DECLINED Producing Requests
        $po_list = model('po')->get_old_po(30);
        $delete_po = array();
        $delete_prototypes = array();
        foreach($po_list as $po){
            $delete_po[] = $po['id_po'];
            $delete_prototypes[] = $po['id_prototype'];
        }

        //DELETE Producing Requests
        if(!empty($delete_po))
            model('po')->delete_po(implode(',', $delete_po));

        //DELETE PROTOTYPES
        if(!empty($delete_prototypes)){
            model('po')->delete_prototypes(implode(',', $delete_prototypes));

            //DELETE PROTOTYPES FILES
            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');

            /** @var LoggerInterface */
            $logger = $this->getContainer()->get('logger.filesystem');

            foreach($delete_prototypes as $proto){

                try {
                    $publicDisk->deleteDirectory($photoDirectory = ItemPathGenerator::prototypeDirectory($proto));
                } catch (\Throwable $e) {
                    $logger->error("Failed to delete directory due to error: {$e->getMessage()}", ['directory' => $photoDirectory]);
                }
            }
        }

        // SET Producing Requests ARCHIVED
        model('po')->set_po_archived();

        $this->cron_params['messages'][] = 'The Producing Requests statuses has been actualized. The Producing Requests with state 2 has been deleted.';
    }

    // SET INQUIRY ARCHIVED
    // CLEAN DELETED BY BOTH USERS AND DECLINED INQUIRIES
    // EVERY DAY AT 02:10 {10 2 * * *}
    function item_inquiry(){
        //GET EXPIRED INQUIRIES
        $inquiries = model('inquiry')->get_old_inquiries(30);

        $delete_inquiries = array();
        $delete_prototypes = array();
        foreach($inquiries as $inquire){
            $delete_inquiries[] = $inquire['id_inquiry'];
            $delete_prototypes[] = $inquire['id_prototype'];
        }

        //DELETE INQUIRIES
        if (!empty($delete_inquiries)) {
            model('inquiry')->delete_inquiries(implode(',' , $delete_inquiries));
        }

        //DELETE PROTOTYPES
        if(!empty($delete_prototypes)){
            model('inquiry')->delete_prototypes(implode(',' , $delete_prototypes));

            //DELETE PROTOTYPES FILES
            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');

            /** @var LoggerInterface */
            $logger = $this->getContainer()->get('logger.filesystem');

            foreach($delete_prototypes as $proto){
                try {
                    $publicDisk->deleteDirectory($photoDirectory = ItemPathGenerator::prototypeDirectory($proto));
                } catch (\Throwable $e) {
                    $logger->error("Failed to delete directory due to error: {$e->getMessage()}", ['directory' => $photoDirectory]);
                }
            }
        }

        model('inquiry')->set_inquiries_archived();

        $this->cron_params['messages'][] = 'The inquiry statuses has been actualized. The inquiries with state 2 has been deleted.';
    }

    // UPDATE FEATURED ITEMS STATUS TO EXPIRED
    // EVERY DAY AT 02:20 {20 2 * * *}
    function item_featured(){
        $users_to_notify_about_expire_soon = $users_to_notify_about_expired = $expired_items = $expired_feature = $user_feature_statistic = array();
        $days = (int) config('count_days_before_expire_featured_items_to_notify_seller', 5);

        $items_featured = model('items')->soon_expire_feature($days);

        if (empty($items_featured)) {
            return;
        }

        foreach ($items_featured as $item) {
            if ($item['days'] == $days) {
                $users_to_notify_about_expire_soon[$item['id_seller']] = $item['id_seller'];
                continue;
            }

            if ($item['days'] == -1) {
                if (isset($user_feature_statistic[$item['id_seller']])) {
                    $user_feature_statistic[$item['id_seller']]['active_featured_items']--;
                } else {
                    $user_feature_statistic[$item['id_seller']]['active_featured_items'] = -1;
                }

                $users_to_notify_about_expired[$item['id_seller']] = $item['id_seller'];
                $expired_feature[] = $item['id_featured'];
                $expired_items[] = $item['id_item'];
            }
        }

        //region notify
        if ( ! empty($users_to_notify_about_expire_soon))
        {
			$data_systmess = [
				'mess_code' => 'feature_item_expire_soon',
				'id_users'  => $users_to_notify_about_expire_soon,
				'replace'   => [
					'[EXPIRE_SOON_LINK]' => __SITE_URL . 'featured/my/status/expire_soon',
				],
				'systmess' => true,
			];

            model('notify')->send_notify($data_systmess);
        }

        if ( ! empty($users_to_notify_about_expired))
        {
			$data_systmess = [
				'mess_code' => 'feature_item_expired',
				'id_users'  => $users_to_notify_about_expired,
				'replace'   => [
					'[EXPIRED_LINK]' => __SITE_URL . 'featured/my/status/expired',
				],
				'systmess' => true,
			];

            model('notify')->send_notify($data_systmess);
        }
        //endregion notify

        //CHANGE TO EXPIRED FEATURED AND ITEMS
        if (!empty($expired_feature)) {
            model('items')->items_feature_expire_by_list($expired_feature);
        }

        if (!empty($expired_items)) {
            model('items')->change_items_feature_by_list($expired_items);
        }

        //CHANGE USER STATISTIC
        if(!empty($user_feature_statistic)){
            model('User_Statistic')->set_users_statistic($user_feature_statistic);
        }

        $this->cron_params['messages'][] = 'The featured items statuses has been actualized.';
    }

    // AUTO EXTEND FEATURED ITEMS
    function auto_extend_featured_items(){
        model(Items_Model::class)->auto_extend_featured_items();

        $this->cron_params['messages'][] = 'The featured items has been auto-extended.';
    }

    // UPDATE HIGHLIGHTED ITEMS STATUS TO EXPIRED
    // EVERY DAY AT 02:30 {30 2 * * *}
    function item_highlight(){
        $expired_items = $expire_highlight = $user_highlight_statistic = $users_to_notify_about_expire_soon = $users_to_notify_about_expired = array();
        $days = (int) config('count_days_before_expire_highlighted_items_to_notify_seller', 5);

        $items_highlight = model('items')->soon_expire_hightlight($days);
        if (empty($items_highlight)) {
            return;
        }

        foreach ($items_highlight as $item) {
            if ($item['days'] == $days) {
                $users_to_notify_about_expire_soon[$item['id_seller']] = $item['id_seller'];
                continue;
            }

            if ($item['days'] == -1) {
                if (isset($user_highlight_statistic[$item['id_seller']])) {
                    $user_highlight_statistic[$item['id_seller']]['active_featured_items']--;
                } else {
                    $user_highlight_statistic[$item['id_seller']]['active_featured_items'] = -1;
                }

                $users_to_notify_about_expired[$item['id_seller']] = $item['id_seller'];

                $expire_highlight[] = $item['id_highlight'];
                $expired_items[] = $item['id_item'];
            }
        }

        //region notify seller
        if ( ! empty($users_to_notify_about_expire_soon))
        {
			$data_systmess = [
				'mess_code' => 'highlight_item_expire_soon',
				'id_users'  => $users_to_notify_about_expire_soon,
				'replace'   => [
					'[EXPIRE_SOON_LINK]' => __SITE_URL . 'highlight/my/status/expire_soon',
				],
				'systmess' => true,
			];

            model('notify')->send_notify($data_systmess);
        }

        if ( ! empty($users_to_notify_about_expired)) {
			$data_systmess = [
				'mess_code' => 'highlight_item_expired',
				'id_users'  => $users_to_notify_about_expired,
				'replace'   => [
					'[EXPIRED_LINK]' => __SITE_URL . 'highlight/my/status/expired',
				],
				'systmess' => true,
			];

            model('notify')->send_notify($data_systmess);
        }
        //endregion notify seller

        //CHANGE TO EXPIRED HIGHLIGHT AND ITEMS
        if ( ! empty($expire_highlight)) {
            model('items')->items_highlight_expire_by_list($expire_highlight);
        }

        if ( ! empty($expired_items)) {
            model('items')->change_items_highlight_by_list($expired_items);
        }

        //CHANGE USER STATISTIC
        if ( ! empty($user_highlight_statistic)) {
            model('User_Statistic')->set_users_statistic($user_highlight_statistic);
        }

        $this->cron_params['messages'][] = 'The highlighted items statuses has been actualized.';
    }

    // NOTIFY ABOUT EXPIRED ADDITIONAL RIGHTS
    // EVERY DAY AT 02:50 {50 2 * * *}
    // function notify_about_expired_rights(){
    //     return true;
    // }

    // CLEAN EXPIRED ADDITIONAL RIGHTS
    // EVERY DAY AT 02:40 {40 2 * * *}
    function clean_expired_rights(){
        $expired_rights = model('blocking')->get_aditional_rights_expired();
        if(empty($expired_rights)){
            return;
        }

        $sellers_rights = array();
        $users_list_by_rights = array();
        foreach ($expired_rights as $expired_right) {
            $sellers_rights[$expired_right['id_user']][] = $expired_right['r_name'];
            $users_list_by_rights[$expired_right['r_alias']][$expired_right['id_user']] = $expired_right['id_user'];
        }

        model('blocking')->block_users_data_by_rights($users_list_by_rights);

        //ADD NOTIFICATIONS
        if(!empty($sellers_rights)){
            foreach ($sellers_rights as $key_seller => $seller_rights) {

				$data_systmess = [
					'mess_code' => 'user_aditional_rights_expired',
					'id_users'  => [$key_seller],
					'replace'   => [
						'[EXPIRED_RIGHTS]' => implode(', ', $seller_rights),
						'[LINK]'           => __SITE_URL . 'upgrade#additional-rights'
					],
					'systmess' => true
				];

                model('notify')->send_notify($data_systmess);
            }
        }

        model('blocking')->delete_aditional_rights_expired();
    }

    // PUBLISHING BLOGS WITH PUBLISHED = 0 AND PUBLISH_ON = CURRENT DAY
    // EVERY DAY
    function blogs_publishing(){
        model('blog')->change_published_status();
    }

    function check_package_paid() {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $users = $userModel->get_users_expired_paid([
            'days_before_expire'    => -3,
            'limit'                 => 50,
        ]);

        if (empty($users)) {
            return true;
        }

        /** @var Upgrade_Model $upgradeModel */
        $upgradeModel = model(Upgrade_Model::class);

        /** @var Packages_Model $packagesModel */
        $packagesModel = model(Packages_Model::class);

        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);

        /** @var User_Popups_Model $userPopupsModel */
        $userPopupsModel = model(User_Popups_Model::class);

        foreach ($users as $user) {
            $upgradeRequest = $upgradeModel->get_latest_request([
                'conditions' => [
                    'status'    => ['confirmed'],
                    'user'      => $user['idu'],
                ],
            ]);

            if (empty($upgradeRequest)) {
                // there should be an admin notification here
                continue;
            }

            $currentPackage = $packagesModel->getGrPackage((int) $upgradeRequest['id_package']);

            if (empty($currentPackage)) {
                // there should be an admin notification here
                continue;
            }

            $userModel->updateUserMain(
                $user['idu'],
                [
                    'user_photo_with_badge' => 0,
                    'user_page_blocked'     => 0,
                    'cookie_salt'           => genRandStr(8),
                    'paid_until'            => '0000-00-00',
                    'paid_price'            => 0,
                    'user_group'            => $currentPackage['downgrade_gr_to'],
                    'logged'                => 0,
                    'paid'                  => 1,
                ]
            );

            // Wake up, Neo
            $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserGroupChangedEvent((int) $user['idu']));

            session()->destroyBySessionId($user['ssid']);

            $upgradeModel->create_request([
                'id_package' => $upgradeRequest['id_package'],
                'id_user'    => $user['idu'],
                'status'     => 'confirmed',
                'type'       => 'downgrade',
            ]);

            $blockingModel->unblock_user_data_by_rights($user['idu'], (int) $currentPackage['downgrade_gr_to']);

            $userPopupsModel->updateMany(
                [
                    'is_viewed' => 1
                ],
                [
                    'conditions' => [
                        'popupHash' => 'update_profile_picture',
                        'userId'    => (int) $user['idu'],
                    ],
                ]
            );
        }
    }


    // UPDATE ORDERS BIDS
    // EVERY DAY AT 03:10 {10 3 * * *}
	function change_orders_bids_status(){
        model('orders_quotes')->make_bids_expired();

        $this->cron_params['messages'][] = 'The status of the order bids was updated.';
    }

    // SEND PERSONAL DOCUMENTS EXPIRING NOTIFICATION
    // EVERY DAY {0 0 * * *}
    /**
     * @deprecated
     * @return void
     */
    public function send_personal_documents_expiring_notification()
    {
        //region Documents
        $documents = model('user_personal_documents')->get_documents(array(
            'with'       => array('owner', 'type'),
            'conditions' => array(
                'expiring_after' => new \DateInterval("P7D")
            )
        ));
        //endregion Documents

        foreach ($documents as $document) {
            //region Vars
            if (null === ($user_id = arrayGet($document, 'owner.idu'))) {
                continue;
            }

            $document_id = (int) $document['id_document'];
            $is_user_verified = filter_var(arrayGet($document, 'owner.is_verified'), FILTER_VALIDATE_BOOLEAN);
            $user_group = arrayGet($document, 'owner.gr_type');
            $link = $is_user_verified
                ? getUrlForGroup('/personal_documents', null === $user_group ? null : snakeCase($user_group))
                : getUrlForGroup('/verification', null === $user_group ? null : snakeCase($user_group));
            $document_title = accreditation_i18n(
                arrayGet($document, 'type.document_i18n'),
                'title',
                null,
                arrayGet($document, 'type.document_title')
            );
            //endregion Vars

			//region Notifications
			model('notify')->send_notify([
				'systmess'  => true,
				'mess_code' => 'verification_documents_expiring',
				'id_users'  => [(int) $user_id],
				'replace'   => [
					'[LINK]'          => $link,
					'[DOCUMENT]'      => orderNumber($document_id),
					'[DOCUMENT_NAME]' => cleanOutput($document_title),
				],
			]);
			//endregion Notifications

        }
    }

    // SEND PERSONAL DOCUMENTS EXPIRATION NOTIFICATION
    // EVERY DAY {0 0 * * *}
    public function send_personal_documents_expiration_notification()
    {
        //region Documents
        $now = new \DateTimeImmutable();
        $current_date = $now->modify('midnight');
        $documents = model('user_personal_documents')->get_documents(array(
            'with'       => array('type'),
            'conditions' => array('expired_at_date' => $current_date),
        ));

        $documents_by_users = arrayByKey($documents, 'id_user', true);
        $user_ids = array_filter(array_keys($documents_by_users));
        $recepients = !empty($user_ids)
            ? arrayByKey(array_filter((array) model('user')->get_simple_users(array('users_list' => implode(',', $user_ids)))), 'idu')
            : array();
        //endregion Document

        //region Notifications
        $this->send_document_notifications(
            $recepients,
            array_filter(array_column(model('user')->get_users_by_additional_right('receive_document_notification'), 'idu')),
            $documents_by_users,
            $current_date,
            'verification_documents_expired_today',
            'verification_user_documents_expired_today'
        );
        //endregion Notifications

        //region Reset profile completion
        // model('complete_profile')->delete_profile_option_for_users($user_ids, 'account_verification');

        // /** @var TinyMVC_Library_Auth $authenticationLibrary */
        // $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        // foreach ($user_ids as $userId) {
        //     $authenticationLibrary->setUserCompleteProfile((int) $userId);
        // }
        //endregion Reset profile completion

        //region Reset verification
        // model('user')->cancel_verification_of_users($user_ids);
        //endregion Reset verification

        //region Log
        $this->cron_params['messages'][] = sprintf("The notification about expired document is sent to %s users", count($recepients));
        //endregion Log
    }

    // SEND PERSONAL DOCUMENTS EXPIRING NOTIFICATION
    // EVERY DAY {0 0 * * *}
    public function send_personal_documents_expiring_notification_per_interval()
    {
        //region Document
        $now = new \DateTimeImmutable();
        $current_date = $now->modify('midnight');
        $documents = model('user_personal_documents')->get_documents(array(
            'with'       => array('type'),
            'conditions' => array(
                'expiring_in_dates' => function () use ($current_date) {
                    return array(
                        $current_date,
                        array(
                            new \DateInterval(config('document_expiration_initial_threshold', 'P7D')),
                            new \DateInterval(config('document_expiration_intermediate_threshold', 'P5D')),
                            new \DateInterval(config('document_expiration_final_threshold', 'P3D')),
                        )
                    );
                }
            )
        ));

        $documents_by_users = arrayByKey($documents, 'id_user', true);
        $user_ids = array_filter(array_keys($documents_by_users));
        $recepients = !empty($user_ids)
            ? arrayByKey(array_filter((array) model('user')->get_simple_users(array('users_list' => implode(',', $user_ids)))), 'idu')
            : array();
        //endregion Document

        //region Notifications
        $this->send_document_notifications(
            $recepients,
            array_filter(array_column(model('user')->get_users_by_additional_right('receive_document_notification'), 'idu')),
            $documents_by_users,
            $current_date,
            'verification_documents_expiring_interval',
            'verification_user_documents_expiring_interval'
        );
        //endregion Notifications

        //region Log
        $this->cron_params['messages'][] = sprintf("The notification about document expiration is sent to %s users", count($recepients));
        //endregion Log
    }

    public function send_personal_documents_reupload_treshold_notification()
    {
        //region Document
        $current_date = new \DateTimeImmutable();
        $documents = model('user_personal_documents')->get_documents(array(
            'with'       => array('type'),
            'conditions' => array(
                'original_created_in_interval' => function () use ($current_date) {
                    $to_date = $current_date->sub(new DateInterval(config('document_re_upload_time_threshold', 'PT12H')));
                    $from_date = $to_date->sub(new DateInterval(config('document_re_upload_time_threshold', 'PT12H')));

                    return array($from_date, $to_date);
                }
            )
        ));

        $documents_by_users = arrayByKey($documents, 'id_user', true);
        $user_ids = array_filter(array_keys($documents_by_users));
        $recepients = !empty($user_ids)
            ? arrayByKey(array_filter((array) model('user')->get_simple_users(array('users_list' => implode(',', $user_ids)))), 'idu')
            : array();
        //endregion Document

        //region Notifications
        $this->send_document_notifications(
            $recepients,
            array_filter(array_column(model('user')->get_users_by_additional_right('receive_document_notification'), 'idu')),
            $documents_by_users,
            $current_date,
            'personal_document_re_upload_limit_timeout',
            'personal_user_document_re_upload_limit_timeout'
        );
        //endregion Notifications

        //region Log
        $this->cron_params['messages'][] = "The notifications about re-upload buffer period expiration are sent.";
        //endregion Log
    }

    public function send_all_personal_documents_expiration_notification()
    {
        //region Document
        $now = new \DateTimeImmutable();
        $current_date = $now->modify('midnight');
        $documents = model('user_personal_documents')->get_documents(array(
            'with'       => array('type'),
            'conditions' => array('expired_to_date' => $current_date)
        ));

        $documents_by_users = arrayByKey($documents, 'id_user', true);
        $user_ids = array_filter(array_keys($documents_by_users));
        $recepients = !empty($user_ids)
            ? arrayByKey(array_filter((array) model('user')->get_simple_users(array('users_list' => implode(',', $user_ids)))), 'idu')
            : array();
        //endregion Document

        //region Notifications
        $this->send_document_notifications(
            $recepients,
            array_filter(array_column(model('user')->get_users_by_additional_right('receive_document_notification'), 'idu')),
            $documents_by_users,
            $current_date,
            'verification_documents_expired',
            'verification_user_documents_expired'
        );
        //endregion Notifications

        //region Reset profile completion
        // model('complete_profile')->delete_profile_option_for_users($user_ids, 'account_verification');

        // /** @var TinyMVC_Library_Auth $authenticationLibrary */
        // $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        // foreach ($user_ids as $userId) {
        //     $authenticationLibrary->setUserCompleteProfile((int) $userId);
        // }
        //endregion Reset profile completion

        //region Reset verification
        // model('user')->cancel_verification_of_users($user_ids);
        //endregion Reset verification

        //region Log
        $this->cron_params['messages'][] = sprintf("The notification about all expired documents is sent to %s users", count($recepients));
        //endregion Log
    }

    public function orders_remainder_ep_manager(): void
    {
        /** @var Orders_Model $orders */
        $orders = model(Orders_Model::class);
        $orderStatus = $orders->get_status_by_alias('new_order');
        $newOrdersAmount = (int) $orders->simple_count_orders([
            'status'     => ((int) $orderStatus) ?: null,
            'ep_manager' => 0,
        ]);
        if (0 === $newOrdersAmount) {
            return;
        }

        $this->notifier->send(
            (
                new SystemNotification('orders_remainder_ep_manager', ['[LINK]' => sprintf('%sorder/admin_not_assigned', __SITE_URL)])
            )->channels([(string) SystemChannel::STORAGE()]),
            new RightfulRecipient(['administrate_orders'])
        );
    }

    public function send_orders_reminders(){
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $this->orders_remainder();
        $this->extend_orders_expire_soon();
        $this->send_extend_request_for_orders_expire_soon();
        $this->extend_orders_by_approved_request();
    }

    private function orders_remainder(){
        $orders = model('orders')->get_orders_by_expires_days();
        $orders_update = array();
        $notify_users_id = array();

        if(!empty($orders)){
            foreach($orders as $orders_item){

                $id_order = $orders_item['id'];
                $order_number = orderNumber($id_order);
                $notify_users = explode(',', $orders_item['notify_users']);
                $notify_users_id = array();
                $notifyManagersId = array();

                foreach($notify_users as $notify_users_item){
                    switch($notify_users_item){
                        case 'seller':
                            $notify_users_id[] = $orders_item['id_seller'];
                        break;
                        case 'buyer':
                            $notify_users_id[] = $orders_item['id_buyer'];
                        break;
                        case 'shipper':
                            if((int)$orders_item['id_shipper'] > 0 && $orders_item['shipper_type'] == 'ep_shipper'){
                                $notify_users_id[] = $orders_item['id_shipper'];
                            }
                        break;
                        case 'ep_manager':
                            if((int)$orders_item['ep_manager'] > 0){
                                $notifyManagersId[] = $orders_item['ep_manager'];
                            }
                        break;
                    }
                }

                if (!empty($notify_users_id)) {
                    $this->notifier->send(
                        (new SystemNotification('orders_remainder', [
                            '[LINK]'        => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                            '[ORDER_ID]'    => $order_number,
                            '[EXPIRE_DAYS]' => $orders_item['expire_days'],
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $notify_users_id)
                    );
                }
                if (!empty($notifyManagersId)) {
                    $this->notifier->send(
                        (new SystemNotification('orders_remainder', [
                            '[LINK]'        => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                            '[ORDER_ID]'    => $order_number,
                            '[EXPIRE_DAYS]' => $orders_item['expire_days'],
                        ]))->channels([(string) SystemChannel::STORAGE()]),
                        ...array_map(fn ($id) => new Recipient((int) $id), $notifyManagersId)
                    );
                }

                model('orders')->change_order($id_order, array('reminder_sent' => $orders_item['expire_days']));
            }
        }
    }

    // Called in send_orders_reminders()
    private function extend_orders_expire_soon(){
        $orders = model('orders')->get_orders_expires_soon();

        if (empty($orders)) {
            return false;
        }

        foreach($orders as $orders_item){
            $id_order = $orders_item['id'];
            $order_number = orderNumber($id_order);
            $notify_users = explode(',', $orders_item['notify_users']);
            $notify_users_id = array();
            $notify_ep_manager = array();

            foreach($notify_users as $notify_users_item){
                switch($notify_users_item){
                    case 'seller':
                        $notify_users_id[] = $orders_item['id_seller'];
                    break;
                    case 'buyer':
                        $notify_users_id[] = $orders_item['id_buyer'];
                    break;
                    case 'shipper':
                        if((int)$orders_item['id_shipper'] > 0 && $orders_item['shipper_type'] == 'ep_shipper'){
                            $notify_users_id[] = $orders_item['id_shipper'];
                        }
                    break;
                    case 'ep_manager':
                        if((int)$orders_item['ep_manager'] > 0){
                            $notify_ep_manager[] = $orders_item['ep_manager'];
                        }
                    break;
                }
            }

            $action_date = date('m/d/Y H:i:s');
            $extend_days = config('order_extend_days');
            $order_log = array(
                'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                'user' => 'EP Manager',
                'message' => 'Current order status ('.$orders_item['order_status'].') time with '.$extend_days.' day(s) has been auto extended.'
            );

            $update_order = array(
                'reminder_sent' => 0,
                'auto_extend' => 1,
                'status_countdown' => date_plus($extend_days, 'days'),
                'order_summary' => $orders_item['order_summary'].','.json_encode($order_log)
            );

            model('orders')->change_order($id_order, $update_order);

            if(in_array($orders_item['status_alias'], array('shipper_assigned', 'payment_processing'))){
                $bills = model('user_bills')->get_user_bills(array('id_order' => $orders_item['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                if(!empty($bills)){
                    foreach($bills as $bill){
                        $update_bill = array(
                            'due_date' => date_plus($extend_days, 'days')
                        );
                        model('user_bills')->change_user_bill($bill['id_bill'], $update_bill);
                    }
                }
            }

            if (!empty($notify_users_id)) {
                $this->notifier->send(
                    (new SystemNotification('auto_extend_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
                        '[DAYS]'     => $extend_days,
                        '[LINK]'     => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $notify_users_id)
                );
            }
            if (!empty($notify_ep_manager)) {
                $this->notifier->send(
                    (new SystemNotification('auto_extend_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
                        '[DAYS]'     => $extend_days,
                        '[LINK]'     => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE()]),
                    ...array_map(fn ($id) => new Recipient((int) $id), $notify_ep_manager)
                );
            }
        }

    }

    // Called in send_orders_reminders()
    private function send_extend_request_for_orders_expire_soon(){
        $orders = model('orders')->get_orders_expires_soon(array('auto_extend' => 1, 'request_auto_extend' => 0));

        if (empty($orders)) {
            return false;
        }

        foreach($orders as $orders_item){
            $id_order = $orders_item['id'];
            model('auto_extend')->set_extend_request(
                array(
                    'id_item' => $id_order,
                    'date_order_expired' => $orders_item['status_countdown'],
                )
            );

            $order_number = orderNumber($id_order);
            $notify_users = explode(',', $orders_item['notify_users']);
            $notify_users_id = array();
            $notify_ep_manager = array();

            foreach($notify_users as $notify_users_item){
                switch($notify_users_item){
                    case 'seller':
                        $notify_users_id[] = $orders_item['id_seller'];
                    break;
                    case 'buyer':
                        $notify_users_id[] = $orders_item['id_buyer'];
                    break;
                    case 'shipper':
                        if((int)$orders_item['id_shipper'] > 0 && $orders_item['shipper_type'] == 'ep_shipper'){
                            $notify_users_id[] = $orders_item['id_shipper'];
                        }
                    break;
                    case 'ep_manager':
                        if((int)$orders_item['ep_manager'] > 0){
                            $notify_ep_manager[] = $orders_item['ep_manager'];
                        }
                    break;
                }
            }

            $action_date = date('m/d/Y H:i:s');
            $order_log = array(
                'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                'user' => 'EP Manager',
                'message' => 'The Order Status ('.$orders_item['order_status'].') expires soon. Please make sure all participants confirmed auto extend status or check for order Cancel request.'
            );

            $update_order = array(
                'request_auto_extend' => 1,
                'order_summary' => $orders_item['order_summary'].','.json_encode($order_log)
            );

            model('orders')->change_order($id_order, $update_order);

            if (!empty($notify_users_id)) {
                $this->notifier->send(
                    (new SystemNotification('confirm_extend_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
						'[LINK]'     => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order)
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $notify_users_id)
                );
            }

            if (!empty($notify_ep_manager)) {
                $this->notifier->send(
                    (new SystemNotification('order_manager_notify_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
						'[LINK]'     => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order)
                    ]))->channels([(string) SystemChannel::STORAGE()]),
                    ...array_map(fn ($id) => new Recipient((int) $id), $notify_ep_manager)
                );
            }
        }
    }

    // Called in send_orders_reminders()
    private function extend_orders_by_approved_request(){
        $params = array(
            'limit' => '0,20',
            'status_buyer' => 'approved',
            'status_seller' => 'approved',
            'status_shipper' => 'approved',
            'remain_hours' => 1
        );

        $auto_extends = model('auto_extend')->get_extend_requests_by_condition($params);

        if (empty($auto_extends)) {
            return false;
        }

        $id_orders = array();
        foreach($auto_extends as $auto_extends_item){
            $id_orders[] = $auto_extends_item['id_item'];
        }

        $orders = model('orders')->get_orders(array('order_list' => implode(',', $id_orders)));

        foreach($orders as $orders_item){
            $id_order = $orders_item['id'];
            $order_number = orderNumber($id_order);
            $notify_users = explode(',', $orders_item['notify_users']);
            $notify_users_id = array();
            $notifyManagersId = array();

            foreach($notify_users as $notify_users_item){
                switch($notify_users_item){
                    case 'seller':
                        $notify_users_id[] = $orders_item['id_seller'];
                    break;
                    case 'buyer':
                        $notify_users_id[] = $orders_item['id_buyer'];
                    break;
                    case 'shipper':
                        if((int)$orders_item['id_shipper'] > 0 && $orders_item['shipper_type'] == 'ep_shipper'){
                            $notify_users_id[] = $orders_item['id_shipper'];
                        }
                    break;
                    case 'ep_manager':
                        if((int)$orders_item['ep_manager'] > 0){
                            $notifyManagersId[] = $orders_item['ep_manager'];
                        }
                    break;
                }
            }

            $action_date = date('m/d/Y H:i:s');
            $extend_days = config('order_extend_days');
            $order_log = array(
                'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                'user' => 'EP Manager',
                'message' => 'Current order status ('.$orders_item['order_status'].') time with '.$extend_days.' day(s) has been auto extended.'
            );

            $update_order = array(
                'auto_extend' => 0,
                'request_auto_extend' => 0,
                'reminder_sent' => 0,
                'status_countdown' => date_plus($extend_days, 'days'),
                'order_summary' => $orders_item['order_summary'].','.json_encode($order_log)
            );

            model('orders')->change_order($id_order, $update_order);
            model('auto_extend')->delete_extend_request_by_item($id_order);

            if(in_array($orders_item['status_alias'], array('shipper_assigned', 'payment_processing'))){
                $bills = model('user_bills')->get_user_bills(array('id_order' => $orders_item['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                if(!empty($bills)){
                    foreach($bills as $bill){
                        $update_bill = array(
                            'due_date' => date_plus($extend_days, 'days')
                        );
                        model('user_bills')->change_user_bill($bill['id_bill'], $update_bill);
                    }
                }
            }

            if (!empty($notify_users_id)) {
                $this->notifier->send(
                    (new SystemNotification('auto_extend_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
                        '[DAYS]'     => $extend_days,
                        '[LINK]'     => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $notify_users_id)
                );
            }
            if (!empty($notifyManagersId)) {
                $this->notifier->send(
                    (new SystemNotification('auto_extend_orders_expired_soon', [
                        '[ORDER_ID]' => $order_number,
                        '[DAYS]'     => $extend_days,
                        '[LINK]'     => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE()]),
                    ...array_map(fn ($id) => new Recipient((int) $id), $notifyManagersId)
                );
            }
        }
    }

    // Called in generate_main_sitemap()
    public function generate_static_sitemap()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $urls = [
            '',
            'about',
            'about/in_the_news',
            'about/our_story',
            'about/culture_and_policy',
            'about/partnership',
            'about/members_protect',
            'about/seller_verification',
            'about/integrity_and_compliance',
            'library_international_standards',
            'about/country_focus_colombia',
            'faq',
            'security',
            'help',
            'export_import',
            // 'library_country_statistic',
            // 'library_customs',
            // 'library_trade',
            'library_consulates/all',
            'library_icc_country/all',
            'library_inspection_agency/all',
            'library_importer_exporter/all',
            'library_accreditation_body',
            'user_guide',
            'about/why_exportportal',
            'categories',
            'register',
            'login',
            'register/buyer',
            'register/seller',
            'register/manufacturer',
            'register/shipper',
            'authenticate/forgot',
            'subscribe',
            'learn_more',
            'manufacturer_description',
            'buying',
            'selling',
            'page/payments',
            'page/shipperfees',
            'page/sellerfees',
            'shipper_description',
            'contact',
            'user/unsubscribe/',
            'contact',
            'items/latest',
            'items/featured',
            'ep_events',
            'ep_events/past',
        ];
        $pathSitemaps = dirname(TMVC_BASEDIR) . '/sitemap/';
        $path = $pathSitemaps . 'static/' . 'static_sitemap.xml';
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $disk = $storageProvider->storage('sitemap.legacy.storage');
        $disk->deleteDirectory('static');
        $disk->createDirectory('static');
        $url_builder = function () use ($urls) {
            foreach ($urls as $url) {
                yield __SITE_URL . $url;
            }
        };

        $sitemap = new SitemapGenerator(
            new UrlsGenerator($url_builder()),
            new SitemapAdapter(
                new Sitemap($path),
                __SITE_URL . 'sitemap/static/'
            )
        );
        $sitemap->generate();

        $this->actualize_main_sitemap_xml($sitemap->getSitemapFilesUrls(), 'static');
    }

    public function generate_downloadable_materials_sitemap()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $pathSitemaps = dirname(TMVC_BASEDIR) . '/sitemap/';
        $path = $pathSitemaps . 'downloadable_materials/' . 'downloadable_materials_sitemap.xml';

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $disk = $storageProvider->storage('sitemap.legacy.storage');
        $disk->deleteDirectory('downloadable_materials');
        $disk->createDirectory('downloadable_materials');

        /** @var Downloadable_Materials_Model $materialsModel*/
        $materialsModel = model(Downloadable_Materials_Model::class);

        $downloadableMaterialsPages = $materialsModel->findAll();

        $urlBuilder = function () use ($downloadableMaterialsPages) {
            foreach ($downloadableMaterialsPages as $page) {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $page['created']);
                yield [
                    __SITE_URL . 'downloadable_materials/details/' . $page['slug'],
                    $date->getTimestamp()
                ];
            }
        };

        $sitemap = new SitemapGenerator(
            new UrlsGenerator($urlBuilder()),
            new SitemapAdapter(new Sitemap($path), __SITE_URL . 'sitemap/downloadable_materials/')
        );

        $sitemap->generate();

        $this->actualize_main_sitemap_xml($sitemap->getSitemapFilesUrls(), 'downloadable_materials');
    }

    // Called in generate_main_sitemap()
    public function generate_blog_sitemap()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $pathSitemaps = dirname(TMVC_BASEDIR) . '/sitemap/';
        $path = $pathSitemaps . 'blog/' . 'blog_sitemap.xml';

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $disk = $storageProvider->storage('sitemap.legacy.storage');
        $disk->deleteDirectory('blog');
        $disk->createDirectory('blog');

        /** @var Elasticsearch_Blogs_Model $elasticsearchBlogsModel */
        $elasticsearchBlogsModel = model(Elasticsearch_Blogs_Model::class);

        $url_builder = function () use ($elasticsearchBlogsModel) {
            $offset = 0;
            $limit = 100;
            $blogs = array();
            do {
                $blogs = $elasticsearchBlogsModel->get_blogs([
                    'allLanguages'  => true,
                    'published'     => 1,
                    'visible'       => 1,
                    'start'         => $offset,
                    'limit'         => $limit,
                ]);

                foreach ($blogs as $blog) {
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $blog['date']);
                    yield array(
                        getBlogUrl($blog),
                        $date->getTimestamp()
                    );
                }

                $offset += $limit;
            } while (!empty($blogs));
        };

        $sitemap = new SitemapGenerator(
            new UrlsGenerator($url_builder()),
            new SitemapAdapter(new Sitemap($path), __SITE_URL . 'sitemap/blog/')
        );
        $sitemap->generate();

        $this->actualize_main_sitemap_xml($sitemap->getSitemapFilesUrls(), 'blog');
    }

    // Called in generate_main_sitemap()
    public function generate_ep_events_sitemap()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $pathSitemaps = dirname(TMVC_BASEDIR) . '/sitemap/';
        $path = $pathSitemaps . 'events/' . 'events_sitemap.xml';
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $disk = $storageProvider->storage('sitemap.legacy.storage');
        $disk->deleteDirectory('events');
        $disk->createDirectory('events');

        /** @var Elasticsearch_Ep_Events_Model $elasticEpEventsModel */
        $elasticEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $elasticEpEventsModel->getEvents();
        $events = $elasticEpEventsModel->records;

        $urlBuilder = function () use ($events) {
            foreach ($events as $event) {
                if (
                    null === $event['published_date']
                    || false === (
                        $date = DateTime::createFromFormat('Y-m-d H:i:s', $event['published_date'])
                    )
                ) {
                    continue;
                }

                yield [getEpEventDetailUrl($event), $date->getTimestamp()];
            }
        };

        $sitemap = new SitemapGenerator(
            new UrlsGenerator($urlBuilder()),
            new SitemapAdapter(new Sitemap($path), __SITE_URL . 'sitemap/events/')
        );
        $sitemap->generate();

        $this->actualize_main_sitemap_xml($sitemap->getSitemapFilesUrls(), 'events');
    }

    private function actualize_main_sitemap_xml(?array $urls, string $entity)
    {
        $current_count_entity_urls = model(Sitemap_Model::class)->count_records(array('conditions' => array('entity' => $entity)));
        if (count($urls) === $current_count_entity_urls) {
            return true;
        }

        model(Sitemap_Model::class)->delete_records(array('conditions' => array('entity' => $entity)));

        $data_to_insert = array_map(
            function ($url) use ($entity) {
                return array(
                    'entity' => $entity,
                    'url'   => $url
                );
            },
            $urls
        );

        model(Sitemap_Model::class)->add_records($data_to_insert);

        $sitemap_urls = model(Sitemap_Model::class)->get_records();
        if (empty($sitemap_urls)) {
            return false;
        }

        $sitemap = new SitemapIndexAdapter(new Index(dirname(TMVC_BASEDIR) . '/sitemap.xml'));
        foreach ($sitemap_urls as $sitemap_url) {
            $sitemap->addSitemap($sitemap_url['url']);
        }

        $sitemap->generateFile();
    }

    public function maintenance_mode()
    {
        $maintenanceDatetime = config('maintenance_mode_datetime');
        $curDatetime = date('Y-m-d H:i');

        if (validateDate(config('maintenance_mode_datetime'), 'Y-d-m H:i') && $maintenanceDatetime === $curDatetime) {
            exec('php bin/console app:maintenance 1');
        }
    }

    public function add_translation_key_changes_systmess()
    {
        $date = new DateTime('now');
        $update_date_from = $date->sub(new DateInterval('P1D'))->format('Y-m-d');

        $updated_pages = model('pages')->get_updated_pages($update_date_from);

        if (empty($updated_pages)) {
            $this->cron_params['messages'][] = 'Updated pages was not found';

            return;
        }

        $translation_lead_id_user = (int) config('translations_lead_user_id');

		model('Notify')->send_notify([
			'mess_code' => 'changes_on_translation_keys',
			'id_users'  => [$translation_lead_id_user],
			'replace'   => [
				'[PAGE]' => implode(', ', array_column($updated_pages, 'page_name', 'page_name')),
			],
			'systmess' => true
		]);

        $this->cron_params['messages'][] = 'Translation keys was updated on ' . implode(', ', array_column($updated_pages, 'page_name')) . ' pages';
    }

    /**
     * Export users bulk to CRM
     */
    public function export_users_to_crm()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

        $users_to_export = $crmModel->get_users_for_export((int) config('env.ZOHO_EXPORT_BULK_LIMIT', 20));
        if (empty($users_to_export)) {
            return;
        }

        $users_ids = array_column($users_to_export, 'id_user');

        /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
        $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

        $users = $crmLibrary->get_users_data_to_export($users_ids);
        $not_created_leads = $exported_users_ids = $users_for_update = array();

        foreach ($users as $user) {
            if (null !== $user['zoho_id_record']) {
                $crmModel->update_record($user['idu'], array('action' => 'update'));
                continue;
            }

            //region to create zoho crm lead
            $response = $crmLibrary->create_lead($user);
            $response_detail = $response->getDetails();

            switch ($response->getStatus()) {
                case 'error':
                    $response_code = $response->getCode();

                    if ($response_code != 'DUPLICATE_DATA') {
                        $error = array(
                            'Code: ' . $response_code,
                            'Details: ' . json_encode($response_detail),
                        );

                        $not_created_leads[$user['idu']] = json_encode($error);

                        continue 2;
                    }

                    $users_for_update[$user['idu']] = $user['idu'];
                break;
            }

            //region to convert zoho crm lead to contact
            $response = $crmLibrary->convert_lead_to_contact_by_lead_id($response_detail['id'], isset($users_for_update[$user['idu']]));
            switch ($response['status']) {
                case 'error':
                    $error = array(
                        'Code: ' . $response['code'],
                        'Details: ' . json_encode($response),
                        'Action: Convert lead ' . $response_detail['id'] . ' to contact'
                    );

                    $crmModel->update_record($user['idu'], array('error' => json_encode($error), 'is_resolved' => 0));
                break;
                case 'success':
                    $exported_users_ids[$user['idu']] = $user['idu'];

                    /** @var User_Model $userModel */
                    $userModel = model(User_Model::class);

                    $userModel->updateUserMain($user['idu'], array('zoho_id_record' => $response['contact_id']));
                break;
            }
            //endregion to convert zoho crm lead to contact
        }

        foreach ($not_created_leads as $id_user => $error) {
            $crmModel->update_record($id_user, array('error' => $error, 'is_resolved' => 0));
        }

        if (!empty($exported_users_ids)) {
            $crmModel->delete_records_by_users_ids($exported_users_ids);
        }

        if (!empty($users_for_update)) {
            foreach ($users_for_update as $id_user) {
                if (isset($exported_users_ids[$id_user])) {
                    $crmModel->create_record(array('id_user' => $id_user, 'action' => 'update'));
                }
            }
        }
    }

    /**
     * Update users bulk on CRM
     */
    public function update_users_to_crm()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $users_to_update = model(Crm_Model::class)->get_users_for_update((int) config('env.ZOHO_EXPORT_BULK_LIMIT', 20));
        if (empty($users_to_update)) {
            return;
        }

        $users_ids = array_column($users_to_update, 'id_user');
        $response = library(TinyMVC_Library_Zoho_crm::class)->update_contacts_by_users_ids($users_ids);

        $failed_users_data = $successfully_updated_users_ids = array();

        $iterator = 0;
        foreach ($response as $response_by_user) {
            $response_detail = $response_by_user->getDetails();
            if ('success' !== $response_by_user->getStatus()) {
                $id_user = $users_ids[$iterator];

                $response_data = array(
                    'Code: ' . $response_by_user->getCode(),
                    'Details: ' . json_encode($response_detail)
                );

                $update_sync_table_row = array(
                    'id_user'       => $id_user,
                    'action'        => 'update',
                    'error'         => json_encode($response_data),
                    'is_resolved'   => 0,
                );

                if (!model(Crm_Model::class)->update_record($id_user, $update_sync_table_row)) {
                    $this->cron_params['messages'][] = 'Failed to update crm_sync_users for user with id = ' . $id_user;
                }

                $failed_users_data[] = implode("\n", $response_data);
            } else {
                $successfully_updated_users_ids[] = (int) $response_by_user->getData()->getFieldValue('EP_USER_ID');
            }

            $iterator++;
        }

        if (!empty($successfully_updated_users_ids) && ! model('crm')->delete_records_by_users_ids($successfully_updated_users_ids)) {
            $this->cron_params['messages'][] = 'Failed to delete from crm_sync_users records for users with id = ' . implode(',', $successfully_updated_users_ids);
        }

        if (!empty($failed_users_data)) {
            $this->cron_params['messages'][] = implode("\n\n", $failed_users_data);
        }

        if (!empty($successfully_updated_users_ids)) {
            $this->cron_params['messages'][] = 'Users with ids [' . implode(', ', $successfully_updated_users_ids) . '] was successfully updated on CRM';

            if (!model(Crm_Model::class)->delete_records_by_users_ids($successfully_updated_users_ids)) {
                $this->cron_params['messages'][] = 'Failed to delete from crm_sync_users records for users with id = ' . implode(',', $successfully_updated_users_ids);
            }
        }

        return;
    }

    public function check_not_verified_emails_through_api()
    {
        /** @var Email_Hash_Model */
        $emailHashes = model(Email_Hash_Model::class);
        $emails = $emailHashes->findAllBy([
            'limit'  => (int) config('env.EMAILCHECKER_API_MAX_BULK', 30),
            'scopes' => [
                'to_verify_not_null'      => true,
                'rechecked_not_more_than' => (int) config('env.EMAILCHECKER_API_MAX_RECHECK')
            ],
        ]);
        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            checkEmailDeliverability($email['to_verify'], true);
        }
        $this->cron_params['messages'][] = 'There have been checked ' . count($emails) . ' emails.';
    }

    public function check_mail_messages_emails_through_api()
    {
        $mails = model('notify')->get_not_verified_mails();

        if(empty($mails)){
            return;
        }

        $emails = array_column($mails, 'to');
        $messages_ids = array_column($mails, 'id');

        $all_unique_emails = array_unique(explode(\App\Common\EMAIL_DELIMITER, implode(\App\Common\EMAIL_DELIMITER, $emails)));

        foreach($all_unique_emails as $one_email)
        {
            checkEmailDeliverability($one_email);
        }

        model('notify')->update_sent_emails($messages_ids, array('is_verified' => 1));
        $this->cron_params['messages'][] = 'There have been checked ' . count($mails) . ' emails.';
    }

    public function image_optimization() {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);

        $image_optimization_model = model(Image_optimization_Model::class);

        $images = $image_optimization_model->get_records(array(
            'order'         => array(
                'id'  => 'ASC'
            ),
            'conditions'    => array(
                'in_process'    => 0,
                'error'         => false,
            ),
            'limit'         => (int) config('env.LIMIT_IMAGES_PER_OPERATION', 2)
        ));

        if (empty($images)) {
            return;
        }

        model(Image_optimization_Model::class)->update_records_by_ids(array_column($images, 'id'), array('in_process' => 1));

        $optimized_images_ids = array();
        foreach ($images as $image) {
            if (!file_exists($image['file_path'])) {
                $image_optimization_model->delete_record(array('conditions' => array('id' => (int) $image['id'])));

                continue;
            }

            switch ($image['type']) {
                case 'downloadable_materials_cover':
                    $image_has_been_optimized = $this->optimizeDownloadableMaterialsCover($image, $image_optimization_model);
                break;
                case 'user_photos':
                    $image_has_been_optimized = $this->optimize_user_photo($image, $image_optimization_model);
                break;
                case 'user_main_photo':
                    $image_has_been_optimized = $this->optimize_user_avatar($image, $image_optimization_model, $elasticsearchUsersModel);
                break;
                case 'company_logo':
                    $image_has_been_optimized = $this->optimize_company_logo($image, $image_optimization_model);
                break;
                case 'shipper_company_logo':
                    $image_has_been_optimized = $this->optimize_shipper_company_logo($image, $image_optimization_model);
                break;
                case 'shipper_photos':
                    $image_has_been_optimized = $this->optimize_shipper_company_photo($image, $image_optimization_model);
                break;
                case 'blog_main_photo':
                    $image_has_been_optimized = $this->optimize_blog_main_photo($image, $image_optimization_model);
                break;
                case 'blog_text_photo':
                    $image_has_been_optimized = $this->optimize_blog_text_photo($image, $image_optimization_model);
                break;
                case 'ep_event_recommended_image':
                    $image_has_been_optimized = $this->optimize_event_recommended_image($image, $image_optimization_model);
                break;
                case 'ep_event_main_image':
                    $image_has_been_optimized = $this->optimize_event_main_image($image, $image_optimization_model);
                break;
                case 'ep_event_gallery_image':
                    $image_has_been_optimized = $this->optimize_event_gallery_image($image, $image_optimization_model);
                break;
                case 'items_compilation_desktop_image':
                    $image_has_been_optimized = $this->optimize_compilation_desktop_image($image, $image_optimization_model);
                break;
                case 'items_compilation_tablet_image':
                    $image_has_been_optimized = $this->optimize_compilation_tablet_image($image, $image_optimization_model);
                break;
                case 'faq_inline_image':
                    $image_has_been_optimized = $this->optimize_faq_inline_image($image, $image_optimization_model);
                break;
                case 'promo_banner_image':
                    $image_has_been_optimized = $this->optimizePromoBannerImage($image, $image_optimization_model);
                break;
                default:
                    $image_has_been_optimized = false;
                break;
            }

            if ($image_has_been_optimized) {
                $optimized_images_ids[] = $image['id'];
            }
        }

        if (!empty($optimized_images_ids)) {
            $image_optimization_model->delete_record(array('conditions' => array('ids' => $optimized_images_ids)));
        }
    }

    public function update_translations_usage_log_in_db() {
        $filePath = dirname(__FILE__, 2) . '/configs/translations/translations_keys_usage_log.php';

        $translationsKeys = (function () use ($filePath): array {
            if (file_exists($filePath)) {
                return include_once $filePath;
            }

            return [];
        })();
        if (empty($translationsKeys)) {
            return;
        }

        $newRecords = [];

        foreach ($translationsKeys as $controller => $data) {
            foreach ($data as $action => $keys) {
                foreach ($keys as $key => $nothing) {
                    $newRecords[] = [
                        'translation_key'   => $key,
                        'controller'        => $controller,
                        'action'            => $action,
                    ];
                }
            }
        }

        $splitRecords = array_chunk($newRecords, 1000);

        /** @var Translations_Model $translationsModel*/
        $translationsModel = model(Translations_Model::class);

        foreach ($splitRecords as $bulkRecords) {
            $translationsModel->insertTranslationsKeysUsageLog($bulkRecords);
        }

        $translationsModel->removeDuplicatesFromTranslationsKeysUsageLog();

        $this->cron_params['messages'][] = 'The "translations keys usage log" has been successfuly exported in DB';
    }

    public function highlight_ep_event() {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        $events = $eventsModel->findAllBy(['highlightedDateTo' => (new \DateTime())->format('Y-m-d H:i:s')]);
        if (empty($events)) {
            return true;
        }

        $eventsIds = array_column($events, 'id');
        $eventsModel->updateMany(['highlighted_end_date' => null], ['ids' => $eventsIds]);

        /** @var Elasticsearch_Ep_Events_Model $elasticEpEventsModel */
        $elasticEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        foreach ($eventsIds as $eventId) {
            $elasticEpEventsModel->updateEvent((int) $eventId);
        }
    }
    //========================================================================================================================================//

    // HELP FUNCTIONS
    private function _delete_recursive($path){
        $period = 86400;
        if(is_file($path)){
            $file_last_changed = filemtime($path);
            if($file_last_changed != false && ($file_last_changed + $period) < time()){
                unlink($path);
                clearstatcache();
                return true;
            }else{
                return false;
            }
        }

        if(is_dir($path)){
            $files = scandir($path);

            unset($files[array_search('.', $files)]);
            unset($files[array_search('..', $files)]);

            $folder_last_changed = filemtime($path);
            if(empty($files) && $folder_last_changed != false && ($folder_last_changed + $period) < time()){
                rmdir($path);
                clearstatcache();
                return true;
            }

            foreach($files as $key => $file){
                if($this->_delete_recursive($path . DS . $file)){
                    unset($files[$key]);
                }
            }

            $file_last_changed = filemtime($path);
            if(empty($files) && $file_last_changed != false && ($file_last_changed + $period) < time()){
                rmdir($path);
                clearstatcache();
                return true;
            }
            return false;
        }
    }

    private function _cron_log()
    {
        $text = "[" . date('d/m/Y g:i A') . "] - ";
        $messages = implode("\n", $this->cron_params['messages']);
        $text .= "Action: " . $this->cron_params['action'] . " \n";
        $text .= "Status: " . $this->cron_params['status'] . " \n";
        $text .= "Message: {$messages} \n\r";

        $fp = fopen(str_replace('/', DIRECTORY_SEPARATOR, $this->cron_log_file), 'ab+');
        if (is_resource($fp)) {
            fwrite($fp, $text . "\n\r");
            fclose($fp);
        }
    }

    private function send_document_notifications(
        array $users,
        array $moderators,
        array $documents_per_user,
        \DateTimeImmutable $current_date,
        $user_notification_alias,
        $manager_notification_alias = null
    ) {
        foreach ($documents_per_user as $user_id => $user_documents) {
            if (empty($user_id) || !isset($users[$user_id])) {
                continue;
            }

            $user = $users[$user_id];
            $user_name = trim(implode(' ', array(arrayGet($user, 'fname'), arrayGet($user, 'lname'))));
            $user_group = arrayGet($user, 'gr_type');
            $user_number = orderNumber($user_id);
            $is_user_verified = filter_var(arrayGet($user, 'is_verified'), FILTER_VALIDATE_BOOLEAN);
            $manager_link = getUrlForGroup($is_user_verified ? "/users/administration/user/{$user_id}" : "/verification/users?user={$user_id}", 'administrator');
            $user_link = getUrlForGroup($is_user_verified ? '/personal_documents' : '/verification', null === $user_group ? null : snakeCase($user_group));

            foreach ($user_documents as $document) {
                $document_id = (int) $document['id_document'];
                $expiration_date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $document['date_latest_version_expires']);
                $document_title = accreditation_i18n(
                    arrayGet($document, 'type.document_i18n'),
                    'title',
                    null,
                    arrayGet($document, 'type.document_title')
                );

                $replacements = array(
                    '[DOCUMENT]'        => orderNumber($document_id),
                    '[DOCUMENT_NAME]'   => cleanOutput($document_title),
                    '[EXPIRATION_DATE]' => getDateFormat($document['date_latest_version_expires']),
                    '[EXPIRATION_DAYS]' => $expiration_date && null !== $current_date ? $expiration_date->diff($current_date, true)->format('%a') : 0,
                );

                //region User notification
				model('notify')->send_notify([
					'systmess'  => true,
					'mess_code' => $user_notification_alias,
					'id_users'  => [(int) $user_id],
					'replace'   => array_merge(
						$replacements,
						['[LINK]' => $user_link]
					),
				]);
                //endregion User notification

                //region Manager notification
                if (null !== $manager_notification_alias && !empty($moderators)) {
                    model('notify')->send_notify([
                        'systmess'  => true,
                        'mess_code' => $manager_notification_alias,
                        'id_users'  => $moderators,
                        'replace'   => array_merge(
                            $replacements,
                            [
                                '[LINK]'      => $manager_link,
                                '[USER]'      => $user_number,
                                '[USER_NAME]' => cleanOutput($user_name),
                            ]
                        ),
                    ]);
                }
                //endregion Manager notification
            }
        }
    }

    private function optimizeDownloadableMaterialsCover(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $isConvertedImage = false;
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext) || empty($imageContext['id'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type downloadable_materials_cover is required and should contain ID!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            /** @var Downloadable_Materials_Model $materialsModel */
            $materialsModel = model(Downloadable_Materials_Model::class);

            if (!$materialsModel->updateOne((int) $imageContext['id'], ['cover' => $imagePathDetails['filename'] . '.jpg'])) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update cover image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
            $isConvertedImage = true;
        }

        $imagesToOptimization[] = [
            'path_to_duplicate' => $imagePathDetails['dirname'] . DS . 'original_' . $imagePathDetails['filename'] . '.jpg',
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        //region thumbs
        $thumbs = config('img.downloadable_materials.cover.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbNewFilePath = null;
                $thumbFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['basename'], $thumb['name']);

                if (!file_exists($thumbFilePath)) {
                    continue;
                }

                if ($isConvertedImage) {
                    $thumbNewFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.jpg';
                    $this->convert_image_to_jpg($thumbFilePath, $thumbNewFilePath);

                    $imagesToRemove[] = $thumbFilePath;
                }

                $imagesToOptimization[] = [
                    'path_to_webp'  => $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.webp',
                    'path_to_jpg'   => $thumbNewFilePath ?? $thumbFilePath,
                ];
            }
        }
        //endregion thumbs

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_user_avatar(array $image_optimization_record, Image_optimization_Model $image_optimization_model, Elasticsearch_Users_Model $elasticsearchUsersModel){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_user'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type user_main_photo is required and should contain id_user!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            if (!model(User_Model::class)->updateUserMain((int) $image_context['id_user'], array('user_photo' => $image_path_details['filename'] . '.jpg'))) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update user_photo in table users'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $elasticsearchUsersModel->sync((int) $image_context['id_user']);

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        $watermark = config('img.users.main.watermark');

        //region thumbs
        $thumbs = config('img.users.main.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumb_new_file_path = null;
                $thumb_name_template = $thumb['name'];
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );

                if(isset($watermark) && file_exists($image_path_details['dirname'] . DS . $watermark['prefix'] . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg'))
                {
                    $images_to_optimization[] = [
                        'path_to_webp'      => $image_path_details['dirname'] . DS . $watermark['prefix'] . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                        'path_to_jpg'       => $image_path_details['dirname'] . DS . $watermark['prefix'] . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg',
                    ];
                }
            }
        }
        //endregion thumbs

        if(isset($watermark) && file_exists($image_path_details['dirname'] . DS . $watermark['prefix'] . $image_path_details['filename'] . '.jpg'))
        {
            $images_to_optimization[] = [
                'path_to_webp'      => $image_path_details['dirname'] . DS . $watermark['prefix'] . $image_path_details['filename'] . '.webp',
                'path_to_jpg'       => $image_path_details['dirname'] . DS . $watermark['prefix'] . $image_path_details['filename'] . '.jpg',
            ];
        }

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_user_photo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_user'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type user_photos is required and should contain id_user!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            $data = array('name_photo' => $image_path_details['filename'] . '.jpg');
            $conditions = array(
                'name_photo'    => $image_path_details['basename'],
                'id_user'       => (int) $image_context['id_user'],
            );

            if (!model(User_photo_Model::class)->update_photo_by_conditions($data, $conditions)) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update name_photo in table user_photo'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        //region thumbs
        $thumbs = config('img.users.photos.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumb_new_file_path = null;
                $thumb_name_template = $thumb['name'];
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );
            }
        }
        //endregion thumbs

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_company_logo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_company'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type company_logo is required and should contain id_company!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            if (!model(Company_Model::class)->update_company((int) $image_context['id_company'], array('logo_company' => $image_path_details['filename'] . '.jpg'))) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update logo_company in table company_base'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        //region thumbs
        $thumbs = config('img.companies.main.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumb_new_file_path = null;
                $thumb_name_template = $thumb['name'];
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );
            }
        }
        //endregion thumbs

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_shipper_company_logo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_company'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type shipper_company_logo is required and should contain id_company!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            if (!model(Shippers_Model::class)->update_shipper(array('logo' => $image_path_details['filename'] . '.jpg'), (int) $image_context['id_company'])) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update logo in table orders_shippers'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        //region thumbs
        $thumbs = config('img.shippers.main.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumb_new_file_path = null;
                $thumb_name_template = $thumb['name'];
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );
            }
        }
        //endregion thumbs

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_shipper_company_photo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_company'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type shipper_photos is required and should contain id_company!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            if (!model(Shippers_photos_Model::class)->update_picture_name((int) $image_context['id_company'], $image_path_details['basename'], $image_path_details['filename'] . '.jpg')) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update picture in table orders_shippers_pictures'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        //region thumbs
        $thumbs = config('img.shippers.photos.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumb_new_file_path = null;
                $thumb_name_template = $thumb['name'];
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );
            }
        }
        //endregion thumbs

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_blog_main_photo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $is_converted_image = false;
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $images_to_remove = $images_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $image_context = $image_optimization_record['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($image_context)) {
                $image_context = json_decode($image_context, true) ?: null;
            }

            if (empty($image_context) || empty($image_context['id_blog'])) {
                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Context for images with type blog_main_photo is required and should contain id_blog!'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);
                return false;
            }

            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            if (!model(Blog_Model::class)->update_blog((int) $image_context['id_blog'], array('photo' => $image_path_details['filename'] . '.jpg'))) {
                unlink($new_file_path);

                $record_updates = array(
                    'error' => json_encode(array(
                        'message' => 'Failed to update photo in tableblogs'
                    ))
                );

                $image_optimization_model->update_record((int) $image_optimization_record['id'], $record_updates);

                return false;
            }

            $images_to_remove[] = $image_optimization_record['file_path'];
            $is_converted_image = true;
        }

        $images_to_optimization[] = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        //region thumbs
        $thumbs = config('blogs_photos_main_thumbs');

        if (!empty($thumbs)) {
            $thumbs_prefix_parts = explode(',', $thumbs);

            foreach ($thumbs_prefix_parts as $thumb_prefix) {
                $thumb_new_file_path = null;
                $thumb_name_template = 'thumb_' . $thumb_prefix . '_{THUMB_NAME}';
                $thumb_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['basename'], $thumb_name_template);

                if (!file_exists($thumb_file_path)) {
                    continue;
                }

                if ($is_converted_image) {
                    $thumb_new_file_path = $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.jpg';
                    $this->convert_image_to_jpg($thumb_file_path, $thumb_new_file_path);

                    $images_to_remove[] = $thumb_file_path;
                }

                $images_to_optimization[] = array(
                    'path_to_webp'  => $image_path_details['dirname'] . DS . str_replace('{THUMB_NAME}', $image_path_details['filename'], $thumb_name_template) . '.webp',
                    'path_to_jpg'   => $thumb_new_file_path ?? $thumb_file_path,
                );
            }
        }
        //endregion thumbs

        foreach ($images_to_optimization as $image_to_optimization) {
            $this->optimize_image($image_to_optimization, 95, 95);
        }

        if (!empty($images_to_remove)) {
            foreach ($images_to_remove as $image_to_remove) {
                unlink($image_to_remove);
            }
        }

        return true;
    }

    private function optimize_blog_text_photo(array $image_optimization_record, Image_optimization_Model $image_optimization_model){
        $image_path_details = pathinfo($image_optimization_record['file_path']);
        $image_to_remove = $image_to_optimization = array();

        if ('jpg' !== $image_path_details['extension']) {
            $new_file_path = $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg';

            $this->convert_image_to_jpg($image_optimization_record['file_path'], $new_file_path);

            $image_to_remove = $image_optimization_record['file_path'];
        }

        $image_to_optimization = array(
            'path_to_duplicate' => $image_path_details['dirname'] . DS . 'original_' . $image_path_details['filename'] . '.jpg',
            'path_to_webp'      => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.webp',
            'path_to_jpg'       => $image_path_details['dirname'] . DS . $image_path_details['filename'] . '.jpg',
        );

        $this->optimize_image($image_to_optimization, 95, 95);

        if (!empty($image_to_remove)) {
            unlink($image_to_remove);
        }

        return true;
    }

    private function optimize_event_recommended_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $isConvertedImage = false;
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext) || empty($imageContext['eventId'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type ep_event_recommended_image is required and should contain eventId!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            /** @var Ep_Events_Model $epEventsModel */
            $epEventsModel = model(Ep_Events_Model::class);

            if (!$epEventsModel->updateOne((int) $imageContext['eventId'], ['recommended_image' => $imagePathDetails['filename'] . '.jpg'])) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update recommended_image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
            $isConvertedImage = true;
        }

        $imagesToOptimization[] = [
            'path_to_duplicate' => $imagePathDetails['dirname'] . DS . 'original_' . $imagePathDetails['filename'] . '.jpg',
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        //region thumbs
        $thumbs = config('img.ep_events.recommended.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbNewFilePath = null;
                $thumbFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['basename'], $thumb['name']);

                if (!file_exists($thumbFilePath)) {
                    continue;
                }

                if ($isConvertedImage) {
                    $thumbNewFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.jpg';
                    $this->convert_image_to_jpg($thumbFilePath, $thumbNewFilePath);

                    $imagesToRemove[] = $thumbFilePath;
                }

                $imagesToOptimization[] = [
                    'path_to_webp'  => $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.webp',
                    'path_to_jpg'   => $thumbNewFilePath ?? $thumbFilePath,
                ];
            }
        }
        //endregion thumbs

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_event_main_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $isConvertedImage = false;
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? [];
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext) || empty($imageContext['eventId'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type ep_event_main_image is required and should contain eventId!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            /** @var Ep_Events_Model $epEventsModel */
            $epEventsModel = model(Ep_Events_Model::class);

            if (!$epEventsModel->updateOne((int) $imageContext['eventId'], ['main_image' => $imagePathDetails['filename'] . '.jpg'])) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update main_image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
            $isConvertedImage = true;
        }

        $imagesToOptimization[] = [
            'path_to_duplicate' => $imagePathDetails['dirname'] . DS . 'original_' . $imagePathDetails['filename'] . '.jpg',
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        //region thumbs
        $thumbs = config('img.ep_events.main.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbNewFilePath = null;
                $thumbFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['basename'], $thumb['name']);

                if (!file_exists($thumbFilePath)) {
                    continue;
                }

                if ($isConvertedImage) {
                    $thumbNewFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.jpg';
                    $this->convert_image_to_jpg($thumbFilePath, $thumbNewFilePath);

                    $imagesToRemove[] = $thumbFilePath;
                }

                $imagesToOptimization[] = [
                    'path_to_webp'  => $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.webp',
                    'path_to_jpg'   => $thumbNewFilePath ?? $thumbFilePath,
                ];
            }
        }
        //endregion thumbs

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_event_gallery_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $isConvertedImage = false;
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext) || empty($imageContext['eventId'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type ep_event_gallery_image is required and should contain eventId!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            /** @var Ep_Events_Images_Model $epEventsImagesModel */
            $epEventsImagesModel = model(Ep_Events_Images_Model::class);

            if (!$epEventsImagesModel->updateMany(['name' => $imagePathDetails['filename'] . '.jpg'], ['eventId' => (int) $imageContext['eventId'], 'images' => [$imagePathDetails['basename']]])) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update gallery image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
            $isConvertedImage = true;
        }

        $imagesToOptimization[] = [
            'path_to_duplicate' => $imagePathDetails['dirname'] . DS . 'original_' . $imagePathDetails['filename'] . '.jpg',
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        //region thumbs
        $thumbs = config('img.ep_events.gallery.thumbs');

        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbNewFilePath = null;
                $thumbFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['basename'], $thumb['name']);

                if (!file_exists($thumbFilePath)) {
                    continue;
                }

                if ($isConvertedImage) {
                    $thumbNewFilePath = $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.jpg';
                    $this->convert_image_to_jpg($thumbFilePath, $thumbNewFilePath);

                    $imagesToRemove[] = $thumbFilePath;
                }

                $imagesToOptimization[] = [
                    'path_to_webp'  => $imagePathDetails['dirname'] . DS . str_replace('{THUMB_NAME}', $imagePathDetails['filename'], $thumb['name']) . '.webp',
                    'path_to_jpg'   => $thumbNewFilePath ?? $thumbFilePath,
                ];
            }
        }
        //endregion thumbs

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_compilation_desktop_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext['compilationId'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type items_compilation_desktop_image is required and should contain compilationId!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            /** @var Items_Compilation_Model $itemsCompilationModel */
            $itemsCompilationModel = model(Items_Compilation_Model::class);

            if (empty($compilation = $itemsCompilationModel->findOne((int) $imageContext['compilationId']))) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => "Compilation with id {$imageContext['compilationId']} not found."
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            $compilation['background_images']['desktop'] = $imagePathDetails['filename'] . '.jpg';

            if (!$itemsCompilationModel->updateOne(
                (int) $imageContext['compilationId'],
                [
                    'background_images' => $compilation['background_images'],
                ]
            )) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
        }

        $imagesToOptimization[] = [
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_compilation_tablet_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = $imagesToOptimization = [];
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        if ('jpg' !== $imagePathDetails['extension']) {
            $imageContext = $imageOptimizationRecord['context'] ?? null;
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext['compilationId'])) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Context for images with type items_compilation_tablet_image is required and should contain compilationId!'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            /** @var Items_Compilation_Model $itemsCompilationModel */
            $itemsCompilationModel = model(Items_Compilation_Model::class);

            if (empty($compilation = $itemsCompilationModel->findOne((int) $imageContext['compilationId']))) {
                $recordUpdates = [
                    'error' => json_encode([
                        'message' => "Compilation with id {$imageContext['compilationId']} not found."
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $newFilePath = $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg';

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath);

            $compilation['background_images']['tablet'] = $imagePathDetails['filename'] . '.jpg';

            if (!$itemsCompilationModel->updateOne(
                (int) $imageContext['compilationId'],
                [
                    'background_images' => $compilation['background_images'],
                ]
            )) {
                unlink($newFilePath);

                $recordUpdates = [
                    'error' => json_encode([
                        'message' => 'Failed to update image name in DB'
                    ])
                ];

                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], $recordUpdates);
                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
        }

        $imagesToOptimization[] = [
            'path_to_webp'      => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'       => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ];

        foreach ($imagesToOptimization as $imageToOptimization) {
            $this->optimize_image($imageToOptimization);
        }

        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function optimize_faq_inline_image(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);

        $this->optimize_image([
            'path_to_webp'  => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.webp',
            'path_to_jpg'   => $imagePathDetails['dirname'] . DS . $imagePathDetails['filename'] . '.jpg',
        ]);

        return true;
    }

    /**
     * Runs optimization process for promo banner image.
     */
    private function optimizePromoBannerImage(array $imageOptimizationRecord, Image_optimization_Model $imageOptimizationModel){
        $imagesToRemove = [];
        $imagePathDetails = pathinfo($imageOptimizationRecord['file_path']);
        if (!in_array($imagePathDetails['extension'], ['jpg', 'jpeg'])) {
            $imageContext = $imageOptimizationRecord['context'] ?? [];
            // To process old records created before invalid encoding issue fix
            // we need to fall back to the good ol' JSON decode from string.
            if (is_string($imageContext)) {
                $imageContext = json_decode($imageContext, true) ?: null;
            }

            if (empty($imageContext) || empty($imageContext['bannerId'])) {
                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], [
                    'error' => [
                        'message' => 'Context for images with type `promo_banner` is required and should contain banner ID value!',
                    ]
                ]);

                return false;
            }

            /** @var Promo_Banners_Model $promoBannersRepository */
            $promoBannersRepository = model(Promo_Banners_Model::class);
            if (
                null === ($promoBanner = $promoBannersRepository->findOne($promoBannerId = (int) $imageContext['bannerId']))
            ) {
                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], [
                    'error' => [
                        'message' => sprintf('Failed to find the promo banner with ID value "%s"', $promoBannerId),
                    ]
                ]);

                return false;
            }

            $this->convert_image_to_jpg($imageOptimizationRecord['file_path'], $newFilePath = "{$imagePathDetails['dirname']}/{$imagePathDetails['filename']}.jpg");
            $promoBannerImages = $promoBanner['images'] ?? [];
            if (is_string($promoBannerImages)) {
                $promoBannerImages = json_decode($promoBannerImages, true) ?: [];
            }
            if (
                !$promoBannersRepository->updateOne($promoBannerId, ['images' => array_merge(
                    $promoBannerImages,
                    [
                        "{$imageContext['bannerId']}" => pathinfo($newFilePath, PATHINFO_BASENAME)
                    ]
                )])
            ) {
                $imageOptimizationModel->updateOne((int) $imageOptimizationRecord['id'], [
                    'error' => [
                        'message' => sprintf('Failed to update content for promo banner with ID value "%s"', $promoBannerId),
                    ]
                ]);

                return false;
            }

            $imagesToRemove[] = $imageOptimizationRecord['file_path'];
        }

        $this->optimize_image(
            [
                'path_to_webp'  => "{$imagePathDetails['dirname']}/{$imagePathDetails['filename']}.webp",
                'path_to_jpg'   => "{$imagePathDetails['dirname']}/{$imagePathDetails['filename']}.jpg",
            ],
            95,
            95
        );
        // Cleanup
        if (!empty($imagesToRemove)) {
            foreach ($imagesToRemove as $imageToRemove) {
                unlink($imageToRemove);
            }
        }

        return true;
    }

    private function convert_image_to_jpg(string $current_image_path, string $new_image_path){
        $im = new Imagick($current_image_path);
        $im->setImageBackgroundColor('#ffffff');
        $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('jpg');
        $im->writeImage($new_image_path);
    }

    private function optimize_image(array $image_to_optimization, int $webpQuality = 80, int $jpegQuality = 85)
    {
        if (!isset($image_to_optimization['path_to_webp'], $image_to_optimization['path_to_jpg'])) {
            return false;
        }

        //region copy original image
        if (isset($image_to_optimization['path_to_duplicate'])) {
            copy($image_to_optimization['path_to_jpg'], $image_to_optimization['path_to_duplicate']);
        }
        //endregion copy original image

        //region cwebp
        $optimizerChain = (new OptimizerChain)->addOptimizer(new CustomCwebp(array(
            '-m 6',
            '-pass 10',
            '-mt',
            '-sharp_yuv',
            "-q {$webpQuality}",
        )));

        $optimizerChain->useLogger(new Logger('Image_optimization', array(new RotatingFileHandler('var/log/cron/image_optimization.log'))))->optimize($image_to_optimization['path_to_jpg'], $image_to_optimization['path_to_webp']);
        //endregion cwebp

        //region jpegoptim
        $optimizerChain = (new OptimizerChain)->addOptimizer(new Jpegoptim(array(
            "-m{$jpegQuality}",
            '--strip-all',
            '--all-progressive',
        )));

        $optimizerChain->useLogger(new Logger('Image_optimization', array(new RotatingFileHandler('var/log/cron/image_optimization.log'))))->optimize($image_to_optimization['path_to_jpg']);
        //endregion jpegoptim
    }

    private function _smtp_send_emails_in_queue()
    {
        // temporary disable cron action for email sending
        return true;

        $count_emails_per_day = (int) config('env.SMTP_GMAIL_LIMIT_MESSAGES_PER_DAY', 0);
        $sent_emails_today = model(Notify_Model::class)->get_sent_messages_in_last_day();
        $count_successfully_sent_emails_today = 0;

        if (!empty($sent_emails_today)) {
            foreach ($sent_emails_today as $email) {
                $email_recipients = array_filter(explode(\App\Common\EMAIL_DELIMITER, $email['to']));
                $count_recipients = count($email_recipients);

                if (null === $email['failure_log']) {
                    $count_successfully_sent_emails_today += $count_recipients;
                    continue;
                }

                if (1 === $count_recipients) {
                    continue;
                }

                $failure_log = json_decode($email['failure_log'], true);
                $count_successfully_sent_emails_today += $count_recipients - count($failure_log);
            }
        }

        $count_available_messages = $count_emails_per_day - $count_successfully_sent_emails_today;

        if ($count_available_messages <= 0) {
            $this->cron_params['messages'][] = 'A limit of ' . $count_emails_per_day . ' emails per day has been reached.';

            return false;
        }

        //DO NOT DELETE COMMENTED CODE
        // $emails = model(Notify_Model::class)->get_emails(true, $count_available_messages);
        // if (empty($emails)) {
        //     return false;
        // }

        $id = request()->query->getInt('id');
        if(empty($id)){
            return false;
        }

        /** @var Mail_Messages_Model $mailMessagesModel */
        $mailMessagesModel = model(Mail_Messages_Model::class);
        $emails = $mailMessagesModel->findAllBy([
            'scopes' => [
                'id' => $id,
                'isSent' => 0,
                'isVerified' => 1,
            ],
            'with'  => ['content'],
        ]);

        if (empty($emails)) {
            return false;
        }

        $messages_ids = array();

        //get all unique emails to send to (including multiple)
        $all_unique_emails = array_unique(explode(\App\Common\EMAIL_DELIMITER, implode(\App\Common\EMAIL_DELIMITER, array_column($emails, 'to'))));

        //get bad status code emails from users and flip them as keys
        $bad_emails = array_flip(model('email_hash')->get_bad_emails($all_unique_emails));

        //region ENV vars
        $gmail_host = config('env.SMTP_GMAIL_HOST');
        $gmail_account = config('env.SMTP_GMAIL_ACCOUNT_EMAIL');
        $gmail_pass = config('env.SMTP_GMAIL_ACCOUNT_PASSWORD');
        $gmail_port = config('env.SMTP_GMAIL_PORT');

        $email_no_reply = config('env.EMAIL_NO_REPLY');
        $email_support = config('env.EMAIL_SUPPORT');
        //endregion ENV vars

        model(Notify_Model::class)->update_sent_emails(array_column($emails, 'id'), array('is_sent' => 1));

        foreach ($emails as $email) {
            $messages_ids[] = $email['id'];
            $error_messages = array();

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                          // Enable verbose debug output
                $mail->isSMTP();                                                // Send using SMTP
                $mail->Host       = $gmail_host;                                // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                       // Enable SMTP authentication
                $mail->Username   = $gmail_account;                             // SMTP username
                $mail->Password   = $gmail_pass;                                // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                // `PHPMailer::ENCRYPTION_STARTTLS` Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $mail->Port       = $gmail_port;                                // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                $mail->setFrom(empty($email['from']) ? $email_no_reply : $email['from']);
                $mail->addReplyTo(empty($email['reply_to']) ? $email_support : $email['reply_to']);

                $emails_list = explode(\App\Common\EMAIL_DELIMITER, $email['to']);

                $exist_valid_recipients = false;

                foreach ($emails_list as $one_email) {
                    if (isset($bad_emails[$one_email])) {
                        $error_messages[$one_email] = "The email {$one_email} is bad.";

                        continue;
                    }

                    $mail->addAddress($one_email);
                    $exist_valid_recipients = true;
                }

                if ($exist_valid_recipients) {
                    if (!empty($email['bcc'])) {
                        $mail->addBCC($email['bcc']);
                    }

                    if (!empty($email['cc'])) {
                        $mail->addCC($email['cc']);
                    }

                    $mail->isHTML(true);
                    $mail->Subject = $email['subject'];
                    $mail->Body = $email['content']['message'];

                    $mail->send();
                }
            } catch (Exception $e) {
                $error_messages[$one_email] = $mail->ErrorInfo;
            }

            if (!empty($error_messages)) {
                model(Notify_Model::class)->update_sent_emails(array($email['id']), array('failure_log' => json_encode($error_messages)));
            }
        }

        $this->cron_params['messages'][] = 'There has been processed ' . count($messages_ids) . ' emails.';

        return true;
    }

    // HIDE OUT OF STOCK ITEMS
	public function hide_out_of_stock_items()
	{
		$oldOutOfStock = model(Items_Model::class)->get_items([
			'is_out_of_stock'   => true,
			'date_out_of_stock' => true,
			'visible'           => 1,
		]);

		if (!empty($oldOutOfStock)) {
            $idsOnly = array_column($oldOutOfStock, 'id');
            model(Items_Model::class)->updateItems($idsOnly, [
                'visible' => 0
            ]);
			model(Elasticsearch_Items_Model::class)->index($idsOnly);
		}
		$this->cron_params['messages'][] = 'Old out of stock items were hidden';
    }

    // NOTIFY USERS ABOUT EXPIRE SOON CERTIFICATION
    // EVERY DAY
    public function notify_users_certification_expire_soon()
    {
        $expireSoon = model('user')->get_soon_expire_certification();

        if (empty($expireSoon)) {
            return false;
        }

        foreach($expireSoon as $expireSoonUser){
            model('notify')->send_notify([
                'mess_code' => 'certification_will_expire_soon',
                'id_users' => [$expireSoonUser['idu']],
                'replace' => [
                    '[SITE_LINK]'   => __SITE_URL,
                    '[DATE]'        => getDateFormat($expireSoonUser['paid_until'], 'Y-m-d', 'j M, Y'),
                ],
                'systmess' => true
            ]);
        }
    }

    // DELETE LAST VIEWED OLDER THAN ONE MONTH (UNCOMMENT WHEN WILL BE USED)
    // EACH MONTH
    // public function remove_last_viewed_items()
    // {
    //     /** @var Items_Model $items */
    //     $items = model(Items_Model::class);

    //     $items->deleteOldLastViewed();
    // }

    //EMAIL THE LAST VIEWED ITEMS TO USERS
    //EACH MONTH (SHOULD BE SENT BEFORE remove_last_viewed_items)
    public function email_last_viewed_items()
    {
        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $itemsByUsers = $itemsModel->getMonthlyLastViewedByUser();

//        /** @var Notify_Model $notify */
//        $notify = model(Notify_Model::class);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($itemsByUsers as $user) {
            $links = '<ul>';
            foreach ($user['items'] as $item) {
                $links .= "<li><a href='" . __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id_item'] . "' target='_blank'>" . $item['title'] . '</a></li>';
            }
            $links .= '</ul>';


            $mailer->send(
                (new LastViewedMonthly($user['user_name'], $links))
                    ->to(new RefAddress((string) $user['id_user'], new Address($user['email'])))
            );

        }
    }

    // NOTIFY USERS ABOUT EXPIRE SOON DOCUMENT
    // EVERY DAY
    public function tomorrow_expire_envelope()
    {
        /** @var Envelope_Recipients_Model $recipientsModel */
        $recipientsModel = model(Envelope_Recipients_Model::class);
        $recipients = $recipientsModel->findSoonToExpire();
        if (empty($recipients)) {
            return;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);
        foreach ($recipients as $recipient) {
            $mailer->send(
                (new EnvelopeExpiresSoonForSigner(
                    trim("{$recipient['signer']['fname']} {$recipient['signer']['lname']}"),
                    $recipient['order_sender']['id']
                ))->to(new RefAddress((string) $recipient['signer']['idu'], new Address($recipient['signer']['email'])))
            );

            // If sender ID is null it means that the document is internal and we have no sendere there
            // So trying to send email is meaningless
            if (null !== ($recipient['order_sender']['id_sender'] ?? null)) {
                $mailer->send(
                    (new EnvelopeExpiresSoonForSender(
                        trim("{$recipient['order_sender']['fname']} {$recipient['order_sender']['lname']}"),
                        orderNumber($recipient['order_sender']['id_order']),
                        $recipient['order_sender']['id_order'],
                        $recipient['due_date']->format(\App\Common\PUBLIC_DATE_FORMAT)
                    ))
                        ->to(new RefAddress((string) $recipient['order_sender']['id_sender'], new Address($recipient['order_sender']['email'])))
                        ->subjectContext([
                            '[orderNumber]' => orderNumber($recipient['order_sender']['id_order']),
                        ])
                );
            }
        }
    }

    // NOTIFY ADMIN THAT A DUE DATE IS IN THE PAST
    // EVERY DAY
    public function today_expired_envelope()
    {
        /** @var Envelope_Recipients_Model $recipientsModel */
        $recipientsModel = model(Envelope_Recipients_Model::class);

        $recipients = $recipientsModel->findExpired();

        if(empty($recipients)){
            return;
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        $epManagers = array_filter(array_column($userModel->get_users_by_right('monitor_documents'), 'idu'));

        if(empty($epManagers)){
            return;
        }

        foreach($recipients as $recipient) {
            $this->notifier->send(
                (new SystemNotification('recipient_due_date_expired', [
                    '[LINK]'          => \sprintf('<a href="%sorder_documents/administration?document=%s">Click here</a>', __SITE_URL, $recipient['order_sender']['id']),
                    '[USER_NAME]'     => \cleanOutput(\trim($recipient['signer']['fname'] . ' ' . $recipient['signer']['lname'])),
                    '[DOCUMENT_NAME]' => \cleanOutput($recipient['order_sender']['document_title']),
                    '[ORDER_NUMBER]'  => $recipient['order_sender']['id_order'],
                    '[DUE_DATE]'      => $recipient['due_date']->format(\App\Common\PUBLIC_DATE_FORMAT),
                ]))->channels([(string) SystemChannel::STORAGE()]),
                ...array_map(fn ($id) => new Recipient((int) $id), $epManagers)
            );
        }
    }

    // NOTIFY USERS ABOUT EXPIRE SOON ITEMS FEATURED
    // EVERY DAY
    public function items_featured_expired_soon(){
        $expireSoon = model(Items_Featured_Model::class)->get_soon_expire_items();

        foreach ($expireSoon as $expireSoonUser) {

            $messCode = 'feature_item_will_expire_soon';
            if(is_certified($expireSoonUser['user_group'])){
                $messCode = 'feature_item_certified_will_expire_soon';
            }

            model(Notify_Model::class)->send_notify([
                'mess_code' => $messCode,
                'id_users'  => [$expireSoonUser['idu']],
                'replace'   => [
                    '[SITE_LINK]'   => __SITE_URL,
                    '[DAYS]'        => $expireSoonUser['remain_days'],
                ],
                'systmess' => true,
            ]);
        }
    }

    // EVERY 20 MIN
    public function send_matchmaking_emails() {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        $usersTable = $userModel->get_users_table();

        $users = $userModel->findUsers([
            'columns' => [
                "`{$usersTable}`.`idu`",
                "`{$usersTable}`.`fname`",
                "`{$usersTable}`.`lname`",
                "`{$usersTable}`.`email`",
                "`{$usersTable}`.`user_group`",
                "`{$usersTable}`.`status`",
                "`{$usersTable}`.`registration_date`",
            ],
            'conditions' => [
                'matchmakingEmailDateLTE'       => (new \DateTime())->modify('-2 month')->format('Y-m-d'),
                'acceptMatchmakingEmail'        => 1, // users with disabled delivery of matchmaking email
                'RegistrationDateLTE'           => (new \DateTime())->modify('-7 day')->format('Y-m-d'),
                'userStatuses'                  => ['new', 'pending', 'active'],
                'userGroups'                    => [1, 2, 3, 5, 6],
                'fakeUser'                      => 0,
                'modelUser'                     => 0,
            ],
            'limit' => 10
        ]);

        if (empty($users)) {
            return;
        }

        $usersByGroup = arrayByKey($users, 'user_group', true);

        $sellersIds = array_column(
            array_merge(
                $usersByGroup[2] ?? [], //verified sellers
                $usersByGroup[3] ?? [], //certified sellers
                $usersByGroup[5] ?? [], //verified manufacturers
                $usersByGroup[6] ?? [] //certified manufacturers
            ),
            'idu'
        );

        if (!empty($sellersIds)) {
            /** @var Products_Model $productsModel */
            $productsModel = model(Products_Model::class);

            $sellersCountItems = array_column(
                $productsModel->findAllBy([
                    'columns' => [
                        "`{$productsModel->getTable()}`.`id_seller`",
                        "COUNT(`{$productsModel->getTable()}`.`id_seller`) AS countItems",
                    ],
                    'scopes' => [
                        'sellersIds' => $sellersIds,
                    ],
                    'group' => [
                        "`{$productsModel->getTable()}`.`id_seller`",
                    ],
                ]),
                'countItems',
                'id_seller'
            );
        }

        /** @var MatchmakingService $matchmakingService */
        $matchmakingService = new MatchmakingService();

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $templateCall = new GroupEmailTemplates();

        foreach ($users as $user) {
            if ('new' == $user['status'] && \DateTime::createFromFormat('Y-m-d H:i:s', $user['registration_date']) > (new \DateTime())->modify('-30 day')) {
                //Buyers/Sellers with New status registered during the last 30 days only

                $userModel->updateUserMain($user['idu'], ['matchmaking_email_date' => $now]);
                continue;
            }

            //region preparation email for buyer
            if (is_buyer((int) $user['user_group'])) {
                list($countSellers, $countItems) = $matchmakingService->counSellersItems((int) $user['idu']);

                if ($countSellers > 1 && $countItems > 1) {
                    $templateCall->sentMatchmakingEmailTemplate('buyer', $user['status'], [
                        'userId'        => $user['idu'],
                        'email'         => $user['email'],
                        'userName'      => "{$user['fname']} {$user['lname']}",
                        'countSellers'  => $countSellers,
                        'countItems'    => $countItems,
                    ]);
                }

                $userModel->updateUserMain($user['idu'], ['matchmaking_email_date' => $now]);

                continue;
            }
            //endregion preparation email for buyer

            //region preparation email for seller
            if (empty($sellersCountItems[$user['idu']])) {
                $userModel->updateUserMain($user['idu'], ['matchmaking_email_date' => $now]);

                continue;
            }

            $countBuyers = $matchmakingService->countBuyers((int) $user['idu']);

            if ($countBuyers > 1) {
                $templateCall->sentMatchmakingEmailTemplate('seller', $user['status'], [
                    'userId'        => $user['idu'],
                    'email'         => $user['email'],
                    'userName'      => "{$user['fname']} {$user['lname']}",
                    'countBuyers'   => $countBuyers,
                ]);
            }

            $userModel->updateUserMain($user['idu'], ['matchmaking_email_date' => $now]);
            //endregion preparation email for seller
        }
    }

    //CREATES REQUESTS IF DRAFT ITEMS WERE CREATED THAT DAY
    //EVERY DAY AT 11:59
    public function check_draft_items_added_today()
    {
        /** @var Draft_Extend_Requests_Model $extend */
        $extend = model(Draft_Extend_Requests_Model::class);

        /** @var Items_Model $items */
        $items = model(Items_Model::class);

        $expireDays = config('draft_items_days_expire', 10);
        $requests = $items->getNewDrafts($expireDays);

        if(!empty($requests)){

            foreach($requests as $request)
            {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $request['draft_expire_date']);

                $exists = $extend->findOneBy(['conditions' => [
                    'id_user'         => (int) $request['id_seller'],
                    'expiration_date' => $date,
                ]]);

                if(empty($exists))
                {
                    $idRequest = $extend->insertOne([
                        'id_user'         => (int) $request['id_seller'],
                        'items'           => $request['items'],
                        'expiration_date' => $date
                    ]);
                }else{
                    $idRequest = $exists['id'];
                }

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new DraftItemExpirationFirstEmail("{$request['fname']} {$request['lname']}", $request['counter'], $expireDays, $date->format('Y-m-d'), $idRequest))
                        ->to(new RefAddress((string) $request['id_seller'], new Address($request['email'])))
                );
            }
        }
    }

    //INCENTIVE EMAIL AND NOTIFICATION ABOUT DRAFT ITEMS TO SEND FEATURED OFFER
    //EVERY DAY
    public function draft_items_incentive_email()
    {
        $today = new DateTimeImmutable();

        /** @var Draft_Extend_Requests_Model $extend */
        $extend = model(Draft_Extend_Requests_Model::class);

        /** @var Notify_Model $notifications */
        $notifications = model(Notify_Model::class);

        $middleDate = $today->add(new DateInterval('P' . config('draft_items_day_number_to_make_featured', 5) . 'D'));

        $requests = $extend->getRequestsWithItemsCount([
            'expirationDate' => $middleDate
        ]);

        if(!empty($requests))
        {
            foreach($requests as $request)
            {
                //$count = count(explode(',', $request['items']));
                if(0 != (int) $request['count_new'])
                {

                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new DraftItemIncentiveOfferEmail("{$request['user']['fname']} {$request['user']['fname']}", $request['count_new'], config('draft_items_day_number_to_make_featured', 5), $middleDate->format('Y-m-d'), $request['id']))
                            ->to(new RefAddress((string) $request['id_user'], new Address($request['user']['email'])))
                    );

                    $dataSystmess = [
                        'mess_code' => 'item_draft_type_offer_incentive',
                        'id_users'  => [$request['id_user']],
                        'replace'   => [
                            '[NUMBER]'       => $request['count_new'],
                            '[DRAFTS_LINK]'  => __SITE_URL . 'items/my?expire=' . $middleDate->format('Y-m-d'),
                            '[REQUEST_LINK]' => __SITE_URL . 'items/my?request=' . $request['id']
                        ],
                        'systmess' => true,
                    ];

                    $notifications->send_notify($dataSystmess);
                }
            }
        }
    }

    //EMAIL AND NOTIFICATION ABOUT DRAFT ITEMS TO BE DELETED TOMORROW
    //EVERY DAY
    public function draft_items_warning_delete_tomorrow()
    {
        $today = new DateTimeImmutable();

        /** @var Draft_Extend_Requests_Model $extend */
        $extend = model(Draft_Extend_Requests_Model::class);

        /** @var Notify_Model $notifications */
        $notifications = model(Notify_Model::class);

        $tomorrow = $today->add(new DateInterval('P1D'));
        $requests = $extend->getRequestsWithItemsCount([
            'expirationDate' => $tomorrow
        ]);

        if(!empty($requests))
        {
            foreach($requests as $request)
            {
                if(0 != (int) $request['count_new'])
                {

                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new DraftItemWarningDeleteEmail("{$request['user']['fname']} {$request['user']['fname']}", $request['count_new'], $tomorrow->format('Y-m-d'), $request['id']))
                            ->to(new RefAddress((string) $request['id_user'], new Address($request['user']['email'])))
                            ->subjectContext([
                                '[counter]' => $request['count_new'],
                            ])
                    );

                    $dataSystmess = [
                        'mess_code' => 'item_draft_removal_warning',
                        'id_users'  => [$request['id_user']],
                        'replace'   => [
                            '[NUMBER]'       => $request['count_new'],
                            '[DRAFTS_LINK]'  => __SITE_URL . 'items/my?expire=' . $tomorrow->format('Y-m-d'),
                            '[REQUEST_LINK]' => __SITE_URL . 'items/my?request=' . $request['id']
                        ],
                        'systmess' => true,
                    ];

                    $notifications->send_notify($dataSystmess);
                }
            }
        }
    }

    //DELETING DRAFT ITEMS AND NOTIFICATION
    //EVERY DAY
    public function draft_items_delete()
    {
        /** @var Draft_Extend_Requests_Model $extend */
        $extend = model(Draft_Extend_Requests_Model::class);

        /** @var Notify_Model $notifications */
        $notifications = model(Notify_Model::class);

        $requests = $extend->getRequestsWithItemsCount([
            'expirationDate' => new DateTimeImmutable()
        ]);

        if(!empty($requests))
        {
            foreach($requests as $request)
            {
                if(0 != $request['count_new'])
                {
                    $dataSystmess = [
                        'mess_code' => 'item_draft_removed_already',
                        'id_users'  => [$request['id_user']],
                        'replace'   => [
                            '[NUMBER]'         => $request['count_new'],
                            '[ADD_ITEMS_LINK]' => __SITE_URL . 'items/my?popup_add=open'
                        ],
                        'systmess'  => true,
                    ];

                    $notifications->send_notify($dataSystmess);
                }
            }
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $itemsModel->deleteExpiredDraftItems();
    }

    public function get_certification_documents_notification()
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        if (empty($users = $usersModel->getUsersToNotifyAboutCertificationDocuments())) {
            return;
        }

        /** @var Notify_Model $notifyModel */
        $notifyModel = model(Notify_Model::class);

        foreach ($users as $user) {
            $dataSystmess = [
                'mess_code' => 'get_certification_documents',
                'id_users'  => [$user['idu']],
                'replace'   => [
                    '[LINK]' => __SITE_URL . 'promo_materials',
                ],
                'systmess' => true,
            ];

            $notifyModel->send_notify($dataSystmess);
        }
    }

    //FIRST REMINDER TO ACTIVATE ACCOUNT
    //EVERY DAY
    public function first_reminder_to_activate_account()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $users = $usersModel->findAllBy([
            'columns'       => [
                'idu',
                'fname',
                'lname',
                'email',
            ],
            'conditions'    => [
                'registrationDate'  => (new \DateTime())->sub(new \DateInterval('P45D')),
                'statuses'          => [
                    UserStatus::FRESH(),
                    UserStatus::PENDING(),
                ],
            ],
        ]);

        if (empty($users)) {
            $this->cron_params['messages'][] = 'No users in need of a first reminder to activate account have been detected';

            return;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

//        /** @var Notify_Model $notifyModel */
//        $notifyModel = model(Notify_Model::class);

        foreach ($users as $user) {
            $mailer->send(
                (new StayActiveOnEp("{$user['fname']} {$user['lname']}"))
                    ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
            );
        }

        $this->cron_params['messages'][] = count($users) . ' users received the first reminder to activate their account';
    }

    //SECOND REMINDER TO ACTIVATE ACCOUNT
    //EVERY DAY
    public function second_reminder_to_activate_account()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $users = $usersModel->findAllBy([
            'columns'       => [
                'idu',
                'fname',
                'lname',
                'email',
            ],
            'conditions'    => [
                'registrationDate'  => (new \DateTime())->sub(new \DateInterval('P89D')),
                'statuses'          => [
                    UserStatus::FRESH(),
                    UserStatus::PENDING(),
                ],
            ],
        ]);

        if (empty($users)) {
            $this->cron_params['messages'][] = 'No users in need of a second reminder to activate account have been detected';

            return;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($users as $user) {
            $mailer->send(
                (new CheckYourAccountOnEp("{$user['fname']} {$user['lname']}"))
                    ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
            );
        }

        $this->cron_params['messages'][] = count($users) . ' users received the second reminder to activate their account';
    }

    //USER RESTRICTION AFTER 90 DAYS OF INACTIVITY
    //EVERY DAY
    public function restrict_users()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $registrationDate = (new \DateTime())->sub(new \DateInterval('P90D'));

        $users = $usersModel->findAllBy([
            'columns'       => [
                'idu',
                'fname',
                'lname',
                'email',
                'status',
                'notice',
            ],
            'conditions'    => [
                'wasNotRestrictedAfterDate' => $registrationDate,
                'registrationDate'          => $registrationDate,
                'statuses'                  => [
                    UserStatus::FRESH(),
                    UserStatus::PENDING(),
                ],
            ],
            'joins' => [
                'restrictionUsersStatistics'
            ],
            'group'                         => [
                "{$usersModel->getTable()}.`idu`"
            ],
        ]);

        if (empty($users)) {
            $this->cron_params['messages'][] = 'No users were found to be restricted.';

            return;
        }

        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);

        /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
        $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($users as $user) {
            $blockingModel->block_user_content($user['idu']);

            $usersModel->updateOne(
                $user['idu'],
                [
                    'status_temp'   => $user['status'],
                    'status'        => UserStatus::RESTRICTED(),
                    'notice' => array_merge(
                        [
                            [
                                'add_date'  => (new \DateTime())->format('Y/m/d H:i:s'),
                                'add_by'    => 'EP automation',
                                'notice'    => 'The user has been Restricted. Status before restriction: ' . ucfirst($user['status']->value) . '. 90 days of inactivity.'
                            ],
                        ],
                        $user['notice'] ?: []
                    ),
                ],
            );

            $usersBlockingStatisticsModel->insertOne([
                'id_user'   => $user['idu'],
                'type'      => RestrictionType::RESTRICTION(),
            ]);

            $mailer->send(
                (new AccountRestriction("{$user['fname']} {$user['lname']}"))
                    ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
            );

            $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasRestrictedEvent((int) $user['idu']));
        }

        $this->cron_params['messages'][] = count($users) . ' users were restricted.';
    }

    //USER BLOCKING AFTER 30 DAYS OF RESTRICTION OR CANCEL RESTRICTION
    //EVERY DAY
    public function block_users()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $users = $usersModel->getUsersForBlockingAccount();

        if (empty($users)) {
            $this->cron_params['messages'][] = 'No users were found to be blocked.';

            return;
        }

        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);

        /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
        $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($users as $user) {
            $blockingModel->block_user_content($user['idu']);

            $usersModel->updateOne(
                $user['idu'],
                [
                    'status_temp'   => $user['status_temp'] ?: $user['status'],
                    'status'        => UserStatus::BLOCKED(),
                    'notice' => array_merge(
                        [
                            [
                                'add_date'  => (new \DateTime())->format('Y/m/d H:i:s'),
                                'add_by'    => 'EP automation',
                                'notice'    => 'The user has been blocked. Status before blocking:: ' . ucfirst($user['status']->value) . '. 120 days of inactivity.'
                            ],
                        ],
                        $user['notice'] ?: []
                    ),
                ],
            );

            $usersBlockingStatisticsModel->insertOne([
                'id_user'   => $user['idu'],
                'type'      => RestrictionType::BLOCKING(),
            ]);

            $mailer->send(
                (new AccountIsNowBlocked(ucwords($user['fname'] . ' ' . $user['lname'])))
                    ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
            );

            $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasBlockedEvent((int) $user['idu']));
        }

        $this->cron_params['messages'][] = count($users) . ' users were blocked.';
    }

    //REMIND ALL USERS ABOUT THE WEBINAR 7 DAYS BEFORE STARTING
    //EVERY DAY
    public function one_week_before_demo_webinar_email()
    {
        /** @var Webinar_Model $webinarModel */
        $webinarModel = model(Webinar_Model::class);

        $webinar = $webinarModel->findOneBy([
            'conditions' => [
                'startDate' => (new DateTime())->add(new DateInterval('P7D'))->format('Y-m-d'),
            ],
            'with'       => [
                'requests'
            ],
        ]);

        if (empty($webinar)) {
            return;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($webinar['requests'] as $request)
        {
            $mailer->send(
                (new DemoWebinarEpComingSoon("{$request['fname']} {$request['lname']}", $webinar['start_date']->format('Y-m-d'), $webinar['start_date']->format('H:i:s')))
                    ->to(new Address($request['email']))
            );
        }
    }

    //REMIND ALL USERS ABOUT THE WEBINAR 1 DAY BEFORE STARTING
    //EVERY DAY
    public function one_day_before_demo_webinar_email()
    {
        /** @var Webinar_Model $webinarModel */
        $webinarModel = model(Webinar_Model::class);

        $webinar = $webinarModel->findOneBy([
            'conditions' => [
                'startDate' => (new DateTime())->add(new DateInterval('P1D'))->format('Y-m-d'),
            ],
            'with'       => [
                'requests'
            ],
        ]);

        if (empty($webinar)) {
            return;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($webinar['requests'] as $request)
        {
            $mailer->send(
                (new DemoWebinarEpTomorrow("{$request['fname']} {$request['lname']}", $webinar['start_date']->format('Y-m-d'), $webinar['start_date']->format('H:i:s'), $webinar['link']))
                    ->to(new Address($request['email']))
            );
        }
    }

    //2 WEEKS AFTER THE WEBINAR
    //EVERY DAY
    public function two_weeks_after_demo_webinar()
    {
        /** @var Webinar_Model $webinarModel */
        $webinarModel = model(Webinar_Model::class);

        $webinar = $webinarModel->findOneBy([
            'conditions' => [
                'startDate'  => (new DateTime())->sub(new DateInterval('P2W'))->format('Y-m-d')
            ],
            'with'       => [
                'requests',
                'requestsLeadsRegistered'
            ],
        ]);

        if (empty($webinar) || empty($webinar['requests'])) {
            return;
        }

        $notRegistered = [];
        if (!empty($webinar['requests_leads_registered'])) {
            $notRegistered = array_column($webinar['requests_leads_registered']->toArray(), 'id');
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($webinar['requests'] as $request)
        {
            if(in_array((int) $request['id'], $notRegistered))
            {
                $mailer->send(
                    (new DemoWebinarEp2WeeksAfter("{$request['fname']} {$request['lname']}", $webinar['start_date']->format('Y-m-d')))
                        ->to(new Address($request['email']))
                );
            }else{
                $emailTemplate = '';

                switch($request['user_type']){
                    case 'Buyer':
                        $mailer->send(
                            (new DemoWebinarEpThanksForParticipatedBuyers("{$request['fname']} {$request['lname']}"))
                                ->to(new Address($request['email']))
                        );
                        break;
                    case 'Seller':
                    case 'Manufacturer':
                    $mailer->send(
                        (new DemoWebinarEpThanksForParticipatedSellers("{$request['fname']} {$request['lname']}"))
                            ->to(new Address($request['email']))
                    );
                        break;
                }

                if(empty($emailTemplate)){
                    continue;
                }
            }
        }
    }

    // Message for number of user views
    // EVERY 5 MIN
    public function notify_sellers_about_items_views()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $now = (new \DateTimeImmutable());

        /** @var Users_Model $userModel */
        $userModel = model(Users_Model::class);
        $usersTable = $userModel->getTable();

        // Get information about users who need to be notified about the number of product views
        $users = $userModel->findAllBy([
            'columns'   => [
                "`{$usersTable}`.`idu`",
                "`{$usersTable}`.`fname`",
                "`{$usersTable}`.`lname`",
                "`{$usersTable}`.`email`",
            ],
            'scopes'    => [
                'activationAccountDateTimeLte' => $now->sub(new \DateInterval("P7D")),
                'itemsViewsEmailDateTimeLte'   => $now,
                'groups'                       => [2, 3, 5, 6],
                'status'                       => UserStatus::ACTIVE(),
            ],
            'limit'     => 50,
        ]);

        if (empty($users)) {
            return;
        }

        $usersIds = array_column($users, 'idu');

        /** @var Items_Views_Notifications_Model $itemsViewsNotificationsModel */
        $itemsViewsNotificationsModel = model(Items_Views_Notifications_Model::class);

        // Get information about the last notification to users in order to know about the number of past views
        $usersNotifications = array_column(
            $itemsViewsNotificationsModel->getLastUsersNotifications($usersIds),
            null,
            'user_id'
        );

        /** @var Items_Views_Model $itemsViewsModel */
        $itemsViewsModel = model(Items_Views_Model::class);
        $itemsTable = $itemsViewsModel->getRelation('item')->getRelated()->getTable();

        // Get information about the number of product views for the last week
        $itemsViewsCounters = arrayByKey(
            $itemsViewsModel->findAllBy([
                'columns'   => [
                    "`{$itemsTable}`.`id`",
                    "`{$itemsTable}`.`id_seller`",
                    "`{$itemsTable}`.`title`",
                    "COUNT(`{$itemsTable}`.`id_seller`) as counter"
                ],
                'joins'     => ['items'],
                'scopes'    => [
                    'sellersIds'            => $usersIds,
                    'viewedDateTimeGte'     => $now->sub(new \DateInterval("P7D")),
                ],
                'group'     => [
                    "`{$itemsTable}`.`id`"
                ],
                'order'     => [
                    'counter' => 'DESC'
                ],
            ]),
            'id_seller',
            true
        );

        $notifiedUsersData = [];
        foreach ($users as $user) {
            $minNecessaryViews = $usersNotifications[$user['idu']]['views_count'] ?: 5;
            $sellerCountViews = (int) array_sum(array_column($itemsViewsCounters[$user['idu']] ?: [], 'counter'));

            if ($sellerCountViews < $minNecessaryViews) {
                // If the user has fewer views than necessary, then we mark him for viewing the next day
                $userModel->updateOne($user['idu'], ['check_items_views_email_date' => $now->add(new \DateInterval("P1D"))]);

                continue;
            }

            // Sending a notification to the user
            $this->notifier->send(
                new SystemNotification('cron_count_items_views', ['[COUNT_VIEWS]' => $sellerCountViews]),
                new Recipient((int) $user['idu'])
            );

            // Prepare data for sending an email to the user
            // Get top 3 must viewed items
            $mustViewedItems = array_slice($itemsViewsCounters[$user['idu']], 0, 3);

            try {
                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new SellerItemsViews("{$user['fname']} {$user['lname']}", $sellerCountViews, $mustViewedItems))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                );
            } catch (\Throwable $th) {
                // here should be an error reporting functionality
                continue;
            }

            // We save the current views counter in order to use it in comparison when sending the next notification
            $notifiedUsersData[] = [
                'user_id'       => (int) $user['idu'],
                'views_count'   => (int) $sellerCountViews,
            ];

            // If the user has received a notification this time, the next one should be at least 7 days later
            $userModel->updateOne($user['idu'], ['check_items_views_email_date' => $now->add(new \DateInterval("P7D"))]);
        }

        if (!empty($notifiedUsersData)) {
            // Insert log about users notifications
            $itemsViewsNotificationsModel->insertMany($notifiedUsersData);
        }
    }

    // Notifications about event is around the corner
    // EVERY 5 MIN
    public function calendar_events_notifications()
    {
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        /** @var Calendar_Notifications_Model $calendarNotificationsModel */
        $calendarNotificationsModel = model(Calendar_Notifications_Model::class);

        $notifications = $calendarNotificationsModel->findAllBy([
            'with'   => ['calendar'],
            'scopes' => [
                'notificationDateLte' => new DateTimeImmutable,
                'isSent'              => false,
            ],
            'limit'  => 20,
        ]);

        if (empty($notifications)) {
            return;
        }

        //mark this notifications as sent to exclude them from subsequent cron calls
        $calendarNotificationsModel->updateMany(
            ['is_sent' => true],
            [
                'scopes' => [
                    'ids' => array_column($notifications, 'id'),
                ]
            ]
        );

        $notificationsByType = $notifiedUsersIds = [];
        foreach ($notifications as $notification) {
            //we collect information about users who will need to be notified
            $notifiedUsersIds[$notification['calendar']['user_id']] = $notification['calendar']['user_id'];
            //in parallel with this, we group notifications by their type, for subsequent processing of each type separately
            $notificationsByType[(string) $notification['notification_type']][] = $notification;
        }

        //region get the users data
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $users = array_column(
            $usersModel->findAllBy([
                'columns' => [
                    'idu',
                    'fname',
                    'lname',
                    'email',
                ],
                'scopes'  => [
                    'ids'    => $notifiedUsersIds,
                    'status' => UserStatus::ACTIVE(),
                ],
            ]),
            null,
            'idu'
        );
        //endregion get the users data

        //region send emails
        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach ($notificationsByType[NotificationType::EMAIL] ?? [] as $notification) {
            //notifications are sent only to activated users
            if (!isset($users[$notification['calendar']['user_id']])) {
                continue;
            }

            $user = $users[$notification['calendar']['user_id']];

            //in the future, there may be separate email templates for different types of calendar events
            switch ($notification['calendar']['event_type']) {
                case EventType::EP_EVENTS():
                    $eventUrl = getEpEventDetailUrl([
                        'id'    => $notification['calendar']['source_id'],
                        'title' => $notification['calendar']['title']
                    ]);

                    try {
                        $mailer->send(
                            (new CalendarEpEventReminder(
                                cleanOutput("{$user['fname']} {$user['lname']}"),
                                sprintf('<a href="%s">%s</a>', $eventUrl, cleanOutput($notification['calendar']['title'])),
                                $notification['count_days'])
                            )->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                        );
                    } catch (\Throwable $th) {
                        $calendarNotificationsModel->updateOne($notification['id'], ['sending_erros' => $th->getMessage()]);
                    }

                    break;
            }
        }
        //endregion send emails

        //region send system notifications
        foreach ($notificationsByType[NotificationType::SYSTEM] ?? [] as $notification) {
            //notifications are sent only to activated users
            if (!isset($users[$notification['calendar']['user_id']])) {
                continue;
            }

            $user = $users[$notification['calendar']['user_id']];
            switch ($notification['calendar']['event_type']) {
                case EventType::EP_EVENTS():
                    $eventUrl = getEpEventDetailUrl([
                        'id'    => $notification['calendar']['source_id'],
                        'title' => $notification['calendar']['title']
                    ]);

                    $this->notifier->send(
                        new SystemNotification(
                            'calendar_ep_event_reminder',
                            [
                                '[eventName]'     => sprintf('<a href="%s" target="_blank">%s</a>', $eventUrl, cleanOutput($notification['calendar']['title'])),
                                '[remainingDays]' => $notification['count_days'],
                            ]
                        ),
                        new Recipient((int) $user['idu'])
                    );

                    break;
            }
        }
        //endregion send system notifications
    }
}
