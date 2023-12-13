<?php

$modules = array_slice($argv, 2);
$elasticUrl = $tmvc->my_config['env']['ELASTIC_SEARCH_API_HOST'] ?? 'http://localhost:9200';

if (!empty($modules)) {
    if (in_array('all', $modules)) {
        $modules = array('*');
    }

    foreach ($modules as $module) {
        $ch = curl_init("{$elasticUrl}/{$tmvc->my_config['env']['ELASTIC_SEARCH_INDEX']}_{$module}");

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $msg = curl_exec($ch);
        curl_close($ch);
        $log->info($msg);
        echo $msg . PHP_EOL;
    }
}
