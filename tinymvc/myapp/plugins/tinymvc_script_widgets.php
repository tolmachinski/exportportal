<?php

use App\Common\Autocomplete;
use App\Common\Exceptions\NotFoundException;
use App\Common\Workflow\Comments\CommentStates;
use App\DataProvider\NavigationBarStateProvider;
use App\Filesystem\UserFilePathGenerator;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\String\UnicodeString;

/**
 * Show notifications popup in admin panel by loading the corresponding view
 *
 * @param array $conditions
 *
 * @return void
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetNavHeader
 */
function widgetNavHeader(array $conditions = []): void
{
    /** @var Currencies_Model $currenciesModel */
    $currenciesModel = model(Currencies_Model::class);

    /** @var User_System_Messages_Model $userSystemMessagesModel */
    $userSystemMessagesModel = model(User_System_Messages_Model::class);

    /** @var FilesystemProviderInterface */
    $storageProvider = container()->get(FilesystemProviderInterface::class);
    $publicDisk = $storageProvider->storage('public.storage');
    $session = tmvc::instance()->controller->session;

    if (empty($session->user_photo)) {
        $userImageUrl = thumbNoPhoto(group_session());
    } else {
        $userImageUrl =  $publicDisk->url(UserFilePathGenerator::imagesThumbUploadFilePath($session->id, $session->user_photo));
    }

    views(
        'admin/nav_header_view',
        [
            'logout_page'           => $conditions['logout_page'] ?? null,
            'userImageUrl'          => $userImageUrl,
            'count_notifications'   => $userSystemMessagesModel->counterUserNotifications(privileged_user_id()),
            'currencyes'            => $currenciesModel->findAllBy([
                'conditions' => [
                    'isEnabled' => 1,
                ],
            ]),
        ]
    );
}

/**
 * Show header navigation bar by loading the corresponding view
 *
 * @return void
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetNewNavHeader
 *
 * @todo Remove [21.04.2022]
 * Not used
 *
 */
function widgetNewNavHeader(): void
{
    if (logged_in()) {
        /** @var User_System_Messages_Model $userSystemMessagesModel */
        $userSystemMessagesModel = model(User_System_Messages_Model::class);

        $data = [
            'count_notifications'   => $userSystemMessagesModel->counterUserNotifications(privileged_user_id()),
            'complete_profile'      => session()->__get('completeProfile'),
        ];
    }

    views('new/template_views/nav_header_view', $data ?? []);
}

/**
 * Show user notifications popover by loading the corresponding view
 *
 * @return void
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetCountNotifyPopover
 */
function widgetCountNotifyPopover(): void
{
    /** @var NavigationBarStateProvider */
    $provider = container()->get(NavigationBarStateProvider::class);

    views('new/nav_header/notifications/popover_view', $provider->getState((int) privileged_user_id()));
}

/**
 * Show page metadata by loading the corresponding view
 *
 * @param array $replaceParams
 * @param array $data
 * @param string $folder
 *
 * @return void
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetMetaHeader
 */
function widgetMetaHeader($replaceParams = [], $data = [], $folder = '', $customMeta = []): void
{
    if (isset($data['error_page'])) {
        switch ($data['error_page']) {
            default:
            case '404':
                $data['meta'] = ['title' => '404 Not Found', 'keywords' => '', 'description' => '', 'image' => 'public/img/error-pages/404-error.jpg'];

            break;
            case '403':
                $data['meta'] = ['title' => '403 Forbidden', 'keywords' => '', 'description' => '', 'image' => 'public/img/error-pages/403-error.jpg'];

            break;
            case 'Oops':
                $data['meta'] = ['title' => 'Oops, something went wrong', 'keywords' => '', 'description' => '', 'image' => 'public/img/error-pages/404-error.jpg'];

            break;
        }

        views("{$folder}header_meta_view", $data);

        return;
    }

    if (!empty($customMeta)) {
        views()->assign(['metaTitle' => $customMeta['title']]);
        views()->display($folder . 'header_meta_view', $customMeta);

        return;
    }

    $pageKey = controller()->name . '_' . tmvc::instance()->action;
    $metaLangIso2 = array_filter(['en', __SITE_LANG === 'en' ? null : __SITE_LANG]);

    if ('default_index' == $pageKey && __CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
        $pageKey = 'default_index_epl';
    }

    if ('default_index' == $pageKey && is_shipper()) {
        $pageKey = 'default_index_shipper';
    }

    if ('login_index' == $pageKey && __CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
        $pageKey = 'login_index_shipper';
    }

    /** @var Meta_Model $metaModel */
    $metaModel = model(Meta_Model::class);

    $metaForPage = $metaModel->get_seo(['page_key' => $pageKey, 'lang_iso2' => $metaLangIso2]);
    $metaForPage = arrayByKey($metaForPage, 'lang_iso2');

    $existingMetaForPage = $metaForPage[__SITE_LANG] ?? $metaForPage['en'] ?? [];

    switch ($pageKey) {
        case 'category_index':
            $meta = array_merge($existingMetaForPage, array_filter($data['base_seo']));

        break;
        case 'user_index':
        case 'seller_index':
        case 'seller_about':
            $newDescription = empty($data['description']) ? [] : ['description' => $data['description']];
            $meta = array_merge($existingMetaForPage, $newDescription);

        break;

        default:
            $meta = $existingMetaForPage;

        break;
    }

    $data['meta'] = $metaModel->handle_meta($meta ?: $data, $replaceParams);

    $metaTitle = new UnicodeString($data['meta']['title']);
    $metaDescription = new UnicodeString($data['meta']['description']);
    $metaKeywords = new UnicodeString($data['meta']['keywords']);

    $metaFind = [' ,', ' .', ' ?'];
    $metaReplace = [',', '.', '?'];
    $data['meta']['title'] = str_replace($metaFind, $metaReplace, (string) $metaTitle->collapseWhitespace());
    $data['meta']['description'] = str_replace($metaFind, $metaReplace, (string) $metaDescription->collapseWhitespace());
    $data['meta']['keywords'] = str_replace($metaFind, $metaReplace, (string) $metaKeywords->collapseWhitespace());

    views()->assign(['metaTitle' => $data['meta']['title']]);
    views()->display($folder . 'header_meta_view', $data);
}

/**
 * Prepare data for generate user guide pdf
 *
 * @param array $menuList
 * @param int $hIndex
 * @param array $docIndex
 * @param int $level
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetDocMenuText
 */
function widgetDocMenuText(array $menuList = [], int $hIndex = 1, array $docIndex = [1], int $level = 0): string
{
    $html = '';
    foreach ($menuList as $menu) {
        $html .= "<h{$hIndex}>" . implode('.', $docIndex) . ". {$menu['menu_title']}</h{$hIndex}><div class=\"text-b\">{$menu['menu_description']}</div>";

        if (!empty($menu['children'])) {
            $html .= widgetDocMenuText($menu['children'], $hIndex + 1, array_merge($docIndex, [$level => 1]), $level + 1);
        }

        if (0 == $level) {
            $html .= "<pagebreak />";
        }

        ++$docIndex[$level];
    }

    return $html;
}

/**
 * @author Usinevici Alexandr
 * @todo Remove [02.12.2021]
 * Reason: not used
 */
/* function widget_epdoc_menu_new($conditions = [])
{
    if (empty($conditions)) {
        return;
    }

    extract($conditions);

    if (empty($menu_list)) {
        return;
    }

    $html = '<div class="sliding-row">';
    foreach ($menu_list as $menu) {
        if (!empty($menu['menu_title']) && (in_array($user_type, $menu['rel_user_types']) || 'all' == $user_type)) {
            $html .= '<div class="sliding-block">
                        <div class="sliding-block__title-wrapper title-btn">
                            <h2 class="sliding-block__title-txt">
                                <a class="fancybox fancybox.ajax" href="' . __SITE_URL . 'user_guide/popup_forms/show_doc/' . $menu['menu_alias'] . '?user_type=' . $user_type . '" data-title="' . $menu['menu_title'] . '" title="' . $menu['menu_title'] . '">
                                ' . $menu['menu_title'] . '
                                </a>
                            </h2>';

            if (!empty($menu['children'])) {
                $html .= '<i class="ep-icon ep-icon_plus-stroke sliding-block__cross"></i>';
            }

            $html .= '</div>';

            if (!empty($menu['children'])) {
                $conditions['menu_list'] = $menu['children'];
                $html .= '<div class="sliding-block__wrapper sliding-block__element-wrp">
                            ' . widget_epdoc_submenu_new($conditions) . '
                        </div>';
            }

            $html .= '</div>';
        }
    }
    $html .= '</div>';

    return $html;
} */

/**
 * @author Usinevici Alexandr
 * @todo Remove [02.12.2021]
 * Reason: not used
 */
/* function widget_epdoc_submenu_new($conditions = [])
{
    if (empty($conditions)) {
        return;
    }

    extract($conditions);

    if (empty($menu_list)) {
        return;
    }

    $html = '<div class="sliding-block__section">';
    foreach ($menu_list as $menu) {
        if (!(in_array($user_type, $menu['rel_user_types']) || 'all' == $user_type)) {
            continue;
        }

        if (!empty($menu['children'])) {
            $title_btn = 'title-btn';
            $children = 'ep-icon_plus-stroke';
        } else {
            $title_btn = '';
            $children = 'ep-icon_circle';
        }

        $html .= '<div class="sliding-block__section-title ' . $title_btn . '">
                    <i class="ep-icon ' . $children . ' mr-15"></i>
					<a class="sliding-block__section-title-span fancybox fancybox.ajax" href="' . __SITE_URL . 'user_guide/popup_forms/show_doc/' . $menu['menu_alias'] . '?user_type=' . $user_type . '" data-title="' . $menu['menu_title'] . '" title="' . $menu['menu_title'] . '">
					    ' . $menu['menu_title'] . '
					</a>
                </div>';

        if (!empty($menu['children'])) {
            $conditions['menu_list'] = $menu['children'];
            $html .= '<div class="sliding-block__element-wrp">
                        ' . widget_epdoc_submenu_new($conditions) . '
                      </div>';
        }
    }
    $html .= '</div>';

    return $html;
} */

/**
 * @author Usinevici Alexandr
 * @todo Remove [02.12.2021]
 * Reason: not used
 */
/* function widget_epdoc_submenu_tablet($conditions = [])
{
    if (empty($conditions)) {
        return;
    }

    extract($conditions);

    if (empty($menu_list)) {
        return;
    }

    $html = '<ul class="epdoc-list__nav">';
    foreach ($menu_list as $menu) {
        if (!empty($menu['children'])) {
            $children = '<i class="epdoc-list__nav-ttl-arrow epdoc-list__nav-ttl-ico-l ep-icon ep-icon_arrow-right"></i> ';
            $collapse = 'epdoc-list__nav--collapse ';
        } else {
            $children = '<i class="epdoc-list__nav-ttl-ico-l ep-icon ep-icon_minus"></i> ';
            $collapse = '';
        }

        $html .= '<li class="epdoc-list__nav-item">
					<div class="epdoc-list__nav-ttl ' . $collapse . '">'
                        . $children
                        . '<div class="epdoc-list__nav-ttl-txt">' . $menu['menu_title'] . '</div>
						<a class="btn btn-primary btn-xs epdoc-list__nav-ttl-ico-r fancybox fancybox.ajax" href="' . __SITE_URL . 'user_guide/popup_forms/show_doc/' . $menu['menu_alias'] . '?user_type=' . $user_type . '" data-title="' . $menu['menu_title'] . '" title="' . $menu['menu_title'] . '">
							<i class="ep-icon ep-icon_info"></i>
						</a>
					</div>';
        if (!empty($menu['children'])) {
            $conditions['menu_list'] = $menu['children'];
            $html .= widget_epdoc_submenu_tablet($conditions);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
} */

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 */
function widget_popup_user_preferences()
{
    $app = tmvc::instance();
    $user_lang = $_COOKIE['_ulang'];
    $page_info = model('translations')->get_page_data($app->controller->name, $app->action);
    $route_key = $app->controller->name . '/' . $app->action;
    $route = model('translations')->get_routing_by_key($route_key);
    $use_google_translate = (bool) (int) config('env.USE_ONLY_GOOGLE_TRANSLATE');

    $params = [
        'lang_active' => 1,
    ];

    $data['tlanguages'] = arrayByKey(model('translations')->get_languages($params), 'lang_iso2');
    $default_lang_route_config = $route ? json_decode($route['lang_en'], true) : [];
    $current_lang_route_config = $app->site_urls[$route_key] ?? null;
    $data['current_lang_flag'] = 'United-States-of-America.png';
    $data['currentLangIso2'] = 'EN';
    $data['connectGtrans'] = false;
    // PREPARE GET PARAMS

    $data['langUrls'] = [];
    $domain_langs = [];
    $google_langs = [];
    $available_langs = [];
    foreach ($data['tlanguages'] as $key => $tlanguage) {
        if (isset($page_info['lang_' . $tlanguage['lang_iso2']])) {
            $is_translated_page = $data['tlanguages'][$key]['is_translated_page'] = (int) $page_info['lang_' . $tlanguage['lang_iso2']];
        }

        $used_domain_translate = 'domain' === $tlanguage['lang_url_type'] || (($is_translated_page ?? false) && !$use_google_translate);

        $available_langs[] = $tlanguage['lang_iso2'];
        if ($used_domain_translate) {
            $domain_langs[] = $tlanguage['lang_iso2'];
        } elseif ('google_hash' == $tlanguage['lang_url_type']) {
            $google_langs[$tlanguage['lang_iso2']] = $tlanguage['lang_google_abbr'];
        }

        $uri_get = '';
        $data_get = $_GET;
        if ('en' !== $tlanguage['lang_iso2']) {
            $data_get['lang'] = $tlanguage['lang_iso2'];
        } else {
            unset($data_get['lang']);
        }

        if (!empty($data_get)) {
            $uri_get = '?' . http_build_query($data_get);
        }

        if ($tlanguage['lang_iso2'] == $user_lang) {
            $data['current_lang_flag'] = $tlanguage['lang_icon'];
            $data['currentLangIso2'] = $tlanguage['lang_iso2'];
            $data['langUrls'][$user_lang] = implode('/', $app->route_url_segments) . $uri_get;

            if (!$used_domain_translate) {
                $data['connectGtrans'] = true;
            }

            continue;
        }

        $lang_uri = [];
        if (empty($route)) {
            $lang_uri = $app->route_url_segments;
            $data['langUrls'][$tlanguage['lang_iso2']] = implode('/', $lang_uri) . $uri_get;

            continue;
        }

        if ($used_domain_translate) {
            $lang_route_config = json_decode($route['lang_' . $tlanguage['lang_iso2']], true);
            if (!empty($lang_route_config['replace_uri_components'])) {
                switch ($app->controller->name) {
                    case 'category':
                        $category_url_segments = $app->route_url_segments;
                        $category_url_segments[2] .= '/' . $category_url_segments[3];
                        unset($category_url_segments[3]);
                        $app->route_url_segments = array_values($category_url_segments);
                        $uri = array_filter($app->controller->uri->uri_to_assoc(1, $app->route_url_segments));

                    break;
                    case 'questions':
                        $questions_route_segments = array_merge([$app->route_url_segments[1], '{_.index._}'], array_slice($app->route_url_segments, 1));
                        $uri = array_filter($app->controller->uri->uri_to_assoc(1, $questions_route_segments));

                    break;
                    case 'blog':
                        $uri = $app->controller->uri->uri_to_assoc(2, $app->route_url_segments);
                        unset($uri['blog']);

                    break;
                    case 'ep_events':
                        switch ($app->action) {
                            case 'index':
                                $uri = array_merge(['ep_events' => 'ep_events'], $app->controller->uri->uri_to_assoc(1, $app->route_url_segments));

                                break;

                            default:
                                $uri = $app->controller->uri->uri_to_assoc(2, $app->route_url_segments);

                                break;
                        }

                    break;

                    default:
                        $uri = $app->controller->uri->uri_to_assoc(2, $app->route_url_segments);

                    break;
                }

                foreach ($uri as $uri_key => $uri_value) {
                    $replace_key = (isset($lang_route_config['replace_uri_components'][$current_lang_route_config['flipped_uri_components'][$uri_key]])) ? $current_lang_route_config['flipped_uri_components'][$uri_key] : $uri_key;

                    if (!empty($replace_key)) {
                        $lang_uri[$replace_key . '_key'] = $lang_route_config['replace_uri_components'][$replace_key];
                    }

                    if (isset($app->routes_priority[$replace_key][$tlanguage['lang_iso2']])) {
                        $lang_uri[$replace_key . '_value'] = $app->routes_priority[$replace_key][$tlanguage['lang_iso2']];
                    } else {
                        if (!empty($uri_value) && '{_.index._}' != $uri_value) {
                            if (isset($lang_route_config['replace_uri_components'][$current_lang_route_config['flipped_uri_components'][$uri_value]])) {
                                $lang_uri[$replace_key . '_value'] = $lang_route_config['replace_uri_components'][$current_lang_route_config['flipped_uri_components'][$uri_value]];
                            } else {
                                $lang_uri[$replace_key . '_value'] = $uri_value;
                            }
                        }
                    }
                }
            } else {
                if (!empty($lang_route_config['route_segments'])) {
                    $lang_uri = $lang_route_config['route_segments'];
                    if (!empty($lang_route_config['replace_route_segments'])) {
                        foreach ($lang_route_config['replace_route_segments'] as $replace_route_segment) {
                            $lang_uri[$replace_route_segment] = (isset($app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment])) ? $app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment] : $app->route_url_segments[$replace_route_segment];
                        }
                    }
                } else {
                    $lang_uri = $default_lang_route_config['route_segments'];
                    if (!empty($default_lang_route_config['replace_route_segments'])) {
                        foreach ($default_lang_route_config['replace_route_segments'] as $replace_route_segment) {
                            $lang_uri[$replace_route_segment] = (isset($app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment])) ? $app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment] : $app->route_url_segments[$replace_route_segment];
                        }
                    }
                }
            }
            $data['langUrls'][$tlanguage['lang_iso2']] = implode('/', $lang_uri) . $uri_get;
        } elseif ('google_hash' === $tlanguage['lang_url_type']) {
            $lang_uri = $default_lang_route_config['route_segments'];
            if (!empty($default_lang_route_config['replace_route_segments'])) {
                foreach ($default_lang_route_config['replace_route_segments'] as $replace_route_segment) {
                    $lang_uri[$replace_route_segment] = (isset($app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment])) ? $app->routes_priority[$tlanguage['lang_iso2']][$replace_route_segment] : $app->route_url_segments[$replace_route_segment];
                }
            }

            $data['langUrls'][$tlanguage['lang_iso2']] = implode('/', $lang_uri);
        }
    }

    $data['siteUrl'] = __CURRENT_SUB_DOMAIN_URL;

    $data['domainLangs'] = json_encode($domain_langs);
    $data['googleLangs'] = json_encode($google_langs);
    $data['availableLangs'] = json_encode($available_langs);
    $data['currencyes'] = model(Currency::class)->get_all_cur();

    $app->controller->view->assign($data);
    $app->controller->view->display('new/user/popup_preferences_view');
}

/**
 * @author Usinevici Alexandr
 * @todo Remove [02.12.2021]
 * Reason: use instead: session()->__get('completeProfile')
 */
/* function widget_get_complete_profile_options($profile_completion = [])
{
    $result = [
        'options'         => [],
        'total_completed' => 20,
    ];

    if (empty($profile_completion)) {
        $profile_completion = model('complete_profile')->get_user_profile_options(privileged_user_id());
    }

    if (empty($profile_completion)) {
        $result['total_completed'] = 100;

        return $result;
    }

    $result['options'] = $profile_completion;

    foreach ($result['options'] as $option) {
        if (1 == $option['option_completed']) {
            $result['total_completed'] += (int) $option['option_percent'];
        }
    }

    return $result;
} */

if (!function_exists('widgetFileUploader')) {
    /**
     * Load view and scripts necessary for uploading some files
     *
     * @param array $params
     * @param string $label
     * @param string $group
     * @param string $inputName
     * @param bool $isEnabled
     * @param bool $isRequired
     * @param bool $isLimited
     * @param bool $hasRemoteDeletion
     * @param bool $usePreview
     * @param string $type
     * @param array $translations
     *
     * @return void
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetFileUploader
     */
    function widgetFileUploader(
        array $params,
        $label,
        $group,
        $inputName,
        $isEnabled = true,
        $isRequired = true,
        $isLimited = false,
        $hasRemoteDeletion = true,
        $usePreview = true,
        $type = 'mixed',
        array $translations = []
    ): void
    {
        views()->display('new/upload_view', [
            'type'                 => $type,
            'name'                 => $inputName,
            'hash'                 => studlyCase(uniqid('callback')),
            'label'                => $label,
            'group'                => $group,
            'is_enabled'           => $isEnabled,
            'is_limited'           => $isLimited,
            'is_required'          => $isRequired,
            'has_remote_deletion'  => $hasRemoteDeletion,
            'use_preview'          => $usePreview,
            'fileupload'           => $params,
            'translations'         => array_merge(
                [
                    'upload_text'         => 'general_dashboard_modal_field_document_upload_placeholder',
                    'button_text'         => 'general_dashboard_modal_field_document_upload_button_text',
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited',
                    'image_limit_text'    => 'general_dashboard_modal_field_image_help_text_line_2',
                ],
                $translations
            ),
        ]);
    }
}

if (!function_exists('widgetAdminFileUploader')) {
    /**
     * Load view and scripts necessary for uploading some files
     *
     * @param array $params
     * @param string $label
     * @param string $group
     * @param string $inputName
     * @param bool $isEnabled
     * @param bool $isRequired
     * @param bool $isLimited
     * @param bool $hasRemoteDeletion
     * @param bool $usePreview
     * @param string $type
     * @param array $translations
     *
     * @return void
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetAdminFileUploader
     */
    function widgetAdminFileUploader(
        array $params,
        array $uploadedFiles = [],
        $label,
        $group,
        $inputName,
        $isEnabled = true,
        $isRequired = true,
        $isLimited = false,
        $hasRemoteDeletion = true,
        $usePreview = true,
        $type = 'mixed',
        array $translations = []
    ): void
    {
        views()->display('admin/upload_view', [
            'type'                 => $type,
            'name'                 => $inputName,
            'hash'                 => studlyCase(uniqid('callback')),
            'label'                => $label,
            'group'                => $group,
            'is_enabled'           => $isEnabled,
            'is_limited'           => $isLimited,
            'is_required'          => $isRequired,
            'has_remote_deletion'  => $hasRemoteDeletion,
            'uploadedFiles'        => $uploadedFiles,
            'use_preview'          => $usePreview,
            'fileupload'           => $params,
            'translations'         => array_merge(
                [
                    'upload_text'         => 'general_dashboard_modal_field_document_upload_placeholder',
                    'button_text'         => 'general_dashboard_modal_field_document_upload_button_text',
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited',
                    'image_limit_text'    => 'general_dashboard_modal_field_image_help_text_line_2',
                    'image_resolution'    => 'general_dashboard_modal_field_image_help_image_resolution',
                ],
                $translations
            ),
        ]);
    }
}

if (!function_exists('widgetEpdocsFileUploader')) {
    /**
     * @param string        $group
     * @param string        $name
     * @param string        $type
     * @param array         $params
     * @param array         $files
     * @param array         $translations
     * @param string|null   $label
     * @param bool          $addScripts
     * @param bool          $isPublic
     * @param bool          $isModal
     * @param bool          $isEnabled
     * @param bool          $isLimited
     * @param bool          $isRequired
     */
    function widgetEpdocsFileUploader(
        $group,
        $name,
        $type,
        array $params,
        array $files = [],
        array $translations = [],
        $label = null,
        $addScripts = false,
        $isPublic = true,
        $isModal = true,
        $isEnabled = true,
        $isLimited = false,
        $isRequired = true
    ) {
        views()->display($isPublic ? 'new/upload_epdocs_file_view' : 'admin/upload_epdocs_file_view', [
            'is_modal'     => $isModal,
            'is_public'    => $isPublic,
            'is_limited'   => $isLimited,
            'is_enabled'   => $isEnabled,
            'is_required'  => $isRequired,
            'add_scripts'  => $addScripts,
            'files'        => $files,
            'group'        => $group,
            'label'        => $label,
            'name'         => $name,
            'type'         => $type,
            'hash'         => uniqid(),
            'fileupload'   => $params,
            'translations' => array_merge(
                [
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1_alternate',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3_alternate',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2_alternate',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate',
                ],
                !empty($translations) ? $translations : []
            ),
        ]);
    }
}

if (!function_exists('widgetIndustriesMultiselect')) {
    /**
     * Shows the block with industries multiselect.
     *
     * @param array $params
     * @return void
     */
    function widgetIndustriesMultiselect(array $params = []): void
    {
        //region Define default params
        $industries = [];
        $selected_industries = [];
        $categories = [];
        $selected_categories = [];
        $max_selected_industries = 0;
        $show_only_industries = false;
        $enable_select_all = false;
        $required = true;
        $industries_top = [];
        $show_maxindustry_text = true;
        $show_disclaimer = false;
        $disclaimer_text = '';
        $input_suffix = '';
        $input_placeholder = '';
        $dispatchDynamicFragment = false;
        //engregion Define default params

        extract($params);

        $data = [
            'widget_id'                 => implode('-', explode('.', uniqid('', true))),
            'industries'                => $industries,
            'max_industries'            => $max_selected_industries,
            'industries_only'           => $show_only_industries,
            'industries_count'          => count($industries),
            'industries_selected_count' => count($selected_industries),
            'industries_select_all'     => $enable_select_all,
            'industries_selected'       => $selected_industries,
            'categories'                => $categories,
            'categories_selected_by_id' => $selected_categories,
            'industries_required'       => $required,
            'industries_top'            => $industries_top,
            'show_maxindustry_text'     => $show_maxindustry_text,
            'show_disclaimer'           => $show_disclaimer,
            'disclaimer_text'           => $disclaimer_text,
            'input_suffix'              => $input_suffix,
            'input_placeholder'         => $input_placeholder,
            'dispatchDynamicFragment'   => $dispatchDynamicFragment,
        ];

        $data['selected_cat_json'] = [];
        $data['selected_categories_array'] = [];

        if (!empty($data['categories_selected_by_id'])) {
            foreach ($data['categories_selected_by_id'] as $selected_categories_item) {
                if (isset($data['selected_categories_array'][$selected_categories_item['parent']])) {
                    ++$data['selected_categories_array'][$selected_categories_item['parent']];
                } else {
                    $data['selected_categories_array'][$selected_categories_item['parent']] = 1;
                }

                $data['selected_cat_json'][$selected_categories_item['parent']][$selected_categories_item['category_id']] = (int) $selected_categories_item['category_id'];
            }
        } elseif (!empty($data['industries_selected'])) {
            foreach ($data['industries_selected'] as $industries_selected_item) {
                $data['selected_cat_json'][$industries_selected_item['category_id']] = [];
            }
        }

        $data['selected_cat_json'] = json_encode($data['selected_cat_json'], JSON_FORCE_OBJECT);
        $html = 'new/multiple_epselect_view';

        views()->display($html, $data);
    }
}

/**
 * Helper function used in widgetGetPopups widget only, to filter popups.
 *
 * @param array $popups - list of popups from database
 *
 * @return array - the array with filtered popups list
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetFilterCurrentPopups
 */
function widgetFilterCurrentPopups($popups): array
{
    if (empty($popups)) {
        return [];
    }

    foreach ($popups as $key => $popup) {
        switch ($popup['popup_hash']) {
            case 'feedback_certification':
                if (!have_right('upgrade_group')) {
                    unset($popups[$key]);
                }

                break;
            case 'add_item':
                if (!have_right('manage_personal_items')) {
                    unset($popups[$key]);
                }

                break;
            case 'bulk_upload_items_promotion':
                if (!have_right('manage_personal_items')) {
                    unset($popups[$key]);
                }

                break;
            case 'subscribe':
                try {
                    // Get current date
                    $currentDate = new DateTimeImmutable();
                    // Get giveaway start date
                    $startDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_start_datetime'))->timezone($currentDate->getTimezone());
                    // Get giveaway start date
                    $endDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_end_datetime'))->timezone($currentDate->getTimezone());
                    $isStarted = $currentDate >= $startDate;
                    $isEnded = $currentDate >= $endDate;
                } catch (InvalidFormatException $e) {
                    $isEnded = false;
                    $isStarted = false;
                }

                // Do not show popup if giveaway started and not yet ended.
                if ($isStarted && !$isEnded) {
                    unset($popups[$key]);

                    continue 2;
                }

                $options = [
                    'status'             => session()->status,
                    'userIsSubscribed'   => isSubscribedUser(),
                ];

                if ((isset($options['status']) && 'active' !== $options['status']) || $options['userIsSubscribed']) {
                    unset($popups[$key]);
                }

                break;
            case 'giveaway_contest':
                try {
                    // Get current date
                    $currentDate = new DateTimeImmutable();
                    // Get giveaway start date
                    $startDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_start_datetime'))->timezone($currentDate->getTimezone());
                    // Get giveaway start date
                    $endDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_end_datetime'))->timezone($currentDate->getTimezone());
                    $isStarted = $currentDate >= $startDate;
                    $isEnded = $currentDate >= $endDate;
                } catch (InvalidFormatException $e) {
                    $isEnded = false;
                    $isStarted = false;
                }

                // Do not show popup if giveaway not started or already ended.
                if (!$isStarted || $isEnded) {
                    unset($popups[$key]);
                }

                break;
            case 'items_more_visible':
                if (!verifyNeedCertifyUpgrade()) {
                    unset($popups[$key]);
                }

                break;
            case 'upgrade_account_now':
                if (!verifyNeedCertifyUpgrade()) {
                    unset($popups[$key]);
                }

                break;
            case 'certification_upgrade':
                if (!group_expired_session()) {
                    unset($popups[$key]);
                }

                break;
            case 'certification_expire_soon':
                if (getCertificationExpireSoon() <= 0) {
                    unset($popups[$key]);
                } else {
                    clearCertificationExpireSoon();
                }

                break;
            case 'show_preactivation':
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);
                $user = $userModel->getSimpleUser(privileged_user_id());

                /** @var Complete_Profile_Model $completeProfileModel */
                $completeProfileModel = model(Complete_Profile_Model::class);
                $profileCompletion = $completeProfileModel->get_user_profile_options(privileged_user_id());
                $profileCompletion = array_filter($profileCompletion, function ($profile_option) {
                    return 1 !== (int) $profile_option['option_completed'];
                });

                if (!empty($profileCompletion)) {
                    unset($popups[$key]);

                    continue 2;
                }

                if ('pending' != $user['status']) {
                    unset($popups[$key]);

                    continue 2;
                }

                break;
            case 'complete_profile':
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);
                $user = $userModel->getSimpleUser(privileged_user_id());

                /** @var Complete_Profile_Model $completeProfileModel */
                $completeProfileModel = model(Complete_Profile_Model::class);
                $profileCompletion = $completeProfileModel->get_user_profile_options(privileged_user_id());
                $profileCompletion = array_filter($profileCompletion, function ($profile_option) {
                    return 1 !== (int) $profile_option['option_completed'];
                });

                if (empty($profileCompletion)) {
                    unset($popups[$key]);

                    continue 2;
                }

                /** @var Pages_Model $pagesModel */
                $pagesModel = model(Pages_Model::class);
                $publicPages = $pagesModel->get_pages([
                    'columns'    => ['page_hash'],
                    'conditions' => ['is_public' => true],
                ]);

                if (!empty($publicPages)) {
                    $popups[$key]['not_pages'] = array_merge($popups[$key]['not_pages'], array_column($publicPages, 'page_hash'));

                    continue 2;
                }

            break;
            case 'bulk_upload_items_promotion':
                if (!have_right('manage_personal_items')) {
                    unset($popups[$key]);
                }

            break;
            case 'event_promotion':
                // Do not show for users in administration groups or staff
                if (
                    logged_in()
                    && (
                        userGroupType()->isAdministration() || userGroupType()->isStaff()
                    )
                ) {
                    unset($popups[$key]);

                    continue 2;
                }

                /** @var Ep_Events_Model $eventsRepository */
                $eventsRepository = model(Ep_Events_Model::class);
                if (null === ($promotedEvent = $eventsRepository->findCurrentPromotedEvent())) {
                    unset($popups[$key]);

                    continue 2;
                }

                // Transform show date into proper datetime object
                /** @var null|Carbon */
                $showDate = $popups[$key]['show_date'] ?? null;
                if (null !== $showDate) {
                    $showDate = Carbon::createFromFormat('Y-m-d H:i:s', $showDate);
                    if (!$showDate) {
                        try {
                            $showDate = Carbon::create($showDate);
                        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                            $showDate = null;
                        }
                    }
                }

                if (null !== $showDate && $showDate->isBetween($promotedEvent['promotion_start_date'], $promotedEvent['promotion_end_date'], false)) {
                    unset($popups[$key]);

                    continue 2;
                }

                break;
            case 'account_restricted':
                if ('restricted' !== session()->status) {
                    unset($popups[$key]);
                }

            break;
            case 'terms_of_use_updated':
                if ('Seller' !== (string) userGroupType()) {
                    unset($popups[$key]);
                }
            break;
        }
    }

    return array_values($popups);
}

/**
 * Widget that writes the list of popups to show to current user (logged or not) in the session.
 *
 * @param bool $update - to update in session even if the popups are already set or not (by default no)
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetGetPopups
 */
function widgetGetPopups($update = false)
{
    if (session()->__isset('popups') && !$update) {
        return;
    }

    /** @var User_Popups_Model $userPopups */
    $userPopups = model(User_Popups_Model::class);

    session()->__set(
        'popups',
        widgetFilterCurrentPopups(
            logged_in()
                ? $userPopups->getPopupsForLogged(id_session())
                : $userPopups->getPopupsForNotLogged(getEpClientIdCookieValue())
        )
    );
}

/**
 * Removes popup from session to not show again during current session.
 *
 * @param string $popupName - the name of the popup
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetPopupsSystemRemoveOneItem
 */
function widgetPopupsSystemRemoveOneItem($popupName = '')
{
    if (empty($popupName)) {
        return true;
    }

    $newPopups = array_filter(session()->popups, function ($item) use ($popupName) {
        return $item['popup_hash'] !== $popupName ? $item : false;
    });

    session()->popups = $newPopups;
}

if (!function_exists('widgetShowBanner')) {
    /**
     * Shows promo banner.
     *
     * @param string $alias             - the name of the banner
     * @param string $mainClass         - the name of the main class
     * @param string $bannerClass       - the class banner or wr modifier
     * @param bool   $firstContentPaint - enable/disable FCT
     *
     * @return bool|string - if banner exists method returns the view
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetShowBanner
     */
    function widgetShowBanner(
        $alias,
        $bannerClass = '',
        $mainClass = 'promo-banner',
        $firstContentPaint = false
    ) {
        /** @var Promo_Banners_Model $promoBannersRepository */
        $promoBannersRepository = model(Promo_Banners_Model::class);

        $joins = ['pagePosition'];
        $conditions = [
            'pageAlias' => $alias,
            'isVisible' => 1,
        ];
        $order = ['order_banner'];
        $data = [
            'banners'           => $promoBannersRepository->get_banners(compact('conditions', 'joins', 'order')),
            'bannerClass'       => $bannerClass,
            'mainClass'         => $mainClass,
            'firstContentPaint' => $firstContentPaint,
        ];

        if (!empty($data['banners'])) {
            views()->assign($data);

            return views()->display('new/promo_banners/banners_view');
        }

        return false;
    }
}

if (!function_exists('widgetLocationBlock')) {
    /**
     * @param ParameterBag|null $address
     * @param ParameterBag|null $components
     * @param ParameterBag|null $selectors
     * @param ParameterBag|null $texts
     * @param string|null $template
     *
     * @return void
     */
    function widgetLocationBlock(
        ?ParameterBag $address = null,
        ?ParameterBag $components = null,
        ?ParameterBag $selectors = null,
        ?ParameterBag $texts = null,
        ?string $template = null
    ): void {
        $texts = $texts ?? new ParameterBag();
        $address = $address ?? new ParameterBag();
        $selectors = $selectors ?? new ParameterBag();
        $components = $components ?? new ParameterBag();

        views()->display('new/location_selector_view', [
            'template'           => $template,
            'saved_address'      => $address->get('saved') ?? null,
            'overrided_address'  => $address->get('overrided') ?? null,
            'overrided_location' => $address->get('overrided_location') ?? null,
            'enable_postal_code' => $components->get('postal_code') ?? false,
            'enable_address'     => $components->get('address') ?? false,
            'wrapper'            => $selectors->get('wrapper') ?? null,
            'input'              => $selectors->get('input') ?? null,
            'type'               => $selectors->get('address_type') ?? null,
            'texts'              => array_merge(
                [
                    'modal_title'                         => 'Choose location',
                    'saved_location_label_text'           => 'Use my saved address:',
                    'overrided_location_label_text'       => 'Provide a different address:',
                    'overrided_location_placeholder_text' => 'Street, Country, Region/State, City, Zip/Postal Code',
                ],
                $texts->all()
            ),
        ]);
    }
}

if (!function_exists('widgetSearchAutocomplete')) {
    /**
     * @param int|null $searchType
     * @param string|null $searchUrl
     * @param string $classPrefix
     *
     * @return void
     */
    function widgetSearchAutocomplete(
        ?int $searchType = null,
        ?string $searchUrl = null,
        string $classPrefix = 'js-search-autocomplete'
    ): void {
        $clearAll = true;
        /** @var TinyMVC_Library_Search_Autocomplete $autocomplete */
        $autocomplete = library(TinyMVC_Library_Search_Autocomplete::class);

        $searchRecords = $autocomplete->getAutocompleteRecords($searchType)->toArray();
        $searchTypeMeta = Autocomplete\TYPES[$searchType] ?? null;
        $searchRecordsHash = $autocomplete->getRecordsHash($searchType);
        if ($autocomplete->isRenewalRequired($searchType)) {
            $autocomplete->finishRenewal($searchType);
        }

        echo dispatchDynamicFragment(
            'autocomplete:boot',
            [
                [
                    'type'        => $searchTypeMeta[0] ?? null,
                    'clear'       => (int) ($clearAll ?? false),
                    'records'     => array_values($searchRecords ?? []),
                    'recordsHash' => $searchRecordsHash ?? null,
                    'url'         => $searchUrl ?? null,
                    'selectors'   => [
                        'form'                => '.' . $classPrefix . '-form',
                        'input'               => '.' . $classPrefix . '-field',
                        'wrapper'             => '.' . $classPrefix . '-wrapper',
                        'container'           => '.' . $classPrefix . '-container',
                        'recentSearchList'    => '.' . $classPrefix . '-recent-search-list',
                        'recentSearchOption'  => '.' . $classPrefix . '-recent-search-option',
                        'suggestionsList'     => '.' . $classPrefix . '-suggestions-list',
                        'suggestionsListItem' => '.' . $classPrefix . '-suggestions-list-item',
                        'suggestionsOption'   => '.' . $classPrefix . '-suggestions-option',
                        'resetBtn'            => '.' . $classPrefix . '-reset-btn',
                    ],
                ],
            ],
            true
        );
    }
}

if (!function_exists('widgetGetSvgIcon')) {
    /**
     * @param string $name
     * @param int $width
     * @param int $height
     * @param string $classes
     *
     * @return string
     */
    function widgetGetSvgIcon(
        string $name = null,
        int $width = 23,
        int $height = 23,
        string $classes = ''
    ): string
    {
        if (is_null($name)) {
            return '';
        }

        switch ($name) {
            case 'eye':
                $svg = '<g transform="translate(-23.286 -209.455)"><path d="M529.564,352.465a23.772,23.772,0,0,1,0,30.94c-33.988,39.865-133.2,143.01-250.278,143.01S62.972,423.27,29.009,383.405a23.772,23.772,0,0,1,0-30.94c33.964-39.864,133.184-143.01,250.277-143.01S495.576,312.6,529.564,352.465ZM279.286,489.843c95.467,0,181.984-84.8,215.217-121.908-33.233-37.108-119.75-121.907-215.217-121.907S97.314,330.827,64.07,367.935C97.3,405.044,183.808,489.843,279.286,489.843Zm0-79.24a42.668,42.668,0,1,1,42.668-42.668A42.714,42.714,0,0,1,279.286,410.6Zm0-121.908a79.241,79.241,0,1,0,79.241,79.24,79.338,79.338,0,0,0-79.241-79.24" transform="translate(0 0)"/></g>';

            break;
            case 'basket-remove':
                $svg = '<g transform="translate(-128.24 -41.999)"><path d="M474.953,114.842V42H285.778v72.843H128.24v39.322h47.131L208.946,590.57h344.13l33.575-436.406H640.24V114.842ZM436.212,80.677V114.2l-111.051,0V80.673ZM244.455,550.6,214.109,153.519H546.618L515.628,550.6Z" transform="translate(0 0)"/><path d="M431.947,475.075l-39.325-2.131,15.542-285.785,39.326,2.131Z" transform="translate(18.31 21.934)"/><path d="M269.1,475.074,253.591,189.289l39.326-2.126,15.506,285.785Z" transform="translate(37.393 21.935)"/></g>';

            break;
            case 'arrow-prev':
                $svg = '<g transform="translate(0 512.001) rotate(-90)"><path d="M256,0a36.589,36.589,0,0,0-25.37,10.055L10.49,220.746a33.284,33.284,0,0,0,0,48.506,37.241,37.241,0,0,0,50.694,0L256,83.048l194.817,186.2a37.241,37.241,0,0,0,50.694,0,33.284,33.284,0,0,0,0-48.506L281.371,10.055A36.589,36.589,0,0,0,256,0Z" transform="translate(0 0)"/></g>';

            break;
            case 'arrow-next':
                $svg = '<g transform="translate(-0.002 512.001) rotate(-90)"><path d="M256,279.211a36.589,36.589,0,0,1-25.37-10.055L10.49,58.465a33.284,33.284,0,0,1,0-48.506,37.241,37.241,0,0,1,50.694,0L256,196.164,450.817,9.959a37.241,37.241,0,0,1,50.694,0,33.284,33.284,0,0,1,0,48.506L281.371,269.156A36.589,36.589,0,0,1,256,279.211Z" transform="translate(0 0)"/></g>';

            break;
            case 'bell-simple':
                $svg = '<path d="M548.461,619.245a88.218,88.218,0,0,1-176.327,0H264.167a59.8,59.8,0,0,1,0-119.592V380.062A199.284,199.284,0,0,1,391.7,193.277v-2.624a68.687,68.687,0,1,1,137.337,0v2.624A199.283,199.283,0,0,1,656.574,380.062V499.653a59.8,59.8,0,1,1,0,119.592Zm-39.354,0H411.488a48.973,48.973,0,0,0,97.619,0ZM416.772,226.8a159.165,159.165,0,0,0-113.36,153.042V499.434A39.573,39.573,0,0,1,264.2,539.3a19.932,19.932,0,0,0,0,39.864H656.428a19.932,19.932,0,0,0,0-39.864,39.5,39.5,0,0,1-39.208-39.864V379.843A159.165,159.165,0,0,0,503.859,226.8l-14.175-4.154V190.617a29.443,29.443,0,1,0-58.849,0v32.029Z" transform="translate(-204.371 -120.379)"/>';

            break;
            case 'remove':
                $svg = '<g transform="translate(0.021 0.021)"><path d="M26.383,512A26.37,26.37,0,0,1,7.767,466.983L467.421,7.314a26.37,26.37,0,0,1,37.266,37.3L45.067,504.25A26.438,26.438,0,0,1,26.382,512Zm0,0" transform="translate(-0.039 0.001)"/><path d="M485.73,512a26.108,26.108,0,0,1-18.576-7.7L8.439,45.589A26.314,26.314,0,1,1,45.626,8.4L504.306,467.077A26.315,26.315,0,0,1,485.73,512Zm0,0" transform="translate(-0.039 0)"/></g>';

            break;
            case 'calendar-simple':
                $svg = '<g transform="translate(-1159 -303)"><path d="m1634.4 351.76h-36.574v-36.574a12.19 12.19 0 1 0-24.379 0v36.574h-146.29v-36.574a12.19 12.19 0 1 0-24.379 0v36.574h-146.29v-36.574a12.19 12.19 0 1 0-24.379 0v36.574h-36.573a36.566 36.566 0 0 0-36.574 36.57v390.1a36.566 36.566 0 0 0 36.574 36.566h438.86a36.566 36.566 0 0 0 36.565-36.567v-390.1a36.566 36.566 0 0 0-36.569-36.569zm-438.86 24.379h36.574v12.189a12.19 12.19 0 0 0 24.379 0v-12.189h146.29v12.189a12.19 12.19 0 0 0 24.379 0v-12.189h146.29v12.189a12.19 12.19 0 1 0 24.379 0v-12.189h36.574a12.194 12.194 0 0 1 12.19 12.19v60.953h-463.24v-60.953a12.192 12.192 0 0 1 12.189-12.189zm438.86 414.48h-438.86a12.192 12.192 0 0 1-12.189-12.189v-304.76h463.24v304.76a12.194 12.194 0 0 1-12.189 12.19z" /><path transform="translate(1044.2 258.2)" d="m200.14 44.8a12.189 12.189 0 0 0-12.189 12.19v36.574h-36.574a36.566 36.566 0 0 0-36.574 36.57v390.1a36.566 36.566 0 0 0 36.574 36.566h438.86a36.566 36.566 0 0 0 36.565-36.567v-390.1a36.566 36.566 0 0 0-36.569-36.569h-36.573v-36.574a12.19 12.19 0 1 0-24.379 0v36.574h-146.29v-36.574a12.19 12.19 0 1 0-24.379 0v36.574h-146.29v-36.574a12.186 12.186 0 0 0-12.189-12.19m-48.765 73.142h36.574v12.19a12.19 12.19 0 0 0 24.379 0v-12.19h146.29v12.19a12.19 12.19 0 0 0 24.379 0v-12.19h146.29v12.19a12.19 12.19 0 1 0 24.379 0v-12.19h36.574a12.194 12.194 0 0 1 12.19 12.19v60.953h-463.24v-60.952a12.192 12.192 0 0 1 12.189-12.19m438.86 414.48h-438.87a12.192 12.192 0 0 1-12.189-12.189v-304.76h463.24v304.76a12.194 12.194 0 0 1-12.189 12.19m-390.1-487.77a12.336 12.336 0 0 1 12.339 12.34v36.423h145.99v-36.424a12.34 12.34 0 1 1 24.679 0v36.424h145.99v-36.424a12.34 12.34 0 1 1 24.679 0v36.424h36.424a36.718 36.718 0 0 1 36.722 36.719v390.1a36.718 36.718 0 0 1-36.719 36.719h-438.86a36.718 36.718 0 0 1-36.719-36.719v-390.1a36.718 36.718 0 0 1 36.719-36.719h36.426v-36.424a12.339 12.339 0 0 1 12.339-12.34zm-12.337 73.442h-36.42a12.053 12.053 0 0 0-12.039 12.04v60.8h462.94v-60.8a12.106 12.106 0 0 0-3.528-8.512 11.959 11.959 0 0 0-8.512-3.528h-36.424v12.04a12.339 12.339 0 0 1-24.678 0.15v-12.19h-145.99v12.04a12.34 12.34 0 0 1-24.678 0.15v-12.19h-145.99v12.04a12.34 12.34 0 1 1-24.679 0zm414.48 97.526h-462.95v304.62a12.053 12.053 0 0 0 12.039 12.039h438.87a12.044 12.044 0 0 0 12.032-12.04z" fill="#000" /></g>';

            break;
            case 'calendar':
                $svg = '<g transform="translate(-114.654 -44.651)"><path d="M 590.235107421875 556.8770141601562 L 151.3735809326172 556.8770141601562 C 146.5368804931641 556.8770141601562 141.8186950683594 555.938720703125 137.3501434326172 554.088134765625 C 132.8808288574219 552.2373046875 128.8807678222656 549.5648193359375 125.4611358642578 546.1449584960938 C 122.041389465332 542.7250366210938 119.3689498901367 538.7249755859375 117.518196105957 534.2559814453125 C 115.6675720214844 529.787353515625 114.7292022705078 525.0692138671875 114.7292022705078 520.2326049804688 L 114.7292022705078 130.1336975097656 C 114.7292022705078 125.2970809936523 115.6675109863281 120.5789489746094 117.5180740356445 116.1103897094727 C 119.3689498901367 111.6411437988281 122.041389465332 107.6411437988281 125.4611358642578 104.2213897705078 C 128.8811340332031 100.8015747070312 132.8811950683594 98.12914276123047 137.3502655029297 96.27832794189453 C 141.8188171386719 94.42770385742188 146.5369567871094 93.48932647705078 151.3735809326172 93.48932647705078 L 187.8725128173828 93.48932647705078 L 187.8725128173828 56.99051666259766 C 187.8725128173828 53.71376419067383 189.1484527587891 50.63351440429688 191.4652557373047 48.31720352172852 C 193.7816314697266 46.00132751464844 196.861328125 44.72589111328125 200.1369476318359 44.72589111328125 C 203.4141998291016 44.72589111328125 206.494384765625 46.00126647949219 208.8101959228516 48.31707763671875 C 211.1260681152344 50.63295364379883 212.4014434814453 53.71320343017578 212.4014434814453 56.99051666259766 L 212.4014434814453 93.48932647705078 L 358.5385131835938 93.48932647705078 L 358.5385131835938 56.99051666259766 C 358.5385131835938 53.71332931518555 359.8138732910156 50.63307952880859 362.1297607421875 48.31720352172852 C 364.4455871582031 46.00132751464844 367.5257568359375 44.72589111328125 370.8029479980469 44.72589111328125 C 374.0802001953125 44.72589111328125 377.160400390625 46.00126647949219 379.4761962890625 48.31707763671875 C 381.7920837402344 50.63295364379883 383.0674438476562 53.71320343017578 383.0674438476562 56.99051666259766 L 383.0674438476562 93.48932647705078 L 529.20458984375 93.48932647705078 L 529.20458984375 56.99051666259766 C 529.20458984375 53.71332931518555 530.4800415039062 50.63307952880859 532.7958984375 48.31720352172852 C 535.1117553710938 46.00132751464844 538.19189453125 44.72589111328125 541.468994140625 44.72589111328125 C 544.7447509765625 44.72589111328125 547.8245239257812 46.00126647949219 550.140869140625 48.31707763671875 C 552.457763671875 50.63338851928711 553.7337036132812 53.71363830566406 553.7337036132812 56.99051666259766 L 553.7337036132812 93.48932647705078 L 590.232421875 93.48932647705078 C 595.0715942382812 93.48932647705078 599.78955078125 94.42764282226562 604.2581787109375 96.27826690673828 C 608.7272338867188 98.12907409667969 612.7274780273438 100.8015747070312 616.1475219726562 104.2213897705078 C 619.5671997070312 107.6413879394531 622.239501953125 111.6413879394531 624.0902709960938 116.1103897094727 C 625.9408569335938 120.5790176391602 626.8792114257812 125.2971420288086 626.8792114257812 130.1336975097656 L 626.8792114257812 520.2326049804688 C 626.8792114257812 525.0692749023438 625.9409790039062 529.7874755859375 624.0904541015625 534.2559814453125 C 622.2396850585938 538.7251586914062 619.5673217773438 542.7252197265625 616.1475830078125 546.14501953125 C 612.7271728515625 549.5648193359375 608.72705078125 552.2372436523438 604.2582397460938 554.0880126953125 C 599.7897338867188 555.9386596679688 595.0717163085938 556.8770141601562 590.235107421875 556.8770141601562 Z M 139.2568817138672 215.5442657470703 L 139.2568817138672 520.2340087890625 C 139.2568817138672 526.9139404296875 144.6913909912109 532.3484497070312 151.3713226318359 532.3484497070312 L 590.232421875 532.3484497070312 L 590.2366943359375 532.3484497070312 L 590.23876953125 532.3486938476562 C 593.4718017578125 532.3475952148438 596.511962890625 531.087158203125 598.7996826171875 528.7991943359375 C 601.0886840820312 526.5103149414062 602.3493041992188 523.4684448242188 602.3493041992188 520.2340087890625 L 602.3493041992188 215.5442657470703 L 139.2568817138672 215.5442657470703 Z M 139.2610778808594 191.0110778808594 L 602.3536987304688 191.0110778808594 L 602.3536987304688 130.1330718994141 C 602.3536987304688 126.9410781860352 601.0598754882812 123.819206237793 598.803955078125 121.5678253173828 C 596.51513671875 119.2789535522461 593.473388671875 118.0183868408203 590.239013671875 118.0183868408203 L 553.7395629882812 118.0183868408203 L 553.7395629882812 130.1328277587891 C 553.7395629882812 133.4095153808594 552.4636840820312 136.4897613525391 550.1469116210938 138.8060150146484 C 547.83056640625 141.1218872070312 544.7509765625 142.3972625732422 541.475341796875 142.3972625732422 C 538.1981811523438 142.3972625732422 535.117919921875 141.1218872070312 532.8020629882812 138.8061370849609 C 530.5037231445312 136.5079345703125 529.2301635742188 133.4570007324219 529.2108764648438 130.2078247070312 L 529.2106323242188 130.2078247070312 L 529.2062377929688 130.1328277587891 L 529.2062377929688 118.0183868408203 L 383.0696411132812 118.0183868408203 L 383.0696411132812 130.1328277587891 C 383.0696411132812 133.4099578857422 381.794189453125 136.4901428222656 379.4783325195312 138.8060150146484 C 377.1623840332031 141.1218872070312 374.0821228027344 142.3972625732422 370.8049621582031 142.3972625732422 C 367.52783203125 142.3972625732422 364.4476318359375 141.1218872070312 362.1317749023438 138.8061370849609 C 359.8335571289062 136.5079345703125 358.5599670410156 133.4570007324219 358.5406799316406 130.2078247070312 L 358.5404357910156 130.2078247070312 L 358.5363159179688 130.1328277587891 L 358.5363159179688 118.0183868408203 L 212.3992004394531 118.0183868408203 L 212.3992004394531 130.1328277587891 C 212.3992004394531 133.4100189208984 211.1238250732422 136.4902038574219 208.8080749511719 138.8060150146484 C 206.4922637939453 141.1218872070312 203.4120178222656 142.3972625732422 200.134765625 142.3972625732422 C 196.8591918945312 142.3972625732422 193.7795715332031 141.1218872070312 191.4631958007812 138.8061370849609 C 189.1463317871094 136.4898223876953 187.8703308105469 133.4095764160156 187.8703308105469 130.1328277587891 L 187.8703308105469 118.0183868408203 L 151.3755187988281 118.0183868408203 L 151.3755187988281 118.0186386108398 C 144.6955718994141 118.0186386108398 139.2610778808594 123.4531402587891 139.2610778808594 130.1330718994141 L 139.2610778808594 191.0110778808594 Z M 370.8017578125 483.7362670898438 C 367.5250244140625 483.7362670898438 364.44482421875 482.4603271484375 362.1285095214844 480.1434631347656 C 359.8127136230469 477.8270263671875 358.5373229980469 474.747314453125 358.5373229980469 471.4717102050781 L 358.5373229980469 386.2105102539062 L 273.27587890625 386.2105102539062 C 270.0002746582031 386.2105102539062 266.9205627441406 384.9351501464844 264.6041870117188 382.6192626953125 C 262.2873840332031 380.3029479980469 261.0114440917969 377.2227783203125 261.0114440917969 373.9460754394531 C 261.0114440917969 367.1833190917969 266.5132751464844 361.6814575195312 273.27587890625 361.6814575195312 L 358.5373229980469 361.6814575195312 L 358.5373229980469 276.4244384765625 C 358.5373229980469 273.1469421386719 359.8127136230469 270.0659484863281 362.1285095214844 267.7490234375 C 364.444580078125 265.4318237304688 367.5248107910156 264.1557006835938 370.8017578125 264.1557006835938 C 374.0787048339844 264.1557006835938 377.1588745117188 265.431884765625 379.4750061035156 267.7491455078125 C 381.7908325195312 270.0660705566406 383.0661926269531 273.1470031738281 383.0661926269531 276.4244384765625 L 383.0661926269531 361.6814575195312 L 468.327392578125 361.6814575195312 C 469.9454345703125 361.6814575195312 471.5240173339844 361.9955749511719 473.0191345214844 362.6151428222656 C 474.5145263671875 363.2347717285156 475.8534545898438 364.1295776367188 476.9988098144531 365.2748413085938 C 478.1438293457031 366.4176330566406 479.0386962890625 367.7559509277344 479.65869140625 369.2527160644531 C 480.2781677246094 370.7482299804688 480.59228515625 372.3271789550781 480.5923156738281 373.9458312988281 L 480.5923156738281 373.9458312988281 C 480.5923156738281 373.9459228515625 480.5923156738281 373.946044921875 480.5923156738281 373.9461364746094 C 480.5923156738281 375.5660705566406 480.2781982421875 377.145751953125 479.65869140625 378.6413269042969 C 479.0391845703125 380.1369018554688 478.1443176269531 381.4759521484375 476.9988098144531 382.621337890625 C 475.8531494140625 383.7648315429688 474.5141296386719 384.658447265625 473.0191345214844 385.2774047851562 C 471.5234985351562 385.8965759277344 469.9450073242188 386.2105102539062 468.327392578125 386.2105102539062 L 383.0661926269531 386.2105102539062 L 383.0661926269531 471.4717102050781 C 383.0661926269531 474.7473754882812 381.7908325195312 477.8270874023438 379.4750061035156 480.1434631347656 C 377.15869140625 482.4603271484375 374.0785217285156 483.7362670898438 370.8017578125 483.7362670898438 Z" stroke="none"/><path d="M 200.136962890625 44.8009033203125 C 193.406494140625 44.8009033203125 187.947509765625 50.25579833984375 187.947509765625 56.990478515625 L 187.947509765625 93.5643310546875 L 151.37353515625 93.5643310546875 C 141.6752319335938 93.5643310546875 132.37255859375 97.4163818359375 125.51416015625 104.2743835449219 C 118.656005859375 111.1324462890625 114.80419921875 120.4356384277344 114.80419921875 130.1336364746094 L 114.80419921875 520.2325439453125 C 114.80419921875 529.930908203125 118.65625 539.233642578125 125.51416015625 546.0919189453125 C 132.3720703125 552.9503173828125 141.675537109375 556.802001953125 151.37353515625 556.802001953125 L 590.235107421875 556.802001953125 C 599.933349609375 556.802001953125 609.235595703125 552.949951171875 616.094482421875 546.0919189453125 C 622.95263671875 539.2337646484375 626.80419921875 529.9307861328125 626.80419921875 520.2325439453125 L 626.80419921875 130.1336364746094 C 626.80419921875 120.4353942871094 622.9521484375 111.1326904296875 616.094482421875 104.2743835449219 C 609.23583984375 97.416259765625 599.932861328125 93.5643310546875 590.235107421875 93.5643310546875 L 553.65869140625 93.5643310546875 L 553.65869140625 56.990478515625 C 553.65869140625 50.25555419921875 548.19970703125 44.8009033203125 541.468994140625 44.8009033203125 C 534.734619140625 44.8009033203125 529.279541015625 50.25579833984375 529.279541015625 56.990478515625 L 529.279541015625 93.5643310546875 L 382.9924621582031 93.5643310546875 L 382.9924621582031 56.990478515625 C 382.9924621582031 50.25555419921875 377.5375671386719 44.8009033203125 370.8029479980469 44.8009033203125 C 364.0683898925781 44.8009033203125 358.613525390625 50.25579833984375 358.613525390625 56.990478515625 L 358.613525390625 93.5643310546875 L 212.326416015625 93.5643310546875 L 212.326416015625 56.990478515625 C 212.326416015625 50.25555419921875 206.87158203125 44.8009033203125 200.136962890625 44.8009033203125 M 151.371337890625 117.9433288574219 L 187.9453125 117.9433288574219 L 187.9453125 130.1328125 C 187.9453125 136.8675231933594 193.404541015625 142.322265625 200.134765625 142.322265625 C 206.86962890625 142.322265625 212.32421875 136.8673400878906 212.32421875 130.1328125 L 212.32421875 117.9433288574219 L 358.611328125 117.9433288574219 L 358.611328125 130.1328125 L 358.6154479980469 130.1328125 C 358.6154479980469 136.8675231933594 364.0705871582031 142.322265625 370.8049621582031 142.322265625 C 377.5395812988281 142.322265625 382.99462890625 136.8673400878906 382.99462890625 130.1328125 L 382.99462890625 117.9433288574219 L 529.28125 117.9433288574219 L 529.28125 130.1328125 L 529.28564453125 130.1328125 C 529.28564453125 136.8675231933594 534.740966796875 142.322265625 541.475341796875 142.322265625 C 548.205810546875 142.322265625 553.6644897460938 136.8673400878906 553.6644897460938 130.1328125 L 553.6644897460938 117.9433288574219 L 590.239013671875 117.9433288574219 C 593.47021484375 117.9433288574219 596.569580078125 119.2272644042969 598.85693359375 121.5147705078125 C 601.144775390625 123.7979431152344 602.4287109375 126.9015808105469 602.4287109375 130.1330871582031 L 602.4287109375 191.0860900878906 L 139.18603515625 191.0860900878906 L 139.18603515625 130.1330871582031 C 139.18603515625 123.402587890625 144.645263671875 117.9435729980469 151.3754272460938 117.9435729980469 L 151.371337890625 117.9433288574219 M 590.234619140625 532.4237060546875 L 590.232421875 532.4234619140625 L 151.371337890625 532.4234619140625 C 144.640869140625 532.4234619140625 139.181884765625 526.9642333984375 139.181884765625 520.2340087890625 L 139.181884765625 215.4692687988281 L 602.42431640625 215.4692687988281 L 602.42431640625 520.2340087890625 C 602.42431640625 523.46533203125 601.140380859375 526.564697265625 598.852783203125 528.8521728515625 C 596.5654296875 531.1397705078125 593.4658203125 532.4237060546875 590.234619140625 532.4237060546875 M 370.8017578125 264.230712890625 C 364.067138671875 264.230712890625 358.6123352050781 269.689697265625 358.6123352050781 276.4244384765625 L 358.6123352050781 361.7564697265625 L 273.27587890625 361.7564697265625 C 266.5456237792969 361.7564697265625 261.0864562988281 367.215576171875 261.0864562988281 373.9460144042969 C 261.0864562988281 380.6805114746094 266.5453796386719 386.135498046875 273.27587890625 386.135498046875 L 358.6123352050781 386.135498046875 L 358.6123352050781 471.4717102050781 C 358.6123352050781 478.2021484375 364.067138671875 483.6612548828125 370.8017578125 483.6612548828125 C 377.536376953125 483.6612548828125 382.9912109375 478.2022705078125 382.9912109375 471.4717102050781 L 382.9912109375 386.135498046875 L 468.327392578125 386.135498046875 C 471.5585632324219 386.135498046875 474.658203125 384.8514404296875 476.9458312988281 382.5682067871094 C 479.2333984375 380.2808837890625 480.517333984375 377.181396484375 480.517333984375 373.9461364746094 C 480.517333984375 370.7147216796875 479.2333984375 367.611083984375 476.9458312988281 365.327880859375 C 474.658203125 363.04052734375 471.5585632324219 361.7564697265625 468.327392578125 361.7564697265625 L 382.9912109375 361.7564697265625 L 382.9912109375 276.4244384765625 C 382.9912109375 269.68994140625 377.536376953125 264.230712890625 370.8017578125 264.230712890625 M 480.517333984375 373.9458312988281 C 480.517333984375 373.9458312988281 480.517333984375 373.9458312988281 480.517333984375 373.9461364746094 L 480.517333984375 373.9458312988281 M 200.136962890625 44.65087890625 C 203.4342041015625 44.65087890625 206.5332641601562 45.93408203125 208.8632202148438 48.2640380859375 C 211.1931762695312 50.593994140625 212.4763793945312 53.693115234375 212.4763793945312 56.990478515625 L 212.4763793945312 93.41433715820312 L 358.4635009765625 93.41433715820312 L 358.4635009765625 56.990478515625 C 358.4635009765625 53.69317626953125 359.7467041015625 50.5941162109375 362.0766906738281 48.26416015625 C 364.4067077636719 45.93408203125 367.5057678222656 44.65087890625 370.8029479980469 44.65087890625 C 374.1001892089844 44.65087890625 377.1992492675781 45.93408203125 379.5292663574219 48.2640380859375 C 381.8592529296875 50.593994140625 383.1424560546875 53.693115234375 383.1424560546875 56.990478515625 L 383.1424560546875 93.41433715820312 L 529.1295166015625 93.41433715820312 L 529.1295166015625 56.990478515625 C 529.1295166015625 53.6932373046875 530.4127807617188 50.59417724609375 532.7427978515625 48.26416015625 C 535.0728149414062 45.93408203125 538.171875 44.65087890625 541.468994140625 44.65087890625 C 544.7648315429688 44.65087890625 547.8634033203125 45.93408203125 550.1939086914062 48.2640380859375 C 552.5249633789062 50.594482421875 553.8087158203125 53.693603515625 553.8087158203125 56.990478515625 L 553.8087158203125 93.41433715820312 L 590.232421875 93.41433715820312 C 595.0814208984375 93.41433715820312 599.80908203125 94.35458374023438 604.2868041992188 96.20895385742188 C 608.7650756835938 98.06356811523438 612.7734375 100.7415161132812 616.2005004882812 104.1683349609375 C 619.627197265625 107.5953369140625 622.3049926757812 111.603515625 624.1595458984375 116.0816345214844 C 626.013916015625 120.5593872070312 626.9542236328125 125.2872009277344 626.9542236328125 130.1336364746094 L 626.9542236328125 520.2325439453125 C 626.9542236328125 525.0791625976562 626.0140380859375 529.8069458007812 624.1597900390625 534.28466796875 C 622.30517578125 538.762939453125 619.6273193359375 542.7711791992188 616.2005004882812 546.1979370117188 C 612.773193359375 549.6248168945312 608.7649536132812 552.302734375 604.2869873046875 554.1573486328125 C 599.8092651367188 556.0117797851562 595.08154296875 556.9520263671875 590.235107421875 556.9520263671875 L 151.37353515625 556.9520263671875 C 146.5269775390625 556.9520263671875 141.7991333007812 556.0117797851562 137.3214111328125 554.157470703125 C 132.8429565429688 552.3028564453125 128.834716796875 549.6248168945312 125.4080810546875 546.1979370117188 C 121.9813232421875 542.77099609375 119.303466796875 538.7627563476562 117.4489135742188 534.28466796875 C 115.5944213867188 529.806884765625 114.6541748046875 525.0791015625 114.6541748046875 520.2325439453125 L 114.6541748046875 130.1336364746094 C 114.6541748046875 125.2871398925781 115.5944213867188 120.5593872070312 117.4487915039062 116.0816345214844 C 119.3034057617188 111.6033325195312 121.9812622070312 107.5950927734375 125.4080810546875 104.1683349609375 C 128.8350830078125 100.7415161132812 132.8433837890625 98.06362915039062 137.321533203125 96.20901489257812 C 141.7992553710938 94.35458374023438 146.5270385742188 93.41433715820312 151.37353515625 93.41433715820312 L 187.7974853515625 93.41433715820312 L 187.7974853515625 56.990478515625 C 187.7974853515625 53.6937255859375 189.0812377929688 50.5946044921875 191.4122314453125 48.26416015625 C 193.7427978515625 45.93408203125 196.8412475585938 44.65087890625 200.136962890625 44.65087890625 Z M 187.7953491210938 118.0933227539062 L 151.3754272460938 118.0935668945312 C 144.7368774414062 118.0935668945312 139.3359985351562 123.4944458007812 139.3359985351562 130.1330871582031 L 139.3359985351562 190.9360656738281 L 602.2786865234375 190.9360656738281 L 602.2786865234375 130.1330871582031 C 602.2786865234375 126.9608764648438 600.992919921875 123.8583374023438 598.7509765625 121.6209411621094 C 596.4762573242188 119.3460693359375 593.4533081054688 118.0933227539062 590.239013671875 118.0933227539062 L 553.8145141601562 118.0933227539062 L 553.8145141601562 130.1328125 C 553.8145141601562 133.4295654296875 552.5308227539062 136.5286254882812 550.1998901367188 138.8590698242188 C 547.8694458007812 141.1890869140625 544.77099609375 142.4722595214844 541.475341796875 142.4722595214844 C 538.1781005859375 142.4722595214844 535.0791015625 141.1890869140625 532.7490234375 138.859130859375 C 530.4542846679688 136.5644836425781 529.1748657226562 133.5238342285156 529.1365356445312 130.2828369140625 L 529.1356201171875 130.2828369140625 L 529.1312255859375 130.1328125 L 529.1312255859375 118.0933227539062 L 383.1446228027344 118.0933227539062 L 383.1446228027344 130.1328125 C 383.1446228027344 133.4300231933594 381.8613891601562 136.5290222167969 379.5313110351562 138.8590698242188 C 377.2012634277344 141.1890869140625 374.1022033691406 142.4722595214844 370.8049621582031 142.4722595214844 C 367.5078125 142.4722595214844 364.4087524414062 141.1890869140625 362.0787658691406 138.859130859375 C 359.7841186523438 136.5645446777344 358.5046997070312 133.5238952636719 358.4663391113281 130.2828369140625 L 358.4654541015625 130.2828369140625 L 358.4613342285156 130.1328125 L 358.4613342285156 118.0933227539062 L 212.4741821289062 118.0933227539062 L 212.4741821289062 130.1328125 C 212.4741821289062 133.4300231933594 211.1911010742188 136.5290832519531 208.8611450195312 138.8590698242188 C 206.5311279296875 141.1890869140625 203.4320678710938 142.4722595214844 200.134765625 142.4722595214844 C 196.839111328125 142.4722595214844 193.74072265625 141.1890869140625 191.41015625 138.859130859375 C 189.0791015625 136.5286254882812 187.7953491210938 133.4295654296875 187.7953491210938 130.1328125 L 187.7953491210938 118.0933227539062 Z M 602.2742919921875 215.6192626953125 L 139.3319091796875 215.6192626953125 L 139.3319091796875 520.2340087890625 C 139.3319091796875 526.87255859375 144.7327880859375 532.2734375 151.371337890625 532.2734375 L 590.2428588867188 532.273681640625 C 593.454345703125 532.2715454101562 596.47412109375 531.0189208984375 598.7467041015625 528.74609375 C 601.021484375 526.471435546875 602.2742919921875 523.448486328125 602.2742919921875 520.2340087890625 L 602.2742919921875 215.6192626953125 Z M 370.8017578125 264.0806884765625 C 374.0986938476562 264.0806884765625 377.19775390625 265.3647155761719 379.528076171875 267.6960754394531 C 381.8580017089844 270.0271911621094 383.1412048339844 273.1270141601562 383.1412048339844 276.4244384765625 L 383.1412048339844 361.6064453125 L 468.327392578125 361.6064453125 C 469.955322265625 361.6064453125 471.5435791015625 361.9225158691406 473.0478820800781 362.5458374023438 C 474.5523986816406 363.1692504882812 475.8995056152344 364.069580078125 477.0518798828125 365.2218322753906 C 478.2038269042969 366.3715209960938 479.1041870117188 367.7180786132812 479.7279968261719 369.2239990234375 C 480.3512268066406 370.7286682128906 480.6672973632812 372.3173217773438 480.6673278808594 373.9458312988281 L 480.6673278808594 373.9461364746094 C 480.6673278808594 375.5760192871094 480.3512573242188 377.1653442382812 479.7279968261719 378.6700744628906 C 479.1047058105469 380.1747436523438 478.2043151855469 381.52197265625 477.0518798828125 382.6742553710938 C 475.8991394042969 383.8248291015625 474.552001953125 384.7239379882812 473.0478210449219 385.3466186523438 C 471.5430603027344 385.9696350097656 469.9548950195312 386.2855224609375 468.327392578125 386.2855224609375 L 383.1412048339844 386.2855224609375 L 383.1412048339844 471.4717102050781 C 383.1412048339844 474.7674560546875 381.8580017089844 477.865966796875 379.528076171875 480.196533203125 C 377.1975708007812 482.5275268554688 374.0985107421875 483.811279296875 370.8017578125 483.811279296875 C 367.5050048828125 483.811279296875 364.4059448242188 482.5275268554688 362.075439453125 480.1964416503906 C 359.7455139160156 477.8659057617188 358.4623107910156 474.7673950195312 358.4623107910156 471.4717102050781 L 358.4623107910156 386.2855224609375 L 273.27587890625 386.2855224609375 C 269.9801940917969 386.2855224609375 266.8817138671875 385.0023193359375 264.5512084960938 382.6722717285156 C 262.2201843261719 380.3418273925781 260.9364624023438 377.2427673339844 260.9364624023438 373.9460144042969 C 260.9364624023438 367.1419677734375 266.4718933105469 361.6064453125 273.27587890625 361.6064453125 L 358.4623107910156 361.6064453125 L 358.4623107910156 276.4244384765625 C 358.4623107910156 273.1268920898438 359.7455139160156 270.0270690917969 362.075439453125 267.6960144042969 C 364.4057006835938 265.3646545410156 367.5047607421875 264.0806884765625 370.8017578125 264.0806884765625 Z"/></g></g>';

            break;
            case 'calendar-success':
                $svg = '<g transform="translate(-114.654 -44.651)"><path d="M 590.235107421875 556.8771362304688 L 151.3735809326172 556.8771362304688 C 146.5369567871094 556.8771362304688 141.8188171386719 555.9388427734375 137.3502655029297 554.0882568359375 C 132.8810119628906 552.2373657226562 128.8810119628906 549.56494140625 125.4613265991211 546.1451416015625 C 122.0415115356445 542.7251586914062 119.369010925293 538.7251586914062 117.518196105957 534.256103515625 C 115.6675720214844 529.7875366210938 114.7292022705078 525.0693969726562 114.7292022705078 520.2328491210938 L 114.7292022705078 130.1339569091797 C 114.7292022705078 125.2973861694336 115.6675720214844 120.5792617797852 117.518196105957 116.1106338500977 C 119.3690719604492 111.6414489746094 122.0415115356445 107.6413879394531 125.4613265991211 104.2215118408203 C 128.8812561035156 100.8017654418945 132.8812561035156 98.12932586669922 137.3502655029297 96.27851104736328 C 141.8188934326172 94.42782592773438 146.5370178222656 93.48944854736328 151.3735809326172 93.48944854736328 L 187.8725128173828 93.48944854736328 L 187.8725128173828 56.99051284790039 C 187.8725128173828 53.71382522583008 189.1484527587891 50.63357543945312 191.4653167724609 48.3172607421875 C 193.7817687988281 46.00138854980469 196.8614501953125 44.72601318359375 200.1371917724609 44.72601318359375 C 203.4143218994141 44.72601318359375 206.4945068359375 46.00138854980469 208.8103942871094 48.3172607421875 C 211.1262664794922 50.63313674926758 212.4017028808594 53.71332550048828 212.4017028808594 56.99051284790039 L 212.4017028808594 93.48944854736328 L 358.5387573242188 93.48944854736328 L 358.5387573242188 56.99051284790039 C 358.5387573242188 53.71338653564453 359.814208984375 50.63320159912109 362.1300659179688 48.3172607421875 C 364.4459533691406 46.00144958496094 367.526123046875 44.72601318359375 370.8031921386719 44.72601318359375 C 374.0803833007812 44.72601318359375 377.1607055664062 46.00138854980469 379.4765625 48.3172607421875 C 381.7925109863281 50.63307571411133 383.0679626464844 53.71332550048828 383.0679626464844 56.99051284790039 L 383.0679626464844 93.48944854736328 L 529.2052612304688 93.48944854736328 L 529.2052612304688 56.99051284790039 C 529.2052612304688 53.71332550048828 530.4805908203125 50.63313674926758 532.7962646484375 48.3172607421875 C 535.1119995117188 46.00144958496094 538.1922607421875 44.72601318359375 541.469482421875 44.72601318359375 C 544.7449340820312 44.72601318359375 547.8246459960938 46.00138854980469 550.14111328125 48.3172607421875 C 552.4581298828125 50.63363647460938 553.7341918945312 53.71382522583008 553.7341918945312 56.99051284790039 L 553.7341918945312 93.48944854736328 L 590.2329711914062 93.48944854736328 C 595.0716552734375 93.48944854736328 599.7897338867188 94.42782592773438 604.2583618164062 96.27845001220703 C 608.7275390625 98.12926483154297 612.7276611328125 100.8016967773438 616.1477661132812 104.2215118408203 C 619.5674438476562 107.6415100097656 622.2396850585938 111.6415710449219 624.0903930664062 116.1105728149414 C 625.9408569335938 120.5791397094727 626.8792114257812 125.2972030639648 626.8792114257812 130.1338195800781 L 626.8792114257812 520.2328491210938 C 626.8792114257812 525.0694580078125 625.9409790039062 529.78759765625 624.0904541015625 534.2561645507812 C 622.23974609375 538.725341796875 619.5674438476562 542.7254028320312 616.1478271484375 546.1451416015625 C 612.7276000976562 549.5648803710938 608.7274780273438 552.2373046875 604.258544921875 554.0880737304688 C 599.7899169921875 555.9387817382812 595.07177734375 556.8771362304688 590.235107421875 556.8771362304688 Z M 139.2568817138672 215.5442657470703 L 139.2568817138672 520.234130859375 C 139.2568817138672 526.9140625 144.6913909912109 532.3485717773438 151.3713226318359 532.3485717773438 L 590.2329711914062 532.3485717773438 L 590.2384643554688 532.3485717773438 L 590.2401123046875 532.3488159179688 C 593.4724731445312 532.347412109375 596.5122680664062 531.0869750976562 598.8002319335938 528.7993774414062 C 601.0890502929688 526.5103149414062 602.3495483398438 523.4684448242188 602.3495483398438 520.234130859375 L 602.3495483398438 215.5442657470703 L 139.2568817138672 215.5442657470703 Z M 139.2612609863281 191.0111999511719 L 602.3536987304688 191.0111999511719 L 602.3536987304688 130.1333312988281 C 602.3536987304688 126.9413909912109 601.0601196289062 123.819450378418 598.8046875 121.5680770874023 C 596.515869140625 119.2791976928711 593.4738159179688 118.0186386108398 590.239013671875 118.0186386108398 L 553.740234375 118.0186386108398 L 553.740234375 130.1330718994141 C 553.740234375 133.4097595214844 552.4642944335938 136.4899444580078 550.1475219726562 138.8062591552734 C 547.8311157226562 141.1221313476562 544.75146484375 142.3975067138672 541.475830078125 142.3975067138672 C 538.1985473632812 142.3975067138672 535.1183471679688 141.1221313476562 532.8024291992188 138.8063812255859 C 530.5042724609375 136.5081787109375 529.2306518554688 133.4573059082031 529.2113647460938 130.2080688476562 L 529.2111206054688 130.2080688476562 L 529.20703125 130.1330718994141 L 529.20703125 118.0186386108398 L 383.0698852539062 118.0186386108398 L 383.0698852539062 130.1330718994141 C 383.0698852539062 133.4102020263672 381.7945251464844 136.4904479980469 379.4786376953125 138.8062591552734 C 377.1628112792969 141.1221313476562 374.0826416015625 142.3975067138672 370.8054504394531 142.3975067138672 C 367.5282592773438 142.3975067138672 364.447998046875 141.1221313476562 362.1322021484375 138.8063812255859 C 359.833984375 136.5082397460938 358.5604858398438 133.4573059082031 358.5411682128906 130.2080688476562 L 358.5409545898438 130.2080688476562 L 358.5365600585938 130.1330718994141 L 358.5365600585938 118.0186386108398 L 212.3994445800781 118.0186386108398 L 212.3994445800781 130.1330718994141 C 212.3994445800781 133.4102020263672 211.1240692138672 136.4904479980469 208.8082580566406 138.8062591552734 C 206.4923858642578 141.1221313476562 203.4122009277344 142.3975067138672 200.135009765625 142.3975067138672 C 196.8593292236328 142.3975067138672 193.7796325683594 141.1221313476562 191.4631958007812 138.8063812255859 C 189.1463317871094 136.4900817871094 187.8703308105469 133.4098205566406 187.8703308105469 130.1330718994141 L 187.8703308105469 118.0186386108398 L 151.3757629394531 118.0186386108398 L 151.3757629394531 118.0187606811523 C 144.6958312988281 118.0187606811523 139.2612609863281 123.4533233642578 139.2612609863281 130.1333312988281 L 139.2612609863281 191.0111999511719 Z M 322.3948364257812 459.361083984375 C 319.2015075683594 459.361083984375 316.1208801269531 458.017822265625 313.9429016113281 455.6756896972656 L 265.3093872070312 407.0422058105469 L 265.3093872070312 407.0422668457031 L 265.1813354492188 406.9183959960938 C 262.8887634277344 404.6237487792969 261.6261901855469 401.5738220214844 261.6261901855469 398.330322265625 C 261.6261901855469 395.0868835449219 262.8887634277344 392.0380249023438 265.1813354492188 389.7453918457031 C 267.4759521484375 387.4508972167969 270.52587890625 386.187255859375 273.7693786621094 386.187255859375 C 277.0128784179688 386.187255859375 280.0617065429688 387.4508972167969 282.3543090820312 389.7454528808594 L 321.7946166992188 428.9393005371094 L 435.1147766113281 292.9573974609375 C 437.4019470214844 290.3085021972656 440.720703125 288.7898254394531 444.2210083007812 288.7898254394531 C 446.9310302734375 288.7898254394531 449.5838012695312 289.7196655273438 451.7049255371094 291.4107055664062 L 451.7052001953125 291.4096374511719 L 451.7975769042969 291.481689453125 C 453.0476379394531 292.5060729980469 454.0635070800781 293.7495727539062 454.8169555664062 295.1777038574219 C 455.5707702636719 296.6065063476562 456.023193359375 298.1469421386719 456.1617126464844 299.7561340332031 C 456.2998962402344 301.3655090332031 456.1166381835938 302.9607543945312 455.616943359375 304.4977111816406 C 455.1174621582031 306.0340270996094 454.32763671875 307.4328308105469 453.2693176269531 308.6552734375 L 331.3642578125 454.9413146972656 C 329.1748352050781 457.5789489746094 325.9565124511719 459.1895751953125 322.5347595214844 459.3601989746094 C 322.4862060546875 459.36083984375 322.4405212402344 459.361083984375 322.3948364257812 459.361083984375 Z" stroke="none"/><path d="M 200.13720703125 44.801025390625 C 193.406494140625 44.801025390625 187.947509765625 50.25592041015625 187.947509765625 56.990478515625 L 187.947509765625 93.564453125 L 151.37353515625 93.564453125 C 141.6752319335938 93.564453125 132.37255859375 97.4166259765625 125.514404296875 104.2745056152344 C 118.65625 111.1326904296875 114.80419921875 120.4358825683594 114.80419921875 130.1338806152344 L 114.80419921875 520.2327880859375 C 114.80419921875 529.9310302734375 118.65625 539.2337646484375 125.514404296875 546.092041015625 C 132.372314453125 552.9503173828125 141.675537109375 556.8021240234375 151.37353515625 556.8021240234375 L 590.235107421875 556.8021240234375 C 599.93359375 556.8021240234375 609.236083984375 552.949951171875 616.0947265625 546.092041015625 C 622.95263671875 539.2340087890625 626.80419921875 529.9307861328125 626.80419921875 520.2327880859375 L 626.80419921875 130.1337585449219 C 626.80419921875 120.4355163574219 622.952392578125 111.1329345703125 616.0947265625 104.2745056152344 C 609.236083984375 97.41650390625 599.93310546875 93.564453125 590.235107421875 93.564453125 L 553.6591796875 93.564453125 L 553.6591796875 56.990478515625 C 553.6591796875 50.25579833984375 548.199462890625 44.801025390625 541.469482421875 44.801025390625 C 534.734619140625 44.801025390625 529.2802734375 50.25592041015625 529.2802734375 56.990478515625 L 529.2802734375 93.564453125 L 382.9929504394531 93.564453125 L 382.9929504394531 56.990478515625 C 382.9929504394531 50.25579833984375 377.5378112792969 44.801025390625 370.8031921386719 44.801025390625 C 364.0688171386719 44.801025390625 358.61376953125 50.25592041015625 358.61376953125 56.990478515625 L 358.61376953125 93.564453125 L 212.32666015625 93.564453125 L 212.32666015625 56.990478515625 C 212.32666015625 50.25579833984375 206.87158203125 44.801025390625 200.13720703125 44.801025390625 M 151.371337890625 117.9435729980469 L 187.9453125 117.9435729980469 L 187.9453125 130.1330871582031 C 187.9453125 136.8677673339844 193.404541015625 142.322509765625 200.135009765625 142.322509765625 C 206.86962890625 142.322509765625 212.324462890625 136.8675842285156 212.324462890625 130.1330871582031 L 212.324462890625 117.9435729980469 L 358.611572265625 117.9435729980469 L 358.611572265625 130.1330871582031 L 358.6159362792969 130.1330871582031 C 358.6159362792969 136.8677673339844 364.0708312988281 142.322509765625 370.8054504394531 142.322509765625 C 377.5400085449219 142.322509765625 382.994873046875 136.8675842285156 382.994873046875 130.1330871582031 L 382.994873046875 117.9435729980469 L 529.281982421875 117.9435729980469 L 529.281982421875 130.1330871582031 L 529.2861328125 130.1330871582031 C 529.2861328125 136.8677673339844 534.7412109375 142.322509765625 541.475830078125 142.322509765625 C 548.206298828125 142.322509765625 553.665283203125 136.8675842285156 553.665283203125 130.1330871582031 L 553.665283203125 117.9435729980469 L 590.239013671875 117.9435729980469 C 593.470458984375 117.9435729980469 596.5703125 119.2275085449219 598.857666015625 121.5150146484375 C 601.14501953125 123.7981872558594 602.4287109375 126.9018249511719 602.4287109375 130.1332702636719 L 602.4287109375 191.0862121582031 L 139.186279296875 191.0862121582031 L 139.186279296875 130.1332702636719 C 139.186279296875 123.4027099609375 144.6455078125 117.9436950683594 151.3756713867188 117.9436950683594 L 151.371337890625 117.9435729980469 M 590.234619140625 532.423828125 L 590.23291015625 532.423583984375 L 151.371337890625 532.423583984375 C 144.640869140625 532.423583984375 139.181884765625 526.96435546875 139.181884765625 520.234130859375 L 139.181884765625 215.4692687988281 L 602.424560546875 215.4692687988281 L 602.424560546875 520.234130859375 C 602.424560546875 523.4654541015625 601.140625 526.5648193359375 598.853271484375 528.8524169921875 C 596.5654296875 531.139892578125 593.466064453125 532.423828125 590.234619140625 532.423828125 M 444.2210083007812 288.8648681640625 C 440.864501953125 288.8649291992188 437.5312194824219 290.2734985351562 435.1723937988281 293.0054016113281 L 321.7998352050781 429.0501403808594 L 282.30126953125 389.7984619140625 C 277.59033203125 385.08349609375 269.949462890625 385.08349609375 265.234375 389.7984619140625 C 260.5234375 394.5093994140625 260.5234375 402.1502685546875 265.234375 406.8653259277344 L 265.234375 406.861083984375 L 313.9978332519531 455.6246337890625 C 316.2001953125 457.9928894042969 319.2946166992188 459.3234558105469 322.531005859375 459.2852783203125 C 325.9408874511719 459.1152038574219 329.1255187988281 457.5208740234375 331.306640625 454.893310546875 L 453.212646484375 308.606201171875 C 455.325439453125 306.1656494140625 456.3630065917969 302.9768371582031 456.0868835449219 299.7625732421875 C 455.8103332519531 296.54833984375 454.2455749511719 293.5846862792969 451.75 291.5397033691406 L 451.749267578125 291.5426940917969 C 449.5374755859375 289.743408203125 446.8720397949219 288.8648071289062 444.2210083007812 288.8648681640625 M 200.13720703125 44.6510009765625 C 203.434326171875 44.6510009765625 206.5333251953125 45.9342041015625 208.8634033203125 48.26422119140625 C 211.1934204101562 50.59417724609375 212.4766235351562 53.6932373046875 212.4766235351562 56.990478515625 L 212.4766235351562 93.41445922851562 L 358.4637756347656 93.41445922851562 L 358.4637756347656 56.990478515625 C 358.4637756347656 53.6932373046875 359.7469482421875 50.59423828125 362.0770263671875 48.26422119140625 C 364.4070739746094 45.9342041015625 367.5060729980469 44.6510009765625 370.8031921386719 44.6510009765625 C 374.1004638671875 44.6510009765625 377.1995849609375 45.9342041015625 379.5296325683594 48.26422119140625 C 381.8597106933594 50.59417724609375 383.1429443359375 53.6932373046875 383.1429443359375 56.990478515625 L 383.1429443359375 93.41445922851562 L 529.1302490234375 93.41445922851562 L 529.1302490234375 56.990478515625 C 529.1302490234375 53.69317626953125 530.4133911132812 50.59417724609375 532.7432861328125 48.26422119140625 C 535.0731201171875 45.9342041015625 538.1721801757812 44.6510009765625 541.469482421875 44.6510009765625 C 544.7649536132812 44.6510009765625 547.8634643554688 45.9342041015625 550.1941528320312 48.26416015625 C 552.5253295898438 50.5947265625 553.8092041015625 53.69378662109375 553.8092041015625 56.990478515625 L 553.8092041015625 93.41445922851562 L 590.23291015625 93.41445922851562 C 595.0814208984375 93.41445922851562 599.8092651367188 94.35470581054688 604.287109375 96.20913696289062 C 608.7653198242188 98.06375122070312 612.7736206054688 100.7416381835938 616.2007446289062 104.16845703125 C 619.62744140625 107.595458984375 622.30517578125 111.6036987304688 624.15966796875 116.0818176269531 C 626.0140380859375 120.5595092773438 626.9542236328125 125.2872619628906 626.9542236328125 130.1337585449219 L 626.9542236328125 520.2327880859375 C 626.9542236328125 525.0792846679688 626.0140380859375 529.8070678710938 624.1597900390625 534.2847900390625 C 622.30517578125 538.7631225585938 619.62744140625 542.7713623046875 616.2007446289062 546.1980590820312 C 612.7735595703125 549.6248779296875 608.7653198242188 552.3028564453125 604.2872314453125 554.1574096679688 C 599.8094482421875 556.0118408203125 595.0816650390625 556.9521484375 590.235107421875 556.9521484375 L 151.37353515625 556.9521484375 C 146.5270385742188 556.9521484375 141.7991943359375 556.0119018554688 137.321533203125 554.1575317382812 C 132.8432006835938 552.3028564453125 128.8350219726562 549.6249389648438 125.4083251953125 546.1980590820312 C 121.9814453125 542.7711181640625 119.3035278320312 538.762939453125 117.4489135742188 534.2847900390625 C 115.5944213867188 529.8070678710938 114.6541748046875 525.0792846679688 114.6541748046875 520.2327880859375 L 114.6541748046875 130.1338806152344 C 114.6541748046875 125.2874450683594 115.5944213867188 120.5596313476562 117.4489135742188 116.0818786621094 C 119.3035888671875 111.6035766601562 121.9815063476562 107.5953369140625 125.4083251953125 104.16845703125 C 128.8352661132812 100.74169921875 132.8434448242188 98.06381225585938 137.321533203125 96.20919799804688 C 141.7992553710938 94.35476684570312 146.527099609375 93.41445922851562 151.37353515625 93.41445922851562 L 187.7974853515625 93.41445922851562 L 187.7974853515625 56.990478515625 C 187.7974853515625 53.6937255859375 189.0812377929688 50.5947265625 191.412353515625 48.26422119140625 C 193.7428588867188 45.9342041015625 196.8414306640625 44.6510009765625 200.13720703125 44.6510009765625 Z M 187.7953491210938 118.0935668945312 L 151.3756713867188 118.0936889648438 C 144.7371215820312 118.0936889648438 139.3362426757812 123.49462890625 139.3362426757812 130.1332702636719 L 139.3362426757812 190.9361877441406 L 602.2786865234375 190.9361877441406 L 602.2786865234375 130.1332702636719 C 602.2786865234375 126.9610595703125 600.9931640625 123.8585815429688 598.7516479492188 121.6211853027344 C 596.4769897460938 119.3463134765625 593.453857421875 118.0935668945312 590.239013671875 118.0935668945312 L 553.8152465820312 118.0935668945312 L 553.8152465820312 130.1330871582031 C 553.8152465820312 133.4298095703125 552.531494140625 136.5288391113281 550.2005004882812 138.8593139648438 C 547.8699340820312 141.1893310546875 544.771484375 142.4725036621094 541.475830078125 142.4725036621094 C 538.1785888671875 142.4725036621094 535.0794677734375 141.1893310546875 532.7494506835938 138.859375 C 530.454833984375 136.5647888183594 529.1754150390625 133.5241394042969 529.1370239257812 130.2830810546875 L 529.1361083984375 130.2830810546875 L 529.1319580078125 130.1330871582031 L 529.1319580078125 118.0935668945312 L 383.1448974609375 118.0935668945312 L 383.1448974609375 130.1330871582031 C 383.1448974609375 133.4302673339844 381.8616943359375 136.5292663574219 379.5317077636719 138.8593139648438 C 377.2016906738281 141.1893310546875 374.1026306152344 142.4725036621094 370.8054504394531 142.4725036621094 C 367.5082092285156 142.4725036621094 364.4091491699219 141.1893310546875 362.0791320800781 138.859375 C 359.7845458984375 136.5647888183594 358.5051879882812 133.5241394042969 358.4668273925781 130.2830810546875 L 358.4659423828125 130.2830810546875 L 358.4615783691406 130.1330871582031 L 358.4615783691406 118.0935668945312 L 212.4744262695312 118.0935668945312 L 212.4744262695312 130.1330871582031 C 212.4744262695312 133.4302673339844 211.1912841796875 136.5292663574219 208.8612670898438 138.8593139648438 C 206.53125 141.1893310546875 203.4322509765625 142.4725036621094 200.135009765625 142.4725036621094 C 196.8392944335938 142.4725036621094 193.7407836914062 141.1893310546875 191.4102172851562 138.859375 C 189.0791015625 136.5289611816406 187.7953491210938 133.4299011230469 187.7953491210938 130.1330871582031 L 187.7953491210938 118.0935668945312 Z M 602.2745361328125 215.6192626953125 L 139.3319091796875 215.6192626953125 L 139.3319091796875 520.234130859375 C 139.3319091796875 526.8726806640625 144.7327880859375 532.2735595703125 151.371337890625 532.2735595703125 L 590.2452392578125 532.2738037109375 C 593.4556884765625 532.2710571289062 596.474609375 531.0185546875 598.7471923828125 528.746337890625 C 601.0218505859375 526.471435546875 602.2745361328125 523.4484252929688 602.2745361328125 520.234130859375 L 602.2745361328125 215.6192626953125 Z M 444.2210083007812 288.7148132324219 C 446.9090881347656 288.7148132324219 449.5413513183594 289.6240844726562 451.6603698730469 291.2799377441406 L 451.6611938476562 291.2765808105469 L 451.8450622558594 291.4237060546875 C 453.1029357910156 292.4544372558594 454.1251220703125 293.7056884765625 454.8832702636719 295.1427001953125 C 455.6417541503906 296.5804443359375 456.0970153808594 298.1305236816406 456.236328125 299.7496948242188 C 456.3754577636719 301.369140625 456.1910705566406 302.9743957519531 455.6882629394531 304.5208740234375 C 455.1856994628906 306.0667724609375 454.3909606933594 307.4743347167969 453.3260803222656 308.7043762207031 L 331.421875 454.9893188476562 C 329.2192077636719 457.6428833007812 325.9812622070312 459.2633666992188 322.5385131835938 459.43505859375 L 322.5328979492188 459.4352416992188 C 322.4869995117188 459.4358215332031 322.4408264160156 459.4360961914062 322.39501953125 459.4360961914062 C 319.1820678710938 459.4360961914062 316.0826110839844 458.085205078125 313.8905639648438 455.7294921875 L 265.3843994140625 407.2232055664062 L 265.3843994140625 407.2234497070312 L 265.1282653808594 406.9713134765625 C 262.8215637207031 404.6625671386719 261.5512084960938 401.5938110351562 261.5512084960938 398.330322265625 C 261.5512084960938 395.0668334960938 262.8215637207031 391.9991455078125 265.1283264160156 389.6923828125 C 267.43701171875 387.3836975097656 270.5057678222656 386.1122436523438 273.7693786621094 386.1122436523438 C 277.032958984375 386.1122436523438 280.1006469726562 387.3836975097656 282.4073791503906 389.6924438476562 L 321.7894287109375 428.8283081054688 L 435.05712890625 292.9093933105469 C 437.359375 290.2430114746094 440.6988220214844 288.7148742675781 444.2210083007812 288.7148132324219 Z"/></g>';

            break;
            case 'calendar-delete':
                $svg = '<g transform="translate(-114.654 -44.651)"><path d="M 590.235107421875 556.8771362304688 L 151.3735809326172 556.8771362304688 C 146.5369567871094 556.8771362304688 141.8188171386719 555.9388427734375 137.3502655029297 554.0882568359375 C 132.8810119628906 552.2373657226562 128.8810119628906 549.56494140625 125.4613265991211 546.1451416015625 C 122.0415115356445 542.7251586914062 119.369010925293 538.7251586914062 117.518196105957 534.256103515625 C 115.6675720214844 529.7875366210938 114.7292022705078 525.0693969726562 114.7292022705078 520.2328491210938 L 114.7292022705078 130.1339569091797 C 114.7292022705078 125.2973861694336 115.6675720214844 120.5792617797852 117.518196105957 116.1106338500977 C 119.3690719604492 111.6414489746094 122.0415115356445 107.6413879394531 125.4613265991211 104.2215118408203 C 128.8812561035156 100.8017654418945 132.8812561035156 98.12932586669922 137.3502655029297 96.27851104736328 C 141.8188934326172 94.42782592773438 146.5370178222656 93.48944854736328 151.3735809326172 93.48944854736328 L 187.8725128173828 93.48944854736328 L 187.8725128173828 56.99051284790039 C 187.8725128173828 53.71382522583008 189.1484527587891 50.63357543945312 191.4653167724609 48.3172607421875 C 193.7817687988281 46.00138854980469 196.8614501953125 44.72601318359375 200.1371917724609 44.72601318359375 C 203.4143218994141 44.72601318359375 206.4945068359375 46.00138854980469 208.8103942871094 48.3172607421875 C 211.1262664794922 50.63313674926758 212.4017028808594 53.71332550048828 212.4017028808594 56.99051284790039 L 212.4017028808594 93.48944854736328 L 358.5387573242188 93.48944854736328 L 358.5387573242188 56.99051284790039 C 358.5387573242188 53.71338653564453 359.814208984375 50.63320159912109 362.1300659179688 48.3172607421875 C 364.4459533691406 46.00144958496094 367.526123046875 44.72601318359375 370.8031921386719 44.72601318359375 C 374.0803833007812 44.72601318359375 377.1607055664062 46.00138854980469 379.4765625 48.3172607421875 C 381.7925109863281 50.63307571411133 383.0679626464844 53.71332550048828 383.0679626464844 56.99051284790039 L 383.0679626464844 93.48944854736328 L 529.2052612304688 93.48944854736328 L 529.2052612304688 56.99051284790039 C 529.2052612304688 53.71332550048828 530.4805908203125 50.63313674926758 532.7962646484375 48.3172607421875 C 535.1119995117188 46.00144958496094 538.1922607421875 44.72601318359375 541.469482421875 44.72601318359375 C 544.7449340820312 44.72601318359375 547.8246459960938 46.00138854980469 550.14111328125 48.3172607421875 C 552.4581298828125 50.63363647460938 553.7341918945312 53.71382522583008 553.7341918945312 56.99051284790039 L 553.7341918945312 93.48944854736328 L 590.2329711914062 93.48944854736328 C 595.0716552734375 93.48944854736328 599.7897338867188 94.42782592773438 604.2583618164062 96.27845001220703 C 608.7275390625 98.12926483154297 612.7276611328125 100.8016967773438 616.1477661132812 104.2215118408203 C 619.5674438476562 107.6415100097656 622.2396850585938 111.6415710449219 624.0903930664062 116.1105728149414 C 625.9408569335938 120.5791397094727 626.8792114257812 125.2972030639648 626.8792114257812 130.1338195800781 L 626.8792114257812 520.2328491210938 C 626.8792114257812 525.0694580078125 625.9409790039062 529.78759765625 624.0904541015625 534.2561645507812 C 622.23974609375 538.725341796875 619.5674438476562 542.7254028320312 616.1478271484375 546.1451416015625 C 612.7276000976562 549.5648803710938 608.7274780273438 552.2373046875 604.258544921875 554.0880737304688 C 599.7899169921875 555.9387817382812 595.07177734375 556.8771362304688 590.235107421875 556.8771362304688 Z M 139.2568817138672 215.5442657470703 L 139.2568817138672 520.234130859375 C 139.2568817138672 526.9140625 144.6913909912109 532.3485717773438 151.3713226318359 532.3485717773438 L 590.2329711914062 532.3485717773438 L 590.2384643554688 532.3485717773438 L 590.2401123046875 532.3488159179688 C 593.4724731445312 532.347412109375 596.5122680664062 531.0869750976562 598.8002319335938 528.7993774414062 C 601.0890502929688 526.5103149414062 602.3495483398438 523.4684448242188 602.3495483398438 520.234130859375 L 602.3495483398438 215.5442657470703 L 139.2568817138672 215.5442657470703 Z M 139.2612609863281 191.0111999511719 L 602.3536987304688 191.0111999511719 L 602.3536987304688 130.1333312988281 C 602.3536987304688 126.9413909912109 601.0601196289062 123.819450378418 598.8046875 121.5680770874023 C 596.515869140625 119.2791976928711 593.4738159179688 118.0186386108398 590.239013671875 118.0186386108398 L 553.740234375 118.0186386108398 L 553.740234375 130.1330718994141 C 553.740234375 133.4097595214844 552.4642944335938 136.4899444580078 550.1475219726562 138.8062591552734 C 547.8311157226562 141.1221313476562 544.75146484375 142.3975067138672 541.475830078125 142.3975067138672 C 538.1985473632812 142.3975067138672 535.1183471679688 141.1221313476562 532.8024291992188 138.8063812255859 C 530.5042724609375 136.5081787109375 529.2306518554688 133.4573059082031 529.2113647460938 130.2080688476562 L 529.2111206054688 130.2080688476562 L 529.20703125 130.1330718994141 L 529.20703125 118.0186386108398 L 383.0698852539062 118.0186386108398 L 383.0698852539062 130.1330718994141 C 383.0698852539062 133.4102020263672 381.7945251464844 136.4904479980469 379.4786376953125 138.8062591552734 C 377.1628112792969 141.1221313476562 374.0826416015625 142.3975067138672 370.8054504394531 142.3975067138672 C 367.5282592773438 142.3975067138672 364.447998046875 141.1221313476562 362.1322021484375 138.8063812255859 C 359.833984375 136.5082397460938 358.5604858398438 133.4573059082031 358.5411682128906 130.2080688476562 L 358.5409545898438 130.2080688476562 L 358.5365600585938 130.1330718994141 L 358.5365600585938 118.0186386108398 L 212.3994445800781 118.0186386108398 L 212.3994445800781 130.1330718994141 C 212.3994445800781 133.4102020263672 211.1240692138672 136.4904479980469 208.8082580566406 138.8062591552734 C 206.4923858642578 141.1221313476562 203.4122009277344 142.3975067138672 200.135009765625 142.3975067138672 C 196.8593292236328 142.3975067138672 193.7796325683594 141.1221313476562 191.4631958007812 138.8063812255859 C 189.1463317871094 136.4900817871094 187.8703308105469 133.4098205566406 187.8703308105469 130.1330718994141 L 187.8703308105469 118.0186386108398 L 151.3757629394531 118.0186386108398 L 151.3757629394531 118.0187606811523 C 144.6958312988281 118.0187606811523 139.2612609863281 123.4533233642578 139.2612609863281 130.1333312988281 L 139.2612609863281 191.0111999511719 Z M 443.5168151855469 458.6280822753906 C 443.4158325195312 458.6280822753906 443.3133239746094 458.6268310546875 443.2120056152344 458.6243286132812 C 443.1613159179688 458.625 443.1075134277344 458.6253967285156 443.053955078125 458.6253967285156 C 441.4868774414062 458.6254577636719 439.9563293457031 458.325439453125 438.5048217773438 457.7337646484375 C 437.0519409179688 457.1415710449219 435.7475891113281 456.2853393554688 434.6279602050781 455.1888122558594 L 370.802001953125 391.3625793457031 L 306.9761352539062 455.1883239746094 C 305.8562622070312 456.2851257324219 304.5519409179688 457.1413269042969 303.098876953125 457.733642578125 C 301.6473999023438 458.3252563476562 300.116943359375 458.6253356933594 298.5502014160156 458.6253967285156 C 298.496337890625 458.6253967285156 298.4429016113281 458.625 298.3888854980469 458.6243286132812 C 298.2906494140625 458.6268310546875 298.1881408691406 458.6280822753906 298.0869445800781 458.6280822753906 C 294.887939453125 458.6280822753906 291.8590087890625 457.4068298339844 289.5580749511719 455.1892700195312 C 287.2826232910156 452.8393859863281 286.030517578125 449.7465209960938 286.030517578125 446.4785766601562 C 286.030517578125 443.2105712890625 287.2826232910156 440.1179504394531 289.5562744140625 437.7703247070312 L 353.3826293945312 373.9436950683594 L 289.5570678710938 310.1181335449219 C 287.231689453125 307.7926330566406 285.9510192871094 304.7002563476562 285.9510192871094 301.4105834960938 C 285.9510192871094 298.1209411621094 287.231689453125 295.0286865234375 289.55712890625 292.7035217285156 C 291.8846435546875 290.3760070800781 294.9783325195312 289.0942077636719 298.268310546875 289.0942077636719 C 301.5583190917969 289.0942077636719 304.6506958007812 290.3760070800781 306.9758911132812 292.7035827636719 L 370.8017578125 356.5291442871094 L 434.6273803710938 292.7035217285156 C 436.9528198242188 290.3760070800781 440.0453186035156 289.0942077636719 443.3353271484375 289.0942077636719 C 446.6253356933594 289.0942077636719 449.7188720703125 290.3760070800781 452.0462646484375 292.7035827636719 C 454.3717651367188 295.0290222167969 455.6524353027344 298.1213989257812 455.6524353027344 301.4110717773438 C 455.6524353027344 304.7007141113281 454.3717651367188 307.7929382324219 452.0462036132812 310.1181335449219 L 452.0242614746094 310.1400756835938 L 452.0203857421875 310.1400756835938 L 388.2167053222656 373.9436950683594 L 452.0203857421875 437.7474975585938 L 452.0210266113281 437.7474975585938 L 452.0472717285156 437.7703247070312 C 454.3208923339844 440.1182556152344 455.5730590820312 443.2110595703125 455.5730590820312 446.4790649414062 C 455.5730590820312 449.7470703125 454.3208923339844 452.8397521972656 452.0472717285156 455.1874389648438 C 449.744873046875 457.4067077636719 446.716064453125 458.6279602050781 443.5168151855469 458.6280822753906 Z" stroke="none"/><path d="M 200.13720703125 44.801025390625 C 193.406494140625 44.801025390625 187.947509765625 50.25592041015625 187.947509765625 56.990478515625 L 187.947509765625 93.564453125 L 151.37353515625 93.564453125 C 141.6752319335938 93.564453125 132.37255859375 97.4166259765625 125.514404296875 104.2745056152344 C 118.65625 111.1326904296875 114.80419921875 120.4358825683594 114.80419921875 130.1338806152344 L 114.80419921875 520.2327880859375 C 114.80419921875 529.9310302734375 118.65625 539.2337646484375 125.514404296875 546.092041015625 C 132.372314453125 552.9503173828125 141.675537109375 556.8021240234375 151.37353515625 556.8021240234375 L 590.235107421875 556.8021240234375 C 599.93359375 556.8021240234375 609.236083984375 552.949951171875 616.0947265625 546.092041015625 C 622.95263671875 539.2340087890625 626.80419921875 529.9307861328125 626.80419921875 520.2327880859375 L 626.80419921875 130.1337585449219 C 626.80419921875 120.4355163574219 622.952392578125 111.1329345703125 616.0947265625 104.2745056152344 C 609.236083984375 97.41650390625 599.93310546875 93.564453125 590.235107421875 93.564453125 L 553.6591796875 93.564453125 L 553.6591796875 56.990478515625 C 553.6591796875 50.25579833984375 548.199462890625 44.801025390625 541.469482421875 44.801025390625 C 534.734619140625 44.801025390625 529.2802734375 50.25592041015625 529.2802734375 56.990478515625 L 529.2802734375 93.564453125 L 382.9929504394531 93.564453125 L 382.9929504394531 56.990478515625 C 382.9929504394531 50.25579833984375 377.5378112792969 44.801025390625 370.8031921386719 44.801025390625 C 364.0688171386719 44.801025390625 358.61376953125 50.25592041015625 358.61376953125 56.990478515625 L 358.61376953125 93.564453125 L 212.32666015625 93.564453125 L 212.32666015625 56.990478515625 C 212.32666015625 50.25579833984375 206.87158203125 44.801025390625 200.13720703125 44.801025390625 M 151.371337890625 117.9435729980469 L 187.9453125 117.9435729980469 L 187.9453125 130.1330871582031 C 187.9453125 136.8677673339844 193.404541015625 142.322509765625 200.135009765625 142.322509765625 C 206.86962890625 142.322509765625 212.324462890625 136.8675842285156 212.324462890625 130.1330871582031 L 212.324462890625 117.9435729980469 L 358.611572265625 117.9435729980469 L 358.611572265625 130.1330871582031 L 358.6159362792969 130.1330871582031 C 358.6159362792969 136.8677673339844 364.0708312988281 142.322509765625 370.8054504394531 142.322509765625 C 377.5400085449219 142.322509765625 382.994873046875 136.8675842285156 382.994873046875 130.1330871582031 L 382.994873046875 117.9435729980469 L 529.281982421875 117.9435729980469 L 529.281982421875 130.1330871582031 L 529.2861328125 130.1330871582031 C 529.2861328125 136.8677673339844 534.7412109375 142.322509765625 541.475830078125 142.322509765625 C 548.206298828125 142.322509765625 553.665283203125 136.8675842285156 553.665283203125 130.1330871582031 L 553.665283203125 117.9435729980469 L 590.239013671875 117.9435729980469 C 593.470458984375 117.9435729980469 596.5703125 119.2275085449219 598.857666015625 121.5150146484375 C 601.14501953125 123.7981872558594 602.4287109375 126.9018249511719 602.4287109375 130.1332702636719 L 602.4287109375 191.0862121582031 L 139.186279296875 191.0862121582031 L 139.186279296875 130.1332702636719 C 139.186279296875 123.4027099609375 144.6455078125 117.9436950683594 151.3756713867188 117.9436950683594 L 151.371337890625 117.9435729980469 M 590.234619140625 532.423828125 L 590.23291015625 532.423583984375 L 151.371337890625 532.423583984375 C 144.640869140625 532.423583984375 139.181884765625 526.96435546875 139.181884765625 520.234130859375 L 139.181884765625 215.4692687988281 L 602.424560546875 215.4692687988281 L 602.424560546875 520.234130859375 C 602.424560546875 523.4654541015625 601.140625 526.5648193359375 598.853271484375 528.8524169921875 C 596.5654296875 531.139892578125 593.466064453125 532.423828125 590.234619140625 532.423828125 M 298.268310546875 289.169189453125 C 295.1353149414062 289.169189453125 292.001708984375 290.364990234375 289.6100769042969 292.7565612792969 C 284.8313293457031 297.5348815917969 284.8313293457031 305.2861328125 289.6100769042969 310.0650634765625 L 353.48876953125 373.9436950683594 L 289.6100769042969 437.822509765625 C 284.937255859375 442.6474609375 284.937255859375 450.3094482421875 289.6100769042969 455.13525390625 C 291.96142578125 457.4013671875 295.1246643066406 458.6295776367188 298.389892578125 458.54931640625 C 301.5739135742188 458.5906372070312 304.6484375 457.3631286621094 306.9230651855469 455.13525390625 L 370.802001953125 391.2564697265625 L 434.6803894042969 455.13525390625 C 436.9553527832031 457.3631591796875 440.0287170410156 458.5916748046875 443.2138977050781 458.54931640625 C 446.4781799316406 458.6298828125 449.6423034667969 457.4014282226562 451.9933776855469 455.13525390625 C 456.666259765625 450.3101806640625 456.666259765625 442.6480712890625 451.9933776855469 437.822509765625 L 451.9892578125 437.822509765625 L 388.1105651855469 373.9436950683594 L 451.9892578125 310.0650634765625 L 451.9931335449219 310.0650634765625 C 456.7721862792969 305.2867736816406 456.7721862792969 297.5355224609375 451.9931335449219 292.7565612792969 C 447.2105102539062 287.9734497070312 439.459228515625 287.973388671875 434.6803894042969 292.7565612792969 L 370.8017578125 356.63525390625 L 306.9228210449219 292.7565612792969 C 304.5336608886719 290.364990234375 301.4013061523438 289.169189453125 298.268310546875 289.169189453125 M 200.13720703125 44.6510009765625 C 203.434326171875 44.6510009765625 206.5333251953125 45.9342041015625 208.8634033203125 48.26422119140625 C 211.1934204101562 50.59417724609375 212.4766235351562 53.6932373046875 212.4766235351562 56.990478515625 L 212.4766235351562 93.41445922851562 L 358.4637756347656 93.41445922851562 L 358.4637756347656 56.990478515625 C 358.4637756347656 53.6932373046875 359.7469482421875 50.59423828125 362.0770263671875 48.26422119140625 C 364.4070739746094 45.9342041015625 367.5060729980469 44.6510009765625 370.8031921386719 44.6510009765625 C 374.1004638671875 44.6510009765625 377.1995849609375 45.9342041015625 379.5296325683594 48.26422119140625 C 381.8597106933594 50.59417724609375 383.1429443359375 53.6932373046875 383.1429443359375 56.990478515625 L 383.1429443359375 93.41445922851562 L 529.1302490234375 93.41445922851562 L 529.1302490234375 56.990478515625 C 529.1302490234375 53.69317626953125 530.4133911132812 50.59417724609375 532.7432861328125 48.26422119140625 C 535.0731201171875 45.9342041015625 538.1721801757812 44.6510009765625 541.469482421875 44.6510009765625 C 544.7649536132812 44.6510009765625 547.8634643554688 45.9342041015625 550.1941528320312 48.26416015625 C 552.5253295898438 50.5947265625 553.8092041015625 53.69378662109375 553.8092041015625 56.990478515625 L 553.8092041015625 93.41445922851562 L 590.23291015625 93.41445922851562 C 595.0814208984375 93.41445922851562 599.8092651367188 94.35470581054688 604.287109375 96.20913696289062 C 608.7653198242188 98.06375122070312 612.7736206054688 100.7416381835938 616.2007446289062 104.16845703125 C 619.62744140625 107.595458984375 622.30517578125 111.6036987304688 624.15966796875 116.0818176269531 C 626.0140380859375 120.5595092773438 626.9542236328125 125.2872619628906 626.9542236328125 130.1337585449219 L 626.9542236328125 520.2327880859375 C 626.9542236328125 525.0792846679688 626.0140380859375 529.8070678710938 624.1597900390625 534.2847900390625 C 622.30517578125 538.7631225585938 619.62744140625 542.7713623046875 616.2007446289062 546.1980590820312 C 612.7735595703125 549.6248779296875 608.7653198242188 552.3028564453125 604.2872314453125 554.1574096679688 C 599.8094482421875 556.0118408203125 595.0816650390625 556.9521484375 590.235107421875 556.9521484375 L 151.37353515625 556.9521484375 C 146.5270385742188 556.9521484375 141.7991943359375 556.0119018554688 137.321533203125 554.1575317382812 C 132.8432006835938 552.3028564453125 128.8350219726562 549.6249389648438 125.4083251953125 546.1980590820312 C 121.9814453125 542.7711181640625 119.3035278320312 538.762939453125 117.4489135742188 534.2847900390625 C 115.5944213867188 529.8070678710938 114.6541748046875 525.0792846679688 114.6541748046875 520.2327880859375 L 114.6541748046875 130.1338806152344 C 114.6541748046875 125.2874450683594 115.5944213867188 120.5596313476562 117.4489135742188 116.0818786621094 C 119.3035888671875 111.6035766601562 121.9815063476562 107.5953369140625 125.4083251953125 104.16845703125 C 128.8352661132812 100.74169921875 132.8434448242188 98.06381225585938 137.321533203125 96.20919799804688 C 141.7992553710938 94.35476684570312 146.527099609375 93.41445922851562 151.37353515625 93.41445922851562 L 187.7974853515625 93.41445922851562 L 187.7974853515625 56.990478515625 C 187.7974853515625 53.6937255859375 189.0812377929688 50.5947265625 191.412353515625 48.26422119140625 C 193.7428588867188 45.9342041015625 196.8414306640625 44.6510009765625 200.13720703125 44.6510009765625 Z M 187.7953491210938 118.0935668945312 L 151.3756713867188 118.0936889648438 C 144.7371215820312 118.0936889648438 139.3362426757812 123.49462890625 139.3362426757812 130.1332702636719 L 139.3362426757812 190.9361877441406 L 602.2786865234375 190.9361877441406 L 602.2786865234375 130.1332702636719 C 602.2786865234375 126.9610595703125 600.9931640625 123.8585815429688 598.7516479492188 121.6211853027344 C 596.4769897460938 119.3463134765625 593.453857421875 118.0935668945312 590.239013671875 118.0935668945312 L 553.8152465820312 118.0935668945312 L 553.8152465820312 130.1330871582031 C 553.8152465820312 133.4298095703125 552.531494140625 136.5288391113281 550.2005004882812 138.8593139648438 C 547.8699340820312 141.1893310546875 544.771484375 142.4725036621094 541.475830078125 142.4725036621094 C 538.1785888671875 142.4725036621094 535.0794677734375 141.1893310546875 532.7494506835938 138.859375 C 530.454833984375 136.5647888183594 529.1754150390625 133.5241394042969 529.1370239257812 130.2830810546875 L 529.1361083984375 130.2830810546875 L 529.1319580078125 130.1330871582031 L 529.1319580078125 118.0935668945312 L 383.1448974609375 118.0935668945312 L 383.1448974609375 130.1330871582031 C 383.1448974609375 133.4302673339844 381.8616943359375 136.5292663574219 379.5317077636719 138.8593139648438 C 377.2016906738281 141.1893310546875 374.1026306152344 142.4725036621094 370.8054504394531 142.4725036621094 C 367.5082092285156 142.4725036621094 364.4091491699219 141.1893310546875 362.0791320800781 138.859375 C 359.7845458984375 136.5647888183594 358.5051879882812 133.5241394042969 358.4668273925781 130.2830810546875 L 358.4659423828125 130.2830810546875 L 358.4615783691406 130.1330871582031 L 358.4615783691406 118.0935668945312 L 212.4744262695312 118.0935668945312 L 212.4744262695312 130.1330871582031 C 212.4744262695312 133.4302673339844 211.1912841796875 136.5292663574219 208.8612670898438 138.8593139648438 C 206.53125 141.1893310546875 203.4322509765625 142.4725036621094 200.135009765625 142.4725036621094 C 196.8392944335938 142.4725036621094 193.7407836914062 141.1893310546875 191.4102172851562 138.859375 C 189.0791015625 136.5289611816406 187.7953491210938 133.4299011230469 187.7953491210938 130.1330871582031 L 187.7953491210938 118.0935668945312 Z M 602.2745361328125 215.6192626953125 L 139.3319091796875 215.6192626953125 L 139.3319091796875 520.234130859375 C 139.3319091796875 526.8726806640625 144.7327880859375 532.2735595703125 151.371337890625 532.2735595703125 L 590.2452392578125 532.2738037109375 C 593.4556884765625 532.2710571289062 596.474609375 531.0185546875 598.7471923828125 528.746337890625 C 601.0218505859375 526.471435546875 602.2745361328125 523.4484252929688 602.2745361328125 520.234130859375 L 602.2745361328125 215.6192626953125 Z M 298.268310546875 289.0191955566406 C 301.5783996582031 289.0191955566406 304.6896362304688 290.308837890625 307.0289611816406 292.6505737304688 L 370.8017578125 356.4231567382812 L 434.5743103027344 292.6505126953125 C 436.9137573242188 290.308837890625 440.0252075195312 289.0191955566406 443.3353271484375 289.0191955566406 C 446.6453857421875 289.0191955566406 449.7577514648438 290.308837890625 452.0992126464844 292.6505126953125 C 454.4388732910156 294.9901428222656 455.7274475097656 298.1013793945312 455.7274475097656 301.4110717773438 C 455.7274475097656 304.7207641601562 454.4388732910156 307.8318176269531 452.0992126464844 310.171142578125 L 452.0552673339844 310.215087890625 L 452.0513916015625 310.215087890625 L 388.3226928710938 373.9436950683594 L 452.1011352539062 437.7181396484375 C 454.3883972167969 440.0801391601562 455.6480102539062 443.1915283203125 455.6480102539062 446.4790649414062 C 455.6480102539062 449.7666320800781 454.3883972167969 452.8778686523438 452.1011352539062 455.2396240234375 L 452.0975036621094 455.2432556152344 C 449.7827758789062 457.4743041992188 446.7354431152344 458.7030639648438 443.5168151855469 458.7030639648438 C 443.4165954589844 458.7030639648438 443.3146362304688 458.7018432617188 443.2138061523438 458.6993408203125 C 441.581298828125 458.7200317382812 439.9861145019531 458.4185791015625 438.4765625 457.8032836914062 C 437.0146484375 457.2073974609375 435.7020874023438 456.3457641601562 434.575439453125 455.242431640625 L 370.802001953125 391.4685668945312 L 307.0291442871094 455.2413330078125 C 305.9017028808594 456.3456420898438 304.5892639160156 457.2072143554688 303.1271362304688 457.8031311035156 C 301.6171569824219 458.4186401367188 300.0223083496094 458.7197875976562 298.3899841308594 458.6993408203125 C 298.2893371582031 458.7018432617188 298.1873779296875 458.7030639648438 298.0869445800781 458.7030639648438 C 294.8684387207031 458.7030639648438 291.8210144042969 457.474365234375 289.5060119628906 455.2432556152344 L 289.5023193359375 455.2396240234375 C 287.215087890625 452.8775024414062 285.9554443359375 449.7660522460938 285.9554443359375 446.4785766601562 C 285.9554443359375 443.1910095214844 287.215087890625 440.079833984375 289.5023193359375 437.7181396484375 L 353.2766418457031 373.9436950683594 L 289.5039978027344 310.171142578125 C 287.1644592285156 307.8315124511719 285.8760070800781 304.7202758789062 285.8760070800781 301.4105834960938 C 285.8760070800781 298.1008911132812 287.1644592285156 294.9898376464844 289.5039978027344 292.6505126953125 C 291.845703125 290.308837890625 294.958251953125 289.0191955566406 298.268310546875 289.0191955566406 Z"/></g>';

            break;
            case 'share2':
                $svg = '<path d="M488.576,419.734a88.135,88.135,0,0,0-44.415-12.088,89.081,89.081,0,0,0-63.624,26.989l-4,4.117L195.6,332.987l1.508-5.434a91.343,91.343,0,0,0,.1-48.652l-1.484-5.422,182-106.388,4,4.117A89.058,89.058,0,0,0,445.346,198.2a88.136,88.136,0,0,0,44.415-12.088c42.584-24.894,57.209-80.189,32.614-123.359A89.213,89.213,0,0,0,445.166,17.63a88.255,88.255,0,0,0-44.415,12.088,90.733,90.733,0,0,0-41.3,102.39l1.484,5.41L178.9,243.917l-4-4.129A89.057,89.057,0,0,0,111.282,212.8a88.135,88.135,0,0,0-44.415,12.088C24.3,249.782,9.658,305.076,34.253,348.247a89.189,89.189,0,0,0,77.208,45.121,88.255,88.255,0,0,0,44.415-12.088,89.891,89.891,0,0,0,18.826-14.7l4-4.081L359.735,468.326l-1.484,5.41a90.721,90.721,0,0,0,41.339,102.39,88.171,88.171,0,0,0,44.4,12.088A89.213,89.213,0,0,0,521.2,543.093C545.785,499.959,531.16,444.628,488.576,419.734Zm-71.1-360.382A54.959,54.959,0,0,1,445.214,51.8a55.606,55.606,0,0,1,48.137,28.1A56.515,56.515,0,0,1,473,156.5a55.139,55.139,0,0,1-75.88-20.55A56.515,56.515,0,0,1,417.471,59.352ZM139.157,351.646a54.959,54.959,0,0,1-27.743,7.528,55.606,55.606,0,0,1-48.137-28.1,56.515,56.515,0,0,1,20.346-76.6,55.138,55.138,0,0,1,75.88,20.55A56.515,56.515,0,0,1,139.157,351.646Zm353.069,174.3a55.105,55.105,0,0,1-75.88,20.55A56.5,56.5,0,0,1,396,469.894a55.139,55.139,0,0,1,75.88-20.55,56.527,56.527,0,0,1,20.286,76.6Z" transform="translate(-22.315 -17.63)"/>';

            break;
            case 'menu':
                $svg = '<g transform="translate(0 -540.36)"><path d="m0.000005 977.86v-25.5h256 256v25.5 25.5h-256-256v-25.5zm0-169.5v-25h256 256v25 25h-256-256v-25zm0-169.5v-25.5h256 256v25.5 25.5h-256-256v-25.5z"/></g>';

            break;
            case 'support-chat':
                $svg = '<path d="m86.374 485.48c-41.507-6.3272-75.157-39.079-84.333-82.08-2.5071-11.75-2.744-32.395-0.49532-43.181 6.331-30.368 22.902-54.932 47.434-70.313l5.6433-3.5384v-31.509c0-17.33 0.40419-35.671 0.89819-40.758 2.4205-24.925 9.9505-50.996 21.502-74.445 11.209-22.755 22.551-38.624 40.245-56.311 29.891-29.879 65.042-48.199 106.69-55.608 10.58-1.8818 14.852-2.1946 30.904-2.2628 24.654-0.10481 39.34 2.1021 61.362 9.2212 65.995 21.335 117.71 78.404 135.06 149.03 4.978 20.267 5.7103 28.271 6.1346 67.056l0.39133 35.774 7.3249 4.7599c9.2369 6.0024 23.037 19.707 28.883 28.682 12.317 18.911 18.043 38.335 17.975 60.962-0.058506 19.194-3.571 33.886-12.021 50.284-14.956 29.023-41.769 48.783-73.087 53.863-11.898 1.9298-172.71 1.8856-177.34-0.048668-3.9931-1.668-7.4551-4.9466-9.7873-9.2687-1.7049-3.1595-1.846-4.7792-2.1028-24.131-0.15142-11.409-0.02906-22.742 0.27191-25.184 0.71744-5.8207 4.6945-11.782 9.7404-14.6 3.8069-2.126 3.8295-2.1278 26.671-2.1278 21.788 0 23.02 0.085537 26.248 1.8227 3.9472 2.1243 6.3894 4.6286 8.5437 8.761 1.2331 2.3654 1.6135 5.4838 1.8847 15.452l0.33982 12.489h54.851l0.020698-70.064c0.013045-44.157 0.33742-71.438 0.87726-73.779 2.3952-10.39 10.634-21.024 19.775-25.524 6.2352-3.0696 12.841-4.1648 25.26-4.188l9.102-0.017024v-22.714c0-12.493-0.40501-26.876-0.90002-31.963-6.0418-62.089-42.322-115.34-96.047-140.97-62.502-29.819-135.24-16.208-184.18 34.467-26.694 27.64-43.109 62.594-47.859 101.91-0.74744 6.1867-1.186 18.767-1.2028 34.503l-0.02641 24.766 9.102 0.00639c17.476 0.01228 25.906 2.7217 34.193 10.989 2.6235 2.6175 5.7696 6.7316 6.9913 9.1425 4.7462 9.3661 4.7232 8.952 4.7368 85.331 0.00808 45.416-0.30144 72.03-0.87372 75.126-2.3249 12.577-9.9103 22.344-21.657 27.889-5.7165 2.6979-6.0554 2.7499-18.944 2.9074-7.2181 0.088164-15.41-0.18819-18.204-0.61411zm23.078-104.73 0.21677-67.101h-8.76c-21.767 0-41.393 10.602-53.591 28.952-22.409 33.707-10.015 81.757 25.51 98.905 10.33 4.9864 17.664 6.6044 29.211 6.4442l7.1969-0.099804zm311.42 66.634c21.86-3.8709 38.893-16.855 48.259-36.788 5.3456-11.376 6.6657-17.468 6.6058-30.481-0.043259-9.3998-0.38015-12.096-2.3071-18.464-1.2411-4.1015-3.2499-9.4357-4.464-11.854-7.8688-15.671-21.589-27.69-37.99-33.277-5.5626-1.8952-8.7035-2.3759-17.569-2.6891l-10.795-0.38139v134.82h6.6181c3.6399 0 8.8789-0.40034 11.642-0.88965z" stroke-width=".84669"/>';

            break;
            case 'user':
                $svg = '<path d="m40.397 511.49c-6.0922-1.2132-10.876-4.6629-13.685-9.8683-1.5034-2.7861-1.5767-3.7915-1.4706-20.16 0.1145-17.666 1.3403-33.596 3.9768-51.681 12.23-83.89 49.25-145.3 108.26-179.59 3.3263-1.9327 6.0479-3.6728 6.0479-3.8669 0-0.19409-1.8115-2.3095-4.0255-4.701-30.501-32.945-43.638-79.525-34.781-123.32 15.506-76.679 93.526-128.61 175.13-116.56 53.814 7.9417 101.28 44.613 119.94 92.66 7.4458 19.173 9.4139 30.191 9.4275 52.782 0.00962 15.962-0.13466 17.903-1.9646 26.434-5.4443 25.378-16.739 47.529-34.4 67.464-2.3526 2.6555-4.2775 5.0183-4.2775 5.2507 0 0.23236 2.7216 1.9805 6.0479 3.8848 36.192 20.719 65.183 53.656 84.177 95.635 13.866 30.644 22.587 66.025 26.338 106.85 1.3346 14.525 2.1287 41.447 1.3326 45.18-1.0381 4.8671-4.5879 9.167-9.7456 11.805l-4.3151 2.2069-214.48 0.10725c-117.96 0.058982-215.85-0.16692-217.54-0.50202zm407.71-41.574c-3.008-38.82-7.0674-61.922-15.24-86.732-14.101-42.806-37.728-75.488-70.151-97.034-5.9704-3.9676-22.728-12.879-26.237-13.953-1.2267-0.37538-3.3995 0.36763-8.0639 2.7576-35.161 18.016-77.48 22.197-115.61 11.422-9.978-2.8193-16.589-5.3765-27.665-10.7l-8.6046-4.136-10.455 5.149c-33.025 16.264-57.941 41.929-75.317 77.583-8.7267 17.906-13.797 32.416-18.695 53.505-4.2843 18.445-8.4443 49.576-8.4443 63.192v4.6276h384.93zm-174.83-215.06c45.499-6.2671 84.16-39.84 94.976-82.478 4.761-18.768 4.1763-39.291-1.6458-57.767-8.1206-25.77-27.555-49.248-52.177-63.033-19.181-10.739-40.475-15.758-63.145-14.882-25.037 0.96734-46.967 8.5956-66.433 23.109-21.31 15.887-35.457 36.872-41.813 62.021-2.5033 9.9051-3.2126 29.435-1.4683 40.428 3.0788 19.404 12.302 39.281 24.817 53.488 20.878 23.699 50.099 38.028 81.965 40.195 5.5855 0.37979 18.334-0.17308 24.925-1.0809z" stroke-width=".73308"/>';

            break;
            case 'magnifier':
                $svg = '<g transform="translate(0 -540.36)"><path d="m480.98 1050.7c-1.3599-0.8985-35.145-34.245-75.077-74.102l-72.605-72.468-8.1232 6.0229c-31.374 23.263-65.553 35.794-107.4 39.378-10.675 0.9143-16.956 0.9057-28.011-0.038-51.571-4.4028-94.188-23.882-129.89-59.374-31.411-31.222-51.689-71.402-58.322-115.56-2.056-13.689-2.0588-46.729-0.005-60.403 5.1116-34.032 18.763-66.458 39.59-94.035 8.0315-10.635 28.027-30.63 38.661-38.661 27.577-20.827 60.002-34.478 94.035-39.59 13.674-2.0538 46.714-2.051 60.403 0.005 21.076 3.1655 39.562 8.9319 59.281 18.491 22.993 11.146 40.813 24.106 57.999 42.18 30.238 31.8 47.62 66.865 55.171 111.29 2.6458 15.569 2.395 47.241-0.50376 63.618-5.9877 33.828-18.787 64.058-37.843 89.381l-5.2677 6.9999 72.876 72.999c40.082 40.15 73.594 74.44 74.472 76.2 0.9975 2.0007 1.5957 5.7864 1.5957 10.1 0 5.9177-0.4034 7.4997-2.8362 11.122-4.5463 6.7694-7.3031 8.0637-17.191 8.0713-6.2238 0.01-9.1953-0.4348-11-1.6273zm-260.83-144.38c83.611-8.5022 146.82-78.22 146.82-161.95 0-45.389-18.906-88.192-52.661-119.22-43.202-39.717-105.68-52.954-161.87-34.296-40.555 13.467-75.242 43.394-93.882 80.998-10.537 21.257-14.54 35.437-16.732 59.272-2.6599 28.919 3.0777 58.957 16.379 85.749 30.115 60.658 94.63 96.291 161.95 89.446z"/></g>';

            break;
            case 'arrowRight':
                $svg = '<g transform="translate(0 -540.36)"><path d="m323.68 969.42c-10.388-6.3338-14.872-19.595-9.9301-29.362 1.1623-2.2971 28.046-30.205 59.741-62.017l57.628-57.841h-206.08c-221.95 0-214.89 0.31136-221.96-9.7839-4.0468-5.7776-4.1258-22.344-0.13384-28.043 7.3115-10.439-2.965-9.9749 221.1-9.9749h207.07l-57.628-57.841c-31.695-31.812-58.579-59.72-59.741-62.017-9.3818-18.542 13.376-40.197 31.921-30.374 7.6285 4.0407 164.2 161.9 165.76 167.13 0.7355 2.4576 0.765 7.6203 0.065 11.472-1.0994 6.0543-12.08 17.848-80.967 86.967-43.832 43.979-82.019 81.193-84.859 82.698-7.1063 3.7639-14.734 3.413-21.991-1.0119z"/></g>';

            break;
            case 'arrowDown':
                $svg = '<g transform="translate(0 -610.52)"><path d="m1.4798 742.43c2.1672-6.1403 9.8393-13.434 16.08-15.288 6.6538-1.976 10.941-1.8531 17.365 0.49782 5.1495 1.8847 10.007 6.5905 113.23 109.69l107.85 107.72 106.85-106.85c75.594-75.596 108.16-107.57 111.34-109.3 17.565-9.5599 39.601 4.8923 37.696 24.722-0.99405 10.345 3.1224 5.9321-123.63 132.54-113.26 113.13-118.54 118.26-123.33 119.81-6.9993 2.2568-16.239 1.5394-21.653-1.6814-2.2305-1.3269-56.61-55.087-122.14-120.75-108.49-108.71-118.25-118.74-119.7-123.01-1.926-5.682-1.9077-12.562 0.048039-18.103z" stroke-width=".92965"/></g>';

            break;
            case 'updates':
                $svg = '<g transform="translate(0 -540.36)"><path d="m217.64 1050.3c-72.42-8.4-137.51-50.2-177.35-113.75-12.94-20.66-19.836-35.3-26.819-56.96-21.77-67.52-17.072-137.03 13.501-199.7 41.39-84.86 125-138.71 215.33-138.71 65.554 0 129.66 28.752 174.2 78.129 54.481 60.397 77.862 141.28 64.325 222.51-2.6863 16.121-9.9372 41.538-15.913 55.779-2.0298 4.8374-3.0413 8.7953-2.2478 8.7953 0.79353 0 7.0183-1.8752 13.833-4.1668 14.397-4.8418 19.531-5.1717 25.965-1.6684 10.077 5.4875 12.804 22.011 5.1183 31.007-3.661 4.2849-10.217 7.1757-40.17 17.714-19.687 6.9264-39.886 13.029-44.886 13.561-8.7467 0.93147-9.2967 0.75154-14.526-4.7554-4.7308-4.9818-6.9865-10.819-17.406-45.042-6.5838-21.625-12.438-40.994-13.01-43.042-0.6228-2.2305 0.18765-6.6461 2.0208-11.01 5.5951-13.32 20.932-16.734 30.737-6.8418 2.9911 3.0177 6.1707 9.851 9.5788 20.585l5.1015 16.068 4.4679-10.405c9.2047-21.435 15.648-54.823 15.612-80.899-0.13-94.47-57.54-176.87-142.86-205.07-98.12-32.42-205.5 19.8-246.09 119.69-27.679 68.09-19.794 147 20.664 206.84 18.332 27.114 44.558 51.271 72.489 66.77 24.744 13.73 49.137 20.562 86.55 24.238 14.866 1.461 16.087 1.8697 20.251 6.776 5.868 6.916 7.0821 12.756 4.346 20.904-4.3502 12.955-14.52 15.966-42.803 12.672z"/></g>';

            break;
            case 'filter':
                $svg = '<path d="M255.44,0H453.31c13.42,0,24.85,4.41,33.15,15.24,11.35,14.82,10.89,32.39-1,47.16Q428.31,133.23,371.11,204c-4.32,5.35-8.55,10.78-13,16a20.87,20.87,0,0,0-5.05,14.19q.07,121.38,0,242.77a47,47,0,0,1-.75,9.34c-3.92,19.32-24.39,30.73-42.7,23.42-28-11.18-55.92-22.79-83.86-34.23q-23.12-9.48-46.23-19c-13.27-5.43-20.48-15.94-20.7-30.24v-3.34q0-94.07.07-188.14a23.22,23.22,0,0,0-5.55-15.49Q89.64,140.74,26.26,61.88C17,50.37,14.59,37.13,20.64,23.43,27,9,38.8,1.44,54.53.12,56.24,0,58,0,59.69,0Zm58.32,469.21V464.8q0-115.46-.05-230.91c0-13.9,3.8-26.28,12.54-37.09L451.92,41.35c.58-.72,1.08-1.51,1.71-2.38H58.28c.77,1,1.29,1.72,1.84,2.41q62.07,76.79,124.2,153.52a60.44,60.44,0,0,1,13.93,39.67c-.12,61.3,0,122.59-.11,183.88,0,2.58.61,3.88,3.15,4.9,21.08,8.46,42.09,17.11,63.12,25.7Z" transform="translate(-17.317)"/>';

            break;
            case 'heart':
                $svg = '<g transform="translate(0 -161.53)"><path d="m244.35 641.49c-17.763-8.5126-56.756-31.893-79.936-47.931-53.793-37.217-94.103-75.794-121.43-116.21-22.303-32.986-34.76-63.162-40.749-98.716-2.1928-13.017-2.4753-47.616-0.4799-58.784 4.786-26.788 13.593-49.792 26.359-68.852 21.06-31.441 49.496-51.105 85.033-58.8 12.674-2.7442 39.664-2.5168 54.524 0.45931 30.023 6.0133 57.855 20.095 79.499 40.223l8.7546 8.1411 8.7454-8.1901c21.398-20.039 49.255-34.118 79.49-40.174 14.859-2.9762 41.85-3.2035 54.524-0.45931 37.519 8.124 67.593 30.207 88.727 65.151 20.391 33.716 28.478 78.405 21.996 121.56-11.964 79.654-73.516 157.22-179.49 226.19-12.592 8.1955-56.521 33.662-64.5 37.392-7.1379 3.3369-12.548 3.0798-21.064-1.0011zm22.884-45.808c79.505-43.849 144.13-99.741 175.47-151.75 29.048-48.207 36.474-96.365 21.703-140.74-10.735-32.252-30.985-55.618-57.585-66.442-26.716-10.872-64.552-6.9467-93.288 9.6787-16.827 9.7355-26.241 18.244-42.531 38.439-1.8734 2.3225-5.2484 5.0594-7.5 6.082-9.635 4.3758-17.004 1.3239-26.793-11.097-15.87-20.136-35.682-34.26-60.179-42.902-21.012-7.4129-45.349-8.3409-65.569-2.5004-40.46 11.687-71.05 60.088-71.038 112.4 0.017613 80.069 58.338 158.21 171.17 229.34 11.329 7.1422 43.504 25.634 44.633 25.652 0.1762 0.00273 5.3512-2.7697 11.5-6.1609z"/></g>';

            break;
            case 'info':
                $svg = '<path d="M255.989,0C114.623,0,0,114.623,0,255.989S114.623,512,255.989,512,512,397.355,512,255.989,397.355,0,255.989,0Zm53.291,396.749q-19.765,7.8-31.533,11.876-11.768,4.1-27.35,4.1-23.926,0-37.211-11.681-13.263-11.67-13.242-29.626a107.614,107.614,0,0,1,.975-14.282c.672-4.855,1.734-10.316,3.186-16.449L220.6,282.429c1.452-5.591,2.709-10.9,3.706-15.842a70.247,70.247,0,0,0,1.474-13.718c0-7.412-1.539-12.613-4.594-15.539-3.1-2.926-8.929-4.356-17.619-4.356a46.24,46.24,0,0,0-13.112,1.95c-4.443,1.365-8.3,2.6-11.464,3.814l4.356-17.944q16.189-6.6,30.991-11.291a91.557,91.557,0,0,1,27.957-4.725c15.842,0,28.065,3.858,36.669,11.486q12.841,11.475,12.873,29.821,0,3.8-.889,13.372a89.478,89.478,0,0,1-3.294,17.576l-16.406,58.081c-1.344,4.659-2.536,9.991-3.619,15.951a84.342,84.342,0,0,0-1.582,13.567c0,7.715,1.712,12.982,5.18,15.777,3.424,2.8,9.427,4.2,17.923,4.2a51.988,51.988,0,0,0,13.567-2.1,77.145,77.145,0,0,0,10.966-3.684ZM306.376,161a39.155,39.155,0,0,1-27.632,10.663A39.563,39.563,0,0,1,251,161c-7.672-7.108-11.551-15.756-11.551-25.855,0-10.077,3.9-18.746,11.551-25.92a39.279,39.279,0,0,1,27.74-10.771,38.839,38.839,0,0,1,27.632,10.771c7.65,7.173,11.486,15.842,11.486,25.92Q317.863,150.328,306.376,161Z"/>';

            break;
            case 'question-circle':
                $svg = '<path d="M256,0C114.615,0,0,114.615,0,256S114.615,512,256,512,512,397.385,512,256,397.385,0,256,0Zm0,391.168-2.509.512h-5.12c-1.536-.307-3.072-.563-4.557-.973a28.365,28.365,0,1,1,12.288.461Zm81.92-185.907A63.283,63.283,0,0,1,323.072,230.4a169.524,169.524,0,0,1-25.6,21.043c-5.12,3.533-9.677,7.219-14.49,10.906a30.72,30.72,0,0,0-9.472,13.67c-1.024,2.765-2.2,5.53-3.277,8.294a19.1,19.1,0,0,1-17.818,12.646,24.934,24.934,0,0,1-12.954-2.048,21.145,21.145,0,0,1-11.622-15.821,36.352,36.352,0,0,1,6.144-27.136,74.547,74.547,0,0,1,16.077-17c6.707-5.478,13.619-10.7,20.48-16.179a54.017,54.017,0,0,0,14.029-16.026,30.054,30.054,0,0,0-3.584-33.28,27.648,27.648,0,0,0-15.36-8.5,48.9,48.9,0,0,0-23.859.41,29.031,29.031,0,0,0-18.483,15.36c-2.406,4.506-4.506,9.114-6.758,13.67a33.228,33.228,0,0,1-6.6,9.267,22.886,22.886,0,0,1-17.357,5.581,24.013,24.013,0,0,1-10.957-2.97,18.944,18.944,0,0,1-10.24-16.9,49.561,49.561,0,0,1,10.24-31.334,78.951,78.951,0,0,1,30.72-24.883,93.7,93.7,0,0,1,29.133-8.4l5.734-.563h10.24l5.53.512a107.52,107.52,0,0,1,39.475,10.24,65.741,65.741,0,0,1,27.238,23.3,60.16,60.16,0,0,1,9.267,26.522A58.675,58.675,0,0,1,337.92,205.261Z" fill="#9e9e9e" opacity="0.6"/>';

            break;
            case 'categories':
                $svg = '<path d="M224.04,19.5H40.484A20.98,20.98,0,0,0,19.5,40.484V224.031a20.98,20.98,0,0,0,20.984,20.984H224.04a20.981,20.981,0,0,0,20.984-20.984V40.484A20.98,20.98,0,0,0,224.04,19.5ZM203.056,203.048H61.467V61.467H203.056ZM224.04,305.985H40.484A20.98,20.98,0,0,0,19.5,326.969V510.516A20.98,20.98,0,0,0,40.484,531.5H224.04a20.98,20.98,0,0,0,20.984-20.984V326.969A20.981,20.981,0,0,0,224.04,305.985ZM203.056,489.533H61.467V347.952H203.056ZM510.516,19.5H326.969a20.98,20.98,0,0,0-20.984,20.984V224.031a20.981,20.981,0,0,0,20.984,20.984H510.516A20.98,20.98,0,0,0,531.5,224.031V40.484A20.98,20.98,0,0,0,510.516,19.5ZM489.533,203.048H347.952V61.467H489.533Zm20.984,102.937H326.969a20.981,20.981,0,0,0-20.984,20.984V510.516A20.98,20.98,0,0,0,326.969,531.5H510.516A20.98,20.98,0,0,0,531.5,510.516V326.969A20.98,20.98,0,0,0,510.516,305.985ZM489.533,489.533H347.952V347.952H489.533Z" transform="translate(-19.5 -19.5)"/>';

            break;
            case 'bell-stroke':
                $svg = '<path d="m241.52 510.59c-30.368-6.0379-54.123-26.978-63.204-55.714l-2.7958-8.847-163-0.0372-4-2.2377c-2.6068-1.4583-4.8107-3.8035-6.3278-6.7336-2.0099-3.882-2.2527-5.4616-1.7778-11.568 0.74123-9.5311 4.6131-20.496 10.029-28.402 2.459-3.5894 10.774-12.502 18.478-19.807 49.294-46.738 71.637-99.963 78.593-187.22 0.32883-4.125 1.004-20.1 1.5003-35.5 0.98563-30.582 2.0913-38.15 8.0359-55 16.566-46.959 54.682-82.178 102.19-94.429 73.183-18.869 147.83 20.284 174.31 91.429 6.5465 17.588 8.3854 29.308 9.493 60.5 1.1179 31.481 2.2745 46.866 4.9617 66 9.3111 66.3 31.136 112.31 71.617 151 22.642 21.637 24.618 24.161 28.955 36.98 1.2924 3.8198 2.6275 10.224 2.9669 14.233 0.5411 6.3897 0.3311 7.8414-1.7049 11.783-1.5156 2.9344-3.7113 5.273-6.3221 6.7336l-4 2.2377-163 0.0372-2.7965 8.8492c-8.2653 26.155-29.631 46.553-56.704 54.137-9.73 2.7255-26.069 3.4507-35.5 1.5757zm29.61-34.603c6.9058-2.3264 12.249-5.4855 17.876-10.57 4.541-4.1025 10.587-12.848 11.559-16.718l0.67155-2.6756h-90.434l0.64832 2.5831c1.5122 6.0249 9.9486 16.518 17.405 21.648 11.159 7.6777 29.226 10.127 42.274 5.7315zm194.39-69.151c-39.953-35.103-67.576-81.016-82.008-136.31-8.2984-31.794-13.473-74.874-13.487-112.29-0.012-30.271-5.5504-51.988-18.2-71.36-14.298-21.895-31.3-35.947-54.938-45.405-13.245-5.2992-24.788-7.4175-40.506-7.4333-31.713-0.03171-58.222 10.838-80.342 32.945-14.845 14.836-25.315 32.833-30.566 52.542-2.4648 9.25-2.7271 12.207-3.4974 39.423-0.85729 30.29-1.0191 32.845-3.5848 56.577-8.5085 78.704-36.804 139.09-87.561 186.87l-11.292 10.629 432.98-0.0382z"/>';

            break;
            case 'bell-stroke-v2':
                $svg = '<path d="M255.16,511.89c-2.99,0-5.98-.16-8.98-.46-19.53-2.58-37.79-11.6-51.58-25.38-10.16-11.06-17.56-24.34-21.57-38.58H70.88c-59.54,0-60.68,0-66.25-7.77-4.24-5.6-4.63-8.1-4.63-15.92,.31-16,6.7-30.89,17.99-42.06,2.35-2.34,9.7-9.54,16.3-15.98,17.63-16.49,31.79-35.84,42.15-57.6,20.65-45.96,30.85-97.01,29.46-147.53,.12-11.82,.98-23.6,2.54-35.22,5.75-33.99,23.04-65.15,48.73-87.92C184.48,13.31,219.58,0,256,0c42.72,0,83.45,18.34,111.73,50.32,8.5,9.56,15.73,20.24,21.55,31.72,10.24,20.35,15.92,43.13,16.41,65.88,0,15.34,1.77,47.46,3.45,62.64,3.4,42.93,17.17,84.74,39.8,121.07,8.53,12.93,18.75,25.02,30.35,35.81l14.78,14.35c11.25,11.17,17.63,26.06,17.93,41.89,0,7.53-.19,10.35-3.97,15.13-6.22,8.67-7.34,8.67-66.92,8.67h-102.1c-4.08,14.37-11.44,27.55-21.38,38.39-16.55,16.6-39.27,26.03-62.48,26.03Zm-34.72-64.09c1.81,2.91,3.97,5.6,6.49,8.04,7.89,7.66,18.28,11.87,29.26,11.87,3.38,0,6.77-.41,10.07-1.22l2.2-.54c6.22-2.01,11.91-5.43,16.6-10,2.55-2.47,4.73-5.16,6.52-8.07-6.66-.65-13.38-.95-20.06-.95-5.03,0-10.07,.16-15.08,.52-5.96-.35-11.15-.54-16.34-.54-6.56,0-13.12,.3-19.66,.9Zm-161.21-44.31c30.21,.16,108.35,.27,197.64,.27h196.5c-39.78-37.09-67.08-84.63-79.12-137.84-7.74-33.16-11.79-67.34-12.01-101.56,1.33-20.62-2.31-41.12-10.49-59.7-7.88-16.07-19.81-30.37-34.4-41.17-17.9-12.78-39.11-19.58-61.21-19.58s-43.31,6.79-61.31,19.63c-14.49,10.72-26.42,25.04-34.41,41.32-8.11,18.38-11.74,38.88-10.42,59.09-.3,29.78-3.3,59.31-8.93,88.2-10.13,57.68-39.12,111.23-81.86,151.33Z"/>';

                break;
            case 'envelope-stroke':
                $svg = '<g transform="translate(-10, 11.36219)"><path d="m215.97 455.45-39.281-44.454-57.181-0.34217c-56.77-0.33971-57.231-0.3589-64.181-2.6731-27.063-9.0116-44.806-27.723-52.727-55.603l-2.2729-8v-267l2.8073-8.9628c7.0621-22.547 20.516-38.809 40.11-48.483 16.606-8.1981-1.5046-7.5545 212.58-7.5545 213.02 0 194.67-0.61719 211 7.0958 21.178 10.003 35.411 27.628 42.722 52.904 1.5991 5.5287 1.7605 16.054 2.0551 134 0.2096 83.904-0.035 130.68-0.7095 135.77-4.0031 30.207-23.748 54.33-53.089 64.861-6.4524 2.3159-6.7091 2.3265-64.06 2.6439l-57.582 0.31874-4.5234 4.705c-2.4879 2.5877-20.413 22.705-39.833 44.705s-35.59 40.118-35.933 40.263-18.3-19.741-39.904-44.191zm57.858-32.745c37.409-42.819 39.226-44.609 49.364-48.641 3.6189-1.4393 11.571-1.7099 63.636-2.1653l59.5-0.52045 6.0163-3.1464c7.4031-3.8717 14.749-11.551 18.304-19.136l2.6801-5.7181 0.2699-129.5c0.2647-127.01 0.2314-129.63-1.7275-136-3.6663-11.931-12.986-21.358-24.69-24.973-6.4923-2.0052-8.5627-2.0272-191.35-2.0272-182.59 0-184.87 0.0241-191.33 2.0204-11.644 3.5965-20.227 12.252-24.852 25.062l-2.3169 6.4171-0.2862 122c-0.169 72.065 0.099 125.21 0.6556 129.84 1.8013 14.989 11.002 27.64 23.884 32.842 5.7311 2.3143 5.907 2.322 64.747 2.8206 64.646 0.54785 61.428 0.24476 70.719 6.6606 2.0453 1.4124 15.993 16.467 30.995 33.454 15.002 16.987 27.504 30.854 27.781 30.815 0.2778-0.0391 8.3801-9.0849 18.005-20.102z"/></g>';

            break;
            case 'favorites':
                $svg = '<g transform="translate(-10, 11.36219)"><path d="m249.13 468.34c-26.789-5.8705-83.228-46.824-150.48-109.19-60.96-56.532-84.11-89.674-95.915-137.31-2.1233-8.5685-2.3494-11.657-2.3064-31.5 0.041-18.868 0.36604-23.489 2.2835-32.457 6.0902-28.484 20.015-54.101 40.545-74.589 16.744-16.709 32.234-26.325 54.829-34.039 14.368-4.905 20.325-5.769 40.24-5.8363 20.356-0.0689 26.221 0.9709 42.257 7.4915 20.788 8.4528 35.946 19.468 59.265 43.067l16.522 16.72 16.478-16.703c41.345-41.91 72.912-55.329 118-50.161 23.442 2.6865 49.923 15.113 70.976 33.307 34.014 29.394 53.579 79.188 49.132 125.05-3.5854 36.978-17.409 68.564-44.328 101.29-10.999 13.37-60.583 62.724-80.304 79.932-48.877 42.648-92.202 73.753-114.75 82.386-9.4146 3.6045-14.839 4.2219-22.445 2.5551zm18.201-34.572c22.81-11.665 62.31-41.36 103.49-77.802 21.243-18.798 59.527-56.943 70.888-70.631 31.605-38.08 43.19-72.537 38.082-113.27-4.7609-37.969-31.479-74.171-65.973-89.393-24.401-10.768-50.062-11.685-71.975-2.5718-14.825 6.1655-26.185 14.994-50.617 39.339-27.915 27.816-30.679 29.73-39.521 27.377-3.8365-1.021-7.3827-4.1944-28.384-25.4-25.635-25.884-32.566-31.413-49.501-39.488-13.416-6.3969-19.197-7.5941-36.499-7.5589-12.585 0.0256-16.369 0.40785-23.5 2.3741-23.845 6.5743-44.976 21.129-60.142 41.424-14.01 18.748-20.935 36.938-22.777 59.821-2.9691 36.896 7.8252 68.844 34.579 102.34 15.333 19.2 51.227 54.537 86.399 85.061 33.111 28.734 73.653 58.662 93.439 68.975 8.7866 4.5799 12.051 4.4906 22.008-0.60177z"/></g>';

            break;
            case 'basket':
                $svg = '<g transform="translate(0, 20.36219)"><path d="m203.1 488.64c-25.997-6.7952-43.237-35.311-37.597-62.186 4.3218-20.591 19.4-36.205 40.057-41.478 15.51-3.9597 33.083 0.81056 45.799 12.432 22.809 20.846 23.458 55.953 1.4412 77.964-13.136 13.133-31.482 18.03-49.7 13.268zm24.26-26.977c20.427-9.0357 20.61-39.376 0.29537-48.835-12.584-5.8596-26.859-0.97886-33.926 11.599-2.6244 4.6713-3.0737 6.5014-3.0691 12.5 0.0152 19.866 18.913 32.604 36.699 24.737zm146.99 27.246c-27.376-7.3717-44.342-36.236-37.745-64.215 6.5856-27.932 35.553-46.479 62.034-39.718 24.724 6.312 40.622 26.819 40.684 52.478 0.0474 19.625-9.3765 36.077-26.568 46.38-10.207 6.1176-26.526 8.2739-38.406 5.0748zm20.422-25.95c6.3463-1.8846 13.434-8.0277 16.45-14.257 6.5132-13.455-0.87662-31.909-14.658-36.605-16.734-5.7019-33.634 4.9946-35.185 22.27-1.2004 13.37 6.3089 24.666 19.045 28.648 5.5602 1.7386 8.3422 1.7277 14.348-0.0559zm-243.77-108.79c-1.8688-1.258-3.9291-3.3955-4.5783-4.75s-12.929-50.388-27.288-108.96c-26.901-109.73-28.409-115.63-40.159-157-3.8273-13.475-7.6182-26.833-8.4243-29.685l-1.4656-5.1849-62.149-0.63014-2.8459-3.1866c-5.7312-6.4173-4.6278-17.096 2.2024-21.314 3.2007-1.9763 4.54-2.0349 40.496-1.7719l37.186 0.27202 2.9918 2.8675c1.6455 1.5771 3.4633 4.0521 4.0396 5.5s5.8042 19.733 11.618 40.633c5.8134 20.9 11.033 39.459 11.6 41.243l1.0301 3.2425 389 0.51498 3.3627 3.6694c1.8495 2.0182 3.6328 5.1682 3.963 7 0.44168 2.4502-6.4304 32.008-25.996 111.81-14.628 59.665-27.134 109.6-27.79 110.96-0.65638 1.3644-2.7225 3.51-4.5913 4.7681l-3.3979 2.2873h-295.41zm308.22-117.21c12.54-51.15 22.818-93.338 22.842-93.75 0.0236-0.4125-81.006-0.75-180.07-0.75h-180.11l0.69516 2.75c0.38234 1.5125 10.58 43.25 22.662 92.75 12.082 49.5 22.154 90.564 22.383 91.254 0.33144 0.99821 27.755 1.202 134.6 1l134.19-0.25363z"/></g>';

            break;
            case 'plus-circle':
                $svg = '<path d="m233.89 511.09c-59.974-5.8927-112.88-29.976-154.8-70.469-22.912-22.132-38.614-43.503-52.215-71.065-12.888-26.118-19.656-47.69-24.157-77-2.9186-19.006-3.1743-54.49-0.51514-71.5 8.887-56.848 31.381-102.82 69.649-142.33 59.807-61.755 143.12-89.031 227.91-74.614 85.134 14.476 158.82 73.305 192.03 153.31 35.172 84.726 23.084 179.97-32.173 253.49-8.7581 11.652-27.197 30.872-39.732 41.414-34.588 29.089-81.15 50.076-125.93 56.759-15.334 2.2886-46.573 3.3312-60.073 2.0048zm45.789-31.092c68.6-7.4725 131.18-46.745 167.31-105 35.422-57.112 43.506-126.16 22.26-190.13-12.683-38.189-36.326-73.459-66.396-99.047-33.096-28.163-70.612-45.556-113.19-52.478-14.349-2.3324-53.185-2.3324-67.535 0-41.331 6.7184-78.606 23.666-111.01 50.475-34.378 28.44-61.059 70.069-72.757 113.52-8.2186 30.526-9.7179 68.832-3.8935 99.476 12.04 63.347 48.601 116.57 103.98 151.38 25.35 15.933 60.929 28.199 91.948 31.701 11.426 1.2898 37.903 1.3472 49.289 0.10688zm-30.471-105.01c-8.1527-4.0356-7.756-1.392-8.3189-55.429l-0.5-48-48-0.5c-47.708-0.49695-48.019-0.51363-51.136-2.7371-3.1084-2.2173-6.3637-8.4902-6.3637-12.263s3.2553-10.046 6.3637-12.263c3.1172-2.2235 3.4287-2.2402 51.136-2.7371l48-0.5 0.5-48c0.56351-54.097 0.16488-51.465 8.3986-55.451 5.2129-2.5235 7.9101-2.5461 13.273-0.11094 8.1105 3.6824 7.7645 1.3761 8.328 55.516l0.5 48.045 48 0.5c47.708 0.49695 48.019 0.51363 51.136 2.7371 3.2886 2.3458 6.3637 8.5422 6.3637 12.823 0 3.647-3.4388 9.8322-6.6924 12.037-2.636 1.7864-5.7419 1.9333-50.808 2.4028l-48 0.5-0.5 48.045c-0.56343 54.14-0.21753 51.834-8.328 55.517-5.3775 2.4416-8.2088 2.4134-13.353-0.13305z"/>';

            break;
            case 'ticket':
                $svg = '<path d="m188.08 509.41c-3.7604-1.9091-10.984-8.5438-26.695-24.518-11.847-12.045-22.316-23.389-23.266-25.208-1.2623-2.418-1.6323-5.0544-1.376-9.807 0.91563-16.984 0.31819-27.408-1.9487-34-6.4965-18.893-21.457-33.538-40.429-39.576-8.2278-2.6188-16.159-3.2569-31.708-2.551-4.3915 0.19937-7.8808-0.24054-10.5-1.3238-4.8773-2.0171-46.558-43.162-49.687-49.049-2.6754-5.0327-2.8525-13.533-0.38784-18.616 2.0716-4.2727 298.68-299.83 303.19-302.12 5.0434-2.5569 10.153-3.0004 15.766-1.3685 4.8007 1.3956 7.0418 3.3656 29.357 25.805 27.423 27.577 26.294 25.687 24.588 41.142-0.59299 5.3715-0.66898 11.887-0.18398 15.772 2.9048 23.272 19.352 42.919 42.357 50.597 7.4381 2.4825 9.3862 2.6996 24.578 2.7393 13.517 0.0353 17.042 0.34598 19.5 1.7184 1.65 0.92142 13.196 11.946 25.657 24.5 18.604 18.741 22.852 23.54 23.744 26.825 1.4372 5.2879 1.3809 7.1855-0.39023 13.15-1.4096 4.7468-9.4382 12.957-151.44 154.87-82.473 82.423-151.15 150.49-152.61 151.25-1.461 0.76551-4.9758 1.6136-7.8106 1.8847-4.2071 0.40231-6.1015 0.012-10.31-2.1245zm92.656-131.03 82.496-82.501-147.5-147.5-165.48 165.48 8.9892 9.0508c6.5723 6.6173 9.4992 8.9203 10.886 8.5653 3.7766-0.96666 24.891 1.5515 33.396 3.983 41.439 11.846 70.015 45.244 75.228 87.921 0.60458 4.95 0.98924 11.383 0.85482 14.295l-0.24442 5.295 8.9135 8.955c4.9024 4.9252 9.1489 8.955 9.4366 8.955 0.28772 0 37.646-37.125 83.019-82.501zm172.51-190.49-8.9678-9.0096h-9.1604c-46.294 0-87.369-32.113-99.546-77.825-1.332-5.0006-2.2266-12.257-2.567-20.823l-0.52246-13.147-17.762-17.679-70.987 70.966 147.5 147.51 70.982-70.982z"/>';

            break;
            case 'play':
                $svg = '<g transform="translate(0 519) scale(.1 -.1)"><path d="m2480 5183c-532-19-1084-228-1510-569-109-87-307-285-394-394-262-328-449-731-525-1130-37-190-44-273-44-475 1-222 11-337 49-530 199-1012 993-1813 2009-2026 275-57 619-68 885-29 572 84 1063 328 1468 729 425 420 684 957 752 1561 14 125 14 386 0 530-117 1188-1034 2143-2215 2309-141 19-335 29-475 24zm452-313c489-72 931-295 1289-650 498-495 740-1194 658-1905-65-558-362-1109-794-1473-335-282-732-461-1170-527-154-24-479-24-640 0-506 73-946 294-1305 654-364 365-579 794-656 1311-25 162-25 496-1 645 66 400 210 745 444 1057 98 131 309 342 440 441 504 380 1114 537 1735 447z"/><path d="m2040 3512c-64-32-64-27-61-682 1-322 5-700 9-841 8-284 8-285 78-315 57-24 51-28 864 439 135 77 331 190 435 250 105 60 202 120 217 134 56 53 58 132 5 180-19 18-121 79-402 243-44 26-199 116-345 202-761 445-725 428-800 390z"/></g>';

            break;
            case 'youtube-icon-play':
                $svg = '<g transform="translate(0)"><path transform="translate(0)" d="M501.451,56.081a64.139,64.139,0,0,0-45.125-45.118C416.26,0,256,0,256,0S95.734,0,55.668,10.545A65.449,65.449,0,0,0,10.543,56.081C0,96.134,0,179.2,0,179.2s0,83.484,10.543,123.119a64.151,64.151,0,0,0,45.129,45.118C96.156,358.4,256,358.4,256,358.4s160.261,0,200.327-10.544a64.139,64.139,0,0,0,45.129-45.114C512,262.684,512,179.622,512,179.622s.422-83.488-10.547-123.541Zm0,0"/><path transform="translate(-.001 -.022)" d="m204.97 255.96 133.27-76.74-133.27-76.74z" fill="#fff"/></g>';

            break;
            case 'ok-circle':
                $svg = '<g transform="translate(-0.561 -540.786)"><path d="M240.587,1052.3A256.234,256.234,0,0,1,87.3,988.739a338.93,338.93,0,0,1-24.312-24.411,256.681,256.681,0,0,1-60.3-132.991,282.776,282.776,0,0,1,0-69.154c10.184-74.4,52.65-140.521,116.551-181.466a261.51,261.51,0,0,1,59.213-27.79A256.461,256.461,0,0,1,510.439,762.18a282.776,282.776,0,0,1,0,69.154,256.815,256.815,0,0,1-60.295,132.989A300.483,300.483,0,0,1,414.913,997.9,256.006,256.006,0,0,1,240.6,1052.3Z" transform="translate(0 0)" fill="#0777ec"/><g transform="translate(128.561 694.383)"><g><g transform="translate(0 0)"><path d="M253.468,88.583,224.935,59.268a8.485,8.485,0,0,0-12.23,0L93.848,181.386,43.294,129.448a8.484,8.484,0,0,0-12.228,0L2.533,158.764a9.058,9.058,0,0,0,0,12.564l85.2,87.536a8.484,8.484,0,0,0,12.229,0L253.468,101.147a9.057,9.057,0,0,0,0-12.564Z" transform="translate(-0.001 -56.666)" fill="#fff"/></g></g></g></g>';

            break;
            case 'clock' :
                $svg = '<g transform="translate(-5.6665 -5.6572)" data-name="Path 1206"><path d="m261.66 517.76c-141.21 0-256.1-114.86-256.1-256.04 0.006875-141.18 114.89-256.04 256.1-256.04 28.629 0 56.718 4.6983 83.487 13.964 26.5 9.1729 51.258 22.663 73.595 40.1v-40.127c0-3.756 1.462-7.2867 4.1167-9.9416 2.6555-2.6557 6.1879-4.1183 9.9466-4.1183 3.7587 0 7.2911 1.4626 9.9466 4.1183 2.6547 2.6549 4.1167 6.1856 4.1167 9.9416v70.999c0 1.8324-0.36508 3.6387-1.0851 5.3687-0.71131 1.713-1.7365 3.2464-3.047 4.5574-1.3126 1.3131-2.8472 2.3389-4.5614 3.049-1.7303 0.71963-3.537 1.0846-5.3699 1.0846h-71.017c-3.7587 0-7.2912-1.4626-9.9466-4.1182-2.6547-2.6549-4.1167-6.1855-4.1167-9.9415 0-3.756 1.462-7.2867 4.1167-9.9416 2.6555-2.6556 6.1879-4.1182 9.9466-4.1182h32.457c-38.907-27.973-84.746-42.756-132.59-42.756-125.7 0-227.96 102.24-227.97 227.92 0 125.68 102.27 227.92 227.97 227.92 125.71 0 227.98-102.24 227.98-227.92 0-3.756 1.462-7.2866 4.1167-9.9416 2.6555-2.6556 6.1879-4.1182 9.9466-4.1182 3.7586 0 7.2911 1.4626 9.9466 4.1184 2.6547 2.655 4.1166 6.1856 4.1166 9.9413 0 141.18-114.89 256.04-256.1 256.04zm0-74.462c-3.6038 0-7.2422-1.4857-9.9821-4.0762l-4e-3 -4e-3c-2.5911-2.738-4.0772-6.3754-4.0772-9.9796 0-3.6704 1.4479-7.2149 4.0768-9.9803 2.5496-2.4833 6.2069-3.906 10.037-3.906 3.8308 0 7.4515 1.4232 9.9338 3.9046 1.2526 1.3204 2.2851 2.9172 2.9876 4.6198 0.71412 1.7309 1.0916 3.5849 1.0916 5.3618 0 3.5958-1.4861 7.2331-4.0771 9.9795l-4e-3 4e-3c-2.7793 2.6286-6.3243 4.0762-9.9822 4.0762zm-128.4-39.153c-3.7551 0-7.2863-1.4628-9.9432-4.119-2.6569-2.6562-4.1201-6.1866-4.1201-9.9407 0-3.7542 1.4632-7.2845 4.1201-9.9408l124.28-124.25v-175.66c0-3.7552 1.4629-7.2859 4.1193-9.9416 2.6563-2.6556 6.1878-4.1182 9.944-4.1182 3.7562 0 7.2877 1.4626 9.9441 4.1182 2.6563 2.6557 4.1193 6.1864 4.1193 9.9416v181.48c0 1.8546-0.36044 3.6645-1.0714 5.3792-0.7107 1.7143-1.7364 3.2488-3.0487 4.5609l-128.4 128.36c-2.6569 2.6562-6.1881 4.119-9.9432 4.119zm295.96-128.36c-1.7771 0-3.6316-0.37744-5.3631-1.0916-1.7028-0.70234-3.3001-1.7344-4.6191-2.9848l-4e-3 -4e-3c-2.6294-2.7638-4.0774-6.308-4.0774-9.9798 0-3.6176 1.4859-7.2552 4.0768-9.9803 2.4842-2.483 6.1234-3.906 9.9862-3.906 3.863 0 7.5022 1.4232 9.9844 3.9046 2.5931 2.7404 4.0792 6.3778 4.0792 9.9817 0 3.6559-1.448 7.2-4.0772 9.9796l-4e-3 4e-3c-2.7461 2.5904-6.3844 4.0761-9.9822 4.0761zm-335.12 0c-3.6038 0-7.2421-1.4858-9.9821-4.0762l-2e-3 -2e-3 -0.0019-2e-3c-1.2532-1.3209-2.2866-2.9186-2.9883-4.6206-0.7125-1.7281-1.0891-3.5813-1.0891-5.3591 0-3.61 1.4859-7.2477 4.0768-9.9803 1.3065-1.2726 2.8619-2.2577 4.6259-2.9307 1.6959-0.64719 3.4986-0.97531 5.3577-0.97531 1.8592 0 3.6623 0.32818 5.3592 0.97543 1.7649 0.67319 3.3216 1.6584 4.6269 2.9283 2.594 2.7413 4.0801 6.3787 4.0801 9.9826 0 1.7759-0.37756 3.629-1.0919 5.3592-0.70181 1.6999-1.7341 3.2976-2.9854 4.6204l-4e-3 4e-3c-1.3228 1.2511-2.9209 2.2832-4.6217 2.9849-1.7305 0.71399-3.5841 1.0914-5.3604 1.0914z"/><path d="m261.66 517.66c141.16 0 256-114.81 256-255.94 0-7.7086-6.2452-13.96-13.963-13.96-7.718 0-13.963 6.2505-13.963 13.96 0 125.73-102.31 228.02-228.08 228.02-125.76 0-228.07-102.29-228.07-228.02 0.0069-125.73 102.32-228.02 228.07-228.02 48.408 0 94.389 15.193 132.9 42.956h-32.767c-7.718 0-13.963 6.2505-13.963 13.96 0 7.7092 6.2453 13.96 13.963 13.96h71.017c1.8135 0 3.6272-0.36807 5.3316-1.0769 3.4228-1.4178 6.1363-4.1375 7.5543-7.5525 0.70917-1.7041 1.0774-3.5172 1.0774-5.3303v-70.999c0-7.7092-6.2454-13.96-13.963-13.96-7.718 0-13.963 6.2506-13.963 13.96v40.332c-44.767-35.002-99.447-54.169-157.18-54.169-141.15 0-255.99 114.81-256 255.94 0 141.13 114.84 255.94 256 255.94m0-74.462c3.7704 0 7.2612-1.5405 9.9135-4.0489 2.5084-2.6587 4.0499-6.2841 4.0499-9.9109 0-3.6267-1.5409-7.2661-4.0499-9.9109-5.1688-5.167-14.523-5.167-19.827-5.8e-4 -2.5148 2.6454-4.0493 6.1354-4.0493 9.9114 0 3.6263 1.534 7.2525 4.0498 9.9109 2.6525 2.5078 6.2788 4.0489 9.9134 4.0489m-128.4-39.153c3.5726 0 7.1453-1.3632 9.8725-4.0898l128.4-128.36c2.6178-2.6174 4.0908-6.1687 4.0908-9.8694v-181.48c0-7.7093-6.2521-13.96-13.963-13.96-7.7112 0-13.963 6.2506-13.963 13.96v175.7l-124.31 124.27c-5.4544 5.453-5.4544 14.287 0 19.74 2.7272 2.7265 6.2999 4.0898 9.8725 4.0898m295.96-128.36c3.6277 0 7.2544-1.5405 9.9135-4.0489 2.5084-2.6518 4.0499-6.1418 4.0499-9.9109 0-3.6331-1.5409-7.2594-4.0499-9.9109-5.1687-5.167-14.658-5.1671-19.827-5e-4 -2.5217 2.6523-4.0493 6.2777-4.0493 9.9114 0 3.7694 1.5272 7.2593 4.0499 9.9109 2.6455 2.5078 6.2858 4.0489 9.9134 4.0489m-335.12 0c3.6272 0 7.2612-1.5405 9.9134-4.0489 2.5083-2.6518 4.0499-6.2842 4.0499-9.9109 0-3.6331-1.5409-7.2594-4.0499-9.9109-5.3111-5.167-14.523-5.1671-19.827-5e-4 -2.5148 2.6523-4.0494 6.2777-4.0494 9.9114 0 3.6262 1.5341 7.2593 4.0499 9.9109 2.6524 2.5078 6.2787 4.0489 9.9134 4.0489m167.56 242.18c-141.27 0-256.2-114.9-256.2-256.14 0.006875-141.24 114.94-256.14 256.2-256.14 28.64 0 56.74 4.7002 83.52 13.97 26.445 9.154 51.16 22.608 73.462 39.989v-39.922c0-3.7827 1.4724-7.3385 4.146-10.012 2.6744-2.6746 6.2319-4.1475 10.017-4.1475 3.7854 0 7.343 1.4729 10.017 4.1475 2.6736 2.6738 4.146 6.2296 4.146 10.012v70.999c0 1.8457-0.36765 3.6649-1.0928 5.4072-0.71631 1.7251-1.7488 3.2694-3.0685 4.5897-1.3219 1.3225-2.8675 2.3556-4.5938 3.0707-1.7425 0.72468-3.5621 1.0922-5.4082 1.0922h-71.017c-3.7854 0-7.343-1.4729-10.017-4.1475-2.6736-2.6738-4.146-6.2296-4.146-10.012 0-3.7828 1.4724-7.3385 4.146-10.012 2.6743-2.6746 6.2319-4.1475 10.017-4.1475h32.147c-38.835-27.842-84.562-42.556-132.28-42.556-125.64 0-227.86 102.2-227.87 227.82 0 125.62 102.22 227.82 227.87 227.82 125.65 0 227.88-102.2 227.88-227.82 0-3.7827 1.4724-7.3385 4.146-10.012 2.6743-2.6746 6.2319-4.1475 10.017-4.1475 3.7854 0 7.343 1.473 10.017 4.1478 2.6735 2.6739 4.1459 6.2295 4.1459 10.012 0 141.24-114.93 256.14-256.2 256.14zm0-74.462c-3.6291 0-7.2925-1.4957-10.051-4.1036l-8e-3 -8e-3c-2.6085-2.7563-4.1046-6.4188-4.1046-10.048 0-3.6962 1.4576-7.265 4.1044-10.049l5e-3 -5e-3c2.5654-2.4985 6.2481-3.9316 10.104-3.9316 3.857 0 7.5036 1.4339 10.004 3.9339l4e-3 4e-3c1.2591 1.3273 2.2984 2.9348 3.0056 4.6486 0.71912 1.7429 1.0992 3.6101 1.0992 5.3999 0 3.621-1.496 7.2834-4.1044 10.048l-8e-3 8e-3c-2.7979 2.6462-6.3674 4.1036-10.051 4.1036zm-128.4-39.153c-3.7818 0-7.3381-1.4732-10.014-4.1483-2.6758-2.675-4.1494-6.2306-4.1494-10.011 0-3.7809 1.4736-7.3364 4.1494-10.011l124.25-124.22v-175.61c0-3.782 1.4733-7.3378 4.1485-10.012 2.6752-2.6746 6.2319-4.1475 10.015-4.1475 3.7829 0 7.3395 1.4729 10.015 4.1475 2.6752 2.6746 4.1486 6.2303 4.1486 10.012v181.48c0 1.8678-0.363 3.6906-1.079 5.4176-0.71575 1.7264-1.7487 3.2719-3.0704 4.5933l-128.4 128.36c-2.6758 2.6751-6.2321 4.1483-10.014 4.1483zm295.96-128.36c-1.7901 0-3.6578-0.38006-5.4012-1.0991-1.7141-0.70694-3.322-1.7459-4.6498-3.0046l-7e-3 -7e-3c-2.6472-2.7824-4.105-6.3512-4.105-10.049 0-3.643 1.496-7.3058 4.1044-10.049l4e-3 -4e-3c2.5011-2.4998 6.166-3.9334 10.055-3.9334 3.8893 0 7.5543 1.4339 10.055 3.934l4e-3 4e-3c2.6086 2.7568 4.1046 6.4193 4.1046 10.048 0 3.6815-1.4577 7.2501-4.1046 10.048l-8e-3 8e-3c-2.7644 2.6078-6.4278 4.1034-10.051 4.1034zm-335.12 0c-3.6291 0-7.2924-1.4957-10.051-4.1036l-0.0077-8e-3c-1.2617-1.3297-2.3019-2.9381-3.0082-4.6514-0.71744-1.74-1.0966-3.6064-1.0966-5.3972 0-3.6354 1.4959-7.2981 4.1042-10.049l0.0056-6e-3c1.3136-1.2794 2.8805-2.2718 4.6572-2.9498 1.7074-0.65149 3.522-0.98188 5.3934-0.98188 1.8714 0 3.6864 0.33039 5.3948 0.982 1.7777 0.67807 3.3459 1.6706 4.661 2.95l6e-3 6e-3c2.6086 2.7568 4.1046 6.4193 4.1046 10.048 0 1.7888-0.38019 3.6552-1.0994 5.3974-0.70644 1.7111-1.7456 3.3194-3.0051 4.6509l-8e-3 8e-3c-1.3316 1.2593-2.9403 2.2983-4.6523 3.0046-1.7425 0.71893-3.6093 1.0989-5.3986 1.0989z"/></g>';
            break;
            case 'attend' :
                $svg = '<path d="m335.56 254.85c53.933 0 97.813-52.18 97.813-116.32 0-61.018-40.224-103.64-97.813-103.64s-97.8 42.626-97.8 103.64c-6e-3 64.144 43.879 116.32 97.8 116.32zm0-190.31c40.677 0 67.999 29.729 67.999 73.985 0 47.79-30.504 86.667-67.999 86.667s-67.987-38.881-67.987-86.667c-6e-3 -44.262 27.316-73.985 67.987-73.985zm176.33 338.85c-0.25603-15.719-0.35205-19.729-0.53707-21.55-8.0011-72.917-69.609-113.09-173.46-113.09-0.66209 0-1.3182 0.024-1.9793 0.047l-0.35804 0.012-0.36405-0.012c-0.65009-0.024-1.3062-0.047-1.9913-0.047-103.85 0-165.44 40.162-173.43 113.08-0.18503 1.7142-0.28004 5.0297-0.51307 19.681l-0.13702 8.3631c0 0.35005 0.072 0.67009 0.083 1.0201 0.036 0.52207 0.06 1.0441 0.13702 1.5662a11.541 11.541 0 0 0 0.32804 1.4112c0.11902 0.46306 0.23303 0.91312 0.39406 1.3642a12.362 12.362 0 0 0 0.60008 1.3232 13.621 13.621 0 0 0 0.66209 1.2752 13.769 13.769 0 0 0 0.82911 1.1682c0.29204 0.38005 0.57207 0.7771 0.90612 1.1332 0.35205 0.38005 0.7451 0.71809 1.1392 1.0681 0.25604 0.23103 0.47707 0.50007 0.7571 0.7061 47.982 36.634 105.36 55.207 170.58 55.207h0.024c63.402 0 122.19-18.952 170.06-54.796a0.16802 0.16802 0 0 0 0.048-0.036c0.13702-0.10702 0.28004-0.20003 0.41706-0.30004 0.28004-0.20803 0.50007-0.47407 0.7571-0.6941 0.40606-0.36205 0.82911-0.70609 1.2002-1.1001 0.32805-0.36205 0.60808-0.7411 0.89412-1.1212 0.28604-0.38005 0.58408-0.7651 0.84112-1.1682 0.25703-0.40306 0.45306-0.84812 0.67409-1.2872 0.22103-0.43905 0.41705-0.84811 0.60008-1.3002 0.16102-0.46306 0.28003-0.92512 0.40505-1.3882a13.83 13.83 0 0 0 0.32804-1.3882 13.202 13.202 0 0 0 0.13702-1.6072c0.012-0.33804 0.10002-0.67009 0.083-1.0201zm-176.33 44.078h-0.024c-55.808 0-105.02-15.06-146.47-44.718l0.012-0.7651c0.10001-6.1868 0.24403-15.547 0.34004-16.934 7.8351-71.595 82.524-86.631 143.79-86.631l2.0273 0.059a6.1368 6.1368 0 0 0 0.66209 0l2.0003-0.059c61.267 0 135.96 15.037 143.78 86.299 0.11901 1.7142 0.28004 11.425 0.38205 17.966-41.629 29.307-92.091 44.783-146.5 44.783zm-138.87-207.45a14.866 14.866 0 0 0-14.902-14.833h-68.571v-68.209a14.907 14.907 0 0 0-29.814 0v68.209h-68.571a14.829 14.829 0 1 0 0 29.658h68.571v68.209a14.907 14.907 0 0 0 29.814 0v-68.209h68.571a14.87 14.87 0 0 0 14.902-14.825z" stroke-width="1.0001" data-name="Path 1205"/>';
            break;
            case 'recommended' :
                $svg = '<g transform="matrix(1.0002 0 0 1.0002 40.641 -1.0477)"><path transform="translate(253.5,92.981)" d="m71.08 36.071a13.013 13.013 0 0 0 12.965 13.061h29.041a13.062 13.062 0 0 0 0-26.122h-29.041a13.013 13.013 0 0 0-12.965 13.061z" data-name="Path 1183"/><path transform="translate(92.676,92.981)" d="m32.66 36.071a13.013 13.013 0 0 0 12.965 13.061h29.041a13.062 13.062 0 0 0 0-26.122h-29.041a13.013 13.013 0 0 0-12.965 13.061z" data-name="Path 1184"/><path transform="translate(184.81)" d="m80.6 43.318v-29.257a12.965 12.965 0 1 0-25.93 0v29.257a12.965 12.965 0 1 0 25.93 0z" data-name="Path 1185"/><path transform="translate(233.35 27.235)" d="m90.813 11.277-20.744 20.9a13.147 13.147 0 0 0 0 18.495 12.914 12.914 0 0 0 18.358 0l20.744-20.9a13.146 13.146 0 0 0 0-18.495 12.914 12.914 0 0 0-18.358 0z" data-name="Path 1186"/><path transform="translate(119.5 27.066)" d="m72.792 54.444a12.964 12.964 0 0 0 12-8.06 13.132 13.132 0 0 0-2.821-14.249l-20.744-20.9a12.914 12.914 0 0 0-18.358 0 13.146 13.146 0 0 0 0 18.495l20.744 20.9a12.865 12.865 0 0 0 9.179 3.814z" data-name="Path 1187"/><path transform="translate(0,67.335)" d="m36.45 164.8a26.027 26.027 0 0 0-25.93 26.122v228.62a26.027 26.027 0 0 0 25.93 26.122h75.092a26.009 26.009 0 0 0 25.93-24.294l16.076 9.718a97.985 97.985 0 0 0 50.667 14.158h109.58a95.068 95.068 0 0 0 38.583-7.732c35.005-15.674 40.5-43.207 37.909-61.492a57.745 57.745 0 0 0 16.854-63.843 57.319 57.319 0 0 0 12.961-37.668 55.007 55.007 0 0 0-10.631-29.727 48.155 48.155 0 0 0-11.869-54.753q-1.608-1.515-3.371-2.873a49.139 49.139 0 0 0-19.914-8.882h-0.57c-34.383-7.367-57.512-7.837-91.532-7.837-1.3-7.889-1.245-24.451 9.024-55.641 9.49-28.891 8.66-52.767-2.489-71a58.51 58.51 0 0 0-41.85-26.854h-1.608a27.454 27.454 0 0 0-26.708 19.54 13.254 13.254 0 0 0-0.415 1.881l-3.889 28.212a84.578 84.578 0 0 1-7.26 24.451l-37.808 79.986a44.622 44.622 0 0 1-31.738 26.122v-6.217a26.027 26.027 0 0 0-25.93-26.122zm0 254.75v-228.63h75.092v228.62zm103.72-196.13a70.264 70.264 0 0 0 52.378-41.8l37.494-79.778a110.74 110.74 0 0 0 9.49-32.078l3.993-26.384a1.4 1.4 0 0 1 0.674-0.366 32.911 32.911 0 0 1 22.351 14.524c6.845 11.18 6.845 28.16 0 49.058-10.061 30.511-13.172 54.6-9.335 71.628a23.477 23.477 0 0 0 23.026 18.39c34.279 0 55.438 0.313 88.161 7.262h0.519a23.762 23.762 0 0 1 9.594 4.075l1.66 1.411c13.276 12.068 4.875 27.533 3.112 30.406a13.131 13.131 0 0 0 2.282 16.771 31.082 31.082 0 0 1 8.764 19.122 36 36 0 0 1-11.669 25.444 13.136 13.136 0 0 0-1.711 15.673c0.57 0.993 13.9 24.346-12.5 43.886a13.328 13.328 0 0 0-4.564 14.838c1.4 4.18 6.9 25.6-21.573 38.191a69.505 69.505 0 0 1-28.523 5.433h-109.78a72.2 72.2 0 0 1-37.287-10.449l-29.249-17.659v-167.18z" data-name="Path 1188"/><ellipse transform="translate(58.179 430.3)" cx="15.817" cy="15.935" rx="15.817" ry="15.935" data-name="Ellipse 38"/></g>';
            break;
            case 'bell' :
                $svg = '<g transform="translate(-.89535 -.96196)"><path transform="translate(327.27 87.33)" d="m123.56 89.926a57.922 57.922 0 0 0-17.047-58.454 37.414 37.414 0 0 0-20.854-9.812 24.324 24.324 0 0 0-4.275 6.367 27.109 27.109 0 0 0-2.815 6.054c2.242 4.071 5.787 8.664 8.967 14.092a76.05 76.05 0 0 1 11.363 37.891c0.261 6.315 0 12.16 0 16.806a31.692 31.692 0 0 0 6.412 3.967 19.539 19.539 0 0 0 5.943 2.505 37.271 37.271 0 0 0 12.3-19.415z" data-name="Path 1196"/><path transform="translate(342.37 34.914)" d="m89.378 9.254c-0.782-0.261-3.024 2.453-4.9 6s-2.659 6-2.19 6.42a244.8 244.8 0 0 1 29.664 30.845 148.7 148.7 0 0 1 30.967 105.11 246.55 246.55 0 0 1-8.237 42.066c0 0.731 2.607 2.818 6.047 4.593s5.839 2.4 6.308 1.827a107.51 107.51 0 0 0 20.488-44.618 138.3 138.3 0 0 0-36.858-125.78 107.69 107.69 0 0 0-41.289-26.461z" data-name="Path 1197"/><path transform="translate(48.578,87.372)" d="m14.643 58.256a57.472 57.472 0 0 0 0 31.628 37.27 37.27 0 0 0 12.095 19.363 23.864 23.864 0 0 0 6.882-3.027 26.061 26.061 0 0 0 5.474-3.445c0.313-4.645 0-10.438 0-16.806a76.053 76.053 0 0 1 11.156-37.786c3.18-5.219 6.725-10.021 8.967-14.092a32.531 32.531 0 0 0-3.337-6.941 20.145 20.145 0 0 0-3.962-5.48 37.414 37.414 0 0 0-20.853 9.812 57.421 57.421 0 0 0-16.422 26.774z" data-name="Path 1198"/><path transform="translate(0,34.971)" d="m24.382 206.27c0.521 0.626 3.754-0.418 7.194-2.349s5.213-3.5 5.213-4.123a246.48 246.48 0 0 1-8.237-42.066 148.7 148.7 0 0 1 30.968-105.11 244.76 244.76 0 0 1 29.663-30.845c0.521-0.522-0.678-3.862-2.659-7.307s-3.7-5.219-4.431-5.219a107.69 107.69 0 0 0-41.341 26.513 138.3 138.3 0 0 0-36.858 125.78 107.52 107.52 0 0 0 20.488 44.728z" data-name="Path 1199"/><path transform="translate(171.84 374.19)" d="m112.58 90.276a46.651 46.651 0 0 1-10.427 13.309 26.044 26.044 0 0 1-34.3 0 46.548 46.548 0 0 1-10.43-13.257 31.244 31.244 0 0 0-7.716-0.522 15.622 15.622 0 0 0-6.621 0.783 28.737 28.737 0 0 0-1.147 12.682 38.435 38.435 0 0 0 6.621 16.023 42.4 42.4 0 0 0 14.962 13.935 44.582 44.582 0 0 0 43.062 0 42.3 42.3 0 0 0 14.962-13.987 38.334 38.334 0 0 0 6.047-16.075 28.578 28.578 0 0 0-0.834-12.682 18.537 18.537 0 0 0-7.455-0.731 25.149 25.149 0 0 0-6.724 0.522z" data-name="Path 1200"/><path transform="translate(63.963)" d="m319.51 429.13a52.544 52.544 0 0 0 44.209-27.714 56.894 56.894 0 0 0 4.64-38.569 52.6 52.6 0 0 0-4.692-12.369 55.342 55.342 0 0 0-3.493-5.584c-0.626-0.887-1.3-1.774-1.981-2.61l-1.877-1.879-6.36-7.307-3.18-3.653-2.659-3.6a65.3 65.3 0 0 1-4.953-6.315 68.68 68.68 0 0 1-10.427-29.436 72.046 72.046 0 0 1-0.469-7.881v-27.3l-0.469-35.229c0-5.845 0-11.169-0.417-17.536a84.851 84.851 0 0 0-0.938-9.186 74.462 74.462 0 0 0-1.929-9.133 122.16 122.16 0 0 0-12.095-33.194 125.57 125.57 0 0 0-68.868-58.663c-3.91-1.357-7.82-2.505-11.678-3.445a265.72 265.72 0 0 0 4.848-37.265v-6.834a38.976 38.976 0 0 0-0.991-9.081 43.811 43.811 0 0 0-8.081-17.223 44.283 44.283 0 0 0-70.014 0 43.757 43.757 0 0 0-8.028 17.223 39.083 39.083 0 0 0-0.991 9.081v6.837a260.19 260.19 0 0 0 5.213 37.682 125.83 125.83 0 0 0-66.209 41.388 118.65 118.65 0 0 0-17.673 27.974 131.93 131.93 0 0 0-6.149 16.336 140.59 140.59 0 0 0-4.848 35.594l-0.261 17.067-0.469 35.229v18.11a87.884 87.884 0 0 1-1.095 16.336 68.724 68.724 0 0 1-12.2 28.81 71.939 71.939 0 0 1-5.213 6.159l-3.18 3.236-3.441 3.392-1.773 1.721-2.242 2.4a52.022 52.022 0 0 0-12.3 42.953 51.5 51.5 0 0 0 4.118 12.578 49.125 49.125 0 0 0 7.09 11.691 52.812 52.812 0 0 0 21.9 15.657 57.292 57.292 0 0 0 12.877 3.288 62.767 62.767 0 0 0 6.621 0.47h5.206l40.142-0.209h204.78zm-122.88-28.288-81.9 0.1h-48.8a29.275 29.275 0 0 1-6.517-1.566 25.127 25.127 0 0 1-10.427-7.1 21.3 21.3 0 0 1-3.18-5.219 24.133 24.133 0 0 1-1.929-6.159 24.765 24.765 0 0 1 0.938-12.474 23.6 23.6 0 0 1 2.763-5.637 24.059 24.059 0 0 1 1.877-2.505l1.043-1.148 1.668-1.67 3.389-3.445 3.754-3.967a97.879 97.879 0 0 0 6.882-8.716 94.317 94.317 0 0 0 16.109-40.187 96.861 96.861 0 0 0 0.996-10.43v-27.87l-0.469-35.229-0.261-17.066a109.72 109.72 0 0 1 0.573-15.292 111.71 111.71 0 0 1 2.346-14.4 112.98 112.98 0 0 1 4.536-13.518 100.13 100.13 0 0 1 13.659-23.851 110.71 110.71 0 0 1 38.578-31.837 117.36 117.36 0 0 1 40.869-11.857 7.253 7.253 0 0 0 6.047-7.776 6.624 6.624 0 0 0-6.777-6.159 121.28 121.28 0 0 0-12.355 0.783 283.59 283.59 0 0 0 4.379-34.968v-5.633a15.67 15.67 0 0 1 0.938-3.653 19.368 19.368 0 0 1 3.858-6.42 18.129 18.129 0 0 1 26.953 0 19.316 19.316 0 0 1 3.806 6.42 15.675 15.675 0 0 1 0.886 3.6v5.584a289.27 289.27 0 0 0 4.223 34.446 132.42 132.42 0 0 0-20.228-0.731 7.2 7.2 0 0 0 0 14.3 121.88 121.88 0 0 1 41.706 9.238 116.62 116.62 0 0 1 40.559 29.279 113.57 113.57 0 0 1 15.223 22.86 101.7 101.7 0 0 1 8.863 27.4 55.913 55.913 0 0 1 0.938 7.359 64.851 64.851 0 0 1 0.521 7.515v16.6l-0.469 35.229v27.3a98.2 98.2 0 0 0 0.417 10.438 94.433 94.433 0 0 0 13.815 41.074 91.55 91.55 0 0 0 6.829 8.925l3.284 3.967 3.128 3.706 6.256 7.411 1.251 1.566 0.938 1.253a26.433 26.433 0 0 1 1.355 27.661 24.551 24.551 0 0 1-20.853 12.682h-40.14z" data-name="Path 1201"/><path transform="translate(142.35 113.65)" d="m75.709 32.289a66.553 66.553 0 0 0-34.929 33.037 63.938 63.938 0 0 0-6.1 25.052v5.95l0.313 4.906 0.782 9.447a139.18 139.18 0 0 0 5.213 29.54 18.382 18.382 0 0 0 7.663 0.835 15.624 15.624 0 0 0 6.673-0.835 139.13 139.13 0 0 0 5.213-29.54l0.782-9.447 0.313-4.906 0.368-4.071a49.468 49.468 0 0 1 4.327-14.718 57.65 57.65 0 0 1 20.59-23.016 117.14 117.14 0 0 1 22.883-11.065 20.9 20.9 0 0 0 0.73-7.568 13.113 13.113 0 0 0-0.886-6.42 53.016 53.016 0 0 0-33.939 2.818z" data-name="Path 1202"/></g>';
            break;
            case 'restricted':
                $svg = '<g transform="translate(-71.789 -608.065)"><circle cx="255.967" cy="255.967" r="255.967" transform="translate(73.789 610.065)" fill="#fff"/><path d="M255.967,0C114.6,0,0,114.6,0,255.967S114.6,511.935,255.967,511.935s255.967-114.6,255.967-255.967S397.334,0,255.967,0m0-2a259.867,259.867,0,0,1,51.987,5.241A256.566,256.566,0,0,1,400.2,42.058a258.715,258.715,0,0,1,93.461,113.5,256.685,256.685,0,0,1,15.032,48.426,260.464,260.464,0,0,1,0,103.975A256.566,256.566,0,0,1,469.877,400.2a258.715,258.715,0,0,1-113.5,93.461,256.685,256.685,0,0,1-48.426,15.032,260.464,260.464,0,0,1-103.975,0,256.566,256.566,0,0,1-92.246-38.817,258.715,258.715,0,0,1-93.461-113.5A256.685,256.685,0,0,1,3.241,307.955a260.464,260.464,0,0,1,0-103.975,256.566,256.566,0,0,1,38.817-92.246,258.715,258.715,0,0,1,113.5-93.461A256.685,256.685,0,0,1,203.98,3.241,259.868,259.868,0,0,1,255.967-2Z" transform="translate(73.789 610.065)" fill="#fff"/><g transform="translate(74.237 610)"><path d="M260.776,5C119.815,5,5,119.815,5,260.776S119.815,516.552,260.776,516.552,516.552,401.737,516.552,260.776,401.737,5,260.776,5Zm0,85.259c31.83,0,61.386,8.526,86.4,23.872L114.131,347.172c-15.347-25.009-23.872-54.566-23.872-86.4C90.259,166.992,166.992,90.259,260.776,90.259Zm0,341.035c-31.83,0-61.386-8.526-86.4-23.872L407.99,173.812a172.932,172.932,0,0,1,23.3,86.964C431.294,354.561,354.561,431.294,260.776,431.294Z" transform="translate(-5 -5)"/></g></g>';
            break;
            case 'plus':
                $svg = '<path d="M50.7,512.006H-.5V0H50.7Z" transform="translate(230.903)"/><path d="M51.2,512.006H0V0H51.2Z" transform="translate(512.006 230.403) rotate(90)"/>';
            break;
            case 'minus':
                $svg = '<path d="M51.5,512H-.5V0h52Z" transform="translate(512 0.5) rotate(90)"/>';
            break;
            case 'phone':
                $svg = '<path d="m39.094-5.9696e-4c-21.45 2.109-38.674 20-39.092 41.713 4.688 147.19 49.169 265.71 130 346.86 80.831 81.153 196.86 123.69 340.9 123.43 23.637 0.111 39.928-20.811 40.367-41.9 0.239-34.778 0.495-69.642 0.726-104.38 0-24.269-20.994-40.017-40.366-40.191-29.834 0.123-71.434-11.237-105.09-20a17.246 17.246 0 0 0-16.364 4.762l-55.456 56.377c-66.807-42.027-97.4-72.992-138.55-140.76l55.09-55.807a19.271 19.271 0 0 0 4.728-17.9c-9.071-35.574-24.026-81.926-21.636-114.48-2.362-25.413-25.382-37.848-43.637-37.712h-111.64zm0.183 36.573h112.18c5.637 0.11 5.637 0.11 8.365 5.523-1.035 39.234 11.254 78.868 19.635 109.33l-57.818 59.048c-5.633 5.731-6.826 15.651-2.727 22.665 49.733 84.451 86.616 121.92 169.46 172.57a17.344 17.344 0 0 0 20.909-2.666l58.366-59.617c31.656 8.38 69.635 18.809 104.18 18.664 2.5 0.493 4.972 4.209 5.272 4.571l-0.726 103.24c-1.214 2.976 0.727 5.637-6.546 5.523-136.95 0-242.62-40.038-315.64-113.33-73.216-73.49-114.81-181.5-119.28-321.7 0-3.7 0-3.7 4.363-3.809z"/>';
            break;
            case 'facebook':
                $svg = '<path d="m198.39 508.54-3.4-3.4v-216.2h-34.55c-36.758 0-38.912-0.25717-41.421-4.9446-0.66418-1.241-1.0218-18.186-1.0074-47.74 0.0203-41.9 0.16991-45.998 1.75-47.949 3.4238-4.2268 4.7267-4.367 40.575-4.367h34.479l0.44376-40.25c0.40357-36.605 0.63959-41.132 2.6065-49.994 10.894-49.083 41.712-80.239 89.88-90.868 8.9084-1.9657 13.456-2.19 53.038-2.6167 23.812-0.25671 44.437-0.17968 45.835 0.17116 1.3979 0.35084 3.6282 1.8104 4.9564 3.2435l2.4148 2.6056v42.92c0 27.342-0.37127 43.613-1.0229 44.831-2.2216 4.1511-4.2871 4.4355-36.477 5.022-35.551 0.64774-38.855 1.2536-46.057 8.4449-6.7908 6.7811-7.4434 10.841-7.4434 46.306v30.184h81.2l6.8 6.8-0.0222 45.85c-0.0203 41.932-0.16987 46.032-1.75 47.983-3.4604 4.2718-4.4668 4.367-46.162 4.367h-40.066l-2e-3 106.75c-1e-3 99.633-0.11813 106.98-1.75 110.24-0.96165 1.9173-2.8734 4.0482-4.2484 4.7352-1.8898 0.9443-13.812 1.2528-48.85 1.2639l-46.35 0.015-3.4-3.4z"/>';
            break;
            case 'twitter':
                $svg = '<path d="m143.72 463.07c-51.365-4.0955-93.253-16.92-134.32-41.122l-8.3168-4.9018 17.862 0.4687c18.956 0.49741 31.677-0.5314 48.771-3.9446 19.199-3.8335 42.625-12.503 60.5-22.391 8.6821-4.8027 25.592-15.986 26.574-17.574 0.29933-0.48432-0.99838-0.88059-2.8838-0.88059-6.1012 0-20.976-3.023-29.497-5.9946-25.982-9.0614-47.922-29.068-59.771-54.505-5.6708-12.174-7.012-11.024 12.836-11.011 11.149 7e-3 19.362-0.47723 23.242-1.3709l6-1.382-8-2.3156c-41.466-12.002-72.537-50.081-75.207-92.17-0.32274-5.0875-0.34621-9.25-0.05215-9.25 0.29406 0 3.3977 1.329 6.8969 2.9533 8.9493 4.1541 22.971 7.9997 31.832 8.7302l7.5299 0.62073-5.5-4.3463c-17.858-14.112-30.931-34.039-37.254-56.782-1.8246-6.564-2.1336-10.354-2.1336-26.175 0-15.742 0.3136-19.627 2.1038-26.059 2.3105-8.3014 7.5686-21.336 9.833-24.376 1.38-1.8525 1.5981-1.7458 5.1086 2.5 7.285 8.8109 31.07 31.798 41.342 39.955 39.517 31.381 86.343 52.487 135 60.847 11.975 2.0576 34.898 4.5689 35.57 3.8968 0.2128-0.21279-0.0507-3.0592-0.58555-6.3253-0.53484-3.2661-0.96914-11.338-0.9651-17.938 0.0171-27.927 9.5901-51.79 28.749-71.664 14.173-14.702 30.567-24.386 50.027-29.548 8.4996-2.255 11.694-2.575 25.706-2.575s17.206 0.32002 25.706 2.575c17.099 4.5365 33.212 13.315 45.008 24.521 6.5197 6.1933 5.7824 6.1397 21.662 1.5752 14.166-4.0721 24.901-8.2606 38.343-14.961l12.719-6.3399-1.974 5.0081c-7.3238 18.581-21.531 36.837-37.384 48.038-7.8464 5.5441-8.6925 5.4046 10.42 1.7179 10.505-2.0264 28.305-7.3338 36.78-10.966 2.4769-1.0617 4.6801-1.7538 4.896-1.5379 0.2159 0.21591-2.4031 4.1647-5.8201 8.7751-7.9977 10.791-24.921 28.21-36.213 37.273l-8.8574 7.1093-0.30605 17.5c-1.053 60.21-19.589 117.79-54.244 168.5-45.687 66.854-114.61 110.4-193.82 122.46-21.448 3.2656-50.957 4.736-67.913 3.384z"/>';
            break;
            case 'pinterest':
                $svg = '<path d="m148.08 510.77c-4.7191-6.2732-11.79-25.713-13.428-36.92-1.6777-11.474-0.62642-41.915 2.0507-59.38 8.1504-53.172 24.647-119.85 40.118-162.16 3.2501-8.8878 5.9093-16.581 5.9093-17.095 0-0.5145-0.94788-1.7927-2.1064-2.8404-4.6202-4.1784-6.2718-9.2962-6.7096-20.791-0.50105-13.157 0.90134-22.687 5.3876-36.614 4.4877-13.931 9.2607-21.846 18.995-31.5 13.407-13.296 25.424-19.06 38.077-18.264 11.511 0.72445 18.368 5.8758 24.802 18.632 8.0881 16.037 7.186 37.796-3.0112 72.631-12.572 42.948-14.29 55.319-9.7186 70 4.1424 13.304 14.005 22.924 27.123 26.457 6.1407 1.6535 18.935 1.4135 26.346-0.49428 7.5578-1.9455 20.81-7.9983 27.487-12.554 2.6547-1.8114 7.6749-6.0124 11.156-9.3355 23.657-22.583 35.668-56.348 37.731-106.07 1.1742-28.292-2.5269-60.085-9.156-78.651-14.363-40.226-44.757-61.306-85.905-59.579-40.639 1.706-85.867 23.709-120.5 58.624-27.694 27.916-42.725 57.25-44.656 87.149-0.92906 14.388 2.0518 28.595 8.9085 42.456 3.895 7.8741 13.278 20.369 16.103 21.443 5.8535 2.2255 5.9963 8.1232 0.59152 24.423-4.3183 13.023-6.3971 16.042-11.326 16.451-4.8803 0.4043-16.248-3.0227-24.707-7.4486-21.508-11.253-37.856-36.596-45.069-69.868-0.8124-3.7472-1.2996-13.134-1.2976-25 0.0027-15.892 0.3764-20.799 2.2847-30 10.685-51.519 42.917-99.727 85.781-128.3 52.563-35.041 119.02-45.317 175.39-27.119 13.077 4.2219 33.61 14.556 45 22.65 48.799 34.674 81.035 101.93 80.992 168.98-0.0332 51.769-20.007 99.611-52.54 125.85-4.7354 3.8187-10.636 8.6391-13.113 10.712-11.348 9.4967-29.027 19.252-43.734 24.133-25.84 8.5746-51.629 8.8968-73.977 0.92437-10.994-3.9222-24.51-10.664-33.671-16.795-8.5316-5.7105-9.9048-5.8456-9.9411-0.97795-8e-3 1.1289-1.6212 8.5539-3.5839 16.5-1.9627 7.9461-5.3563 23.897-7.5413 35.447s-5.5678 25.725-7.5174 31.5c-6.628 19.633-19.225 41.218-41.142 70.5-7.559 10.099-10.308 13.067-12.316 13.3-1.4221 0.1653-3.0258-0.2847-3.564-1zm-16.934-242.16c-0.34679-0.90372-1.0482-1.6431-1.5587-1.6431-0.51083 0-0.42629 0.93781 0.18801 2.0856 1.2733 2.3792 2.3234 2.0402 1.3707-0.44252zm-33.635-147.52c-1.376-0.5051-2.256 1.043-5.8963 10.372-4.654 11.927-4.4958 11.981 1.4612 0.5 2.9965-5.775 4.9922-10.667 4.435-10.872z"/>';
            break;
            case 'linkedin':
                $svg = '<path d="m6.9021 335.43v-164.5h109v329h-109zm170.32 7.6e-4 0.18353-164.5 109.46-0.00152 0.26878 22.623 0.26877 22.623 4-5.8733c9.4154-13.825 26.43-28.818 41-36.128 30.053-15.079 76.754-14.984 109.12 0.22156 12.264 5.761 20.244 11.295 30.219 20.955 22.426 21.718 35.614 53.578 39.15 94.578 0.57094 6.6205 0.99537 53.612 1.0003 110.75l9e-3 99.25h-109.93l-0.32647-99.25c-0.31793-96.657-0.3804-99.446-2.3906-106.75-6.81-24.744-17.907-37.626-36.693-42.592-23.582-6.2338-48.198 2.0479-62.537 21.04-5.4226 7.1822-10.799 18.474-12.111 25.438-0.64398 3.4145-1.0156 41.397-1.0156 103.75v98.364h-109.87zm-133.32-212.01c-12.607-3.073-25.231-10.912-31.989-19.862-19.699-26.092-14.135-63.764 11.984-81.128 11.378-7.5643 18.956-9.7895 35.005-10.279 15.585-0.47523 23.01 0.84164 33.734 5.9833 13.774 6.6038 26.022 22.389 29.169 37.591 1.5771 7.6185 1.3506 20.428-0.48722 27.556-5.0979 19.772-22.216 35.741-43.045 40.154-8.4781 1.7962-26.97 1.7872-34.371-0.01684z"/>';
            break;
            case 'envelope-fill':
                $svg = '<path d="m32.496 446.64c-7.0544-1.7155-14.904-6.2439-19.538-11.271-5.0692-5.4995-5.4827-10.696-1.2116-15.23 1.5125-1.6057 16.7-12.942 33.75-25.192 35.825-25.739 98.043-72.032 121.71-90.556 25.761-20.165 24.582-19.364 28.524-19.364 4.0541 0 6.421 1.5982 19.268 13.011 4.675 4.153 10.525 9.1714 13 11.152 10.56 8.4503 22.807 11.947 34.632 9.8877 11.521-2.0063 15.451-4.5378 40.27-25.942 8.0096-6.9074 9.9352-8.1076 13-8.1032 4.4469 7e-3 4.4831 0.0306 28.748 19.07 31.486 24.705 77.003 58.587 132.11 98.34 12.794 9.2291 23.931 17.736 24.75 18.905 0.81852 1.1686 1.4882 3.8233 1.4882 5.8993 0 6.885-9.4416 14.949-22.125 18.896-5.8549 1.8222-13.22 1.8812-224.71 1.799-168.01-0.0653-219.82-0.36671-223.66-1.301zm-27.08-56.758c-1.1439-0.55505-2.8314-2.2815-3.75-3.8366-1.5361-2.6004-1.6702-12.794-1.6702-126.97 0-133.68-0.20442-128.26 4.9446-131.02 3.369-1.803 9.5499-1.2127 12.67 1.2102 3.3411 2.594 75.396 63.796 121.38 103.1 17.322 14.805 32.119 27.808 32.881 28.897 2.3519 3.3579 1.9224 9.2816-0.89438 12.335-5.57 6.0384-63.93 50.305-122.34 92.798-29.907 21.756-34.286 24.625-37.5 24.565-2.0013-0.0372-4.5746-0.52182-5.7185-1.0769zm456.47-24.631c-57.735-42.054-115.33-85.728-120.77-91.578-1.7061-1.8345-2.3864-3.7292-2.3864-6.6467 0-2.9085 0.68552-4.8271 2.3864-6.6789 2.6726-2.9098 43.365-37.897 103.39-88.893 57.004-48.432 51.887-44.432 56.802-44.406 4.4421 0.0235 7.6743 1.9336 9.581 5.662 0.80498 1.5741 1.117 36.869 1.117 126.35 0 114.05-0.13565 124.39-1.6653 126.98-1.9742 3.3421-5.1918 4.9895-9.7454 4.9895-2.7544 0-9.3148-4.3691-38.703-25.775zm-209.89-67.157c-5.5418-1.1037-7.2065-2.3276-27.914-20.523-25.525-22.429-91.327-78.784-157.1-134.54-28.869-24.475-53.925-45.891-55.68-47.591-3.5567-3.4453-4.8505-7.1149-3.8766-10.995 0.92358-3.6799 8.6647-11.604 14.368-14.709 11.15-6.0688-3.7659-5.7049 233.86-5.7049 157.04 0 218.71 0.31159 222.54 1.1244 7.0868 1.5037 14.014 5.1108 19.466 10.136 6.96 6.4155 8.633 10.792 6.1819 16.172-0.6331 1.3895-22.781 20.873-49.217 43.297-64.268 54.513-142.6 121.62-166.75 142.84-10.791 9.4844-20.916 17.901-22.5 18.704-3.8346 1.9429-9.092 2.6478-13.38 1.7938z"/>';
            break;
            case 'question':
                $svg = '<g><path d="M306.71 268.215v-4.4a9.826 9.826 0 0 0-9.877-9.775 9.826 9.826 0 0 0-9.775 9.826v4.35a9.775 9.775 0 0 0 9.775 9.775 9.929 9.929 0 0 0 6.96-2.866 9.673 9.673 0 0 0 2.917-6.91ZM307.07 208.489a22.109 22.109 0 0 1 12.436-20.011 51.178 51.178 0 0 0 27.022-56.3 50.257 50.257 0 0 0-84.396-25.585 51.69 51.69 0 0 0-15.35 33.778v2.61a9.775 9.775 0 1 0 19.55 0 31.475 31.475 0 0 1 9.365-22.26 30.707 30.707 0 0 1 21.546-8.8h.819a31.116 31.116 0 0 1 12.59 59 41.915 41.915 0 0 0-23.491 37.514v13.511a9.775 9.775 0 0 0 9.671 9.625 9.826 9.826 0 0 0 10.236-9.775ZM90.586 54.597H33.778A34.085 34.085 0 0 0 0 88.681v297.654a9.877 9.877 0 0 0 2.866 7.011L111.006 502.1a9.57 9.57 0 0 0 6.909 2.917h190.179a34.085 34.085 0 0 0 33.829-34.034v-52.048a9.929 9.929 0 0 0-2.866-6.96 9.724 9.724 0 0 0-6.909-2.866 9.775 9.775 0 0 0-9.724 9.775v52.1a14.33 14.33 0 0 1-14.224 14.279H127.946v-80.811a27.688 27.688 0 0 0-27.585-27.79H19.55V88.681a14.381 14.381 0 0 1 14.228-14.33h56.808a9.878 9.878 0 0 0 0-19.755Zm9.57 341.513a8.086 8.086 0 0 1 7.984 7.933v67.3L33.215 396.11Z"/><path class="c" d="m361.166 351.124 3.48-1.382 38.742 88.18a56.3 56.3 0 0 0 36.746 31.884c1.791.512 3.582.921 5.118 1.228a56.3 56.3 0 0 0 62.54-34.7 57.473 57.473 0 0 0-13-62.591l-68.272-67.351 2.457-2.764a179.483 179.483 0 0 0 15.354-217.1 175.9 175.9 0 0 0-306.149 21.089 177.487 177.487 0 0 0 159.526 255.534 172.777 172.777 0 0 0 63.461-12.027Zm119.911 36.9a37.514 37.514 0 0 1 11.208 26.664v3.173a37.718 37.718 0 0 1-14.432 26.562 37.1 37.1 0 0 1-22.621 7.728 35.815 35.815 0 0 1-6.6-.614l-3.381-.666a37.207 37.207 0 0 1-24.207-20.83l-38.844-88.9 3.02-1.74c4.4-2.508 8.5-5.118 12.59-7.984s7.318-5.374 11.873-9.11l2.712-2.252Zm-211.93-47.135a157.578 157.578 0 0 1-126.615-183.066v-.972a157.189 157.189 0 1 1 154.3 186.545 155.327 155.327 0 0 1-27.687-2.508Z"/></g>';
            break;
            case 'share':
                $svg = '<g transform="translate(-1286 -3984)">
                <path d="M1580.615 4453.508a11.674 11.674 0 0 0 12.8-2.56l201.171-202.552a12.032 12.032 0 0 0 0-16.794l-201.375-202.506a11.623 11.623 0 0 0-12.8-2.611 11.879 11.879 0 0 0-7.271 11.011v104.041h-2.458c-159.943 8.406-285.174 140.799-284.681 300.959a11.93 11.93 0 0 0 7.169 10.96 11.725 11.725 0 0 0 12.8-2.406 396.351 396.351 0 0 1 264.712-112.131h2.662v103.577a11.828 11.828 0 0 0 7.271 11.012Zm-264.762-43.012-5.12 4.045.87-6.4a277.154 277.154 0 0 1 273.518-243.051 11.879 11.879 0 0 0 11.78-11.879v-87.3l172.648 174.085-172.648 174.087v-87.043a11.776 11.776 0 0 0-11.828-11.879h-1.792a422.685 422.685 0 0 0-267.448 95.353Z"/></g>';
            break;
            case 'circle-user':
                $svg = '<g id="Layer_1-2"><g id="Layer_2-2"><g id="Layer_1-2"><path class="cls-1" d="M256.47,0C115.08-.25,.25,114.16,0,255.55-.25,396.93,114.17,511.75,255.56,512c62.51,.11,122.91-22.65,169.79-64l1.02-.94c105.53-94.1,114.78-255.93,20.68-361.45C398.59,31.27,329.28,.14,256.47,0Zm0,490.33c-51.07-.09-100.7-16.97-141.24-48.04,81.93-34.99,97.03-51.88,100.7-63.83,.34-1.05,.52-2.14,.51-3.24v-21.5c-.01-2.81-1.11-5.5-3.07-7.51-14.14-15.24-24.23-33.78-29.36-53.93-.64-2.47-2.12-4.64-4.18-6.14-10.76-7.72-13.22-22.7-5.5-33.46,.31-.43,.64-.86,.98-1.27,1.53-1.96,2.4-4.35,2.47-6.83v-48.3c0-47.02,28.42-72.79,79.71-72.79s79.79,25.6,79.79,72.79v48.04c.05,2.49,.92,4.89,2.47,6.83,8.46,10.31,6.97,25.52-3.34,33.99-.39,.32-.78,.62-1.18,.91-2.09,1.48-3.57,3.66-4.18,6.14-5.14,20.12-15.23,38.63-29.36,53.85-1.98,2.03-3.08,4.76-3.07,7.59v21.85c0,1.07,.18,2.14,.51,3.16,2.22,7.17,7.42,23.47,99.25,63.32-40.68,31.33-90.57,48.34-141.92,48.38Zm160.61-64.26c-79.36-33.62-93.87-49.15-96.52-53.25v-14.42c14.28-16.34,24.77-35.63,30.72-56.49,17.86-15,21.54-41.05,8.53-60.42v-45.23c-.09-59.73-36.95-93.87-101.72-93.87s-101.64,34.99-101.64,93.87v44.54c-6.49,9.42-9.18,20.94-7.51,32.26,1.53,11.07,7.06,21.21,15.53,28.5,5.91,20.98,16.4,40.39,30.72,56.83v15.02c-2.65,4.52-17.07,19.8-98.22,53.59h0C2.64,338.68-2.21,190.6,86.13,96.28,174.46,1.96,322.54-2.89,416.87,85.44c94.33,88.33,99.18,236.4,10.84,330.73-3.32,3.55-6.76,7-10.3,10.33l-.34-.43Z"/></g></g></g>';

            break;
            case 'support':
                $svg = '<g id="Layer_1-2"><g id="Layer_2-2"><g id="Layer_1-2"><path id="Icon_24_7_Online_Support_" class="cls-1" d="M466.93,177.64h-14l-1.23-5.08C427.89,71,347.37,0,255.91,0S84.55,71,60.3,172.65l-1.23,4.99h-14.09c-24.86,0-44.99,23.73-44.99,52.71v66.63c0,29.07,20.13,52.53,44.99,52.53h12.25l.7,5.87c7.96,72.23,61.26,126.69,124.54,126.69h17.07l1.49,4.55c4.74,14.62,18.08,24.75,33.43,25.39h43.24c20.04,0,36.32-19.17,36.32-42.64s-16.45-42.55-36.32-42.55h-43.76c-15.09,.95-28.06,11.06-32.65,25.48l-1.49,4.55h-17.33c-58.03,0-105.03-53.76-105.03-119.86V191.21C95.4,94.91,170.4,25.3,256,25.3s161.21,70.04,178.63,166.35v145.25c-.56,6.39,4.12,12.05,10.5,12.69h21.79c24.86,0,45.07-23.55,45.07-52.53v-66.71c0-28.98-20.3-52.71-45.07-52.71ZM56.89,324.38h-12.43c-14.23-1.12-24.96-13.41-24.16-27.67v-66.36c-.61-14.12,10.07-26.2,24.16-27.32h12.43v121.35Zm161.91,142.8c1.09-7.72,7.16-13.79,14.88-14.88h45.25c4.59,.63,8.75,3.05,11.55,6.74,5.8,7.74,4.23,18.71-3.51,24.51-2.36,1.77-5.13,2.91-8.05,3.33h-45.6c-9.39-1.53-15.84-10.27-14.53-19.7Zm272.8-170.11c.61,14.12-10.07,26.2-24.16,27.32h-12.34v-121.35h12.34c14.27,1.12,25.01,13.46,24.16,27.75v66.28Z"/></g></g></g>';

            break;
            case 'cash':
                $svg = '<path d="m10.089 110.94a11.153 11.153 0 0 0-10.087 11.161v267.8a11.152 11.152 0 0 0 11.133 11.161h489.73a11.152 11.152 0 0 0 11.136-11.162v-267.8a11.152 11.152 0 0 0-11.136-11.16h-489.73c-0.349-0.016-0.7-0.016-1.044 0zm12.174 22.318h49.739a55.6 55.6 0 0 1-49.74 50.036zm72.173 0h84.693c-28.194 29.075-45.563 73.421-45.563 122.74 0 49.42 17.442 93.709 45.737 122.74h-84.867a78.25 78.25 0 0 0-72.173-72.179v-100.94a78.3 78.3 0 0 0 72.173-72.355zm117.91 0h87.13a11.112 11.112 0 0 0 3.478 3.487c30.83 22.031 53.218 66.922 53.218 119.26s-22.387 97.047-53.218 119.08a11.218 11.218 0 0 0-3.3 3.662h-87.309a11.22 11.22 0 0 0-3.3-3.662c-30.83-22.03-53.218-66.746-53.218-119.08s22.387-97.223 53.218-119.26a11.14 11.14 0 0 0 3.3-3.487zm120.52 0h84.693a78.3 78.3 0 0 0 72.173 72.355v100.94a78.251 78.251 0 0 0-72.174 72.184h-84.863c28.3-29.032 45.737-73.32 45.737-122.74 0-49.319-17.368-93.666-45.563-122.74zm107.13 0h49.74v50.036a55.6 55.6 0 0 1-49.74-50.036zm-185.22 38.882a11.149 11.149 0 0 0-9.911 11.333v12.2a49.07 49.07 0 0 0-17.915 6.277c-8.517 5.211-15.477 14.84-15.477 26.152 0 12.092 7.682 21.214 15.827 27.024s17.426 9.278 25.737 11.508c6.378 1.709 13.9 4.884 18.784 8.367s6.432 6.3 6.432 8.892c0 3.257-0.932 4.67-4.693 6.974s-10.365 4.184-17.566 4.184c-8.082 0-22.981-5.62-25.737-8.192a11.149 11.149 0 0 0-15.307 16.213c8.105 7.569 18.947 11.939 29.915 13.6v11.858a11.131 11.131 0 1 0 22.259 0v-12.031a49.42 49.42 0 0 0 17.915-6.453c8.517-5.211 15.477-14.839 15.477-26.152 0-12.092-7.682-21.389-15.827-27.2s-17.426-9.278-25.737-11.508c-6.378-1.708-13.9-4.709-18.784-8.192s-6.432-6.3-6.432-8.892c0-3.257 0.932-4.845 4.693-7.149s10.368-4.013 17.568-4.013c8.082 0 22.981 5.445 25.737 8.021a11.15 11.15 0 0 0 15.307-16.217c-8.106-7.569-18.948-11.636-29.915-13.252v-12.024a11.138 11.138 0 0 0-12.35-11.328zm-232.52 156.73a55.618 55.618 0 0 1 49.744 49.866h-49.74zm467.29 0c0.058 0 0.12 8e-3 0.175 0v49.866h-49.74a55.633 55.633 0 0 1 49.565-49.865z"/>';

            break;
            case 'world':
                $svg = '<g id="Layer_1-2"><g id="Layer_2-2"><g id="Layer_1-2"><g id="Icon_Users_Worldwide_no"><path id="Path_1029" class="cls-1" d="M256.53,0C114.84,0,0,114.66,0,256.08c0,45.27,12.03,89.73,34.85,128.85v1.13c.54,2.1,1.73,3.98,3.39,5.38h0v.52c75.24,119.78,233.5,156,353.5,80.9,120-75.1,156.29-233.08,81.05-352.86C426.1,45.66,344.51,.38,256.61,0m-62.59,39.74c-16.8,21.75-29.66,46.28-37.99,72.46l-.96,3.04H69.62l5.82-7.03c29.39-35.99,68.86-62.42,113.36-75.93l13.13-3.64m-46.51,216.94v-4.51c1.04-33.89,6-67.55,14.78-100.31l.78-3.21h74.24v107.69l-89.8,.35Zm89.8,21.52v105.26h-74.59l-.87-3.21c-8.37-31.88-13.12-64.6-14.17-97.53v-4.51h89.63ZM22.59,240.54c2.4-35.63,12.93-70.23,30.77-101.18l1.3-2.17h93.8l-1.3,5.38c-7.96,32.23-12.47,65.22-13.47,98.4v4.25H22.24l.35-4.69Zm29.64,129.55c-17.07-30.2-27.2-63.81-29.64-98.4v-4.6h110.84v4.25c.99,32.17,5.3,64.16,12.87,95.45l1.3,5.38H53.02l-.78-2.08Zm140.57,111.07c-47.04-13.29-88.7-41-119.1-79.22l-6.52-7.64h87.28l.96,3.04c8.72,28.1,22.83,54.24,41.55,76.97l8.69,10.76-12.87-3.9Zm52.94,1.13l-6-2.6c-30.6-13.28-50.42-55.54-59.46-79.57l-2.26-5.81h67.72v87.99Zm0-454.44V115.41h-67.55l2.26-5.9c8.69-23.08,27.73-63.26,56.07-77.66m221.5,105.34l1.22,2.17c17.93,30.95,28.48,65.6,30.86,101.27v4.69h-111.01v-4.25c-1.14-33.23-5.8-66.24-13.91-98.49l-1.3-5.38h94.15ZM321.72,31.67c45.36,13.24,85.63,39.93,115.45,76.53l6,7.03h-85.8l-.87-3.04c-8.49-26.67-21.68-51.62-38.95-73.67l-8.69-10.67,12.87,3.82Zm-54.85-3.82l6.09,2.69c29.9,13.19,49.64,55.1,58.68,78.88l2.26,5.9h-66.68l-.35-87.47Zm0,109.34h73.98l.87,3.21c8.81,32.78,13.8,66.47,14.87,100.4v4.51h-89.37l-.35-108.12Zm0,130.16h90.15v4.51c-1.05,32.91-5.81,65.6-14.17,97.45l-.87,3.3h-74.76l-.35-105.26Zm0,216.15v-89.2h67.72l-2.17,5.81c-8.69,24.04-28.69,66.38-59.37,79.57m38.6-1.13l3.56-4.34c18.66-22.77,32.76-48.9,41.55-76.97l.96-3.04h87.8l-5.65,7.03c-30.43,38.58-72.3,66.58-119.62,80.01l-15.04,3.73m184.82-213.29c-2.27,32.12-11.15,63.44-26.08,91.98l-4.78,8.68h-94.5l1.3-5.38c7.55-31.3,11.89-63.28,12.95-95.45v-4.25h111.27l-.17,4.43Z"/></g></g></g></g>';

            break;
            case 'arrow-increase':
                $svg = '<path d="M294.017,0l254.86,279.3H36.9Z" transform="translate(-36.877 93.091)"/>';

                break;
            case 'arrow-reduction':
                $svg = '<path d="M294.017,279.3,548.877,0H36.9Z" transform="translate(-36.877 138.615)"/>';

            break;
        }

        $classes = '' !== $classes ? " class=\"{$classes}\"" : '';

        return '<svg' . $classes . ' xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;" height="' . $height . '" width="' . $width . '" viewBox="0 0 512 512">' . $svg . '</svg>';
    }
}

if (!function_exists('widgetComments')) {
    /**
     * @param int $type_id
     * @param array $hash_components
     *
     * @return void
     */
    function widgetComments(int $typeId, array $hash_components): void
    {
        $resource_token = getCommentResourceToken($hash_components);

        /**
         * @var Comment_Resources_Model $comment_resources_model
         */
        $comment_resources_model = model(Comment_Resources_Model::class);

        try {
            $resource = $comment_resources_model->get_resource_by_conditions([
                'conditions' => ['token' => $resource_token],
            ]);

            $resourceId = $resource['id'];
        } catch (NotFoundException $exception) {
            $resourceId = $comment_resources_model->add([
                'id_type'   => $typeId,
                'token'     => $resource_token,
                'context'   => $hash_components,
            ]);
        } catch (Exception $e) {
            return;
        }

        views()->display('new/comments/index_view', [
            'resourceId'    => (int) $resourceId,
            'countComments' => model(Comments_Model::class)->get_count_comments([
                'conditions'    => [
                    'resource'  => (int) $resourceId,
                    'state'     => CommentStates::PUBLISHED,
                ],
            ]),
        ]);
    }
}

if (!function_exists('widgetGetSvgIconEpl')) {
    function widgetGetSvgIconEpl(
        string $name = null,
        int $width = 0,
        int $height = 0,
        string $classes = ''
    ) {
        if (is_null($name)) {
            return;
        }

        switch ($name) {
            case 'facebook':
                $svg = [
                    'path'      => '<path d="m15.659 99.342-0.63478-0.65758v-42.237l-6.8938-0.06412c-8.1999-0.076267-7.8211 0.020641-8.0168-2.0512-0.16748-1.7736-0.14318-15.774 0.02906-16.742 0.090238-0.50729 0.31859-0.89718 0.68633-1.1719 0.5317-0.39719 0.79021-0.41388 7.3416-0.47387l6.7905-0.062178 0.12647-7.7633c0.13994-8.5903 0.24803-9.5727 1.4255-12.956 2.6488-7.6097 7.5637-12.129 15.597-14.343 1.5365-0.4233 2.4097-0.51006 6.8233-0.67802 2.7837-0.10594 7.0189-0.16608 9.4116-0.13364l4.3503 0.058979 1.1967 1.1473-0.019316 7.6296c-0.010624 4.1963-0.076543 8.1122-0.14649 8.702-0.18639 1.5717-0.15124 1.5629-7.0205 1.7622-3.1912 0.092591-6.2417 0.26621-6.7787 0.38582-1.0966 0.2442-2.1777 0.91461-2.7634 1.7136-0.85703 1.1693-0.91343 1.6321-0.99937 8.201l-0.082403 6.299h15.976l1.2485 1.2882-0.011256 8.3893c-0.00619 4.6142-0.07211 8.7314-0.14649 9.1495-0.090245 0.50732-0.31858 0.89716-0.68638 1.1719-0.53577 0.4002-0.77128 0.41343-8.4384 0.47425l-7.8873 0.06256-0.057796 20.934c-0.056169 20.345-0.068504 20.952-0.4381 21.563-0.20917 0.34576-0.51679 0.69366-0.6836 0.77311-0.16682 0.079454-4.4343 0.17618-9.4832 0.21495l-9.1799 0.070488z" stroke-width=".19532"/>',
                    'width'     => '53.892',
                    'height'    => '100',
                ];

            break;
            case 'instagram':
                $svg = [
                    'path'      => '<path d="m19.822 99.337c-7.9841-2.6562-13.947-7.7876-17.495-15.055-0.6293-1.2891-1.3892-3.0471-1.6886-3.9065l-0.54444-1.5626-0.10279-57.189 0.80244-2.3645c3.0345-8.9416 10.005-15.721 19.177-18.651l1.9067-0.60913h56.297l2.0565 0.68815c2.5273 0.84569 5.3556 2.2317 7.5963 3.7227 2.1224 1.4122 5.3797 4.5325 7.0109 6.716 1.5654 2.0954 3.3448 5.5196 4.3173 8.308l0.74933 2.1486 0.10412 56.38-0.69768 2.0853c-3.1333 9.3652-9.7794 16.081-18.977 19.176l-2.3057 0.77586-56.253-0.01342zm51.663-6.5496c3.4094-0.13882 4.2507-0.23645 5.6644-0.65728 3.6655-1.0912 6.7013-2.9013 9.378-5.5917 3.1407-3.1567 5.08-6.7346 5.9873-11.046 0.40054-1.9032 0.5374-18.941 0.29654-36.916-0.21408-15.977-0.12611-15.195-2.1764-19.337-3.0362-6.1341-7.4689-9.6749-14.776-11.803-1.3712-0.3994-1.6892-0.40439-25.798-0.40439h-24.409l-2.1336 0.59797c-7.9098 2.2168-13.402 7.5284-15.842 15.322l-0.55012 1.7568v50.589l0.65595 1.9532c2.3188 6.9048 6.5838 11.599 12.806 14.095 2.7196 1.0908 3.6933 1.2825 7.2428 1.4258 4.8325 0.19503 38.952 0.20775 43.655 0.01627zm-27.088-16.026c-9.1617-1.9884-17.082-8.9188-20.157-17.639-1.1243-3.1875-1.4453-5.2671-1.4532-9.4123-0.00639-3.3587 0.041405-3.907 0.50328-5.7738 1.2972-5.2431 3.5983-9.3083 7.4635-13.185 3.9547-3.967 8.6115-6.4896 14.012-7.5908 2.0641-0.42081 8.101-0.42167 10.316-0.00147 3.5643 0.67613 7.4949 2.3758 10.576 4.5732 2.2145 1.5793 5.4358 4.911 6.9599 7.1983 2.2168 3.3268 3.6866 7.0676 4.2667 10.859 0.45499 2.9739 0.26422 8.1801-0.39059 10.659-1.9241 7.2854-6.4144 13.255-12.85 17.083-1.9893 1.1833-4.6805 2.2998-7.1112 2.9501-1.756 0.46979-2.3504 0.52728-6.1436 0.59427-3.6477 0.064416-4.4351 0.023023-5.9927-0.31504zm10.472-7.9249c3.5685-0.90341 6.5039-2.6555 9.214-5.4998 2.3389-2.4547 3.7716-4.943 4.7085-8.1777 0.39365-1.3591 0.44665-1.9708 0.44665-5.1556 0-3.2041-0.051466-3.7905-0.45429-5.1761-1.0224-3.5168-2.8112-6.4078-5.6228-9.0876-2.755-2.6259-6.1186-4.3039-9.9751-4.9764-4.0622-0.70838-8.5364-0.019257-12.172 1.8748-4.5137 2.3514-7.9973 6.4454-9.707 11.408-1.1173 3.2431-1.0543 8.6946 0.14009 12.11 3.4561 9.8832 13.338 15.233 23.422 12.68zm22.714-42.559c-1.4967-0.46735-2.6148-1.4638-3.5135-3.1311-0.71524-1.3269-0.75794-3.6667-0.089908-4.9265 1.1413-2.1523 2.9416-3.2806 5.2203-3.2718 3.6353 0.014051 6.313 3.1382 5.7488 6.7072-0.18762 1.1869-0.57737 1.9626-1.4982 2.9819-0.8738 0.96719-1.7703 1.4376-3.2672 1.7143-1.4024 0.25924-1.5463 0.25515-2.6003-0.073968z" stroke-width=".19532"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'linkedin':
                $svg = [
                    'path'      => '<path d="m1.2858 63.103v-32.142h21.298v64.285h-21.298zm33.217 6.3015c1.802e-4 -14.212 0.058354-28.677 0.12928-32.142l0.12895-6.3015h21.235v4.2987c0 2.3643 0.065946 4.2964 0.14655 4.2936 0.0806-0.00277 0.45429-0.46412 0.83043-1.0252 1.1012-1.6427 4.0406-4.4486 6.0403-5.766 2.146-1.4138 4.0102-2.1761 6.8557-2.8036 2.6526-0.58496 7.9117-0.63856 10.587-0.10789 9.5958 1.9035 15.92 8.2547 18.346 18.426 0.99798 4.1831 1.0015 4.2679 1.103 26.307l0.095143 20.663h-21.534l-0.014448-15.094c-0.00795-8.3018-0.085559-17.292-0.17247-19.979-0.17409-5.3815-0.33285-6.1701-1.7943-8.912-1.7474-3.2786-4.6838-4.93-8.7655-4.93-4.7953 0-8.6772 2.6032-10.772 7.2239-0.46016 1.0149-0.64416 1.7772-0.77089 3.194-0.091556 1.0235-0.1696 10.104-0.17344 20.179l-0.00697 18.318h-21.493l3.282e-4 -25.841zm-25.011-47.556c-3.8574-0.50067-7.5579-3.4392-8.8041-6.9914-2.1709-6.1875 0.95619-12.502 7.1433-14.425 1.1326-0.35195 1.855-0.42628 4.201-0.43222 3.1511-0.00798 4.2546 0.22257 6.3503 1.3268 2.5139 1.3245 4.8679 4.5761 5.3982 7.4565 0.30019 1.6305 0.089255 4.4655-0.43786 5.8848-1.203 3.2392-3.9455 5.7595-7.4028 6.8032-1.0729 0.32386-5.0613 0.55716-6.448 0.37717z" stroke-width=".19539"/>',
                    'width'     => '100',
                    'height'    => '95.247',
                ];

            break;
            case 'odnoklassniki':
                $svg = [
                    'path'      => '<path d="m7.6625 99.802c-2.319-0.6498-4.175-2.1684-5.0898-4.1646-1.0796-2.3559-0.73292-5.6878 0.79129-7.6044 0.35222-0.44291 3.8086-4.0369 7.6809-7.9866 3.8723-3.9497 7.0643-7.2479 7.0932-7.3292 0.028992-0.081333-0.67567-0.35655-1.5659-0.61159-6.6676-1.9102-13.686-5.5255-15.276-7.8688-1.0327-1.5223-1.3844-2.7974-1.278-4.6344 0.21235-3.6682 2.7778-6.4975 6.4525-7.1161 1.7972-0.30256 3.0042 0.020204 6.1747 1.6511 5.9151 3.0428 9.4708 4.019 15.325 4.2076 6.178 0.19903 10.225-0.74626 16.552-3.8657 1.9443-0.95872 4.0039-1.8437 4.5769-1.9665 3.0788-0.66029 6.5539 1.183 8.082 4.287 0.58657 1.1915 0.63962 1.4538 0.63962 3.1629 0 1.6046-0.071701 2.0166-0.51581 2.9637-1.0232 2.1823-2.5322 3.4128-6.8149 5.5577-3.0178 1.5114-6.1985 2.7844-8.8178 3.5292-1.0227 0.29082-1.8926 0.55843-1.9331 0.59467-0.04044 0.036247 3.3173 3.5356 7.4616 7.7764s7.7599 8.098 8.0345 8.5716c0.65103 1.1227 1.0064 2.9902 0.83301 4.3778-0.29491 2.3604-2.2476 5.0585-4.3709 6.0394-0.77157 0.35645-1.2773 0.42657-3.0765 0.42657-1.8243 0-2.3001-0.067773-3.1155-0.44383-0.75146-0.34656-2.6411-2.1161-8.6224-8.0742-4.213-4.1967-7.7368-7.6304-7.8307-7.6304-0.093883 0-3.4806 3.2876-7.5261 7.3058-7.1961 7.1475-8.4402 8.2564-9.7725 8.7103-1.1293 0.38477-2.9813 0.44579-4.0912 0.13479zm17.974-48.451c-3.2141-0.45106-6.9814-1.7517-9.6282-3.3241-6.5745-3.9058-10.769-9.7868-12.432-17.432-0.50643-2.3277-0.50533-7.6391 0.00205-9.9724 2.0757-9.5452 8.9884-16.989 18.276-19.681 2.5754-0.74632 4.1947-0.95532 7.2906-0.94099 6.7221 0.031114 12.618 2.4247 17.495 7.1019 3.6755 3.5254 5.9918 7.4959 7.3065 12.524 0.66861 2.5573 0.85777 8.0372 0.36726 10.639-1.0087 5.3512-3.5563 10.051-7.5367 13.904-3.6994 3.5809-8.1811 5.9248-13.183 6.8946-1.9383 0.37581-6.2187 0.52931-7.9571 0.28536zm6.5159-15.466c1.5581-0.48126 3.2712-1.5936 4.5413-2.9488 1.9411-2.0712 2.857-4.3208 2.8581-7.0202 0.00131-3.2375-0.86476-5.3349-3.163-7.6603-1.6759-1.6957-3.2111-2.5548-5.3843-3.0131-1.7071-0.35999-2.5861-0.36314-4.2278-0.015137-3.7071 0.78582-6.9352 3.6563-8.016 7.1281-0.46472 1.4928-0.55623 4.3906-0.18946 5.9993 0.52991 2.3243 2.0832 4.5674 4.1807 6.0374 2.8879 2.024 6.053 2.5265 9.4005 1.4926z" stroke-width=".19574"/>',
                    'width'     => '57.821',
                    'height'    => '100',
                ];

            break;
            case 'pinterest':
                $svg = [
                    'path'      => '<path d="m16.806 99.613c-0.50413-0.54112-1.4157-2.7323-2.0656-4.9651-1.2633-4.3404-0.7162-11.927 1.671-23.17 1.6943-7.9803 3.6678-15.207 5.9648-21.842l1.3001-3.7554-0.58608-0.6675c-0.94042-1.0711-1.1165-1.8579-1.0973-4.9044 0.020709-3.2907 0.54414-5.5507 1.9697-8.5045 0.78045-1.6171 1.1397-2.0975 2.7774-3.7146 2.1555-2.1283 3.4874-2.9852 5.3573-3.4466 2.6266-0.64818 4.751 0.14822 6.2055 2.3264 1.2444 1.8635 1.6592 3.4868 1.6349 6.3989-0.02344 2.8185-0.32197 4.3979-1.9216 10.166-1.2863 4.6385-1.5791 5.8896-1.8597 7.9481-0.54308 3.9832 1.1277 7.5654 4.244 9.0996 1.067 0.52526 1.5484 0.63851 3.0116 0.70847 2.1356 0.1021 3.5889-0.16891 5.7444-1.0712 2.2564-0.94455 3.7335-1.9399 5.5383-3.7321 4.8876-4.8534 7.1318-11.937 7.1385-22.533 0.00607-9.6999-1.758-15.817-5.7839-20.055-2.2025-2.3187-4.5302-3.6802-7.6822-4.4937-2.0385-0.52607-6.3742-0.49074-9.123 0.074346-6.9683 1.4325-13.872 5.3281-19.552 11.032-6.9812 7.0108-10.039 15.28-8.1585 22.064 0.88494 3.1934 2.5906 5.9699 4.7668 7.7595 0.50617 0.41625 0.53641 0.52077 0.42428 1.4663-0.11773 0.99275-0.7797 3.2285-1.3815 4.6657-0.16466 0.39329-0.54218 0.90606-0.83892 1.1395-0.66918 0.52637-1.5403 0.43224-3.8993-0.42137-4.8091-1.7402-8.4149-6.4416-10.171-13.261-0.45491-1.767-0.47444-2.0806-0.40172-6.4517 0.07296-4.3856 0.10391-4.6985 0.68133-6.886 0.8252-3.1262 1.6366-5.2976 3.0117-8.0594 4.042-8.1185 10.034-14.169 17.915-18.091 9.5403-4.7472 20.289-5.7332 29.686-2.7231 2.3558 0.75464 6.9846 3.0934 9.0757 4.5857 2.0203 1.4417 5.1763 4.5461 6.8034 6.692 10.291 13.572 11.699 32.889 3.3126 45.427-1.5524 2.3208-3.3723 4.2312-6.2092 6.5175-5.7595 4.642-10.497 6.6158-16.528 6.8858-4.6615 0.20874-8.6453-0.98695-13.933-4.1819-0.99663-0.60215-1.8716-1.0353-1.9443-0.96259-0.15573 0.15573-1.0786 4.2066-2.3033 10.11-1.361 6.5608-2.0918 8.7442-4.1314 12.345-1.6945 2.9912-5.6508 8.6763-7.0766 10.169-0.80846 0.84627-1.0439 0.89243-1.5857 0.31089zm-3.2657-47.183c-0.061893-0.16129-0.14711-0.29326-0.18937-0.29326s-0.076836 0.13197-0.076836 0.29326 0.085216 0.29326 0.18937 0.29326c0.10415 0 0.13873-0.13197 0.076836-0.29326zm-7.1939-27.162c0.73023-1.5805 0.75801-1.6746 0.49429-1.6746-0.15034 0-1.3868 2.6897-1.3868 3.0167 0 0.38644 0.30749-0.075966 0.89249-1.3421z" stroke-width=".19551"/>',
                    'width'     => '75.977',
                    'height'    => '100',
                ];

            break;
            case 'twitter':
                $svg = [
                    'path'      => '<path d="m26.589 81.758c-8.9218-0.8373-16.849-3.2107-24.017-7.1903-3.4562-1.9189-3.4709-1.7966 0.20627-1.7191 7.3589 0.15499 13.379-1.1531 19.855-4.314 2.6484-1.2927 7.0215-3.9939 7.0215-4.3371 0-0.089788-0.55628-0.2212-1.2362-0.29204-3.1936-0.33271-6.9338-1.669-9.6007-3.43-1.5213-1.0046-4.2425-3.6342-5.3162-5.1372-0.99031-1.3863-2.6296-4.5328-2.634-5.0555-0.00252-0.30145 0.20774-0.34688 1.6289-0.3519 2.3518-0.00831 6.6312-0.34541 6.9992-0.55133 0.2392-0.13386 0.086877-0.23311-0.63778-0.41559-5.1055-1.2856-10.014-5.0558-12.769-9.8069-1.5551-2.6824-2.4245-5.5147-2.6689-8.6947l-0.1322-1.7199 0.77207 0.33798c2.5577 1.1197 6.0954 2.0662 7.7224 2.0662h0.68513l-1.1476-0.96111c-3.5175-2.9459-6.1525-6.9873-7.183-11.016-0.51144-1.9997-0.56839-8.216-0.092379-10.084 0.39766-1.5606 1.6662-4.7801 1.9889-5.048 0.19623-0.16285 1.0611 0.59099 3.7065 3.2307 1.8978 1.8937 4.3735 4.1804 5.5017 5.0815 7.3308 5.8557 16.247 10.007 25.265 11.762 2.4665 0.48018 8.2809 1.2198 8.4286 1.0722 0.041827-0.041827-4e-3 -0.69771-0.10185-1.4575-0.4097-3.1816-0.090513-7.035 0.81074-9.7875 2.0364-6.2196 7.1441-11.158 13.833-13.374 1.6201-0.53671 1.8287-0.55721 5.7359-0.56378 3.7106-0.00624 4.1806 0.032534 5.5381 0.45686 3.2711 1.0225 6.1876 2.6228 8.4061 4.6125 0.54392 0.48784 1.152 0.94712 1.3514 1.0206 0.85231 0.31428 6.2769-1.4649 10.201-3.3458 1.4586-0.6991 2.6945-1.2285 2.7466-1.1765 0.14041 0.14041-0.69021 1.9984-1.5697 3.511-1.4969 2.5747-3.7733 5.1058-6.0686 6.7476-0.83114 0.59451-0.85116 0.63073-0.38024 0.68791 0.77086 0.093607 4.909-0.89146 7.8372-1.8656 1.4553-0.48415 2.6766-0.84965 2.714-0.81223 0.15561 0.15561-1.3911 2.1885-3.1323 4.1167-1.7664 1.9562-3.6653 3.7471-5.7183 5.3932l-0.9193 0.73706-0.12495 2.9101c-0.27023 6.2937-0.77278 10.033-1.9828 14.754-2.078 8.1078-6.8253 17.325-12.236 23.758-8.1287 9.6639-18.954 16.244-31.188 18.958-5.5377 1.2286-13.056 1.7655-18.098 1.2922z" stroke-width=".19779"/>',
                    'width'     => '100',
                    'height'    => '81.927',
                ];

            break;
            case 'vkontakte':
                $svg = [
                    'path'      => '<path d="m44.588 58.194c-7.8617-1.6721-15.521-6.0743-21.694-12.469-6.111-6.3304-13.88-18.335-18.328-28.322-2.5381-5.6983-4.2731-10.997-4.5366-13.856-0.14234-1.5444 0.23309-2.4305 1.2956-3.0579 0.62145-0.36697 1.074-0.3948 7.3442-0.45156 7.6228-0.069002 7.9551-0.025331 9.1663 1.2046 0.8371 0.85003 0.94157 1.0732 3.1257 6.6772 1.3558 3.4787 4.4535 10.026 6.1863 13.075 4.1784 7.3523 8.2825 12.051 10.526 12.051 0.81291 0 1.4814-0.51897 1.7072-1.3253 0.081959-0.29277 0.14902-5.1078 0.14902-10.7v-10.168l-0.53899-1.5903c-0.37552-1.1079-0.9963-2.2644-2.0468-3.8129-1.2959-1.9102-1.5087-2.3415-1.5141-3.0677-0.00553-0.74465 0.091556-0.93631 0.81717-1.6132l0.82344-0.7681 8.9532 0.014275c4.9243 0.00785 9.42 0.080688 9.9906 0.16186 0.71638 0.10192 1.1909 0.30118 1.5338 0.64402 0.46072 0.46072 0.50857 0.68468 0.66513 3.1134 0.09279 1.4393 0.17816 6.2686 0.1897 10.732 0.024482 9.4658 0.17251 12.396 0.6641 13.146 1.5109 2.306 6.0319-1.4856 11.362-9.529 2.3983-3.6191 3.5871-5.7118 6.4658-11.382 1.3219-2.6038 2.6065-5.0104 2.8547-5.3479 0.24824-0.33751 0.8033-0.80213 1.2335-1.0325 0.74497-0.39898 1.084-0.42207 7.1343-0.48605 7.3908-0.078145 8.2084-0.01332 9.024 0.71551 1.2458 1.1132 0.9159 3.0759-1.2723 7.5689-1.8338 3.7654-3.5018 6.5214-8.6737 14.332-4.7726 7.207-5.1369 7.8333-5.1369 8.8311 0 1.0617 0.55732 1.7672 5.5089 6.9732 4.7519 4.9962 6.3858 6.8398 8.3623 9.4364 2.8768 3.7792 4.3251 6.769 4.0332 8.3254-0.15885 0.84672-0.97456 1.8302-1.8062 2.1777-0.49965 0.20877-2.1921 0.26672-7.7894 0.26672-6.8824 0-7.1813-0.015448-7.9573-0.41131-0.49249-0.25125-3.1337-2.7486-6.787-6.4176-6.3693-6.3964-8.5636-8.3082-11.065-9.6401-1.4956-0.79641-1.6391-0.83305-3.2693-0.83439-1.4584-0.0012-1.763 0.0565-2.1058 0.3989-0.62397 0.62322-0.78146 2.2437-0.78778 8.1054-0.00319 2.9575-0.084637 5.717-0.181 6.1323-0.09636 0.4153-0.42067 1.0113-0.72069 1.3245-0.93935 0.98047-2.0441 1.1922-6.6228 1.2691-3.7328 0.062744-4.3042 0.026912-6.2833-0.39401z" stroke-width=".19554"/>',
                    'width'     => '100',
                    'height'    => '58.661',
                ];

            break;
            case 'whatsapp':
                $svg = [
                    'path'      => '<path d="m3.3476 89.655c1.9463-5.7403 3.9364-11.612 4.4224-13.049l0.88355-2.6124-1.7682-3.6476c-2.6648-5.4972-3.6413-8.4177-4.5557-13.625-0.52416-2.9849-0.73146-9.953-0.3935-13.227 0.90067-8.7253 4.6856-18.01 10.154-24.908 1.5915-2.0075 5.3161-5.7701 7.5346-7.6115 5.803-4.8164 13.057-8.3182 20.672-9.9795 4.0155-0.87594 5.1668-0.98443 10.564-0.99548 5.6459-0.011564 7.2059 0.15715 11.704 1.2658 14.136 3.4843 26.052 12.97 32.309 25.72 3.5449 7.224 5.0974 13.884 5.0846 21.812-0.00605 3.7558-0.23284 6.16-0.87229 9.2471-0.9399 4.5376-2.348 8.5747-4.4194 12.67-4.7214 9.3355-12.413 16.891-22.069 21.678-6.7533 3.3484-13.84 4.9802-21.639 4.9827-7.6966 0.0025-14.647-1.529-21.21-4.6736l-2.6913-1.2895-9.9597 3.1639c-5.4778 1.7402-11.5 3.6511-13.383 4.2464-1.8829 0.59538-3.5321 1.1242-3.6648 1.1751-0.13276 0.050923 1.3511-4.604 3.2974-10.344zm51.101-0.16947c3.3406-0.24087 5.8968-0.75592 9.3136-1.8766 15.223-4.9929 26.39-18.573 28.189-34.278 0.3417-2.9828 0.14633-9.1497-0.37726-11.908-2.6714-14.075-12.222-25.621-25.595-30.944-10.734-4.2717-22.914-3.7808-33.254 1.3405-8.4956 4.2074-15.113 10.79-19.14 19.038-6.2245 12.751-5.2775 27.998 2.4832 39.978l1.2188 1.8815-2.4032 7.1724c-1.3217 3.9448-2.3727 7.352-2.3354 7.5715 0.063643 0.37487 0.48953 0.26459 7.0125-1.8158 3.8196-1.2182 7.2969-2.3073 7.7272-2.4201 0.75398-0.19777 0.86808-0.15863 3.13 1.0736 7.9781 4.3463 14.919 5.8447 24.031 5.1877zm7.9167-17.988c-2.5371-0.46029-8.7883-2.6587-12.216-4.2962-6.0338-2.8823-11.305-7.5347-16.737-14.772-3.5982-4.7942-5.0968-7.5738-6.0501-11.222-0.51063-1.9542-0.54697-5.1012-0.079467-6.8823 0.7449-2.8381 2.9201-6.109 4.7958-7.2118 0.79326-0.46637 1.115-0.52583 3.1838-0.58839 2.7883-0.084318 2.9969 0.015567 3.933 1.8835 0.94981 1.8952 4.036 9.6928 4.036 10.197 0 0.68524-0.73513 1.9438-2.2775 3.899-0.79192 1.0039-1.4919 2.0327-1.5555 2.2862-0.39414 1.5704 4.9175 8.0853 9.2086 11.295 1.9102 1.4286 5.9878 3.6156 7.7771 4.1711 1.4104 0.43784 1.6136 0.34014 3.1821-1.5298 3.369-4.0165 3.5084-4.1548 4.2522-4.2178 0.47167-0.03997 1.2686 0.17318 2.4027 0.64265 2.2075 0.91381 8.6934 4.1459 9.166 4.5677 0.32563 0.29058 0.3544 0.53415 0.25812 2.1855-0.13244 2.2717-0.48456 3.466-1.4241 4.8301-2.151 3.1231-7.9815 5.4651-11.856 4.7622z" stroke-width=".19563"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'youtube':
                $svg = [
                    'path'      => '<path d="m14.539 99.919c-3.6434-0.11428-4.4662-0.19648-5.3808-0.53761-3.8806-1.4474-6.3624-3.6341-8.0598-7.1015l-0.8135-1.6618-0.13872-4.3011c-0.20152-6.2486-0.19297-26.985 0.012222-29.633 0.12733-1.6435 0.30696-2.5867 0.65639-3.4467 1.3796-3.395 4.3151-6.1269 8.188-7.6199l1.4306-0.55148h62.366l1.5851 0.61158c3.736 1.4415 6.6689 4.2013 8.0335 7.5598 0.34942 0.85994 0.52906 1.8032 0.65639 3.4467 0.2059 2.6575 0.21349 23.412 0.010839 29.633l-0.1401 4.3011-0.72139 1.4933c-0.92341 1.9115-2.5379 3.9727-3.9422 5.033-1.3903 1.0497-4.006 2.3087-5.2999 2.551-1.1953 0.22384-52.212 0.41906-58.443 0.22362zm14.039-9.4948c1.4998-0.39063 2.7776-1.0618 3.8903-2.043l0.9199-0.8113 0.11524 2.8526 4.5944 0.11016v-26.601h-5.6697v20.885l-0.60159 0.5062c-1.0433 0.87788-1.9742 0.90307-2.5269 0.068383-0.249-0.37598-0.30149-2.0131-0.3511-10.951l-0.058327-10.509h-5.8463l0.00463 9.7264c0.00656 13.785 0.20515 15.572 1.8226 16.409 1.3643 0.70551 2.0993 0.77642 3.7069 0.35766zm41.583-0.12417c2.7911-0.95327 4.5518-3.4806 4.8274-6.9295 0.17181-2.1502 0.082677-2.2344-2.3647-2.2344h-2.0193l-0.09991 1.8085c-0.054951 0.99463-0.21604 2.057-0.35796 2.3607-0.3527 0.75489-1.1294 1.1095-2.4302 1.1095-0.60209 0-1.3372-0.12537-1.6335-0.27859-0.94541-0.48889-1.085-1.0719-1.1622-4.8534l-0.070843-3.4702h10.003l-0.00347-3.7635c-0.00599-6.494-0.335-8.3138-1.7623-9.7473-1.2408-1.2462-2.6155-1.7387-4.8533-1.7387-3.4134 0-5.651 1.2291-6.9408 3.8124-0.87234 1.7472-1.006 3.1494-0.997 10.46 0.00725 5.8918 0.052467 6.8932 0.35751 7.9179 0.84316 2.8322 2.7146 4.8566 5.262 5.692 0.93076 0.30523 3.1499 0.22952 4.2461-0.14487zm-5.0504-19.184c0.090898-2.336 0.34271-2.8744 1.575-3.3675 1.112-0.44492 2.1481-0.24309 2.9696 0.5785 0.73647 0.73647 0.89585 1.3327 0.89585 3.351v1.4419h-5.5185zm-47.785 3.8123 0.050054-15.494h6.8403v-4.6921h-18.573v4.4966h5.8652v31.291l5.7674-0.10821zm36.406 15.249c0.87219-0.4391 1.8137-1.6883 2.3526-3.1214 0.40875-1.087 0.42907-1.4912 0.52888-10.515l0.10379-9.3842-0.66049-1.2619c-0.747-1.4272-1.8757-2.4158-3.1495-2.7588-1.0425-0.2807-2.798 0.16139-4.3485 1.095l-1.0777 0.64895v-10.138h-5.8652v35.398l4.7781-0.10979 0.17202-0.87977c0.094608-0.48388 0.20611-0.9153 0.24776-0.95873 0.041661-0.043427 0.6137 0.29839 1.2712 0.75959 2.1705 1.5225 4.1873 1.9601 5.647 1.2253zm-5.0458-4.4845c-1.3372-0.65888-1.3029-0.40637-1.3029-9.5789v-8.2649l0.59621-0.59638c0.70616-0.70636 1.6348-0.90504 2.7838-0.59563 1.5496 0.41726 1.5076 0.15029 1.5076 9.5821 0 6.1703-0.061347 8.4247-0.23762 8.7321-0.29466 0.51388-1.3959 1.0534-2.138 1.0475-0.30633-0.00245-0.85047-0.14908-1.2092-0.32583zm-28.575-57.12v-10.529l-3.6356-8.5822c-1.9996-4.7202-3.713-8.7801-3.8075-9.0221l-0.17187-0.43989h6.5593l1.7204 6.0118c0.94618 3.3065 1.8092 6.2318 1.9178 6.5006 0.18383 0.45497 0.23044 0.39414 0.67403-0.87977 0.40901-1.1746 2.5146-8.3227 3.1853-10.814l0.22059-0.81919h3.0068c1.6538 0 3.1332 0.0485 3.2876 0.10778 0.21861 0.083886-0.51461 2.1081-3.3076 9.1312l-3.5884 9.0234v20.839h-6.0607zm17.84 10.24c-2.371-0.66907-3.3857-1.6624-4.2769-4.1869-0.44965-1.2738-0.45601-1.4055-0.46008-9.525-0.00236-4.7086 0.080016-8.6842 0.19242-9.2865 0.44747-2.3976 1.9859-4.4814 4.0113-5.4335 0.72108-0.33895 1.246-0.40945 3.0486-0.40945 2.0455 0 2.2532 0.037191 3.4231 0.61321 2.0905 1.0292 3.3959 2.8178 3.9757 5.4475 0.17171 0.77874 0.21619 3.2589 0.16127 8.9932-0.072614 7.582-0.09411 7.967-0.50666 9.0752-0.87793 2.3582-2.2323 3.8999-3.9794 4.5301-1.2225 0.4409-4.3154 0.54175-5.5894 0.18226zm3.545-4.4789c0.35691-0.14913 0.85995-0.56702 1.1179-0.92865l0.46895-0.65752v-8.3176c0-9.4047 0.052658-9.0716-1.5459-9.7787-1.0545-0.46644-1.9257-0.26036-2.8259 0.66845l-0.63987 0.66018 0.062022 8.4905c0.068909 9.4335 0.044474 9.2611 1.3965 9.8492 0.79482 0.34573 1.1664 0.3484 1.9664 0.01413zm12.821 3.9368c-0.62522-0.31527-1.0437-0.72764-1.4774-1.4557-0.54012-0.90683-0.61878-1.2405-0.73314-3.1096-0.070461-1.1516-0.12811-6.796-0.12811-12.543v-10.449l2.4276 0.06898c1.3352 0.037938 2.5009 0.14218 2.5904 0.23164 0.08954 0.089468 0.21043 5.0602 0.26862 11.046 0.078601 8.084 0.16776 10.996 0.34661 11.319 0.32115 0.5807 0.83885 0.83538 1.6982 0.83538 0.9154 0 1.4311-0.2848 1.7031-0.94054 0.14631-0.35278 0.25578-4.0741 0.33277-11.312 0.063104-5.9321 0.18803-10.859 0.27761-10.949 0.089584-0.089485 1.2113-0.19396 2.4926-0.23216l2.3298-0.069469v27.607l-6.354 2.431e-4 0.1322-2.0531-0.94587 1.013c-0.52023 0.55715-1.1936 1.107-1.4963 1.2219-0.93907 0.35647-2.5073 0.25193-3.4649-0.23097z" stroke-width=".19551"/>',
                    'width'     => '83.233',
                    'height'    => '100',
                ];

            break;
            case 'bell-clock':
                $svg = [
                    'path'      => '<path d="m38.897 99.807c-6.059-1.1491-10.846-5.6485-12.348-11.607l-0.17352-0.68815-21.097-0.090618-1.1026-0.5392c-3.1629-1.5467-4.8159-5.0749-3.9447-8.4196 0.41347-1.5874 1.008-2.4615 2.8738-4.2254 4.7116-4.4542 7.425-9.1991 8.7936-15.378 0.32971-1.4884 0.36001-2.1984 0.46795-10.963 0.10509-8.5333 0.1462-9.5397 0.45949-11.246 1.686-9.1847 7.268-16.802 15.338-20.93 3.0038-1.5365 6.4244-2.6425 7.6556-2.4754 1.976 0.26822 3.1323 2.2301 2.4797 4.2073-0.37112 1.1245-1.0265 1.525-4.0901 2.4994-4.5833 1.4577-8.8521 4.6899-11.598 8.7818-1.6491 2.4572-2.6486 4.71-3.3253 7.4954-0.52681 2.1684-0.56425 2.8891-0.63144 12.156-0.045 6.2063-0.11338 8.2687-0.3182 9.5974-1.2219 7.9265-4.4616 14.336-10.08 19.943-1.2872 1.2845-2.0808 2.2014-2.0808 2.4041 0 0.18023 0.14669 0.47439 0.32598 0.65368 0.32394 0.32394 0.5433 0.32598 35.018 0.32598 33.402 0 34.702-0.011083 34.962-0.29807 0.57822-0.63892 0.36523-1.0517-1.4587-2.8274-3.017-2.9371-5.0786-5.6879-6.771-9.0344-0.714-1.4118-0.84402-1.8061-0.82798-2.5106 0.045774-2.0096 1.9065-3.4385 3.8101-2.9259 0.97528 0.26262 1.8335 1.2132 2.8691 3.1777 1.2306 2.3344 3.1128 4.7704 5.2672 6.8165 2.2025 2.0919 2.8197 2.9102 3.2529 4.3125 1.0997 3.5603-0.53708 7.2781-3.9159 8.8949l-1.0594 0.50696-20.656 0.16299-0.45957 1.4102c-1.7606 5.4026-5.7333 9.1562-11.209 10.591-1.6699 0.43757-4.7282 0.54298-6.4238 0.22141zm5.7471-6.4973c2.1504-0.74784 3.9307-2.2125 5.0896-4.1874 1.0479-1.7857 1.8804-1.6194-8.1088-1.6194-5.8659 0-8.72 0.05487-8.72 0.16764 0 0.092201 0.23002 0.62396 0.51115 1.1817 0.73428 1.4567 2.6588 3.3291 4.1322 4.0203 2.3146 1.0858 4.7917 1.2384 7.0958 0.43717zm25.157-39.554c-1.7705-0.20175-4.7644-0.9872-6.798-1.7835-8.3429-3.2667-14.616-10.847-16.42-19.842-0.49772-2.4817-0.61117-6.6933-0.24348-9.0381 1.6748-10.68 9.0497-19.096 19.326-22.055 6.4793-1.8653 13.524-1.1829 19.548 1.8937 2.6347 1.3455 4.6725 2.84 6.8562 5.0283 2.2478 2.2525 3.7335 4.2978 5.0348 6.931 5.386 10.899 3.1281 23.45-5.7166 31.776-3.5788 3.369-8.2564 5.7452-13.335 6.7744-1.9736 0.39992-6.1189 0.55816-8.2526 0.31503zm7.8451-6.5875c4.3999-1.1157 7.608-2.9749 10.575-6.1288 3.6149-3.8427 5.5471-8.7087 5.5291-13.925-0.012079-3.5029-0.69113-6.432-2.1726-9.372-6.1589-12.222-21.84-15.321-32.238-6.371-1.4215 1.2234-3.1158 3.2945-4.2455 5.1896-2.7148 4.554-3.5058 10.356-2.1287 15.613 1.2811 4.891 4.3729 9.2314 8.5607 12.018 2.4267 1.6146 5.3181 2.7745 7.9706 3.1975 0.62751 0.10008 1.3243 0.21356 1.5484 0.25218 0.22411 0.038626 1.4343 0.040819 2.6893 0.00487 1.7412-0.04987 2.6681-0.16329 3.9118-0.47864zm-5.0527-8.0193c-0.75507-0.39896-1.0795-0.73155-1.4049-1.44-0.27561-0.6002-0.29967-1.2099-0.30296-7.6787l-0.00358-7.0267-1.4575-0.083104c-1.1833-0.067469-1.5946-0.16344-2.1862-0.51014-0.89189-0.52268-1.3927-1.2957-1.5119-2.3338-0.12866-1.12 0.55157-2.4014 1.557-2.9331 0.68301-0.36118 0.87095-0.37849 4.0725-0.37508 3.9104 0.00417 4.3918 0.12651 5.1793 1.3162l0.459 0.69346 0.049385 8.9765c0.051275 9.3201 0.025554 9.7489-0.63519 10.589-0.78119 0.99312-2.6951 1.3973-3.8149 0.80555z" stroke-width=".16299"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'blockchain':
                $svg = [
                    'path'      => '<path d="m1.5716 99.557c-0.35839-0.2439-0.83582-0.69203-1.061-0.99583-0.40669-0.54881-0.40967-0.6584-0.46154-17.004-0.051403-16.196-0.046047-16.464 0.34527-17.254 0.80013-1.6151 0.58932-1.5725 8.3164-1.6829l6.8484-0.097835 0.10073-24.948h-6.5149c-6.3671 0-6.5347-0.00999-7.388-0.44026-0.63779-0.32162-0.99169-0.67552-1.3133-1.3133l-0.44026-0.87306v-16.205c0-17.934-0.063094-17.047 1.2847-18.075l0.73922-0.56388 33.034-0.10437 0.77589 0.43607c0.42674 0.23984 0.96965 0.74761 1.2065 1.1284 0.41838 0.67266 0.43231 0.88242 0.49121 7.3954l0.060617 6.7031 24.924-0.10072 0.19567-13.774 0.67929-0.70057c0.37361-0.38531 0.98997-0.79061 1.3697-0.90065 0.4532-0.13134 6.282-0.18 16.966-0.14161 16.087 0.057792 16.281 0.063141 16.815 0.4619 0.29715 0.22189 0.72182 0.64659 0.94371 0.94378 0.40013 0.53593 0.40343 0.6777 0.40343 17.339 0 16.649-0.0036 16.804-0.40279 17.351-0.22154 0.30384-0.79051 0.75055-1.2644 0.9927-0.8425 0.4305-1.006 0.44026-7.3765 0.44026h-6.5149l0.10073 24.948 6.8484 0.097835c7.6608 0.10944 7.4523 0.070487 8.2822 1.5471l0.43162 0.76797-0.052186 16.536c-0.051856 16.431-0.054779 16.54-0.46152 17.089-0.22513 0.3038-0.70042 0.75048-1.0562 0.99262l-0.64687 0.44026h-16.459c-15.645 0-16.493-0.017914-17.135-0.36227-0.37152-0.19925-0.85443-0.60179-1.0731-0.89453-0.37735-0.50511-0.40263-0.88341-0.49547-7.4156l-0.097835-6.8833-24.929-0.10072-0.058477 6.7031c-0.064788 7.4266-0.071793 7.4694-1.3763 8.3998l-0.63888 0.45568-33.294 0.10424zm29.739-10.52v-4.6961h-6.7217c-6.6485 0-6.7287-0.00479-7.3685-0.44026-1.1011-0.74942-1.4655-1.4098-1.4655-2.6559 0-1.3632 0.55541-2.2537 1.7417-2.7924 0.73-0.33153 1.3867-0.36463 7.3116-0.36854l6.5095-0.0043-0.10492-9.2943-24.948-0.10073v25.049h25.046zm62.419-7.8303v-12.526l-9.2943 0.10492-0.097835 6.7506c-0.11013 7.599-0.1044 7.5692-1.6155 8.4184-1.4568 0.81876-3.2776 0.29351-4.1568-1.1991-0.36725-0.62349-0.39507-1.0772-0.45197-7.3696l-0.060628-6.7052-9.2709 0.10486-0.10073 24.948h25.049zm-31.111-9.8288c6.926e-4 -6.3421 0.021594-6.7359 0.38955-7.3394 0.21385-0.35074 0.67579-0.81268 1.0265-1.0265 0.6035-0.36796 0.9973-0.38886 7.3394-0.38955l6.7017-7.318e-4v-25.027l-6.7366-0.05847c-6.481-0.056252-6.7575-0.074001-7.2887-0.4678-0.30365-0.22513-0.7502-0.70042-0.99234-1.0562-0.43546-0.63983-0.44026-0.72003-0.44026-7.3685v-6.7217h-25.046l-0.00467 6.2125c-0.00257 3.4169-0.083772 6.5535-0.18045 6.9702-0.21903 0.94405-1.2391 2.0218-2.1542 2.276-0.38878 0.10801-3.4663 0.19314-7.0039 0.19376l-6.3103 0.00109v25.046h6.7217c6.6496 0 6.7287 0.00473 7.3759 0.44092 0.35986 0.24251 0.83515 0.7488 1.0562 1.1251 0.38049 0.6477 0.40504 1.0363 0.46051 7.288l0.058588 6.6038h25.026l7.318e-4 -6.7017zm-46.92-46.898c0.054069-6.5822 0.071246-6.8548 0.46547-7.3865 0.78262-1.0557 1.6278-1.4768 2.7784-1.3844 1.2631 0.1014 1.9467 0.56424 2.5356 1.7168 0.43049 0.84249 0.44026 1.0061 0.44026 7.3751v6.5135h9.3921v-25.046h-25.046v25.046h9.3777zm78.03-5.6885v-12.523h-25.053l0.10492 9.2943 6.7855 0.097835c6.4978 0.093686 6.8089 0.11519 7.3376 0.50716 1.0556 0.78265 1.4767 1.6278 1.3844 2.7784-0.1014 1.2631-0.56424 1.9467-1.7168 2.5356-0.84249 0.43049-1.0061 0.44026-7.3751 0.44026h-6.5135v4.5656c0 2.5111 0.058701 4.6243 0.13045 4.6961 0.071745 0.071745 5.707 0.13045 12.523 0.13045h12.392z" stroke-width=".19567"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'browser':
                $svg = [
                    'path'      => '<path d="m12.763 97.373c-6.3766-1.0962-11.398-6.0748-12.5-12.392-0.23069-1.3229-0.26544-5.3907-0.26345-30.836 0.00219-27.879 0.018184-29.389 0.32722-30.887 1.1566-5.6039 5.2314-9.9565 10.916-11.66 1.15-0.34468 1.7492-0.38335 6.9652-0.44945l5.707-0.07232 0.46048-1.0835c1.0383-2.443 3.2182-5.2086 5.4153-6.8703 1.4141-1.0695 3.8623-2.2401 5.7063-2.7285l1.4864-0.39366h48.965l1.5369 0.48208c6.4589 2.026 11.123 7.3723 12.28 14.075 0.23458 1.3593 0.26133 5.1173 0.21574 30.308-0.049237 27.206-0.069183 28.813-0.36984 29.806-1.3675 4.5148-4.0431 7.7278-8.1563 9.7939-2.9805 1.4972-4.848 1.8347-10.154 1.835l-3.9484 2.396e-4 -0.55383 1.458c-1.6687 4.3929-5.317 7.7404-10.086 9.2545l-1.2241 0.38862-26.144 0.023284c-14.379 0.012807-26.341-0.010537-26.581-0.051875zm52.382-5.0476c3.1052-0.80604 5.5719-2.8274 6.8457-5.6097 0.41948-0.91626 0.43288-1.0202 0.18619-1.4445-0.51636-0.88812-0.5304-1.4724-0.05232-2.1768 1.046-1.5413 0.97026 0.32969 0.97026-23.978v-22.305h-68.207l0.093506 47.828 0.40792 1.0882c0.92343 2.4635 2.7758 4.6022 4.9347 5.6975 2.3985 1.2169 0.46463 1.1382 28.415 1.1555 22.357 0.013783 25.487-0.016434 26.406-0.25492zm-33.822-12.362c-0.5882-0.19562-10.154-8.2753-10.86-9.1727-0.19936-0.25344-0.41042-0.65188-0.46904-0.88541-0.30416-1.2119 1.0115-2.8932 2.4305-3.106 1.1209-0.16809 1.7004 0.14567 4.0787 2.2084 1.1517 0.99891 2.8089 2.4218 3.6826 3.162l1.5886 1.3458 4.022-5.3679c3.8799-5.1782 6.491-8.8036 9.7748-13.572 0.85533-1.2419 1.7657-2.4137 2.023-2.6038 0.71727-0.53005 2.0245-0.59021 2.9182-0.13428 1.0114 0.51596 1.4618 1.2734 1.3729 2.3088-0.060418 0.70396-0.37583 1.2541-2.4064 4.197-5.2336 7.5852-14.506 20.094-15.758 21.259-0.49885 0.464-1.5926 0.62855-2.3979 0.36074zm55.516 1.0909c4.1699-1.054 7.1739-3.9579 8.1406-7.8695 0.19433-0.78635 0.2377-5.2049 0.2377-24.22v-23.258l-17.4-0.090702v55.788h3.8193c3.241 0 4.0287-0.05294 5.2025-0.34962zm-13.798-51.807c-0.12515-3.0817-0.37007-4.1949-1.3754-6.2518-0.60002-1.2275-1.0185-1.7775-2.4028-3.1577-1.4231-1.4188-1.9275-1.7953-3.3398-2.4924-2.8804-1.4218-1.1353-1.3384-28.081-1.3418-23.348-0.00296-24.009 0.00603-25.331 0.34415-0.74662 0.19095-1.8019 0.5987-2.3451 0.90611-1.4497 0.82044-3.5069 2.9616-4.1725 4.3427-0.94381 1.9585-1.0995 2.9092-1.0995 6.7138v3.4281l33.795 0.044609c18.587 0.024535 33.945 0.064082 34.128 0.087881 0.319 0.041436 0.32854-0.069887 0.22484-2.6236zm-58.05-2.1278c-0.89798-0.3232-1.1794-0.58705-1.573-1.475-1.0135-2.2861 1.6885-4.6767 3.7306-3.3006 0.9896 0.66689 1.4294 1.4746 1.3379 2.4571-0.16392 1.7594-1.8822 2.8991-3.4954 2.3185zm8.8974 0.067032c-2.1631-0.64765-2.4969-3.7147-0.52728-4.8439 1.8394-1.0545 3.9165 0.18623 3.9165 2.3396 0 0.82833-0.072058 1.0152-0.62928 1.632-0.36743 0.4067-0.89499 0.77034-1.2678 0.8739-0.73193 0.2033-0.80808 0.20322-1.4921-0.00158zm8.7229-0.078744c-1.9273-0.68421-2.3988-3.1001-0.86744-4.4446 1.055-0.92631 2.4323-0.92311 3.4929 0.00812 2.1717 1.9067 0.10164 5.4047-2.6254 4.4365zm62.636-8.8805c-0.12941-2.6984-0.54627-4.5395-1.4323-6.3262-1.5917-3.2096-3.9527-5.3709-7.246-6.6333-0.96987-0.37177-0.97899-0.37192-24.666-0.42133-26.499-0.05527-24.98-0.12079-27.593 1.1898-0.74186 0.37203-1.8027 1.0818-2.3575 1.5773-0.98723 0.88174-2.5763 2.9101-2.5763 3.2886 0 0.1364 5.1305 0.20516 17.968 0.24084l17.968 0.049927 1.5739 0.49169c4.0754 1.2732 7.3249 3.9634 9.2792 7.6823l0.62033 1.1804h18.572z" stroke-width=".17488"/>',
                    'width'     => '100',
                    'height'    => '97.428',
                ];

            break;
            case 'browser-secure':
                $svg = [
                    'path'      => '<path d="m2.2152 85.002c-0.76311-0.21685-1.4767-0.80671-1.8731-1.5482l-0.34237-0.64051v-80.521l0.42424-0.7217c0.2751-0.468 0.67794-0.87084 1.1459-1.1459l0.7217-0.42424h47.595c26.177 0 47.786 0.053312 48.019 0.11847 0.57388 0.16034 1.6384 1.1444 1.8912 1.7483 0.17084 0.40808 0.2054 7.3435 0.20332 40.795l-0.00251 40.304-0.3436 0.60665c-0.3871 0.68346-1.1056 1.2259-1.9253 1.4536-0.75895 0.21081-94.77 0.18692-95.513-0.024281zm15.89-6.8209c0-1.0675-0.25729-2.3867-0.72067-3.6951-0.37897-1.0701-0.51995-1.2602-2.1291-2.8708-1.3959-1.3973-1.8319-1.9448-2.2953-2.8824-0.50822-1.0283-0.57874-1.3133-0.63191-2.5533-0.095227-2.2209 0.49154-3.9855 2.5105-7.5501 1.9997-3.5306 3.6832-5.502 5.4249-6.3529 1.7794-0.86924 3.4479-0.88905 5.8113-0.06898 1.2319 0.42746 1.6253 0.49408 2.8785 0.48753 2.0725-0.010842 3.5722-0.58591 6.4903-2.4887 0.97207-0.63385 2.4878-2.2756 2.8943-3.135 0.36396-0.76934 0.48337-1.1944 0.85165-3.0317 0.26893-1.3416 1.0951-2.8498 2.0621-3.7643 1.443-1.3648 3.0299-1.9062 6.3391-2.163 3.9316-0.30505 7.887 0.1221 9.7258 1.0503 1.8706 0.94428 3.2133 2.8463 3.6552 5.1777 0.62601 3.3032 1.6669 4.687 5.0396 6.6995 2.096 1.2507 3.3163 1.6458 5.1125 1.6552 1.2445 0.00651 1.6487-0.060906 2.8337-0.47265 3.2797-1.1396 5.4416-0.72264 7.7824 1.5009 1.3783 1.3092 2.7247 3.2753 4.2151 6.155 2.1442 4.143 2.4297 6.329 1.166 8.9288-0.38255 0.78699-0.85952 1.3678-2.1951 2.6732-1.3271 1.297-1.7955 1.8663-2.1074 2.5612-0.4649 1.0359-0.84641 2.8607-0.84641 4.0485v0.82692h11.918v-51.797h-87.703v51.797h11.918zm14.145-0.48644c0.27368-1.768 0.78371-3.4566 1.5039-4.9789 5.3293-11.265 19.792-13.981 28.735-5.3953 2.7618 2.6514 4.7405 6.3964 5.2795 9.9922 0.10078 0.67229 0.20722 1.3083 0.23653 1.4133 0.040851 0.14637 0.95912 0.19099 3.931 0.19099h3.8776l0.097857-1.6425c0.1613-2.7074 1.0304-5.6338 2.2194-7.4735 0.59739-0.92428 2.1162-2.565 3.1009-3.3498l0.55853-0.44514-0.41916-1.2454c-0.54049-1.6059-2.2178-4.5631-3.3818-5.9625l-0.91178-1.0962-1.2282 0.44825c-3.7071 1.3529-7.3423 1.0785-11.222-0.84699-5.3956-2.6778-8.3583-5.9698-9.4107-10.457-0.51597-2.1997-0.42598-2.0493-1.3609-2.2756-1.9111-0.46265-6.1162-0.39954-7.9564 0.11941-0.53634 0.15125-0.55296 0.17922-0.74492 1.2534-0.59164 3.3106-2.2593 6.1346-4.9732 8.4218-1.4284 1.2038-4.522 3.0271-6.1125 3.6026-3.6205 1.31-6.0589 1.3861-9.4359 0.29421l-1.4937-0.48295-0.54351 0.52267c-1.1422 1.0984-3.2593 4.6979-3.8696 6.5792l-0.32906 1.0144 1.6493 1.6628c0.90713 0.91455 1.8758 2.0478 2.1526 2.5184 1.1236 1.9101 2.0178 5.2495 2.0178 7.536 0 0.60525 0.046992 1.1475 0.10443 1.2049 0.057434 0.057434 1.825 0.086083 3.928 0.063663l3.8235-0.040763zm29.489 0.04882c-0.13925-1.5367-0.81516-3.6054-1.6274-4.981-0.82848-1.403-2.6601-3.2392-4.0381-4.0482-2.0423-1.1989-4.7582-1.8407-6.9318-1.6379-5.7503 0.5365-10.218 5.0057-10.806 10.809l-0.10447 1.0313h23.614zm32.151-64.222v-7.334h-87.703v14.668h87.703zm-80.648 3.662c-3.8058-1.9256-2.5041-7.6537 1.7393-7.6537 2.3542 0 4.0451 1.6684 4.0373 3.9835-0.00768 2.2927-1.5446 3.941-3.8164 4.093-0.88601 0.059272-1.0975 0.013659-1.9601-0.42281zm11.814 0.10412c-0.7904-0.36807-1.5518-1.1123-2.0184-1.9729-0.40625-0.74931-0.37164-2.7423 0.061792-3.5584 1.0809-2.0353 3.4531-2.8198 5.4994-1.8187 2.4414 1.1945 2.939 4.6811 0.94087 6.5929-1.1858 1.1346-3.0112 1.4428-4.4837 0.75713zm12.086 0.12823c-1.9945-0.76199-3.0703-2.9585-2.4664-5.0358 0.49783-1.7125 2.0153-2.8503 3.8013-2.8503 2.3005 0 3.8796 1.4452 4.0408 3.698 0.12442 1.7389-0.6906 3.2389-2.1705 3.9946-0.80288 0.40999-2.3879 0.50562-3.2052 0.19339z" stroke-width=".15279"/>',
                    'width'     => '100',
                    'height'    => '85.17300',
                ];

            break;
            case 'credit-card-safe':
                $svg = [
                    'path'      => '<path d="m66.388 90.738c-0.13831-0.051969-0.44006-0.22542-0.67057-0.38545s-1.5507-0.90984-2.9337-1.6662c-3.8714-2.1173-5.638-3.3588-7.6277-5.3606-1.8218-1.8329-2.6971-3.1094-3.595-5.2434l-0.47359-1.1255-21.142-0.052249c-19.496-0.04818-21.221-0.074959-22.148-0.34388-3.0727-0.89088-5.5022-2.8643-6.7507-5.4835-0.37357-0.78373-0.76145-1.8247-0.86196-2.3133-0.12785-0.6215-0.18275-9.7642-0.18275-30.434 0-32.749-0.082465-30.335 1.1202-32.797 0.83974-1.7194 2.6734-3.5488 4.412-4.4019 2.485-1.2193-1.0677-1.1291 44.425-1.1291 36.174 0 41.117 0.030078 42.078 0.25606 2.9307 0.68868 5.5775 2.7941 6.8374 5.4388 0.37334 0.78373 0.77934 1.8776 0.90223 2.4308 0.18149 0.81709 0.22342 6.5588 0.22342 30.595v29.589l-0.38588 1.3411c-0.82861 2.8799-2.8068 5.2272-5.4461 6.4622-1.8824 0.88086-2.6235 1.0144-6.1991 1.1167l-3.3137 0.094834-0.66432 1.4176c-0.84495 1.8031-1.8303 3.1676-3.3815 4.6826-1.5586 1.5222-3.1958 2.6871-6.7014 4.7679-4.028 2.3909-4.6638 2.6675-6.0954 2.6515-0.64542-0.00721-1.2867-0.055623-1.425-0.10759zm3.323-8.4203c4.449-2.3599 6.53-4.1262 7.7127-6.5466 1.0399-2.128 1.2022-3.3888 1.2022-9.3393v-4.9366l-5.278-2.5853c-2.9029-1.4219-5.3809-2.5853-5.5066-2.5853-0.12573 0-2.626 1.1772-5.5562 2.6161l-5.3276 2.6161 0.091253 5.2212c0.10195 5.8331 0.22147 6.7008 1.2046 8.7448 0.73258 1.5231 2.431 3.3201 4.3626 4.616 0.96781 0.64924 5.0966 3.1679 5.2058 3.1757 0.0049 3.4871e-4 0.85501-0.44817 1.8891-0.99673zm-19.752-17.519c0-3.2425 0.071919-5.6078 0.18679-6.1435 0.35784-1.6686 0.60979-1.8361 8.8046-5.8546l7.3433-3.601 2.8603 0.0062 7.2925 3.5785c4.0108 1.9682 7.5516 3.7817 7.8684 4.03 1.3223 1.0367 1.2584 0.69293 1.352 7.2755l0.083821 5.8974h2.0117c1.1064 0 2.3014-0.061268 2.6554-0.13615 0.81253-0.17186 2.0302-1.2717 2.3234-2.0986 0.1758-0.49584 0.21811-6.1607 0.21811-29.198 0-15.721-0.0603-28.868-0.134-29.215-0.16947-0.79957-1.0061-1.7612-1.8763-2.1565-0.64403-0.29258-2.3504-0.30529-40.99-0.30529-38.844 0-40.343 0.011316-40.989 0.30949-0.81155 0.37456-1.3223 0.90528-1.6684 1.7337-0.2292 0.54855-0.25947 2.9195-0.25947 20.324v19.703l18.147 9.3541e-4c12.276 6.32e-4 18.355 0.058476 18.788 0.1788 0.83094 0.23074 1.8867 1.1919 2.1347 1.9434 0.10751 0.32575 0.16528 1.0664 0.12838 1.6458-0.084913 1.3335-0.64348 2.243-1.6882 2.7488l-0.73456 0.35559h-36.776v5.7891c0 4.4751 0.051257 5.9115 0.22584 6.3285 0.33215 0.7934 1.4788 1.7583 2.2369 1.8823 0.35126 0.057452 0.75182 0.1284 0.89014 0.15767 0.13831 0.029266 9.0968 0.061962 19.908 0.072658l19.656 0.019447zm-25.314-23.238c-5.2-1.3578-9.2302-5.5666-10.296-10.752-1.297-6.311 1.7775-12.596 7.5298-15.393 2.4397-1.1861 3.4073-1.4054 6.2028-1.4054 2.5308 0 3.3808 0.16041 5.8131 1.0971l1.2868 0.49551 1.0073-0.42398c2.2092-0.92986 3.4591-1.1749 5.9748-1.1717 2.6817 0.0035 3.7067 0.21947 5.8965 1.2423 1.7943 0.83813 2.747 1.5067 4.1016 2.8785 6.0936 6.1706 5.0898 16.511-2.0781 21.409-3.9388 2.6911-9.1004 3.1384-13.772 1.1935l-1.2158-0.50612-0.93765 0.42927c-0.5157 0.2361-1.6698 0.61499-2.5646 0.84199-2.1136 0.53616-5.0414 0.56395-6.9486 0.065963zm5.037-6.8026c0.13649-0.13649 0.071402-0.41271-0.24385-1.0348-0.87468-1.7261-1.1857-3.2519-1.1851-5.814 6.136e-4 -2.5505 0.19853-3.5461 1.1134-5.6006 0.2631-0.59082 0.44595-1.1066 0.40634-1.1462-0.20136-0.20136-1.4549-0.35901-2.1941-0.27594-3.5779 0.4021-6.202 2.9676-6.5624 6.4158-0.33951 3.2482 1.968 6.587 5.1684 7.4782 1.0039 0.27955 3.2095 0.26539 3.4973-0.022442zm14.055 0.098298c2.0782-0.47911 3.8622-1.9526 4.8593-4.0134 0.57237-1.183 0.6077-1.3514 0.59787-2.8499-0.00813-1.2397-0.0944-1.8063-0.38937-2.5569-1.7296-4.4014-7.2683-5.9539-10.944-3.0676-3.5129 2.7584-3.7266 7.7001-0.46369 10.721 1.8012 1.6676 4.0509 2.2945 6.34 1.7668z" stroke-width=".16764"/>',
                    'width'     => '100',
                    'height'    => '90.847',
                ];

            break;
            case 'document':
                $svg = [
                    'path'      => '<path d="m10.625 99.804c-5.0012-1.0795-8.9868-4.9712-10.258-10.017l-0.36636-1.4537v-38.234c0-36.518 0.012747-38.288 0.2841-39.445 1.3692-5.8384 6.0377-9.9296 11.973-10.492 1.0808-0.10249 9.2159-0.1615 22.276-0.16159 19.368-1.22e-4 20.592 0.015767 20.893 0.27131 0.17602 0.14929 5.7676 6.3788 12.426 13.843l12.106 13.572-0.040202 30.634c-0.038454 29.301-0.052717 30.677-0.3279 31.642-0.35988 1.2612-1.3011 3.3082-1.9735 4.2923-1.665 2.4368-4.4224 4.4181-7.4579 5.3588l-1.1355 0.35191-28.695 0.028345c-24.12 0.023826-28.855-0.00635-29.703-0.18926zm58.652-7.3954c1.4295-0.70654 2.4423-1.7383 3.1289-3.1876l0.49401-1.0428 0.08171-55.606h-22.805v-25.442l-38.389 0.082314-1.0507 0.49768c-1.2922 0.61209-2.5009 1.7946-3.1378 3.0698l-0.46477 0.93064v76.623l0.47874 1.0082c0.53945 1.136 1.5334 2.2389 2.5602 2.8406 1.5569 0.91242-0.11017 0.86632 30.162 0.83402l27.772-0.029637zm-54.464-17.104v-3.4899h50.254v6.9798h-50.254zm0.035865-15.394 0.041689-3.5287 50.177-0.07849v7.1358h-50.26zm-0.035865-15.394v-3.5674h50.254v7.1349h-50.254zm53.641-19.074c-0.077191-0.22494-11.045-12.543-11.181-12.556-0.1437-0.014464-0.15649 12.46-0.012926 12.604 0.1703 0.1703 11.252 0.12351 11.194-0.047261z" stroke-width=".15511"/>',
                    'width'     => '79.957',
                    'height'    => '100',
                ];

            break;
            case 'low':
                $svg = [
                    'path'      => '<path d="m1.9403e-4 94.888c0-3.1406 0.061869-5.4107 0.16044-5.8869 0.47515-2.2953 2.3242-4.6596 4.4372-5.6735 0.64876-0.31132 1.5701-0.63639 2.0474-0.72237 0.59411-0.10702 8.8057-0.15564 26.034-0.15416 27.696 0.00238 25.986-0.053218 27.981 0.91007 1.3174 0.63632 2.8897 2.1902 3.528 3.4867 0.86118 1.7493 0.89348 2.0325 0.89538 7.8502l0.00174 5.3018h-65.085zm60.842-2.1645c-0.00136-2.8786-0.019161-3.0729-0.34574-3.7743-0.18937-0.40668-0.62421-0.979-0.9663-1.2718-1.2414-1.0626 0.8297-0.98705-27.074-0.98705h-25.118l-0.92689 0.47844c-1.0026 0.51755-1.734 1.3944-2.0191 2.4208-0.08216 0.29581-0.14975 1.8048-0.1502 3.3533l-8.161e-4 2.8155h56.602l-0.00143-3.0348zm23.36-20.666c-0.30506-0.075334-0.93031-0.32452-1.3894-0.55374-0.45914-0.22922-8.9868-6.0215-18.95-12.872-9.9635-6.8502-18.165-12.455-18.225-12.455-0.13832 0-4.5387 7.6454-4.5387 7.8857 0 0.097899 0.16192 0.39029 0.35982 0.64975 0.576 0.75517 1.0773 1.9873 1.2731 3.1294 0.60596 3.5337-2.0382 7.5038-5.6358 8.4617-1.1671 0.31076-2.9228 0.25357-4.0413-0.13164-0.5675-0.19544-5.574-3.0088-12.421-6.98-9.2408-5.3594-11.65-6.8189-12.393-7.5081-1.597-1.4813-2.2435-3.0082-2.2435-5.2985 0-1.7312 0.35936-2.9178 1.3175-4.3503 1.5102-2.258 4.1143-3.5161 6.7065-3.2402l0.82465 0.08778 7.5323-13.115c4.1428-7.213 7.5536-13.172 7.5796-13.241 0.02602-0.069667-0.11861-0.3442-0.32141-0.61008-0.20279-0.26588-0.58007-0.93506-0.83839-1.4871-0.39916-0.85299-0.47991-1.2161-0.53783-2.419-0.11071-2.2991 0.59181-4.1106 2.2447-5.7881 2.2489-2.2824 5.4503-2.8574 8.2985-1.4904 1.6125 0.77385 22.723 13.049 23.585 13.713 0.91655 0.70711 1.8683 2.0245 2.2907 3.1707 0.45771 1.242 0.4547 3.5524-0.00637 4.895-1.1416 3.3242-4.0719 5.3827-7.4216 5.2135l-1.0636-0.053707-2.2718 3.9507c-1.2495 2.1729-2.2255 3.9925-2.1689 4.0436 0.056574 0.051066 8.4286 4.0909 18.604 8.9774 10.175 4.8865 19.195 9.2317 20.043 9.656 1.8578 0.92998 2.9855 1.8179 3.7266 2.9345 0.836 1.2595 1.1349 2.3585 1.0505 3.8622-0.090226 1.6073-0.39214 2.3621-2.2232 5.5583-1.7866 3.1185-2.6712 4.1371-4.2869 4.9362-1.0239 0.50642-1.2136 0.55005-2.5057 0.57619-0.76885 0.015552-1.6475-0.03336-1.9526-0.10869zm2.9777-4.4194c0.35836-0.2415 0.93744-1.1046 2.1342-3.1811 1.4095-2.4455 1.6436-2.943 1.6503-3.5078 0.00982-0.81806-0.34826-1.5422-1.0154-2.0534-0.28155-0.21575-9.4629-4.689-20.403-9.9406-10.94-5.2516-19.934-9.5714-19.986-9.5996-0.12523-0.067842-1.7688 2.8405-1.7078 3.0219 0.069367 0.20632 35.74 24.706 36.669 25.185 1.0074 0.51989 1.9588 0.5469 2.6584 0.07547zm-50.154-5.8939c1.721-1.2201 1.9941-3.8105 0.54056-5.1284-0.57872-0.52474-22.65-13.247-23.366-13.468-2.9778-0.92125-5.379 3.2354-3.1186 5.3985 0.51535 0.49316 22.279 13.191 23.216 13.546 0.24133 0.091248 0.81756 0.14691 1.2805 0.12369 0.65338-0.032768 0.97727-0.1383 1.4474-0.4716zm7.9476-23.096c3.9267-6.8356 7.1632-12.529 7.1924-12.651 0.038186-0.16053-2.5357-1.7189-9.1853-5.5614-5.0811-2.9361-9.3108-5.3547-9.3993-5.3747-0.14642-0.032996-14.948 25.531-14.835 25.623 0.026641 0.021583 4.2578 2.4793 9.4025 5.4617 8.2756 4.7972 9.3732 5.3941 9.5198 5.177 0.091116-0.13499 3.3784-5.8382 7.305-12.674zm13.718-15.339c2.1678-0.90844 2.8342-3.7786 1.2367-5.3269-0.62929-0.6099-22.724-13.424-23.513-13.637-1.8407-0.49566-3.7163 0.9737-3.9054 3.0596-0.086374 0.95255 0.23988 1.8709 0.87531 2.4639 0.34878 0.32548 22.007 12.911 22.998 13.363 0.69534 0.31781 1.6565 0.3497 2.3083 0.076591z" stroke-width=".14626"/>',
                    'width'     => '95.185',
                    'height'    => '100',
                ];

            break;
            case 'menu':
                $svg = [
                    'path'      => '<path d="m3.6022 73.825c-2.4022-0.75427-3.9678-3.2388-3.5287-5.6 0.41194-2.2151 2.0486-3.7754 4.3021-4.1014 1.0491-0.15177 50.193-0.15079 51.243 0.00102 2.2195 0.32101 3.869 1.9152 4.2779 4.1345 0.38972 2.1151-0.75928 4.258-2.8295 5.2772l-0.83253 0.40986-26.046 0.024281c-21.299 0.019855-26.144-0.00666-26.586-0.14546zm-0.13245-32.074c-1.2129-0.37971-2.3228-1.3304-2.9131-2.4953-1.2542-2.4751-0.28554-5.4341 2.1904-6.6908l0.86914-0.44115h92.768l0.73996 0.36335c1.5027 0.73789 2.5075 2.099 2.8077 3.8035 0.36219 2.0562-0.81305 4.241-2.808 5.2202l-0.73964 0.36305-46.185 0.023854c-38.11 0.019684-46.28-0.00596-46.73-0.14669zm0.2477-31.958c-0.9544-0.25684-1.7071-0.7309-2.4484-1.542-2.3437-2.5644-1.2972-6.654 2.0399-7.9722 0.54885-0.21679 2.8348-0.22966 46.371-0.26099 50.407-0.036276 46.626-0.09437 47.919 0.73624 2.8327 1.8198 3.2289 5.6207 0.81589 7.8261-0.79263 0.72442-1.5218 1.1033-2.4693 1.283-0.45685 0.086639-15.886 0.12738-46.116 0.12176-38.978-0.00724-45.526-0.034503-46.111-0.19196z" stroke-width=".12829"/>',
                    'width'     => '100',
                    'height'    => '73.976',
                ];

            break;
            case 'minus':
                $svg = [
                    'path'      => '<path d="m0 7.8984v-7.8984h100v15.797h-100z" stroke-width=".13736"/>',
                    'width'     => '100',
                    'height'    => '15.797',
                ];

            break;
            case 'mobile-secure':
                $svg = [
                    'path'      => '<path d="m7.5107 99.82c-3.5661-0.71622-6.1485-3.0026-7.1688-6.3472-0.26054-0.85404-0.27538-2.8556-0.31777-42.846-0.049755-46.947-0.13654-43.176 1.0505-45.647 0.82552-1.7179 2.3139-3.2375 3.9161-3.9984 2.1703-1.0307 0.68524-0.98121 29.464-0.98121 17.234 0 26.546 0.054086 27.639 0.16054 6.2256 0.60635 12.048 3.2644 16.61 7.5831 9.9216 9.3916 11.639 24.622 4.0528 35.941-3.0692 4.5798-7.2922 8.0872-12.375 10.279l-1.509 0.65055-0.081975 18.83c-0.092076 21.15 0.00665 19.423-1.249 21.848-1.1268 2.1759-2.9998 3.6888-5.4595 4.4096-0.84706 0.24824-2.6018 0.26705-27.237 0.29198-21.433 0.021689-26.518-0.010808-27.334-0.1747zm53.289-6.2814c0.66839-0.31068 1.2678-0.96526 1.4884-1.6255 0.11958-0.3578 0.17143-2.9936 0.17143-8.7148v-8.2018l-56.209 0.076242v8.2994c0 8.2836 6.368e-4 8.3006 0.33483 8.9255 0.38666 0.72303 1.103 1.2173 2.0319 1.4019 0.35818 0.071213 12.127 0.11936 26.153 0.107 23.205-0.020456 25.549-0.044587 26.03-0.26797zm-34.857-9.1102v-3.0934h16.599v6.1868h-16.599zm36.517-21.972v-6.3544l-1.7731 0.10532c-4.2886 0.25475-9.1936-0.71169-13.183-2.5976-10.334-4.8845-16.818-15.578-16.196-26.711l0.1033-1.8485h-25.236v21.78c0 11.979 0.045269 21.825 0.1006 21.88 0.055329 0.055329 12.719 0.1006 28.142 0.1006h28.042zm0.48827-12.752c4.7185-0.77668 8.7673-2.9302 12.194-6.4855 3.9931-4.1436 6.0868-9.333 6.0818-15.075-0.00373-4.3254-1.192-8.342-3.5228-11.908l-0.78044-1.1939-23.742 23.74-11.615-11.618 4.3702-4.3781 7.1718 7.1671 19.363-19.363-1.194-0.7804c-3.5268-2.3051-7.5729-3.5085-11.832-3.5193-4.4381-0.01124-8.436 1.1814-12.138 3.6209-1.973 1.3002-4.7926 4.1043-6.0929 6.0595-4.9713 7.4746-4.935 16.876 0.094291 24.471 1.1146 1.6833 4.0794 4.6481 5.7627 5.7627 4.8557 3.2151 10.308 4.4167 15.88 3.4996zm-29.39-32.608c1.5268-3.5438 3.4083-6.2689 6.3071-9.135l1.7932-1.7731-16.271 0.00369c-10.45 0.00237-16.508 0.058447-16.935 0.15675-0.92297 0.21276-1.5071 0.67371-1.8826 1.4855-0.30333 0.65582-0.32301 0.96587-0.36735 5.7866l-0.046853 5.0936 26.738-0.077083z" stroke-width=".1509"/>',
                    'width'     => '87.473',
                    'height'    => '100',
                ];

            break;
            case 'plus':
                $svg = [
                    'path'      => '<path d="m42.202 78.991v-21.009h-42.202v-15.78h42.202v-42.202h15.78v42.202h42.018v15.78h-42.018v42.018h-15.78z" stroke-width=".18349"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'press':
                $svg = [
                    'path'      => '<path d="m35.967 99.873c-2.559-0.2974-4.7084-0.92302-7.1255-2.0739-3.3387-1.5897-5.8019-3.467-8.0021-6.0986-1.8768-2.2448-2.7112-3.7362-5.9729-10.676-2.1016-4.4715-7.7947-15.708-12.715-25.096-1.0591-2.0208-1.9917-3.955-2.0724-4.2981-0.30653-1.3039 0.27593-2.3584 1.8992-3.4382 3.8555-2.5647 5.6209-3.217 8.7063-3.217 3.7882 0 7.3323 1.7863 9.652 4.8647l0.83283 1.1052 0.076208-31.689 0.3246-0.90122c0.57066-1.5844 1.1569-2.5219 2.3145-3.7013 0.90431-0.92131 1.3372-1.2427 2.2883-1.6989 1.5279-0.73283 2.9157-1.0283 4.362-0.92877 2.127 0.14644 4.1214 1.0662 5.6105 2.5874 1.5648 1.5985 2.3694 3.316 2.6335 5.6211 0.078129 0.68201 0.14257 3.7508 0.14319 6.8195l0.00114 5.5795 0.51994-0.19746c1.3823-0.52499 3.6514-0.65141 5.1009-0.28419 2.0231 0.51254 4.1269 1.9448 5.2484 3.5732l0.51763 0.75158 0.85775-0.45286c0.47176-0.24907 1.2321-0.56257 1.6896-0.69666 1.1298-0.33111 3.4365-0.33943 4.6448-0.016755 2.6194 0.69955 4.8139 2.5703 5.9881 5.1046 0.3391 0.73189 0.49802 0.94182 0.66076 0.87287 1.389-0.58853 2.3707-0.79591 3.8192-0.80684 1.3305-0.010031 1.6832 0.040303 2.7634 0.39431 2.8695 0.94043 4.9797 3.0861 6.0097 6.1107l0.37774 1.1092-0.031824 13.865c-0.032645 14.222-0.081507 15.698-0.60585 18.302-1.0742 5.3338-4.1607 10.621-8.1928 14.033-2.2594 1.9123-4.8954 3.4502-7.5332 4.395-1.3061 0.46782-3.6141 1.0091-5.054 1.1853-1.3734 0.16809-18.287 0.16583-19.737-0.00263zm21.129-6.1208c6.4614-1.7456 11.274-6.2714 13.338-12.542 0.93269-2.8345 0.93697-2.8917 1.0871-14.536 0.074727-5.7955 0.11762-12.16 0.095307-14.142l-0.04056-3.6049-0.40887-0.73811c-1.2113-2.1867-3.9207-2.6034-5.6154-0.86357-1.0055 1.0323-1.116 1.5382-1.1176 5.1185-0.0013 2.8789-0.014835 3.0287-0.33611 3.7187-0.44146 0.94816-1.1556 1.4305-2.253 1.5218-1.0323 0.085862-1.8665-0.31219-2.4684-1.1779l-0.4178-0.60088-0.13865-12.247-0.38863-0.7914c-1.3237-2.6955-4.935-2.7184-6.3827-0.040498-0.29122 0.53871-0.3018 0.73138-0.36914 6.7245l-0.069325 6.1699-0.33612 0.58683c-0.85215 1.4877-2.8818 1.8623-4.1785 0.77124-0.92227-0.77604-0.88563-0.43912-0.96204-8.8451l-0.069325-7.6257-0.32854-0.72675c-0.36606-0.80976-1.1744-1.6173-1.9398-1.9379-1.258-0.52695-3.0259-0.11389-3.9326 0.91884-0.96145 1.095-0.93304 0.82874-0.93641 8.7755-0.00202 4.7503-0.055355 7.4122-0.15812 7.8914-0.21633 1.0087-1.1918 1.999-2.1037 2.1358-1.3541 0.20306-2.5098-0.3786-3.0959-1.5581-0.24983-0.50282-0.26347-1.259-0.32993-18.283l-0.069325-17.758-0.41961-0.67864c-0.76766-1.2415-2.4078-1.8881-3.7042-1.4603-0.65664 0.21671-1.5757 1.0311-1.9469 1.7251l-0.30718 0.57436-0.13865 41.918-0.42171 0.59999c-0.23194 0.32999-0.66869 0.73987-0.97055 0.91084-1.0723 0.60734-2.7118 0.28403-3.4214-0.6747-0.18958-0.25614-1.555-2.4876-3.0342-4.9588-2.9721-4.9652-3.6005-5.7605-5.1553-6.5242-2.1927-1.0771-4.5796-1.0137-6.4398 0.171l-0.70277 0.44759 1.3789 2.6343c3.2178 6.1473 9.2408 18.02 10.867 21.421 4.5084 9.4288 4.8426 10.039 6.6126 12.082 0.71735 0.82774 2.3867 2.2794 3.4489 2.9991 1.1014 0.74623 3.057 1.6879 4.4007 2.119 2.5203 0.80854 2.5441 0.80995 12.987 0.77297l9.5668-0.033884zm-45.565-68.727c-0.31042-0.14099-0.75651-0.48941-0.99131-0.77426-0.64973-0.78823-0.78831-1.488-0.68226-3.445 0.25533-4.7115 2.1078-9.3836 5.2439-13.226 2.9935-3.6673 7.8145-6.4706 12.63-7.3442 1.7549-0.31832 5.3968-0.31281 7.1622 0.010838 10.124 1.856 17.709 11.013 17.812 21.505 0.012128 1.2361-0.023995 1.4535-0.33302 2.0043-0.97901 1.7448-3.2995 2.0316-4.6192 0.57078-0.61613-0.68198-0.73245-1.0912-0.92385-3.2504-0.087008-0.98153-0.31231-2.3854-0.50068-3.1196-0.41625-1.6226-1.7006-4.29-2.6965-5.6003-2.2111-2.9091-5.311-4.9969-8.7296-5.8794-1.2786-0.33005-1.7175-0.37574-3.6049-0.37528-1.8565 4.52e-4 -2.3398 0.049321-3.5502 0.35903-5.2267 1.3373-9.32 5.1074-11.183 10.3-0.489 1.3631-0.78557 2.9369-0.92119 4.8886-0.1224 1.7615-0.28236 2.221-1.0118 2.9062-0.77826 0.73114-2.0889 0.92964-3.1015 0.46972z" stroke-width=".13865"/>',
                    'width'     => '77.121',
                    'height'    => '100',
                ];

            break;
            case 'user':
                $svg = [
                    'path'      => '<path d="m11.956 99.958c-0.1807-0.031133-0.88696-0.20256-1.5695-0.38095-5.457-1.4264-9.5091-6.1217-10.254-11.882-0.52821-4.0834 0.56708-11.316 2.5058-16.548 1.2888-3.4775 3.4958-7.5756 5.633-10.459 2.7979-3.7753 6.633-7.4872 10.239-9.9104 2.1114-1.4188 5.6183-3.3145 7.5606-4.0869l0.7756-0.30846-0.64418-0.53054c-3.0753-2.5328-5.7825-5.9614-7.3051-9.2517-5.3668-11.598-1.6168-25.216 8.9193-32.391 1.5974-1.0878 2.5454-1.5855 4.5592-2.3936 3.2895-1.32 5.8544-1.8147 9.4129-1.8155 7.0363-0.00169 13.348 2.7682 18.283 8.0242 2.6314 2.8022 4.5807 6.1794 5.7626 9.9833 0.93104 2.9968 1.3465 6.7814 1.0747 9.7883-0.46807 5.1769-2.2779 9.7505-5.475 13.836-0.9016 1.1521-3.0089 3.2767-4.1235 4.1575-0.69373 0.54817-0.70195 0.56256-0.39426 0.69015 3.8791 1.6085 7.675 3.8409 10.762 6.3296 2.6723 2.1542 6.0647 5.7864 8.0336 8.6016 3.5546 5.0824 6.0476 11.149 7.0751 17.216 0.43209 2.5515 0.57735 4.2946 0.58044 6.9653 0.00275 2.3713-0.023241 2.6594-0.34787 3.8573-1.3615 5.024-4.961 8.6987-9.9575 10.166l-1.2439 0.36519-29.767 0.017179c-16.372 0.00945-29.915-0.00829-30.095-0.039427zm58.352-7.6713c1.495-0.18745 2.5835-0.71508 3.6286-1.7588 1.7639-1.7616 2.1722-3.6077 1.7479-7.9027-0.81325-8.2319-4.2274-15.473-10.037-21.288-3.9546-3.9582-7.9451-6.4747-12.988-8.1908-3.5037-1.1922-6.2094-1.6914-9.8191-1.8117-7.7648-0.25871-15.114 2.0771-21.458 6.8204-1.834 1.3712-4.9948 4.4579-6.2944 6.1469-4.677 6.0783-7.191 12.887-7.4249 20.107-0.082618 2.5512 0.049602 3.4038 0.72324 4.6637 0.87246 1.6318 2.7953 2.9745 4.5908 3.2057 1.3347 0.17188 55.964 0.17936 57.332 0.00785zm-25.76-48.953c5.0283-0.81488 9.6719-4.017 12.39-8.5437 1.0586-1.7632 1.8958-4.1073 2.2695-6.3543 0.27256-1.6389 0.20255-4.8698-0.13979-6.4517-0.74935-3.4627-2.3251-6.4222-4.7689-8.9569-2.4384-2.529-5.3359-4.1599-8.9619-5.0442-1.3353-0.32564-4.7682-0.39802-6.3736-0.13438-5.2118 0.8559-9.9558 4.1801-12.583 8.8167-0.70112 1.2376-0.91705 1.7219-1.4183 3.1808-0.71001 2.0663-0.85068 3.027-0.84227 5.7521 0.00685 2.2198 0.046163 2.6611 0.35412 3.9751 1.746 7.4502 7.4928 12.739 15.08 13.879 0.89544 0.13449 3.8689 0.064081 4.994-0.11825z" stroke-width=".13142"/>',
                    'width'     => '83.367',
                    'height'    => '100',
                ];

            break;
            case 'warning-circle-stroke':
                $svg = [
                    'path'      => '<path d="M0,50a50,50,0,1,1,50,50A50.069,50.069,0,0,1,0,50Zm4.878,0A45.122,45.122,0,1,0,50,4.879,45.176,45.176,0,0,0,4.878,50ZM46.667,71.582a3.408,3.408,0,1,1,3.408,3.408A3.408,3.408,0,0,1,46.667,71.582Zm0-13.1v-28.4a3.408,3.408,0,1,1,6.817,0v28.4a3.408,3.408,0,0,1-6.817,0Z" transform="translate(0 0)"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'question-circle':
                $svg = [
                    'path'      => '<path d="M0,50a50,50,0,1,1,50,50A50.069,50.069,0,0,1,0,50Zm4.878,0A45.122,45.122,0,1,0,50,4.878,45.176,45.176,0,0,0,4.878,50ZM46.286,70.4a3.351,3.351,0,1,1,3.351,3.351A3.351,3.351,0,0,1,46.286,70.4Zm.97-9.7V53.561a2.371,2.371,0,0,1,2.381-2.381A9.876,9.876,0,1,0,39.761,41.3,2.381,2.381,0,0,1,35,41.3,14.637,14.637,0,1,1,52.018,55.765V60.7a2.381,2.381,0,1,1-4.761,0Z" transform="translate(0 -0.212)"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'success-circle':
                $svg = [
                    'path'      => '<path d="M59,9a50,50,0,1,0,50,50A50.068,50.068,0,0,0,59,9Zm0,95.12A45.121,45.121,0,1,1,104.122,59,45.176,45.176,0,0,1,59,104.12Z" transform="translate(-9 -9)"/><g transform="translate(28.305 33.311)"><path d="M66.293,984.367a2.688,2.688,0,0,0-1.581.839c-9.043,9.292-15.812,16.96-24.443,25.928l-9.822-8.508a2.668,2.668,0,0,0-3.824.333,2.831,2.831,0,0,0,.325,3.921l11.742,10.186a2.661,2.661,0,0,0,3.669-.173c9.69-9.956,16.615-17.939,26.193-27.781A2.829,2.829,0,0,0,69.1,985.9a2.7,2.7,0,0,0-2.806-1.534Z" transform="translate(-25.984 -984.341)"/></g>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'bell-stroke':
                $svg = [
                    'path'      => '<path d="M96.07,75.844c-8.989-7.813-17.307-18.836-17.307-46.358a28.763,28.763,0,1,0-57.525,0c0,27.547-8.315,38.559-17.312,46.366A11.472,11.472,0,0,0,0,84.479a3.28,3.28,0,0,0,3.272,3.288H34.32a16,16,0,0,0,31.36,0H96.728A3.28,3.28,0,0,0,100,84.479,11.406,11.406,0,0,0,96.07,75.844ZM50,94a9.492,9.492,0,0,1-8.891-6.236H58.891A9.492,9.492,0,0,1,50,94ZM7.832,81.191a4.827,4.827,0,0,1,.38-.37C21.928,68.919,27.78,53.566,27.78,29.486a22.219,22.219,0,1,1,44.438,0c0,24.058,5.853,39.41,19.576,51.338a4.875,4.875,0,0,1,.38.367Z" transform="translate(0 -0.58)"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
            case 'envelope-stroke':
                $svg = [
                    'path'      => '<path d="M88.881,7H18.611A15.043,15.043,0,0,0,3.8,22.282v47.4A15.134,15.134,0,0,0,18.719,84.958H38.286l15.459,17.5L69.314,84.847H88.881A15.134,15.134,0,0,0,103.8,69.566V22.282C103.692,13.866,97.1,7,88.881,7ZM11.259,22.282a7.43,7.43,0,0,1,7.459-7.641H88.881a7.43,7.43,0,0,1,7.459,7.641v47.4a7.43,7.43,0,0,1-7.459,7.641H69.1a7.506,7.506,0,0,0-5.189,2.325L53.746,91.159,43.476,79.532a7.064,7.064,0,0,0-5.189-2.325H18.611a7.43,7.43,0,0,1-7.459-7.641V22.282Z" transform="translate(-3.7 -6.9)" stroke="#fff" stroke-width="1.2"/>',
                    'width'     => '100',
                    'height'    => '95.44',
                ];

            break;
            case 'favorites':
                $svg = [
                    'path'      => '<path d="M47.572,670.1a35.52,35.52,0,0,1-6.122-3.17c-9.869-6.262-27.375-21.829-33.8-30.044A36.616,36.616,0,0,1,.51,622,22.961,22.961,0,0,1,0,616.009a26.883,26.883,0,0,1,2.916-13.172,27.833,27.833,0,0,1,19.077-15.2,22.664,22.664,0,0,1,5.178-.357c3.216.007,3.689.055,5.452.55,5.323,1.5,9.743,4.458,14.894,9.978l2.529,2.71,3.039-3.13c5.348-5.507,9.562-8.273,14.727-9.665a29.947,29.947,0,0,1,10.119-.086,28.753,28.753,0,0,1,8.293,3.264c12.913,7.924,17.454,24.525,10.588,38.7-2.63,5.429-6.358,10.065-14.081,17.5-10.219,9.846-21.307,18.62-27.627,21.861-3.355,1.721-5.166,2-7.531,1.144Zm6-7.225c7.341-4.33,19.266-14.228,28.492-23.651,7.145-7.3,10.332-12.529,11.536-18.941a18.22,18.22,0,0,0,.327-5.085,32.116,32.116,0,0,0-.4-4.2c-2.038-9.5-9.815-16.764-18.892-17.635-4.52-.434-8.306.768-12.648,4.017-.747.558-3.223,2.934-5.5,5.278-4.292,4.415-5.106,5.045-6.518,5.045-1.354,0-2.163-.609-5.059-3.808a45.048,45.048,0,0,0-7.833-7.294,16.671,16.671,0,0,0-11.776-3.238C16.216,594.24,8.432,601.508,6.4,611a29.408,29.408,0,0,0-.18,8.688A31.043,31.043,0,0,0,13.705,634.7c1.973,2.313,9.053,9.411,11.856,11.885,5.3,4.68,6.523,5.722,9.433,8.05,6.577,5.259,12.325,9.11,14.575,9.764q.951.276,4-1.521Z" transform="translate(0.003 -587.246)"/>',
                    'width'     => '100',
                    'height'    => '83.33',
                ];

            break;
            case 'info':
                $svg = [
                    'path'      => '<path d="M50,0a50,50,0,1,0,50,50A50,50,0,0,0,50,0ZM60.406,77.49q-3.86,1.524-6.159,2.32a16.248,16.248,0,0,1-5.342.8q-4.673,0-7.268-2.281a7.362,7.362,0,0,1-2.586-5.786,21.018,21.018,0,0,1,.19-2.789c.131-.948.339-2.015.622-3.213l3.221-11.378c.284-1.092.529-2.129.724-3.094a13.72,13.72,0,0,0,.288-2.679,4.126,4.126,0,0,0-.9-3.035c-.605-.571-1.744-.851-3.441-.851a9.031,9.031,0,0,0-2.561.381c-.868.267-1.621.508-2.239.745l.851-3.5q3.162-1.289,6.053-2.205A17.882,17.882,0,0,1,47.323,40c3.094,0,5.481.753,7.162,2.243A7.453,7.453,0,0,1,57,48.063q0,.743-.174,2.612a17.476,17.476,0,0,1-.643,3.433l-3.2,11.344c-.262.91-.5,1.951-.707,3.115a16.473,16.473,0,0,0-.309,2.65A3.806,3.806,0,0,0,52.974,74.3a5.663,5.663,0,0,0,3.5.821,10.154,10.154,0,0,0,2.65-.411,15.066,15.066,0,0,0,2.142-.72Zm-.567-46.044a7.647,7.647,0,0,1-5.4,2.083,7.727,7.727,0,0,1-5.418-2.083,6.648,6.648,0,0,1-2.256-5.05,6.715,6.715,0,0,1,2.256-5.062,7.672,7.672,0,0,1,5.418-2.1,7.586,7.586,0,0,1,5.4,2.1,6.819,6.819,0,0,1,0,10.112Z"/>',
                    'width'     => '100',
                    'height'    => '100',
                ];

            break;
        }

        $classes = '' !== $classes ? " class=\"{$classes}\"" : '';
        $ratio = $svg['width'] / $svg['height'];
        // if ($svg["height"] > $svg["width"]) {
        //     $ratio = $svg["width"] / $svg["height"];
        // } else {
        //     $ratio = $svg["height"] / $svg["width"];
        // }

        if (0 === $width && 0 === $height) {
            $width = $svg['width'];
            $height = $svg['height'];
        } elseif (0 === $width) {
            $width = round(($svg['width'] * $height) / $svg['height'], 3);
        } elseif (0 === $height) {
            $height = round(($svg['height'] * $width) / $svg['width'], 3);
        }

        return "<svg{$classes} xmlns=\"http://www.w3.org/2000/svg\" style=\"vertical-align: middle;\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$svg['width']} {$svg['height']}\">{$svg['path']}</svg>";
    }
}

if (!function_exists('widgetGetUserGroupDistributor')) {
    /**
     * @author Usinevici Alexandr
     * @todo Remove [05.12.2021]
     * not used
     * @deprecated use instead groupNameWithSuffix
     */
    // function widgetGetUserGroupDistributor()
    // {
    //     return group_name_session() . (session()->group_name_suffix ?? '');
    // }
}

if (!function_exists('widgetCounterUserNotifications')) {
    /**
     * Return notifications count if user is logged in.
     *
     * @return array - array with notifications types and count
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/k.-Widgets#widgetCounterUserNotifications
     */

    function widgetCounterUserNotifications(): array
    {
        /** @var Systmess_Model $systmessModel */
        $systmessModel = model(Systmess_Model::class);

        return $systmessModel->counter_user_notifications(privileged_user_id());
    }
}

if (!function_exists('getEpIconSvg')) {
    /**
     * Return svg icon.
     *
     * @return string
     *
     */

    function getEpIconSvg($name = 'share', $size = [20, 20], $class = '', $file = false): string
    {
        list($width, $height) = $size;
        $svgFile = '';

        if ($file){
            $svgFile = asset('public/build/images/svg_icons.svg');
        }

        return "<svg class=\"ep-icon-svg {$class}\" width=\"{$width}\" height=\"{$height}\"><use href=\"{$svgFile}#ep-icon-{$name}\"></use></svg>";
    }

}
