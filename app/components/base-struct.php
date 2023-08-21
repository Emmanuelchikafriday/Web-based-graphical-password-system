<?php require dirname(__DIR__) . '/config/session.php' ?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo app_name . ' :: ' . ($title ?? 'Home') ?></title>
	
	<!--<link rel="apple-touch-icon" sizes="180x180" href="<?/*= asset('meta/apple-touch-icon.png') */?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?/*= asset('meta/favicon-32x32.png') */?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?/*= asset('meta/favicon-16x16.png') */?>">
	<link rel="manifest" href="<?/*= asset('meta/site.webmanifest')  */?>">-->
	
	<link rel="stylesheet" href="<?= asset('plugins/bootstrap/css/bootstrap.css') ?>">
	<link rel="stylesheet" href="<?= asset('plugins/pin-login/jquery.pinlogin.css') ?>">
	<link rel="stylesheet" href="<?= asset('icons/fontawesome/css/all.css') ?>">
	<link rel="stylesheet" href="<?= asset('css/custom/sidebar.css') ?>">
	<link rel="stylesheet" href="<?= asset('css/custom/style.css') ?>">
	<link rel="stylesheet" href="<?= asset('plugins/dropify/css/dropify.css') ?>">
	<link rel="stylesheet" href="<?= asset('plugins/fusion-util/css/fonts.css') ?>">
	<link rel="stylesheet" href="<?= asset('plugins/fusion-util/css/fusion.form.util.css') ?>">
	
	<?php require_once dirname(__DIR__) . '/components/scripts.php' ?>
</head>

<div id="global-message-wrapper"></div>
<div id="global-modal-wrapper"></div>
