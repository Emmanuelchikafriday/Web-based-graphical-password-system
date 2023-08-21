<div class="overlay"></div>
<aside class="sidebar expanded">
	<?php if (Auth::user()->check()) : ?>
		<div class="sidebar-header">
			<span class="text-light text-opacity-50">Welcome</span>
			<h5><?= Auth::user()->name; ?></h5>
			<p><span class="fs-5">Phone</span>: <?= Auth::user()->phone ?></p>
			<p>1 Account &bullet; <span class="text-primary text-opacity-75">Personal</span></p>
		</div>
		<div class="sidebar-body">
			<a href="<?= view('') ?>" class="d-flex align-items-center<?= empty(uriSegment(2)) || uriSegment(2) === 'index' ? ' active' : NULL ?>"><span>Dashboard</span> <i class="fa fa-angle-right"></i></a>
			<a href="<?= view('deposit') ?>" class="d-flex align-items-center<?= str_contains(CURRENT_URL, 'deposit') ? ' active' : NULL ?>"><span>Deposit</span> <i class="fa fa-angle-right"></i></a>
			<a href="<?= view('withdrawal') ?>" class="d-flex align-items-center<?= str_contains(CURRENT_URL, 'withdrawal') ? ' active' : NULL ?>"><span>Withdraw</span> <i class="fa fa-angle-right"></i></a>
			<a href="<?= view('transactions') ?>" class="d-flex align-items-center<?= str_contains(CURRENT_URL, 'transactions') ? ' active' : NULL ?>"><span>Transactions</span> <i class="fa fa-angle-right"></i></a>
			<a href="#" class="d-flex align-items-center"><span>Card Management</span> <i class="fa fa-angle-right"></i></a>
		</div>
	<?php endif; ?>
</aside>
