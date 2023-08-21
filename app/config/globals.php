<?php
require_once dirname(__DIR__) . '/helpers/app.php';

global $db_connection;
global $cache;

const default_storage_path = 'public/storage/';
const default_asset_path = 'public/assets/';
const default_views_path = 'views/';
const default_http_path = 'app/Http/';

const NUM_CHARS = '0123456789';
const APLHA_LOWER_CHARS = 'qwertyuiopasdfghjklzxcvbnm';
const APLHA_UPPER_CHARS = 'QWERTYUIOPASDFGHJKLZXCVBNM';
const APLHA_ALL_CHARS = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
const APLHA_NUM_CHARS = APLHA_ALL_CHARS . NUM_CHARS;

define('app_name', get_env('APP_NAME'));

define('DB_HOST', get_env('DB_HOST'));
define('DB_USER', get_env('DB_USER'));
define('DB_NAME', get_env('DB_NAME'));
define('DB_PASSWORD', get_env('DB_PASSWORD'));

define('HTTP_HOST', $_SERVER['HTTP_HOST']);
define("REQUEST_URI", ($_SERVER['REQUEST_URI']));
define("QUERY_STRING", ($_SERVER['QUERY_STRING']));
define('REQUEST_SCHEME', $_SERVER['REQUEST_SCHEME']);
define("HTTP_REFERER", ($_SERVER['HTTP_REFERER'] ?? NULL));
define("REDIRECT_STATUS", ($_SERVER['REDIRECT_STATUS'] ?? NULL));

define('URI_SPLIT', preg_split('/\//', REQUEST_URI));
define('__ROOT__', (!empty(URI_SPLIT[0]) ? URI_SPLIT[0] : URI_SPLIT[1]) . '/');

const ROOT_URL = REQUEST_SCHEME . '://' . HTTP_HOST . '/' . __ROOT__;
const CURRENT_URL = REQUEST_SCHEME . '://' . HTTP_HOST . REQUEST_URI . QUERY_STRING;
