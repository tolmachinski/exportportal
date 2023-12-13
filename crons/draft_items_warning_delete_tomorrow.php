<?php

require __DIR__ . './../tinymvc/bootstrap.php';

httpGet(rtrim(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost', '/') . '/cron/draft_items_warning_delete_tomorrow/cronaction');
