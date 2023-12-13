<?php

require __DIR__ . './../tinymvc/bootstrap.php';

httpGet(rtrim(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost', '/') . '/cron/check_draft_items_added_today/cronaction');
