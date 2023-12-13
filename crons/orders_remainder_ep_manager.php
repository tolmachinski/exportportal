<?php

require __DIR__ . './../tinymvc/bootstrap.php';

httpGet(rtrim(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost', '/') . '/cron/orders_remainder_ep_manager/cronaction');
