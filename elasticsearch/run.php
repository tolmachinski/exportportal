<?php

require dirname(__DIR__) . '/tinymvc/bootstrap.php';

require_once 'elasticsearch/for_developer.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$log = new Logger('name');
$streamHandler = new StreamHandler(__DIR__ . '/run_elasticsearch.log');
$log->pushHandler($streamHandler);

if (count($argv) < 2) {
    $msg = 'wrong number of arguments';
    $log->error($msg);

    exit($msg);
}

$tmvc = get_tmvc();
$command = $argv[1];

require "elasticsearch/{$command}/index.php";

function get_tmvc()
{
    define('DEBUG_MODE', filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN));

    $tmvc = new tmvc();
    $tmvc->boot();
    // Controller hot replacement
    $tmvc->controller = new Elasticsearch_Controller($tmvc->getContainer());
    $tmvc->getContainer()->set('controller', $tmvc->controller);
    if (!empty($config['models'])) {
        foreach ($config['models'] as $model) {
            $tmvc->controller->load->model($model . '_Model', $model);
        }
    }
    $tmvc->controller->load->script('output');

    return $tmvc;
}
