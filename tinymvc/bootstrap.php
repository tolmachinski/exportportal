<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load cached env vars if the .env.local.php file exists
// Run "php bin/console app:dump-env prod" to create it (imported from symfony/flex)
if (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
}

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

// if the /tinymvc/ dir is not up one directory, uncomment and set here
define('TMVC_BASEDIR', realpath(__DIR__) . '/');
// if the /myapp/ dir is not inside the /tinymvc/ dir, uncomment and set here
define('TMVC_MYAPPDIR', realpath(__DIR__ . '/myapp') . '/');
// define to 0 if you want errors/exceptions handled externally
define('TMVC_ERROR_HANDLING', 0);
