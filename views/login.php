<?php
$title = 'Login';
include dirname(__DIR__) . '/app/components/base-struct.php';

use Controllers\Auth\AuthenticatedSessionController;

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
						<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="login-form">
							<a href="" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#global-modal-wrapper"></a>
							<input type="hidden" name="action" value="login">
							
							<div class="form-group floating-label">
								<i class="far fa-envelope icon"></i>
								<input type="email" name="email" id="email" class="form-control" placeholder="E-Mail Address">
								<label for="email">E-Mail Address</label>
							</div>
							
							<div class="d-flex flex-column">
								<a href="<?= view('forgot-pattern') ?>" class="text-end"><small>Recover Account?</small></a>
								<button type="submit" class="btn btn-outline-primary w-100">
									Continue <i class="far fa-1x fa-arrow-right-long"></i>
									<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
								</button>
								<a href="<?= view('register') ?>" class="text-center"><small>Don't have an account?</small></a>
								<div class="form-message"></div>
							</div>
						</form>
						
						<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="verify-picfuse-form">
							<input type="hidden" name="action" value="verify-picfuse">
						</form>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
<script defer src="<?= asset('js/views/login.js') ?>"></script>
