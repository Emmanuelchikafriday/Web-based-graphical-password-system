<div class="card-starter d-flex align-items-center">
	<div class="card rounded-3 shadow-sm m-auto" style="min-width: 380px;max-width: 400px;background-image: linear-gradient(145deg, rgba(50,20,250,0.6), rgba(180,10,50,0.8));border: 1px solid rgba(180,10,50,0.8)">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<div class="d-flex align-items-center">
					<div class="fs-1 fw-700 me-2">FP</div>
					<div class="fs-3 fw-700 ms-2">FPay</div>
				</div>
				<div class="d-flex flex-column align-items-end fw-700">
					<div class="d-flex align-items-center position-relative fs-4">
						<div class="position-absolute d-flex rounded-circle bg-danger text-white" style="height: 28px;width: 28px;transform: translate(-85%,-10%);z-index: 1">
							<span class="m-auto">V</span>
						</div>
						<span style="z-index: 2">erve</span>
					</div>
					<div class="me-2">DEBIT</div>
				</div>
			</div>
			
			<div class="d-flex align-items-center text-white fw-600 fs-4 mb-2">
				<u><?= '&pound' . number_format((Auth::user()->email_verified_at ? (!empty(Auth::user()->user_account()->account_balance) ? Auth::user()->user_account()->account_balance : '0') : '0'), 2) ?></u>
			</div>
			
			<div class="d-flex flex-column align-items-center">
				<div class="d-flex align-items-center justify-content-between text-white fw-600 fs-5 mb-1" style="width: 85%">
					<?php if (!Auth::user()->email_verified_at): ?>
						<span>****</span> <span>****</span> <span>****</span> <span>****</span>
					<?php else: ?>
						<?= !empty(Auth::user()->user_account()->account_number) ? formatAccountNumber(Auth::user()->user_account()->account_number) : '<span>****</span> <span>****</span> <span>****</span> <span>****</span>' ?>
					<?php endif; ?>
				</div>
				<div class="text-light text-opacity-50 mb-2"><?= strtoupper(Auth::user()->name) ?></div>
			</div>
		</div>
	</div>
</div>

<?php if (!Auth::user()->email_verified_at) : ?>
	<div class="alert alert-info px-2 p-1 my-3">
		<i class="far fa-1x fa-info-circle"></i>
		<a href="#" class="open-modal" data-modal-route="<?= view('modals/verify-email-modal.php') ?>" data-modal-target="#verify-email-modal" data-modal-wrapper="#global-modal-wrapper">
			Please Verify your E-Mail Address To continue
		</a>
	</div>
<?php endif; ?>
