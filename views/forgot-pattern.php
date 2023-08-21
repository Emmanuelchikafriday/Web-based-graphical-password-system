<?php
$title = 'Register';
include dirname(__DIR__) . '/app/components/base-struct.php';

use Controllers\Auth\AuthenticatedSessionController;
use Controllers\Auth\PatternRecoveryController;

if (Auth::user()->check())
	redirect('./');
?>
<link rel="stylesheet" href="<?= asset('css/views/login.css') ?>">
<link rel="stylesheet" href="<?= asset('css/custom/picfuse.css') ?>">

<body>
	<main id="auth-page" class="body">
		<div class="container-fluid">
			<div class="row min-vh-100">
				<div class="d-none d-lg-block col" style="background: url(<?= asset('img/image.png') ?>) no-repeat fixed bottom;background-size: cover"></div>
				<div class="col-lg-6 d-flex flex-column">
					<div class="col-md-8 col-12 m-auto">
						<div id="recovery-wizard">
							<h5>Enter Registered E-mail</h5>
							<section>
								<form action="<?= controller(PatternRecoveryController::class) ?>" method="post" id="init-forgot-password-form">
									<a href="" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#global-modal-wrapper"></a>
									<input type="hidden" name="action" value="initiate-recovery">
									
									<div class="form-group floating-label">
										<i class="far fa-envelope icon"></i>
										<input type="email" name="email" id="email" class="form-control" placeholder="E-Mail Address">
										<label for="email">E-Mail Address</label>
									</div>
									
									<div class="d-flex flex-column">
										<!--<button type="submit" class="btn btn-outline-primary w-100">
											Continue <i class="far fa-1x fa-arrow-right-long"></i>
											<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
										</button>-->
										<a href="<?= view('login') ?>" class="text-center"><small>Already have an account?</small></a>
										<div class="form-message"></div>
									</div>
								</form>
							</section>
							<h5>Confirm OTP</h5>
							<section>
								<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="confirm-otp-form">
									<input type="hidden" name="action" value="confirm-otp">
									<div class="form-group text-center mb-3">
										<div id="otp"></div>
										<div id="time" class="mt-3">
											<span>The code expires in <span>5</span> minutes.</span>
											<span style="display: none"><a href="#" class="btn btn-link link-primary" id="resend-otp">Resend OTP <i class="far fa-1x fa-redo-alt"></i></a></span>
										</div>
										<div class="form-message"></div>
									</div>
								</form>
							</section>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
<script defer src="<?= asset('plugins/dropify/js/dropify.js') ?>"></script>
<script defer src="<?= asset('js/jquery-steps/jquery.steps.js') ?>"></script>
<script defer src="<?= asset('js/custom/file-upload.js') ?>"></script>
<script defer src="<?= asset('js/views/forgot-pattern.js') ?>"></script>
