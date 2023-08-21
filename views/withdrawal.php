<?php
$title = 'Withdrawal';
include dirname(__DIR__) . '/app/components/navbar.php';
include path('app/components/sidebar.php');

use Controllers\AccountController;
use Controllers\Auth\AuthenticatedSessionController;

if (!Auth::user()->check())
	redirect('register');
?>

<link rel="stylesheet" href="<?= asset('css/custom/picfuse.css') ?>">

<body>
	<main class="body">
		<div class="container-fluid">
			<div class="fw-600">
				<h4>Withdrawal</h4>
				<?php if (Auth::user()->check()) : ?>
					<?= greet(Auth::user()->name); ?>
				<?php endif; ?>
			</div>
			
			<div class="user-card my-3"><?php include path('app/components/user-card.php'); ?></div>
			
			<?php if (Auth::user()->email_verified_at) : ?>
				<form action="<?= controller(AccountController::class) ?>" method="post" id="make-transaction-form" data-final-action="complete-verify-withdrawal">
					<a href="" id="start-picfuse-auth" class="d-none" data-modal-route="<?= view('modals/authenticator-modal.php') ?>" data-modal-target="#authenticator-modal" data-modal-wrapper="#global-modal-wrapper"></a>
					<input type="hidden" name="action" value="init-withdrawal">
					
					<div class="form-group floating-label">
						<i class="far fa-money-check-pen icon"></i>
						<input type="number" min="10" max="60000" name="amount" id="amount" class="form-control" placeholder="Enter Amount">
						<label for="amount">Withdrawal Amount</label>
					</div>
					
					<div class="form-group">
						<button type="submit" class="btn btn-outline-primary w-100">
							Continue
							<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
						</button>
						<div class="form-message"></div>
					</div>
				</form>
				
				<form action="<?= controller(AuthenticatedSessionController::class) ?>" method="post" id="verify-picfuse-form">
					<input type="hidden" name="action" value="verify-picfuse">
				</form>
			<?php endif; ?>
		</div>
	</main>
</body>

<script defer src="<?= asset('js/views/dashboard.js') ?>"></script>
<script defer src="<?= asset('js/views/account.js') ?>"></script>
