<?php
date_default_timezone_set('Africa/Lagos');

use Models\User;

require_once 'db.php';
require_once 'models.php';

session_name('picfuse_user');
session_start();

class Auth
{
	public static function user($force = false):User
	{
		return (new User(DB['connection'], $force));
	}
}
