<?php

require __DIR__ . './../tinymvc/bootstrap.php';

httpGet(rtrim(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost', '/') . '/cron/update_translations_usage_log_in_db/cronaction');
