<?php
$title = 'Transactions';
include dirname(__DIR__) . '/app/components/navbar.php';
include path('app/components/sidebar.php');

if (!Auth::user()->check())
	redirect('register');
?>

<body>
	<main class="body">
		<div class="container-fluid">
			<div class="fw-600">
				<h4>Transactions</h4>
				<?php if (Auth::user()->check()) : ?>
					<?= greet(Auth::user()->name); ?>
				<?php endif; ?>
			</div>
		</div>
	</main>
</body>
