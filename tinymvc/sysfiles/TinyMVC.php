<?php

use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\DependencyInjection\StaticContainer;
use App\Common\File\File;
use App\Common\Http\Exceptions\BadRequestHttpException;
use App\Common\Http\Exceptions\NotFoundHttpException;
use App\Common\Http\Request;
use App\Services\BlogCategoryRouteResolverService;
use Psr\Link\LinkProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\WebLink\HttpHeaderSerializer;

/*
* Name:       TinyMVC
* About:      An MVC application framework for PHP
* Copyright:  (C) 2007-2009 Monte Ohrt, All rights reserved.
* Author:     Monte Ohrt, monte [at] ohrt [dot] com
* License:    LGPL, see included license file
*/

if (!defined('TMVC_VERSION')) {
    define('TMVC_VERSION', '1.2.1');
}

// directory separator alias
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// define myapp directory
if (!defined('TMVC_MYAPPDIR')) {
    define('TMVC_MYAPPDIR', TMVC_BASEDIR . 'myapp' . DS);
}

// set include_path for spl_autoload
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . TMVC_BASEDIR . 'sysfiles' . DS . 'plugins' . DS
    . PATH_SEPARATOR . TMVC_BASEDIR . 'myfiles' . DS . 'plugins' . DS
    . PATH_SEPARATOR . TMVC_MYAPPDIR . 'models' . DS
    . PATH_SEPARATOR . TMVC_MYAPPDIR . 'plugins' . DS
);

// set .php first for speed
spl_autoload_extensions('.php,.inc');

$spl_funcs = spl_autoload_functions();
if (false === $spl_funcs) {
    spl_autoload_register();
} elseif (!in_array('spl_autoload', $spl_funcs)) {
    spl_autoload_register('spl_autoload');
}

/**
 * tmvc.
 *
 * main object class
 *
 * @author		Monte Ohrt
 */
class tmvc
{
    // config file values
    public $config;
    public $is_acces_by_pesonalized_link;
    // controller object
    public $controller;
    // controller method name
    public $action;
    // server path_info
    public $path_info;
    public $route_path_info;
    // array of url path_info segments
    public $url_segments;
    public $route_url_segments = [];
    public $my_config;
    public $lang;
    public $default_lang;
    public $current_lang_detail = [];
    public $site_urls;
    public $routes_priority = [];
    public $translationsKeysUsageLog = [];

    /**
     * The flag that indicates if kernel is already booted.
     */
    protected $booted = false;

    /**
     * The current environment.
     */
    protected string $environment;

    /**
     * The flag that indicates debug mode status - true if debug mode is enabled, false otherwise.
     */
    protected bool $debug;

    /**
     * The incomming request.
     *
     * @var Request
     */
    private $request;

    /**
     * The request stack size.
     *
     * @var int
     */
    private $requestStackSize = 0;

    /**
     * The kernel container.
     */
    private ContainerInterface $container;

    /**
     * class constructor.
     *
     * @param mixed $id
     */
    public function __construct($id = 'default')
    {
        // set instance
        self::instance($this, $id);

        $this->debug = (bool) $_SERVER['APP_DEBUG'];
        $this->environment = $_SERVER['APP_ENV'];
    }

    public function __destruct()
    {
        if (!function_exists('config') && !function_exists('getControllerActionFromUrl')) {
            return;
        }

        if (config('env.ENABLE_TRANSLATIONS_USAGE_LOG') && !empty($this->translationsKeysUsageLog)) {
            $filePath = dirname(dirname(__FILE__)) . DS . 'myapp' . DS . 'configs' . DS . 'translations' . DS . 'translations_keys_usage_log.php';

            if (isset($this->translationsKeysUsageLog['byAjax'])) {
                $requestedKeys = $this->translationsKeysUsageLog['byAjax'];
                unset($this->translationsKeysUsageLog['byAjax']);

                foreach ($requestedKeys as $fromUrl => $keys) {
                    $pathDetails = getControllerActionFromUrl($fromUrl);

                    if (is_null($pathDetails)) {
                        continue;
                    }

                    list('controller' => $controllerName, 'action' => $actionName) = $pathDetails;

                    foreach ($keys as $translationKey => $nothing) {
                        $this->translationsKeysUsageLog[$controllerName][$actionName][$translationKey] = '';
                    }
                }
            }

            $alreadyLoggedKeys = file_exists($filePath) ? include $filePath : [];
            $finallyLog = array_replace_recursive($alreadyLoggedKeys, $this->translationsKeysUsageLog);

            $logHandle = fopen($filePath, 'w');
            fwrite($logHandle, "<?php \r\nreturn " . var_export($finallyLog, true) . ';');
            fclose($logHandle);
        }
    }

