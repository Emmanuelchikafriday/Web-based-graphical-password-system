<?php

use Models\User;
use Models\UserTransaction;

include dirname(__DIR__) . '/app/components/navbar.php';
include path('app/components/sidebar.php');

if (!Auth::user()->check())
    redirect('register');

?>
<link rel="stylesheet" href="<?= asset('css/custom/picfuse.css') ?>">

<body>
    <main class="body">
        <div class="container-fluid">
            <div class="page-header fw-600">
                <h4>Dashboard</h4>
                <?php if (Auth::user()->check()) : ?>
                    <?= greet(Auth::user()->name); ?>
                <?php endif; ?>
            </div>
            <div class="user-card my-3"><?php include path('app/components/user-card.php'); ?></div>
	        
            <div class="my-4">
                <?php if (!empty(Auth::user()->transactions()->all) && $transactions = Auth::user()->transactions('ORDER BY id DESC limit 6')): ?>
                <h5>Last 6 Transactions: </h5>
                <div class="card shadow-sm p-0">
                    <div class="card-body px-3 p-1">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center trans-header">
                                <strong class="flex-shrink-0">#</strong>
                                <strong class="flex-shrink-0">Amount</strong>
                                <strong class="flex-shrink-0">Balance</strong>
                                <strong class="flex-shrink-0">Type</strong>
                                <strong class="flex-shrink-0">Transaction Date</strong>
                            </div>
                            <div class="d-flex flex-column mt-2">
                                <?php foreach ($transactions->all as $key => $transaction): ?>
                                    <div class="d-flex justify-content-between align-items-center trans-body mb-2<?= $transactions->last !== $transaction ? ' border-bottom' : NULL ?>">
                                        <span><?= $key + 1 ?></span>
                                        <span class="flex-shrink-0<?= $transaction->transaction_type === 'withdrawal' ? ' text-danger' : ' text-success' ?>"><?= ($transaction->transaction_type === 'withdrawal' ? '-' : '+') . ('&pound' . number_format($transaction->amount, 2)); ?></span>
                                        <span class="flex-shrink-0"><?= '&pound' . number_format($transaction->transaction_balance, 2); ?></span>
                                        <span class="flex-shrink-0 text-capitalize"><?= $transaction->transaction_type; ?></span>
                                        <span class="flex-shrink-0"><?= (new DateTime($transaction->created_at))->format('F j, Y, g:i a') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
    </main>
</body>

<script defer src="<?= asset('js/views/dashboard.js') ?>"></script>
