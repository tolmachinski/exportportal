<?php

namespace App\Common;

$baseDir = realpath((\defined('TMVC_BASEDIR') ? dirname(TMVC_BASEDIR) : \dirname(__DIR__, 3)) . '/');

define('App\\Common\\ROOT_PATH', $baseDir);
define('App\\Common\\PUBLIC_PATH', $baseDir . '/public');
define('App\\Common\\CONFIG_PATH', $baseDir . '/tinymvc/myapp/configs');
define('App\\Common\\STORAGE_PATH', $baseDir . '/var/storage');
define('App\\Common\\CACHE_PATH', $baseDir . '/var/cache');
define('App\\Common\\TEMP_PATH', $baseDir . '/var/temp');
define('App\\Common\\LOGS_PATH', $baseDir . '/var/log');
define('App\\Common\\VAR_PATH', $baseDir . '/var');

const DB_DATE_FORMAT = 'Y-m-d H:i:s';
const PUBLIC_DATE_FORMAT = 'm/d/Y';
const PUBLIC_TIME_FORMAT = 'h:i A';
const PUBLIC_DATETIME_FORMAT = 'm/d/Y h:i A';
const PUBLIC_TIME_FORMAT_INTERNATIONAL = 'h:i';
const PUBLIC_DATETIME_FORMAT_INTERNATIONAL = 'm/d/Y H:i';
const EMAIL_DELIMITER = ',';
const SELLER_GROUPS_ID = [2, 3];
const MANUFACTURER_GROUPS_ID = [5, 6];
const GENERIC_UUID_NAMESPACE = '291c29b8-8891-11ea-ac30-04d9f5070b70';
const THEME_MAP = 'new';

namespace App\Common\Autocomplete;

define('App\\Common\\Autocomplete\\CONFIG_PATH', $baseDir . '/tinymvc/myapp/configs/autocomplete.php');

const RECORDS_PER_TYPE = 5;
const VERSION = 1;
const TYPE_ITEMS = 5;
const TYPE_ITEMS_TEXT = 'items';
const TYPE_ITEMS_COOKIE_POSTFIX = '_i';
const TYPES = [
    TYPE_ITEMS      => [TYPE_ITEMS_TEXT, TYPE_ITEMS_COOKIE_POSTFIX],
    TYPE_ITEMS_TEXT => TYPE_ITEMS,
];

namespace App\Common\Complaints;

// Complaint types
const TYPE_BLOG = 15;
const TYPE_CHAT = 26;
const TYPE_COMMENT = 27;
const TYPE_COMPANY = 4;
const TYPE_COMPANY_LIBRARY = 9;
const TYPE_COMPANY_NEWS = 5;
const TYPE_COMPANY_PHOTOS = 6;
const TYPE_COMPANY_UPDATES = 8;
const TYPE_COMPANY_VIDEOS = 7;
const TYPE_EVENT = 10;
const TYPE_EVENT_COMMENT = 19;
const TYPE_FEEDBACK = 14;
const TYPE_ITEM = 1;
const TYPE_ITEM_COMMENT = 2;
const TYPE_ITEM_QUESTIONS = 11;
const TYPE_MESSAGE = 17;
const TYPE_ORDER = 18;
const TYPE_PAGE_CONTENT = 16;
const TYPE_QUESTION = 12;
const TYPE_QUESTION_ANSWER = 24;
const TYPE_QUESTIONS_COMMENT = 20;
const TYPE_REVIEW = 13;
const TYPE_SELLER_NEWS_COMMENT = 21;
const TYPE_SELLER_PHOTO_COMMENT = 22;
const TYPE_SELLER_VIDEO_COMMENT = 23;
const TYPE_SHIPPER = 25;
const TYPE_USER = 3;

namespace App\Common\Comments;

const TYPE_ITEM_ALIAS = 'item';
const PUBLIC_DATE_FORMAT = 'M j, Y';