    /**
     * getCharset.
     */
    public function getCharset(): string
    {
        return 'UTF-8';
    }

    /**
     * Gets the environment.
     *
     * @return string The current environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Gets the current container.
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            throw new \LogicException('Cannot retrieve the container from a non-booted kernel.');
        }

        return $this->container;
    }

    /**
     * Handle all request stack.
     */
    public function handle(Request $request, int $type = 1, bool $catch = true): Response
    {
        $this->request = $request;
        $this->boot();
        ++$this->requestStackSize;

        try {
            return $this->handleRequest($request, $type, $catch);
        } finally {
            --$this->requestStackSize;
        }
    }

    /**
     * Boot the kernel.
     */
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        // set initial timer
        self::timer('tmvc_app_start');

        // internal error handling
        $this->setupErrorHandling();

        // define constants
        $this->setupConstants();

        // Setup configs
        $this->setupConfig();

        // Setup route path
        $this->setupRoutePath();

        $this->initializeContainer();

        // Setup domain paths
        $this->setupSubDomainPath();

        // url remapping/routing
        $this->setupRouting();

        // split path_info into array
        $this->setupSegments();

        // create controller object
        $this->setupController();

        if (null != $this->controller) {
            // get controller method
            $this->setupAction();

            // run library/script autoloaders
            $this->setupAutoloaders();

            $this->setupDeviceView();

            // set site display language dictionaries
            $this->setupLanguage();
        }

