<?php

namespace App\Base;

require dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/Exception.php';
require dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use JetBrains\PhpStorm\Pure;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

abstract class Mail
{
	/**
	 * @return string
	 */
	private static function mailStyling():string
	{
		return "
			<style>
				* {
					box-sizing: border-box;
				}
				
				a {
					text-decoration: none;
				}
				
				body {
					background-color: #000;
					color: #fff;
					margin: 0;
					width: 100%;
					padding: 1rem;
				}
				
				hr {
					border: none;
					border-top: 1px solid #fff;
					width: 95%;
				}
				
				.btn {
					display: inline-block;
					align-self: center;
					font-weight: 400;
					line-height: 1.5;
					color: #fff;
					text-align: center;
					text-decoration: none;
					vertical-align: middle;
					cursor: pointer;
					-webkit-user-select: none;
					-moz-user-select: none;
					user-select: none;
					background-color: #0d6efd;
					border: 1px solid #0d6efd;
					padding: 0.375rem 0.75rem;
					font-size: 1rem;
					border-radius: 0.25rem;
					transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
				}
				
				.btn:hover {
					color: #fff;
					background-color: #0b5ed7;
					border-color: #0a58ca;
				}
				
				.text-center {
					text-align: center;
				}
				
				footer {
					margin-top: 1rem;
				}
				
				footer a {
					color: #0d6efd;
					text-decoration: underline;
				}
				
				footer a:hover, footer a:focus {
					color: #0a58ca;
				}
				
				@media (min-width: 768px) {
					.text-md-start {
						text-align: left;
					}
				}
				
				@media (min-width: 992px) {
					hr {
						width: 50%;
					}
				}
			</style>
		";
	}
	
	/**
	 * @param $user
	 * @return string
	 */
	#[Pure] public static function activationMail($user):string
	{
		return "
			<!DOCTYPE html>
			<html lang='en'>
			" . self::mailStyling() . "
			<body>
				<h3>FinalProject Logo</h3>
				<hr>
				<p><strong>Hello $user[name].</strong></p>
				<p>Thank you for registering on FinalProject.</p>
				<p>
					We're glad to have you on-board here at FinalProject.<br>
					You're one step away from completing your registration.<br>
					Use the OTP below to complete your registration
				</p>
				<div class='text-center text-md-start'>$user[token]</div>
			</body>
			<footer>
				For futher enquiries, you can contact us via phone on: <a href='tel:08076079667' class='link-primary'>08076079667</a>.<br> Or via whatsapp: <a href='#'>FinalProject</a>
			</footer>
			</html>
		";
	}
	
	/**
	 * @param $user
	 * @return string
	 */
	public static function recoveryMail($user):string
	{
		return "
			<!DOCTYPE html>
			<html lang='en'>
			" . self::mailStyling() . "
			<body>
				<h3>FinalProject Logo</h3>
				<hr>
				<p><strong>Hello $user[name].</strong></p>
				<p>Account recovery action was initiated on your account.</p>
				<p>
					An account recovery action was initiated on your account at ". now() .".<br>
					Please ignore this mail if you didn't initiate this action.<br>
					Use the OTP below to continue
				</p>
				<div class='text-center text-md-start'><kbd>$user[token]</kbd></div>
			</body>
			<footer>
				For futher enquiries, you can contact us via phone on: <a href='tel:08076079667' class='link-primary'>08076079667</a>.<br> Or via whatsapp: <a href='#'>FinalProject</a>
			</footer>
			</html>
		";
	}

    /**
     * @param $user
     * @param null $title
     * @param null $action
     * @return string
     */
	#[Pure] public static function OTPMail($user, $title = NULL, $action = NULL):string
	{
		if (empty($title))
			$title = 'Registered E-Mail Change.';
			
		if (empty($action))
			$action = 'You are about to change the E-Mail Address associated with your account';
			
		return "
			<!DOCTYPE html>
			<html lang='en'>
			" . self::mailStyling() . "
			<body>
				<h3>FinalProject Logo</h3>
				<hr>
				<p><strong>Hello $user[name].</strong></p>
				<p>$title</p>
				<p>
					$action, please approve with OTP: <strong><kbd>$user[token]</kbd></strong> which expires in 10 minutes.<br>
					Please ignore this mail if you did not initiate this change. Thank you.
				</p>
			</body>
			<footer>
				For further enquiries, you can contact us via phone on: <a href='tel:08076079667' class='link-primary'>08076079667</a>.<br> Or via whatsapp: <a href='#'>Sound Community</a>
			</footer>
			</html>
		";
	}
	
	/**
	 * @param PHPMailer $mail
	 */
	public static function mailPrepare(PHPMailer $mail):void
	{
		/*$mail->SMTPDebug = SMTP::DEBUG_SERVER;*/
		$mail->isSMTP();
		$mail->Host = 'mail.farmy.ng';
		$mail->Port = 587;
		/*$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;*/
		$mail->SMTPAuth = true;
		$mail->Username = 'info@farmy.ng';
		$mail->Password = 'v3l1wUGZ]2]Lq2';
		/*$mail->Timeout = 360;*/
	}
}
