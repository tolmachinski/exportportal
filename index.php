<?php

/*
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007, New Digital Group Inc.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file
 */

require __DIR__ . '/tinymvc/bootstrap.php';

if (
    'on' === ($_ENV['DEV_RESTRICT'] ?? 'off')
    && !(
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match('/^(89\.28\.49\.94|192\.168\.(0|1)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))/', $_SERVER['HTTP_X_FORWARDED_FOR'], $outputXFF)
        || preg_match('/^(89\.28\.49\.94|192\.168\.(0|1)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))/', $_SERVER['REMOTE_ADDR'], $outputRA)
    )
    && (
        !isset($_COOKIE['ep_dev_access'])
        || $_COOKIE['ep_dev_access'] != $_ENV['DEV_ACCESS_HASH']
    )
    && !str_contains($_SERVER['REQUEST_URI'], 'maintenance/access_dev')
) {
    header('Location: https://www.exportportal.com');

    exit();
}

if (
    ($_ENV['MAINTENANCE_MODE'] ?? 'off') === 'on'
    && new DateTime('now', new DateTimeZone('UTC')) < new DateTime($_ENV['MAINTENANCE_END'] ?? 'now', new DateTimeZone('UTC'))
    && new DateTime('now', new DateTimeZone('UTC')) > new DateTime($_ENV['MAINTENANCE_START'] ?? 'now', new DateTimeZone('UTC'))
) {
    if (
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) && in_array($_SERVER['HTTP_X_FORWARDED_FOR'], ['89.28.49.94', '144.76.85.121'])
        || !isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !in_array($_SERVER['REMOTE_ADDR'], ['89.28.49.94', '144.76.85.121'])
    ) {
        require __DIR__ . '/tinymvc/maintenance.php';

        exit();
    }
}

define('DEBUG_MODE', filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN));

// PHP error reporting level, if different from default
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
} else {
    error_reporting(0);
}

// Configure session
$appHost = parse_url($_ENV['APP_URL'] ?? 'http://localhost', PHP_URL_HOST) ?? 'localhost';
$currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
ini_set('session.cookie_domain', 'localhost' === $currentHost ? $currentHost : '.' . $appHost);
session_cache_expire(15);
session_start();

$tmvc = new tmvc(); // instantiate
$request = \App\Common\Http\Request::createFromGlobals();
$response = $tmvc->handle($request);
$response->send(); // tally-ho!
