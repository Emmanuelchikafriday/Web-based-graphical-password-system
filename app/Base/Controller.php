<?php
namespace Base;

require_once dirname(__DIR__) . '/config/session.php';

use mysqli;

abstract class Controller
{
	public ?array $data;
	protected static int $error_count = 0;
	
	public function __construct(public mysqli $db, public mixed $request)
	{
	}
	
	protected function response($data, int $code = 200):bool|string
	{
		http_response_code($code);
		return json_encode($data);
	}
}
