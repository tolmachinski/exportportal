<?php
/**
 * @author Usinevici Alexandr
 * @deprecated [24.12.2021]
 * Reason: Old functionality. Not used
 */
require __DIR__ . './../tinymvc/bootstrap.php';

httpGet(rtrim(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost', '/') . '/cron/translate_items/cronaction');
