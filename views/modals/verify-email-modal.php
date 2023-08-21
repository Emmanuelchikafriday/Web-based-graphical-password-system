<?php
require dirname(__DIR__, 2) . '/app/config/session.php';

use Controllers\Auth\AuthenticatedSessionController;

$_fade = true;
$_close = true;
$_static = true;
$_centered = true;
$_scrollable = false;

$_modal_title = '<i class="far fa-1x fa-lock-alt"></i> Verify E-Mail Address';
$_modal_id = 'verify-email-modal';
$_color_classes = ['bg-dark', 'text-white'];

$body = '
	<div class="d-flex flex-column">
		<div class="forms">
			<form action="' . controller(AuthenticatedSessionController::class) . '" method="post" id="verify-email-form">
				<a href="javascript:void(0);" id="load-otp-form" hidden></a>
				<input type="hidden" name="action" value="verify-email">
				
				<div class="form-group floating-label">
					<i class="far fa-envelope icon"></i>
					<input type="email" name="email" id="email" class="form-control" placeholder="E-Mail Address">
					<label for="email">E-Mail Address</label>
				</div>
				<hr>
				<button type="submit" class="btn btn-outline-primary w-100">
					Continue
					<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
				</button>
				<div class="form-message"></div>
			</form>
		</div>
		<div class="forms" id="otp-wrapper" data-load-page="./app/components/verify-otp-form.php' .'" style="display: none"></div>
	</div>
';

require dirname(__DIR__, 2) . '/app/components/modal-struct.php';