        $this->booted = true;
    }

    /**
     * Returns the incomming request.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the request stack.
     */
    public function getRequestStack(): RequestStack
    {
        return $this->container->get('request_stack');
    }

    /**
     * setup display device view.
     */
    public function setupDeviceView()
    {
        $this->controller->is_phone = false;
        $this->controller->is_tablet = false;
        $this->controller->view_folder = '';
        $this->controller->is_pc = true;
    }

    /**
     * setup display language.
     */
    public function setupLanguage()
    {
        $use_google_translate = (bool) (int) config('env.USE_ONLY_GOOGLE_TRANSLATE');
        $site_url = __SITE_URL;
        $site_lang = $default_site_lang = 'en';
        $is_default_lang = true;
        $isset_get_lang = false;
        $db_languages = arrayByKey(model('translations')->get_languages(['lang_active' => 1]), 'lang_iso2');

        if (isset($_GET['lang'], $db_languages[$_GET['lang']])) {
            $get_lang = $_GET['lang'];
            $isset_get_lang = true;
        } else {
            unset($_GET['lang']);
        }

        if (!isset($_COOKIE['_ulang']) || ($isset_get_lang && $_COOKIE['_ulang'] !== $get_lang)) {
            $_COOKIE['_ulang'] = $cookie_lang = $isset_get_lang ? $get_lang : $site_lang;
            $this->controller->cookies->setCookieParam('_ulang', $cookie_lang);
        }

        $lang_to_translate = null;
        $is_translated_page = false;

        $cookie_lang = $_COOKIE['_ulang'];
        $this->current_lang_detail = model('translations')->get_language_by_iso2($cookie_lang);

        if ($isset_get_lang || $cookie_lang !== $default_site_lang) {
            $is_default_lang = false;
            $is_translated_page = false;
            $lang_to_translate = $cookie_lang;

            $db_language_column = 'lang_' . $lang_to_translate;

            $page_detail = model('translations')->get_page_data($this->controller->name, $this->action);

            if (!$use_google_translate && isset($page_detail[$db_language_column]) && 1 == $page_detail[$db_language_column]) {
                $is_translated_page = true;
            }
        }

        $valid_languages = [];
        $langs_specification = [];
        $langs_url_type = [];

        foreach ($db_languages as $iso2 => $language) {
            if ('domain' !== $language['lang_url_type'] && !$is_translated_page) {
                continue;
            }

            $valid_languages[$iso2] = $language['lang_name'];
            $langs_specification[$iso2] = $language['lang_spec'];
            $langs_url_type[$iso2] = $language['lang_url_type'];
        }

        if ('blog' === $this->controller->name) {
            $site_url = __BLOG_URL;
        } elseif ('community' === $this->controller->name) {
            $site_url = __COMMUNITY_URL;
        } elseif (in_array(__CURRENT_SUB_DOMAIN, $this->config['cr_available']) || __CURRENT_SUB_DOMAIN === config('env.SHIPPER_SUBDOMAIN')) {
            $site_url = __CURRENT_SUB_DOMAIN_URL;
        }

        if (array_key_exists(__CURRENT_SUB_DOMAIN, $valid_languages)) {
            $site_lang = __CURRENT_SUB_DOMAIN;
        } elseif (
            (
                (
                    isset($langs_url_type[$lang_to_translate]) && 'domain' === $langs_url_type[$lang_to_translate]
                )
                || $is_translated_page
            )
            && array_key_exists($lang_to_translate, $valid_languages)
        ) {
            $site_lang = $lang_to_translate;
        }
        $this->default_site_urls = require TMVC_MYAPPDIR . 'configs/translations/urls' . DS . 'lang_en.php';

        $file_name = TMVC_MYAPPDIR . 'configs/translations/urls' . DS . 'lang_' . $site_lang . '.php';
        if (('domain' === $langs_url_type[$site_lang] || $is_translated_page) && file_exists($file_name)) {
            $this->site_urls = require $file_name;
        } else {
            $this->site_urls = $this->default_site_urls;
        }

        if (!empty($this->request) && !$this->request->isAjaxRequest()) {
            $uri_get = '';
            $data_get = $_GET;

            if ($is_default_lang) {
                unset($data_get['lang']);
            } else {
                $data_get['lang'] = $lang_to_translate;
            }

            if (!empty($data_get)) {
                $uri_get = '?' . http_build_query($data_get);
            }

            $lang_uri = $current_uri = implode('/', $this->route_url_segments);
            $route_key = $this->controller->name . '/' . $this->action;
            $route = $this->controller->translations->get_routing_by_key($route_key);
            if (!$is_default_lang && 'domain' !== $langs_url_type[$site_lang] && !$is_translated_page && !$isset_get_lang) {
                headerRedirect($site_url . $lang_uri . $uri_get);
            }

            if (!empty($route) && !empty($route['lang_' . $site_lang])) {
                $lang_uri = [];
                $lang_route_config = json_decode($route['lang_' . $site_lang], true);
                if (!empty($lang_route_config['replace_uri_components'])) {
                    switch ($this->controller->name) {
                        case 'category':
                            $category_url_segments = $this->route_url_segments;

                            if (empty($category_url_segments[3])) {
                                break;
                            }

                            $category_url_segments[2] .= '/' . $category_url_segments[3];
                            unset($category_url_segments[3]);
                            $this->route_url_segments = array_values($category_url_segments);
                            $uri = array_filter($this->controller->uri->uri_to_assoc(1, $this->route_url_segments));

                        break;

                        case 'questions':
                            $questions_route_segments = array_merge(['questions', '{_.index._}'], array_slice($this->route_url_segments, 1));
                            $uri = array_filter($this->controller->uri->uri_to_assoc(1, $questions_route_segments));

                        break;

                        default:
                            $uri = $this->controller->uri->uri_to_assoc(2, $this->route_url_segments);

                        break;
                    }

                    $current_lang_route_config = $this->site_urls[$route_key];
                    foreach ($uri as $uri_key => $uri_value) {
                        $replace_key = (isset($current_lang_route_config['flipped_uri_components'][$uri_key])) ? $current_lang_route_config['flipped_uri_components'][$uri_key] : $uri_key;
                        $replace_key = (isset($current_lang_route_config['replace_uri_components'][$replace_key])) ? $current_lang_route_config['replace_uri_components'][$replace_key] : $replace_key;
                        if (!empty($replace_key)) {
                            $lang_uri[$replace_key . '_key'] = $replace_key;
                        }

                        $replace_key_value = (isset($current_lang_route_config['replace_uri_components'][$uri_value])) ? $current_lang_route_config['replace_uri_components'][$uri_value] : $uri_value;
                        if (!empty($replace_key_value) && '{_.index._}' != $replace_key_value) {
                            $lang_uri[$replace_key . '_value'] = $replace_key_value;
                        }
                    }
                } else {
                    if (!empty($lang_route_config['route_segments'])) {
                        $lang_uri = $lang_route_config['route_segments'];
                        if (!empty($lang_route_config['replace_route_segments'])) {
                            foreach ($lang_route_config['replace_route_segments'] as $replace_route_segment) {
                                $lang_uri[$replace_route_segment] = $this->route_url_segments[$replace_route_segment];
                            }
                        }
                    } else {
                        $default_lang_route_config = json_decode($route['lang_en'], true);
                        $lang_uri = $default_lang_route_config['route_segments'];
                        if (!empty($default_lang_route_config['replace_route_segments'])) {
                            foreach ($default_lang_route_config['replace_route_segments'] as $replace_route_segment) {
                                $lang_uri[$replace_route_segment] = $tmvc->route_url_segments[$replace_route_segment];
                            }
                        }
                    }
                }

                $lang_uri = implode('/', array_filter($lang_uri));
            }

            if ((!empty($lang_uri) && $lang_uri != $current_uri || !empty($uri_get) && $uri_get != '?' . $_SERVER['QUERY_STRING'])) {
                headerRedirect($site_url . $lang_uri . $uri_get);
            }
        }

        $lang_folder = 'languages/' . $site_lang;
        foreach (glob($lang_folder . '/*_lang.php') as $lang_file) {
            require $lang_file;
        }
        $this->lang = $lang;

        if ('en' != $lang) {
            $lang_folder = 'languages/en';
            foreach (glob($lang_folder . '/*_lang.php') as $lang_file) {
                require $lang_file;
            }
        }
        $this->default_lang = $site_lang;
        $this->active_languages = $valid_languages;
        define('__SITE_LANG', $site_lang);
        define('__SITE_LANG_SPEC', $langs_specification[$site_lang]);
    }

    /**
     * setup constants from config.
     */
    public function setupConstants()
    {
        $fileLoader = \Closure::bind(function ($path) { return include $path; }, $this, static::class);
        $constantFiles = array_merge(
            glob(TMVC_BASEDIR . 'myapp/constants/*.php'),
            glob(TMVC_BASEDIR . sprintf('myapp/constants/%s/*.php', $_ENV['APP_ENV'] ?? 'dev'))
        );
        foreach ($constantFiles as $file) {
            $callback = $fileLoader($file) ?? null;
            if ($callback instanceof \Closure) {
                $callback();
            }
        }
    }

    /**
     * Setup the app configurations.
     */
    public function setupConfig()
    {
        $fileLoader = \Closure::bind(function ($path) {
            $result = null;
            $config = [];
            if (\file_exists($path)) {
                $result = include $path;
            }

            return is_array($result) ? $result : $config;
        }, $this, static::class);
        $configMapping = [
            'cached'       => realpath(\App\Common\CACHE_PATH) . '/' .  $this->getEnvironment() . '/customConfigs.php',
            'application'  => TMVC_MYAPPDIR . 'configs/application.php',
            'database'     => TMVC_MYAPPDIR . 'configs/database.php',
            'files'        => TMVC_MYAPPDIR . 'configs/files_config.php',
            'img'          => TMVC_MYAPPDIR . 'configs/img_config.php',
            'cr'           => TMVC_MYAPPDIR . 'configs/cr_domains_config.php',
        ];

        // Load configurations
        $loadedConfigs = [];
        foreach ($configMapping as $name => $file) {
            $config = $fileLoader($file);
            if ($config instanceof \Closure) {
                $config = $config($this);
            }

            $loadedConfigs[$name] = $config;
        }

        $baseConfigs = array_merge($loadedConfigs['application'], ['database' => $loadedConfigs['database'], 'cr_available' => $loadedConfigs['cr']]);
        $this->my_config = array_merge($loadedConfigs['cached'], [
            'app'   => $baseConfigs,
            'env'   => $_ENV ?? [],
            'img'   => $loadedConfigs['img'],
            'files' => $loadedConfigs['files'],
        ]);

        $this->config = array_merge($baseConfigs, [
            'env'   => $_ENV ?? [],
            'img'   => $loadedConfigs['img'],
            'files' => $loadedConfigs['files'],
        ]);
    }

    /**
     * Setup the custom configurations.
     *
     * @deprecated
     */
    public function setupCustomConfig()
    {
        $file = realpath(\App\Common\CACHE_PATH) . '/' .  $this->getEnvironment() . '/configs/customConfigs.php';
        $this->my_config = [];
        if (file_exists($file)) {
            $this->my_config = require $file;
        }

        $this->my_config['app'] = $this->config;
    }

    /**
     * Setup the img variables into configurations.
     *
     * @deprecated
     */
    public function setupImg()
    {
        $file = TMVC_MYAPPDIR . 'configs/img_config.php';
        if (file_exists($file)) {
            $this->config['img'] = $this->my_config['img'] = require $file;
        }
    }

    /**
     * Setup the files variables into configurations.
     *
     * @deprecated
     */
    public function setupFiles()
    {
        $file = TMVC_MYAPPDIR . 'configs/files_config.php';
        if (file_exists($file)) {
            $this->config['files'] = $this->my_config['files'] = require $file;
        }
    }

    /**
     * Setup the ENV variables into configurations.
     *
     * @deprecated
     */
    public function setupEnv()
    {
        $this->config['env'] = $this->my_config['env'] = !empty($_ENV) ? $_ENV : [];
    }

    /**
     * setup error handling for tmvc.
     */
    public function setupErrorHandling()
    {
        if (defined('TMVC_ERROR_HANDLING') && TMVC_ERROR_HANDLING == 1) {
            // catch all uncaught exceptions
            set_exception_handler(['TinyMVC_ExceptionHandler', 'handleException']);

            require_once TMVC_BASEDIR . 'sysfiles/plugins/tinymvc_errorhandler.php';
            set_error_handler('TinyMVC_ErrorHandler');
        }
    }

    /**
     * Setup the route path.
     */
    public function setupRoutePath(): void
    {
        // set path_info
        $this->route_path_info = $this->path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');
    }

    /**
     * setup url routing for tmvc.
     */
    public function setupSubDomainPath()
    {
        switch (__CURRENT_SUB_DOMAIN) {
            case $_ENV['BLOG_SUBDOMAIN']:
                /** @var BlogCategoryRouteResolverService $resolver */
                $resolver = $this->getContainer()->get(BlogCategoryRouteResolverService::class);

                $this->path_info = $resolver->resolvePathInfo($this->path_info, $this->getRequest());

                break;

            case $_ENV['BLOGGERS_SUBDOMAIN']:
                $path_info = array_filter(explode('/', $this->path_info));
                if (isset($path_info[1]) && $path_info[1] == 'preview') {
                    $this->path_info = "/bloggers/" . ltrim($this->path_info, '/');
                }
                break;

            case $_ENV['COMMUNITY_SUBDOMAIN']:
                $path_info = array_filter(explode('/', $this->path_info));
                if (isset($path_info[1]) && !in_array($path_info[1], ['question', 'questions']) && !$this->request->isAjaxRequest()) {
                    header(sprintf('Location: %s', __SITE_URL . $this->config['errors_controller'] . '/' . $this->config['404_action']));

                    exit();
                }
                if (!$this->request->isAjaxRequest()) {
                    $this->path_info = '/community/' . ltrim($this->path_info, '/');
                }

                break;

            case $_ENV['GIVEAWAY_SUBDOMAIN']:
                if (!$this->request->isAjaxRequest()) {
                    $pathInfo = array_filter(explode('/', $this->path_info));
                    if (count($pathInfo) > 0) {
                        header(sprintf('Location: %s/%s', rtrim(__SITE_URL, '/'), '404'));

                        exit();
                    }

                    $this->path_info = '/landing/giveaway';
                }

                break;

            default:
                if (in_array(__CURRENT_SUB_DOMAIN, $this->config['cr_available'])) {
                    if (false === (bool) $_ENV['CR_SUBDOMAIN_ACTIVE']) {
                        header('Location: ' . __SITE_URL);

                        exit();
                    }

                    $path_info = array_filter(explode('/', $this->path_info));
                    $this->path_info = $this->config['cr_controller'] . $this->path_info;
                }

            break;
        }
    }

    public function setupRouting()
    {
        if (!empty($this->config['routing']['search']) && !empty($this->config['routing']['replace'])) {
            $this->config['routing_map'] = array_combine($this->config['routing']['search'], $this->config['routing']['replace']);
            $this->path_info = preg_replace(
                $this->config['routing']['search'],
                $this->config['routing']['replace'],
                $this->path_info
            );
        }
    }

    /**
     * setup url segments array.
     */
    public function setupSegments()
    {
        $this->url_segments = !empty($this->path_info) ? array_filter(explode('/', $this->path_info), 'mb_strlen') : [];
        $this->route_url_segments = !empty($this->route_path_info) ? array_filter(explode('/', $this->route_path_info)) : [];
    }

    /**
     * setup controller.
     */
    public function setupController()
    {
        $controller_name = $this->config['root_controller'] ?? null;
        $is_root_controller = true;
        if (null === $controller_name) {
            $controller_name = ($this->url_segments[1] ?? null) ?? $this->config['default_controller'] ?? null;
            $is_root_controller = false;
            if (__CURRENT_SUB_DOMAIN === $_ENV['BLOG_SUBDOMAIN'] && !$this->request->isAjaxRequest()) {
                $controller_name = $this->config['blog_controller'];
            } elseif (__CURRENT_SUB_DOMAIN === $_ENV['COMMUNITY_SUBDOMAIN'] && !$this->request->isAjaxRequest()) {
                $controller_name = $this->config['community_controller'];
            } elseif (in_array(__CURRENT_SUB_DOMAIN, $this->config['cr_available']) && !$this->request->isAjaxRequest()) {
                $controller_name = $this->config['cr_controller'];
            } elseif (__CURRENT_SUB_DOMAIN === $_ENV['BLOGGERS_SUBDOMAIN']  && !$this->request->isAjaxRequest()) {
                $controller_name = 'bloggers';
            }
        }

        try {
            $directory = new File(TMVC_MYAPPDIR . 'controllers', false);
            $controller_file = new File(TMVC_MYAPPDIR . "controllers/{$controller_name}.php");
            if ($directory->getRealPath() !== dirname($controller_file->getRealPath())) {
                throw new FileNotFoundException('The controller file is in wrong directory.');
            }
        } catch (FileNotFoundException $exception) {
            if ($is_root_controller || null === ($controller_name = $this->config['company_controller'] ?? null)) {
                header(sprintf('Location: %s', __SITE_URL . $this->config['errors_controller'] . '/' . $this->config['404_action']));
            }

            try {
                $controller_file = new File(TMVC_MYAPPDIR . "controllers/{$controller_name}.php");
                $this->is_acces_by_pesonalized_link = true;
            } catch (FileNotFoundException $exception) {
                header(sprintf('Location: %s', __SITE_URL . $this->config['errors_controller'] . '/' . $this->config['404_action']));
            }
        }

        include $controller_file->getRealPath();
        // see if controller class exists
        $controller_class = "{$controller_name}_Controller";
        // instantiate the controller
        $this->controller = new $controller_class($this->container);
        $this->controller->name = $controller_name;
        $this->container->set('controller', $this->controller);
    }

    /**
     * setup controller method (action) to execute.
     */
    public function setupAction()
    {
        if (!empty($this->config['root_action'])) {
            // user override if set
            $this->action = $this->config['root_action'];
        } else {
            // get from url if present, else use default
            switch ($this->controller->name) {
                case $this->config['blog_controller']:
                    $this->action = $this->config['blog_default_action'];

                    if ('preview_blog' === $this->url_segments[1]) {
                        $this->action = 'preview_blog';
                    } elseif ($this->getContainer()->get(BlogCategoryRouteResolverService::class)->isValidBlogDetailUrl($this->url_segments)) {
                        $this->action = 'detail';
                    }

                break;

                case $this->config['cr_controller']:
                    $current_method = $this->url_segments[1];
                    $this->action = (!empty($current_method) && method_exists($this->controller, $current_method)) ? $current_method : $this->config['cr_default_action'];

                break;

                default:
                    $this->action = !empty($this->url_segments[2]) ? $this->url_segments[2] : (!empty($this->config['default_action']) ? $this->config['default_action'] : 'index');

                break;
            }

            if (!method_exists($this->controller, $this->action) && !$this->isDebug()) {
                header(sprintf('Location: %s', __SITE_URL . $this->config['errors_controller'] . '/' . $this->config['404_action']));
            }

            // cannot call method names starting with underscore
            if ('_' == substr($this->action, 0, 1)) {
                throw new Exception("Action name not allowed '{$this->action}'");
            }
        }
    }

    /**
     * autoload any libs/scripts.
     */
    public function setupAutoloaders()
    {
        include TMVC_MYAPPDIR . 'configs' . DS . 'autoload.php';

        if (!empty($config['scripts'])) {
            foreach ($config['scripts'] as $script) {
                $this->controller->load->script($script);
            }
        }
        if (!empty($config['libraries'])) {
            foreach ($config['libraries'] as $library) {
                if (is_array($library)) {
                    $this->controller->load->library($library[0], $library[1]);
                } else {
                    $this->controller->load->library($library);
                }
            }
        }
        if (!empty($config['models'])) {
            foreach ($config['models'] as $model) {
                $this->controller->load->model($model . '_Model', $model);
            }
        }
    }

    /**
     * instance.
     *
     * get/set the tmvc object instance(s)
     *
     * @param object $new_instance reference to new object instance
     * @param string $id           object instance id
     *
     * @return static $instance reference to object instance
     */
    public static function &instance($new_instance = null, $id = 'default')
    {
        static $instance = [];
        if (isset($new_instance) && is_object($new_instance)) {
            $instance[$id] = $new_instance;
        }

        return $instance[$id];
    }

    /**
     * timer.
     *
     * get/set timer values
     *
     * @param string $id  the timer id to set (or compare with $id2)
     * @param string $id2 the timer id to compare with $id
     *
     * @return float difference of two times
     */
    public static function timer($id = null, $id2 = null)
    {
        static $times = [];
        if (null !== $id && null !== $id2) {
            return (isset($times[$id], $times[$id2])) ? ($times[$id2] - $times[$id]) : false;
        }
        if (null !== $id) {
            return $times[$id] = microtime(true);
        }

        return false;
    }

    /**
     * Returns the kernel parameters.
     */
    protected function getKernelParameters()
    {
        $buildParameters = [
            'kernel.logs_dir'            => realpath(\App\Common\LOGS_PATH),
            'kernel.root_dir'            => realpath(\App\Common\ROOT_PATH),
            'kernel.cache_dir'           => realpath(\App\Common\CACHE_PATH) . '/' .  $this->getEnvironment(),
            'kernel.build_dir'           => realpath(\App\Common\CACHE_PATH) . '/' .  $this->getEnvironment(),
            'kernel.config_dir'          => realpath(\App\Common\CONFIG_PATH),
            'kernel.project_dir'         => realpath(\App\Common\ROOT_PATH),
            'kernel.controllers_dir'     => realpath(\App\Common\ROOT_PATH . '/tinymvc/myapp/controllers'),
            'kernel.environment'         => $this->getEnvironment(),
            'kernel.runtime_environment' => $this->config['env.APP_RUNTIME_ENV'] ?? $this->getEnvironment(),
            'kernel.debug'               => $this->isDebug(),
            'kernel.charset'             => $this->getCharset(),
            'app.base_uri'               => rtrim(__SITE_URL, '\/'),
            'app.current_url'            => rtrim(__CURRENT_URL, '\/'),
        ];

        $parametersRemap = [
            'default_controller' => 'kernel.default_controller',
            'default_action'     => 'kernel.default_controller.action',
            'root_controller'    => 'kernel.root_controller',
            'root_action'        => 'kernel.root_controller.action',
            'errors_controller'  => 'kernel.not_found_controller',
            '404_action'         => 'kernel.not_found_controller.action',
        ];
        foreach ($parametersRemap as $fromParam => $toParam) {
            if (!empty($this->config[$fromParam])) {
                $buildParameters[$toParam] = $this->config[$fromParam];
            }
        }

        $databaseConnections = $this->config['database'] ?? [];
        $defaultDatabaseConnection = $databaseConnections['default_connection'] ?? $databaseConnections['default_pool'] ?? 'default';
        unset($databaseConnections['default_connection'], $databaseConnections['default_pool']);
        $buildParameters['database.connections'] = $databaseConnections;
        $buildParameters['database.connections.default_connection'] = $defaultDatabaseConnection;
        $buildParameters['session.storage.options'] = $this->config['session'] ?? [];
        $buildParameters['kernel.routing_replacement_map'] = (array) ($this->config['routing_map'] ?? []);
        $buildParameters['kernel.autoload_options'] = (array) ($this->config['autoload'] ?? []);
        $buildParameters['kernel.runtime_config'] = new FrozenParameterBag($this->my_config);
        $buildParameters['kernel.build_config'] = new FrozenParameterBag($this->config);

        return $buildParameters;
    }

    /**
     * Initializes the service container.
     */
    protected function initializeContainer()
    {
        $this->configureKernel();
        $this->container = new StaticContainer($this->getKernelParameters());
        $this->container->set('kernel', $this);
    }

    /**
     * Handles the request.
     */
    private function handleRequest(Request $request, int $type = 1, bool $catch = true): Response
    {
        $request->headers->set('X-Php-Ob-Level', (string) ob_get_level());

        try {
            return $this->handleRaw($request, $type);
        } catch (\Exception $exception) {
            if ($exception instanceof RequestExceptionInterface) {
                $exception = new BadRequestHttpException($exception->getMessage(), $exception);
            }
            if (false === $catch) {
                $this->finishRequest($request, $type);

                throw $exception;
            }

            return $this->handleThrowable($exception, $request, $type);
        }
    }

    /**
     * Handles a request to convert it to a response.
     *
     * Exceptions are not caught.
     *
     * @throws NotFoundHttpException When controller cannot be found
     */
    private function handleRaw(Request $request, int $type = 1): Response
    {
        $this->getRequestStack()->push($request);

        // Resolve controller
        if (null === $controller = $this->controller) {
            throw new NotFoundHttpException(
                sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo())
            );
        }
        if (null === $action = $this->action) {
            throw new NotFoundHttpException(
                sprintf('Unable to find the action for path "%s". The route is wrongly configured.', $request->getPathInfo())
            );
        }

        if (
            __CURRENT_SUB_DOMAIN === config('env.SHIPPER_SUBDOMAIN')
            && logged_in()
            && !is_shipper()
        ) {
            return $this->filterResponse(
                new RedirectResponse(
                    __SITE_URL . implode('/', $this->route_url_segments) . (empty($getParameters = request()->query->all()) ? '' : '?' . http_build_query($getParameters)),
                    301
                ),
                $request,
                $type
            );
        }

        if (!logged_in()) {
            /** @var TinyMVC_Library_Auth $authenticationLibrary */
            $authenticationLibrary = library(TinyMVC_Library_Auth::class);

            $authenticationLibrary->login_from_cookie();
        }

        if (null === session()->csrfToken) {
            if (logged_in()) {
                session()->csrfToken = hash('sha512', random_bytes(32));
            }
        } elseif (isAjaxRequest() && logged_in() && request()->server->get('HTTP_X_CSRF_TOKEN') !== session()->csrfToken) {
            header("HTTP/1.1 400 Bad Request");
            exit;
        }

        // Call controller
        $response = $controller($action);

        // View
        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        return $this->filterResponse($response, $request, $type);
    }

    /**
     * Handles a throwable by trying to convert it to a Response.
     *
     * @throws \Exception
     */
    private function handleThrowable(Throwable $e, Request $request, int $type): Response
    {
        // @todo Conver throwable to the response.
        throw $e;
    }

    /**
     * Filters a response object.
     */
    private function filterResponse(Response $response, Request $request, int $type): Response
    {
        //@todo move to the events
        if ($request === $this->getRequestStack()->getMasterRequest() && false !== $response->getContent()) {
            // Assets
            $assetsHandler = encore();
            if ($request->attributes->get('_encore.links.container') ?? false) {
                $response = (clone $response)->setContent(
                    str_replace(
                        $assetsHandler->getLinksBlockTag(),
                        implode('', $assetsHandler->getRenderedLinkEntries() ?? []),
                        $response->getContent() ?? ''
                    )
                );
            }
            if ($request->attributes->get('_encore.scripts.container') ?? false) {
                $response = (clone $response)->setContent(
                    str_replace(
                        $assetsHandler->getScriptsBlockTag(),
                        implode('', $assetsHandler->getRenderedScriptEntries() ?? []),
                        $response->getContent() ?? ''
                    )
                );
            }

            // Web-links
            $linkProvider = $request->attributes->get('_links') ?? null;
            if ($linkProvider instanceof LinkProviderInterface && $links = $linkProvider->getLinks()) {
                $response->headers->set('Link', (new HttpHeaderSerializer())->serialize($links), false);
            }

            // Timer
            if ($this->config['timer']) {
                // Insert timing, dammit.
                tmvc::timer('tmvc_app_end');
                $response = (clone $response)->setContent(
                    str_replace(
                        '{TMVC_TIMER}',
                        sprintf('%0.5f', tmvc::timer('tmvc_app_start', 'tmvc_app_end')),
                        $response->getContent() ?? ''
                    )
                );
            }
        }

        $this->finishRequest($request, $type);

        return $response;
    }

    /**
     * Publishes the finish request event, then pop the request from the stack.
     */
    private function finishRequest(Request $request, int $type): void
    {
        $this->getRequestStack()->pop();
    }

    /**
     * Configures the kernel.
     */
    private function configureKernel()
    {
        // Setup app configs
        // $this->loadBuildConfigurations();

        foreach (['cache' => \App\Common\CACHE_PATH, 'build' => \App\Common\CACHE_PATH, 'logs' => \App\Common\LOGS_PATH] as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Unable to create the "%s" directory (%s).', $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to write in the "%s" directory (%s).', $name, $dir));
            }
        }
    }
}
