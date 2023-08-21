<?php
$title = 'Register';
include dirname(__DIR__) . '/app/components/base-struct.php';

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
						<form action="<?= controller(PatternRecoveryController::class) ?>" method="post" id="recreate-pattern-form">
							<a href="" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#global-modal-wrapper"></a>
							<input type="hidden" name="action" value="complete-recover-pattern">
							
							<div class="mb-3">
								<label for="picfuse_auth">
									Authenticator <br>
									<small><em>Upload an image to continue *</em></small>
								</label>
								<div class="input-group align-items-stretch flex-nowrap">
									<i class="far fa-lock-alt m-auto pe-2"></i>
									<div class="form-field-group w-100">
										<input type="file" id="picfuse_auth" name="picfuse_auth" class="dropify" data-bs-height="180" placeholder="PicFuse Authenticator" data-/>
									</div>
								</div>
								<div id="picfuse_authValid" class="valid-text"></div>
							</div>
							
							<div class="d-flex flex-column justify-content-between">
								<button type="submit" class="btn btn-outline-primary w-100 mb-2">
									Save
									<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
								</button>
								<a href="<?= view('login') ?>" class="text-center"><small>Already have an account?</small></a>
								<div class="form-message"></div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
<script defer src="<?= asset('plugins/dropify/js/dropify.js') ?>"></script>
<script defer src="<?= asset('js/jquery-steps/jquery.steps.js') ?>"></script>
<script defer src="<?= asset('js/custom/file-upload.js') ?>"></script>
<script defer src="<?= asset('js/views/recreate-pattern.js') ?>"></script>
