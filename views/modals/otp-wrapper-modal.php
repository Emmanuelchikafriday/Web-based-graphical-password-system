<?php
require dirname(__DIR__, 2) . '/app/config/session.php';

use Controllers\Auth\AuthenticatedSessionController;

$_fade = true;
$_close = true;
$_static = true;
$_centered = true;
$_scrollable = false;

$_modal_title = '<i class="far fa-1x fa-lock-alt"></i> Verify E-Mail Address';
$_modal_id = 'otp-wrapper-modal';
$_color_classes = ['bg-dark', 'text-white'];

$body = '
	<div class="d-flex flex-column">
		<div id="otp-wrapper" data-load-page="./app/components/verify-otp-form.php' .'"></div>
	</div>
';

require dirname(__DIR__, 2) . '/app/components/modal-struct.php';
