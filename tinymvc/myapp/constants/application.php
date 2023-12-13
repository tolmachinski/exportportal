<?php

declare(strict_types=1);

$isCli = in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) || \str_starts_with(\PHP_SAPI, 'cli');
$appHost = parse_url($_ENV['APP_URL'] ?? 'http://localhost', PHP_URL_HOST) ?? 'localhost';
$currentHost = (!$isCli ? $_SERVER['HTTP_HOST'] : $appHost) ?? 'localhost';
$hostParts = explode('.', $currentHost);
$isLocalhost = 'localhost' === $currentHost;
$httpProtocol = !$isCli ? ((isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'https://' : 'http://') : sprintf('%s://', parse_url($_ENV['APP_URL'] ?? '', PHP_URL_SCHEME) ?? 'http');
$makeDomainUrl = fn (string $host, ?string $domainKey = null, string $path = '/') => sprintf(
    '%s%s%s',
    $httpProtocol,
    !$isLocalhost ? (null !== $domainKey && isset($_ENV[$domainKey]) ? ltrim(rtrim($_ENV[$domainKey], '.') . '.' . $host, '.') : $host) : $currentHost,
    $path
);

// HTTP constants
define('__HTTP_S', $httpProtocol); // HTTP protocol
define('__HTTP_HOST_ORIGIN', $appHost); // App host name
define('__CURRENT_URL', $makeDomainUrl($currentHost, null, $_SERVER['REQUEST_URI'] ?? '')); // Current URL
define('__FILES_URL', $makeDomainUrl($appHost, 'FILES_SUBDOMAIN')); // FILES URL
define('__SITE_URL', $makeDomainUrl($appHost, 'WWW_SUBDOMAIN')); // Base site URL
define('__IMG_URL', $makeDomainUrl($appHost, 'IMAGES_SUBDOMAIN')); // IMAGES URL
define('__BLOG_URL', $makeDomainUrl($appHost, 'BLOG_SUBDOMAIN')); // BLOG URL
define('__FORUM_URL', $makeDomainUrl($appHost, 'FORUM_SUBDOMAIN')); // FORUM URL
define('__SHIPPER_URL', $makeDomainUrl($appHost, 'SHIPPER_SUBDOMAIN')); // SHIPPERS URL
define('__BLOGGERS_URL', $makeDomainUrl($appHost, 'BLOGGERS_SUBDOMAIN')); //BLOGGERS URL
define('__GIVEAWAY_URL', $makeDomainUrl($appHost, 'GIVEAWAY_SUBDOMAIN')); // GIVEAWAY URL
define('__COMMUNITY_URL', $makeDomainUrl($appHost, 'COMMUNITY_SUBDOMAIN')); //COMMUNITY URL
define('__COMMUNITY_ALL_URL', $makeDomainUrl($appHost, 'COMMUNITY_SUBDOMAIN', '/questions/')); //COMMUNITY URL all
define('__CURRENT_SUB_DOMAIN_URL', $makeDomainUrl($currentHost, null, '/')); // Current URL for subdomain
define('__CURRENT_URL_NO_QUERY', str_replace([parse_url(__CURRENT_URL, PHP_URL_QUERY), '?'], '', __CURRENT_URL)); // Current URL without query
define('__CURRENT_SUB_DOMAIN', strtolower(count($hostParts) > 2 ? implode('.', array_slice($hostParts, 0, -2)) : '')); // Current sub-domain
define('__JS_COOKIE_DOMAIN', $isLocalhost ? $currentHost : $appHost); // Cookie domain
define('__ANALYTIC_API_URL', $_ENV['CUSTOM_TRACKING_URL'] ?? 'http://localhost'); // Tracking URL

// Cache constants. Deprecated
define('__CACHE_ENABLE', false); // Enable cache
define('__CACHE_FOLDER', dirname(TMVC_BASEDIR) . '/cache'); // Cache folder

// View constants
define('THEME_MAP', 'new/');
