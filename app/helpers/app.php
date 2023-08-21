<?php

use JetBrains\PhpStorm\Language;
use JetBrains\PhpStorm\NoReturn;

const NAME_REGEX = "/^([a-zA-Z-]{2,255})(\s[a-zA-Z-]{2,255}){1,2}$/";
const PHONE_REGEX = "/^(\+\d{1,3}?\s)(\(\d{3}\)\s)?(\d+\s)*(\d{2,3}-?\d+)+$/";

/**
 * Access files in the asset folder [public/asset/]
 * @param string $path
 * @return string
 */
function asset(#[Language('file-reference')] string $path):string
{
	return ROOT_URL . default_asset_path . $path;
}

/**
 * Access the controllers in the controllers folder [app/Http/Controllers/]
 * @param string $controller
 * @return string
 */
function controller(string $controller):string
{
	$controller = str_replace("\\", "/", $controller) . '.php';
	return ROOT_URL . default_http_path . $controller;
}

/**
 * Dump the given data
 * @param mixed ...$vars
 * @return void
 */
#[NoReturn] function dd(mixed...$vars):void
{
	foreach ($vars as $var) {
		echo '<pre>';
		gettype($var) === 'array' || gettype($var) === 'object' ? print_r($var) : print $var;
		echo '</pre>';
	}
	exit();
}

/**
 * Check if te given file is already included or required
 * @param string $filename
 * @return bool
 */
function fileIsIncluded(string $filename):bool
{
	$is_included = false;
	
	foreach (get_included_files() as $file) {
		if (str_contains($file, $filename)) {
			$is_included = true;
			break;
		}
	}
	return $is_included;
}

/**
 * Format the given numbers splitting them into parts of 4
 * @param string $account_number
 * @param int $length
 * @return string
 */
function formatAccountNumber(string $account_number, int $length = 4):string
{
	$acc_number_formatted = '';
	$acc_number_chunks = str_split($account_number, $length);
	
	foreach ($acc_number_chunks as $key => $acc_number_chunk) {
		$acc_number_formatted .= '<span>' . $acc_number_chunk . '</span>' . ($key < (count($acc_number_chunks) - 1) ? ' ' : '');
	}
	return $acc_number_formatted;
}

/**
 * Get configuration from the projects' .env file
 * @param string $config
 * @return mixed
 */
function get_env(string $config):mixed
{
	$env = parse_ini_file(dirname(__DIR__, 2) . '/.env');
	
	if (key_exists($config, $env))
		return $env[$config];
	die('Given config key: <strong><em>' . $config . '</em></strong> not an environment variable. Make sure you typed the key correctly');
}

/**
 * Greet according to the ime of the day and the given Name
 * @param string $suffix
 * @param string $timezone
 * @return string
 */
function greet(string $suffix = 'John Doe', string $timezone = 'Africa/Lagos'):string
{
	try {
		$dateTime = new DateTime('now', new DateTimeZone($timezone));
		$current_hour = intval($dateTime->format('H'));
		
		if ($current_hour && $current_hour < 12) {
			$greeting = 'Good Morning, ';
		} else if ($current_hour > 11 && $current_hour < 16) {
			$greeting = 'Good Afternoon, ';
		} else if ($current_hour > 15 && $current_hour < 24) {
			$greeting = 'Good Evening, ';
		} else {
			$greeting = 'Good Day, ';
		}
		
		return $greeting . $suffix;
	} catch (Exception $exception) {
		return $exception->getMessage();
	}
}

/**
 * Get the current time, formatted as [Year-Month-Day Hour:Minute:Second;meridian] (e.g. 2023-01-01 06:00:00am)
 * @param string $timezone
 * @return string
 */
function now(string $timezone = 'Africa/Lagos'):string
{
	try {
		$dateTime = new DateTime('now', new DateTimeZone($timezone));
		return $dateTime->format('Y-m-d G:i:s');
	} catch (Exception $exception) {
		return $exception->getMessage();
	}
}

/**
 * Get the given file from the path
 * @param string $path
 * @return string
 */
function path(string $path):string
{
	return dirname(__DIR__, 2) . '/' . $path;
}

/**
 * Redirect to the given path
 * @param string $uri
 * @param int $timeout
 * @return void
 */
#[NoReturn] function redirect(string $uri, int $timeout = 0):void
{
	die('<meta http-equiv="refresh" content="' . $timeout . '; url=' . $uri . '">');
}


/**
 * Get the value of the given URL Segment<br>
 * (e.g. **localhost/picfuse/register** -: <i>picfuse is segment 1 and register is segment 2</i>)
 * @param int $segment
 * @return mixed Value of the given segment.<br>NULL if it does not exist
 */
function uriSegment(int $segment):mixed
{
	return array_key_exists($segment, URI_SPLIT) ? URI_SPLIT[$segment] : NULL;
}

/**
 * Access the view files in the views folder [views/]
 * @param $view
 * @return string
 */
function view(#[Language('file-reference')] $view):string
{
	return ROOT_URL . default_views_path . $view;
}
