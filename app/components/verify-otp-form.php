<?php
require dirname(__DIR__, 2) . '/app/config/session.php';
use Controllers\Auth\AuthenticatedSessionController;
?>

<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="confirm-otp-form">
	<a href="#" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#temp-modal-wrapper">Hello</a>
	
	<input type="hidden" name="action" value="confirm-otp">
	<div class="form-group text-center mb-3">
		<div id="otp" data-email="<?= Auth::user()->email ?>"></div>
		<div id="time" class="mt-3">
			<span>The code expires in <span>5</span> minutes.</span>
			<span style="display: none"><a href="#" class="btn btn-link link-primary" id="resend-otp">Resend OTP <i class="far fa-1x fa-redo-alt"></i></a></span>
		</div>
		<div class="form-message"></div>
	</div>
</form>

<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="verify-picfuse-form">
	<input type="hidden" name="action" value="verify-picfuse">
</form>
