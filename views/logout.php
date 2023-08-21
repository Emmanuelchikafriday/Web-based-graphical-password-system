<?php
$title = 'Logout';
include dirname(__DIR__) . '/app/config/session.php';

session_destroy();
redirect('login');
