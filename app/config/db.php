<?php
require_once 'globals.php';

try {
	$db_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	define('DB', ['connection' => $db_connection]);
	
} catch (Exception $exception) {
	dd('<strong>Error: </strong>' . $exception->getMessage(), '<strong>in file: </strong>' . $exception->getFile(), '<strong>on line: </strong>' . $exception->getLine());
}
