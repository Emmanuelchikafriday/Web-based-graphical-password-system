<?php

/*use Controllers\Auth\AuthenticatedSessionController;*/

use Controllers\UserSettingsController;

include dirname(__DIR__) . '/app/components/navbar.php';
include path('app/components/sidebar.php');

if (!Auth::user()->check())
	redirect('register');
?>

<link rel="stylesheet" href="<?= asset('css/custom/picfuse.css') ?>">

<body>
	<main class="body">
		<div class="container-fluid">
			<div class="fw-600">
				<h4>Settings</h4>
				<?php if (Auth::user()->check()) : ?>
					<?= greet(Auth::user()->name); ?>
				<?php endif; ?>
			</div>
			
			<div class="offset-lg-2 offset-md-1 col-lg-8 col-md-10 my-3">
				<form action="<?= controller(UserSettingsController::class) ?>" method="post" id="update-account-form">
					<a href="" id="init-email-otp" class="d-none" data-modal-route="<?= view('modals/otp-wrapper-modal.php') ?>" data-modal-target="#otp-wrapper-modal" data-modal-wrapper="#global-modal-wrapper"></a>
					<input type="hidden" name="action" value="basic-settings">
					
					<div class="form-group floating-label required mb-3">
						<i class="far fa-user icon"></i>
						<input type="text" id="name" name="name" class="form-control" placeholder="Name" value="<?= Auth::user()->name ?>">
						<label for="name">Name</label>
					</div>
					<div class="form-group floating-label required mb-3">
						<i class="far fa-phone icon"></i>
						<input type="tel" id="phone" name="phone" class="form-control" placeholder="Phone" value="<?= Auth::user()->phone ?>">
						<label for="phone">Phone</label>
					</div>
					<div class="form-group floating-label required mb-3">
						<i class="far fa-envelope icon"></i>
						<input type="email" id="email" name="email" class="form-control" placeholder="E-Mail Address" value="<?= Auth::user()->email ?>">
						<label for="email">E-Mail Address</label>
					</div>
					
					<div class="mmb-2">
						<button type="submit" class="btn btn-outline-primary w-100 mb-2">
							Save
							<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
						</button>
						<div class="form-message"></div>
					</div>
				</form>
			</div>
			
			<?php if (Auth::user()->email_verified_at) : ?>
				<div class="card">
					<div class="card-body px-2 p-1">
						<form action="<?= controller(UserSettingsController::class); ?>" method="post" data-final-action="complete-delete-user-account" id="delete-user-account-form">
							<input type="hidden" name="action" value="init-delete-user-account">
							<button type="submit" class="btn link-danger btn-link text-decoration-none"><i class="fa-1x far fa-trash-alt"></i> Delete your account <i class="fa-1x far fa-exclamation"></i></button>
							<div class="form-message"></div>
						</form>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</main>
</body>

<script defer src="<?= asset('js/views/settings.js') ?>"></script>
