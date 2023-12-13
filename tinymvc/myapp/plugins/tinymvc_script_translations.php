<?php
/**
 * Modify and standardize the url.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $url  - input url
 * @param bool   $lang - if language should be taken in consideration. By default false
 *
 * @return string - the normalized url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#normalize_url
 */
function normalize_url($url = '', $lang = false)
{
    $url = urldecode($url);
    $components = explode('?', $url);
    $data_get = (!empty($components[1])) ? strToGET($components[1]) : [];
    if ($lang) {
        if (__SITE_LANG != 'en') {
            $data_get['lang'] = __SITE_LANG;
        } else {
            unset($data_get['lang']);
        }
    }

    $uri_get = '';
    if (!empty($data_get)) {
        $uri_get = http_build_query($data_get);
        $components[1] = $uri_get;
    } else {
        unset($components[1]);
    }

    $components[0] = preg_replace('/([^:])(\/{2,})/', '$1/', $components[0]);
    $components[0] = rtrim($components[0], '/');

    return implode('?', $components);
}

/**
 * Get the link by key from site static urls.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $url_key    - key of the url in site_urls
 * @param string $site_url   - by default __SITE_URL
 * @param string $url_suffix - if suffix is needed
 *
 * @return string the url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#get_static_url
 */
function get_static_url($url_key = '', $site_url = __SITE_URL, $url_suffix = '')
{
    global $tmvc;

    if (!empty($tmvc->site_urls[$url_key]['replace_uri_string'])) {
        $url_key = $tmvc->site_urls[$url_key]['replace_uri_string'];
    }

    if (!empty($url_suffix)) {
        $url_key .= '/' . $url_suffix;
    }

    // $current_lang_route_config = $this->site_urls[$url_key];
    // if(!empty($this->site_urls[$url_key]['replace_uri_components'])){
    // 	foreach ($uri as $uri_key => $uri_value) {
    // 		$replace_key = (isset($current_lang_route_config['replace_uri_components'][$replace_key]))?$current_lang_route_config['replace_uri_components'][$replace_key]:$replace_key;
    // 		if(!empty($replace_key)){
    // 			$lang_uri[$replace_key.'_key'] = $replace_key;
    // 		}

    // 		$replace_key_value = (isset($current_lang_route_config['replace_uri_components'][$uri_value]))?$current_lang_route_config['replace_uri_components'][$uri_value]:$uri_value;
    // 		if(!empty($replace_key_value) && $replace_key_value != '{_.index._}'){
    // 			$lang_uri[$replace_key.'_value'] = $replace_key_value;
    // 		}
    // 	}
    // }

    return normalize_url($site_url . '/' . $url_key);
}

/**
 * Get the url dynamically and normalize it.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $url_key  - the url itself
 * @param string $site_url - by default __SITE_URL
 * @param bool   $lang     - take in consideration of the language. By default false.
 *
 * @return string - normalized url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#get_dynamic_url
 */
function get_dynamic_url($url_key = '', $site_url = __SITE_URL, $lang = false)
{
    return normalize_url($site_url . '/' . $url_key, $lang);
}

/**
 * Same as get_dynamic_url by with replace.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $replace  - replace string
 * @param string $str      - replace tot what
 * @param string $site_url - by default __SITE_URL
 * @param bool   $lang     - take in consideration of the language. By default false.
 *
 * @return string - normalized url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#replace_dynamic_uri
 */
function replace_dynamic_uri($replace = '', $str = '', $site_url = __SITE_URL, $lang = false)
{
    return normalize_url($site_url . '/' . str_replace(config('replace_uri_template'), $replace, $str), $lang);
}

/**
 * Translation of a key.
 *
 * @param string $langKey          - the key
 * @param array  $replaceArguments - array with replacements
 * @param bool   $escape           - does the string need to be escaped
 *
 * @return string - the translated text
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#translate
 */
function translate($langKey, $replaceArguments = null, $escape = false)
{
    static $cache;

    if (
        // If translation usage log is enabled
        config('env.ENABLE_TRANSLATIONS_USAGE_LOG')
        // and the process is not called from CLI
        && !in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'])
    ) {
        // Then log the translation usage
        $kernel = tmvc::instance();
        $isAjax = isAjaxRequest();
        $controller = $kernel->controller->name;
        $action = $kernel->action;
        $url = $isAjax ? $_SERVER['HTTP_REFERER'] : __CURRENT_URL;

        if ($isAjax) {
            $kernel->translationsKeysUsageLog['byAjax'][$url][$langKey] = '';
        } else {
            $kernel->translationsKeysUsageLog[$controller][$action][$langKey] = '';
        }
    }

    if (isset($cache[$langKey]) && empty($replaceArguments)) {
        return $escape ? cleanOutput($cache[$langKey]) : $cache[$langKey];
    }

    if (!empty(tmvc::instance()->lang[$langKey])) {
        $str = stripslashes(tmvc::instance()->lang[$langKey] ?? '');
    } else {
        $str = stripslashes(tmvc::instance()->default_lang[$langKey] ?? '');
    }

    if (!empty($replaceArguments)) {
        $str = str_replace(array_keys($replaceArguments), array_values($replaceArguments), $str);
    }

    $cache[$langKey] = $str;

    return $escape ? cleanOutput($str) : $str;
}
