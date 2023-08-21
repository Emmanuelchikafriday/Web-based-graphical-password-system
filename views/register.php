<?php
$title = 'Register';
include dirname(__DIR__) . '/app/components/base-struct.php';

use Controllers\Auth\AuthenticatedSessionController;

if (Auth::user()->check())
	redirect('./');
?>
<link rel="stylesheet" href="<?= asset('css/custom/picfuse.css') ?>">

<body>
	<main id="auth-page" class="body">
		<div class="container-fluid">
			<div class="row min-vh-100">
				<div class="col-lg-6 d-flex flex-column">
					<div class="col-md-8 col-12 m-auto">
						<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="register-form">
							<a href="" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#global-modal-wrapper"></a>
							<input type="hidden" name="action" value="register">

							<div class="form-group floating-label required mb-3">
								<i class="far fa-user icon"></i>
								<input type="text" id="name" name="name" class="form-control" placeholder="Name">
								<label for="name">Name</label>
							</div>
							<div class="form-group floating-label required mb-3">
								<i class="far fa-phone icon"></i>
								<input type="tel" id="phone" name="phone" class="form-control" placeholder="Phone">
								<label for="phone">Phone</label>
							</div>
							<div class="form-group floating-label required mb-3">
								<i class="far fa-envelope icon"></i>
								<input type="email" id="email" name="email" class="form-control" placeholder="E-Mail Address">
								<label for="email">E-Mail Address</label>
							</div>
							<div class="mb-3">
								<label for="picfuse_auth">
									Authenticator <br>
									<small><em>Upload an image to continue *</em></small>
								</label>
								<div class="input-group align-items-stretch flex-nowrap">
									<i class="far fa-lock-alt m-auto pe-2"></i>
									<div class="form-field-group w-100">
										<input type="file" id="picfuse_auth" name="picfuse_auth" class="dropify" data-bs-height="180" placeholder="PicFuse Authenticator" data- />
									</div>
								</div>
								<div id="picfuse_authValid" class="valid-text"></div>
							</div>

							<div class="d-flex flex-column justify-content-between">
								<div class="form-check mb-2">
									<input type="checkbox" class="form-check-input" id="accept" name="accept" required>
									<label class="form-check-label" for="accept">
										By clicking Register you agree to our
										<a class="link-primary open-policy-modal" data-modal="service-terms" data-bs-target="#service-terms-modal">Terms of Service</a> and <a class="link-primary open-policy-modal" data-modal="privacy-policy" data-bs-target="#privacy-policy-modal">Privacy Policy</a>
									</label>
								</div>

								<button type="submit" class="btn btn-outline-primary w-100 mb-2">
									Register
									<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
								</button>
								<a href="<?= view('login') ?>" class="text-center"><small>Already have an account?</small></a>
								<div class="form-message"></div>
							</div>
						</form>
					</div>
				</div>
				<div class="d-none d-lg-block col" style="background: url(<?= asset('img/image.png') ?>) no-repeat fixed bottom;background-size: cover"></div>
			</div>
		</div>
	</main>
</body>

<script defer src="<?= asset('plugins/dropify/js/dropify.js') ?>"></script>
<script defer src="<?= asset('js/custom/file-upload.js') ?>"></script>
<script defer src="<?= asset('js/views/register.js') ?>"></script>
