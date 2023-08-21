<?php require dirname(__DIR__) . '/components/base-struct.php' ?>

<nav class="navbar navbar-expand border-bottom fixed-top bg-white">
	<div class="container-lg align-items-center px-3">
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav flex-fill justify-content-between justify-content-lg-end align-items-center">
				<li class="nav-item d-lg-none">
					<a href="#" class="nav-link fs-5 sidebar-toggler">
						<i class="far fa-bars"></i>
						Menu
					</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<div class="d-flex bg-secondary rounded-circle" style="height: 40px;width: 40px"><i class="text-white fa fa-user-alt m-auto"></i></div>
					</a>
					<ul class="dropdown-menu dropdown-menu-end">
						<li><a class="dropdown-item" href="<?= view('deposit') ?>"><i class="fal fa-1x fa-wallet"></i> Deposit</a></li>
						<li><a class="dropdown-item" href="<?= view('withdrawal') ?>"><i class="fal fa-1x fa-money-bill"></i> Withdraw</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="<?= view('settings') ?>"><i class="fal fa-1x fa-cog"></i> Account Settings</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="<?= view('logout') ?>"><i class="fal fa-1x fa-power-off"></i> Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
