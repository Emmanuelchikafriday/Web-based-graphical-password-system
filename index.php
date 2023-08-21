<?php
$request = $_SERVER['REQUEST_URI'];
$request_split = preg_split("/\//", $request);
$request_exploded = explode('.', end($request_split));
$requested_file = !empty(end($request_split)) ? (mb_strtolower(end($request_exploded)) !== 'php' ? end($request_split) . '.php' : end($request_split)) : '';

//echo($requested_file);
//echo '<pre>';
//print_r(parse_ini_file('.env'));
//echo '</pre>';
//dd(get_env('DB_NAME'));

switch ($requested_file) {
    case '':
        require __DIR__ . '/views/index.php';
        break;
    case file_exists(__DIR__ . '/views/' . $requested_file):
        require __DIR__ . '/views/' . $requested_file;
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/views/errors/404.php';
        break;

}
