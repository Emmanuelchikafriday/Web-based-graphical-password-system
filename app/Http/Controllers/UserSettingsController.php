<?php

namespace Controllers;

require dirname(__DIR__, 2) . '/Base/Controller.php';

require path('app/Base/File.php');
require path('app/Base/Mail.php');

require path('app/Http/Helpers/PicFuseAuth.php');

use App\Base\Mail;
use Auth;
use Base\Controller;
use DateTime;
use Exception;
use Helpers\PicFuseAuth;
use Models\Fusion;
use Models\User;
use Models\UserToken;
use PHPMailer\PHPMailer\PHPMailer;

class UserSettingsController extends Controller
{
	/**
	 * @param $email
	 * @param $otp
	 * @param NULL $title
	 * @param NULL $action
	 * @return bool
	 * @throws Exception
	 */
	private function sendOTPMail($email, $otp, $title = NULL, $action = NULL): bool
	{
		$user = ['name' => Auth::user()->name, 'token' => $otp];
		$mailer = new PHPMailer(true);

		try {
			Mail::mailPrepare($mailer);
			$mailer->setFrom('info@farmy.ng', 'PicFuse');
			$mailer->addAddress($email);
			$mailer->isHTML();

			$mailer->Subject = $title;
			$mailer->Body = Mail::OTPMail($user, $title, $action);

			return $mailer->send();
		} catch (Exception $e) {
			return throw new Exception($e->getMessage());
		}
	}

	/**
	 * @return bool|string
	 */
	public function resendOTPMail(): bool|string
	{
		extract($this->request);

		if ((!empty($email) && !empty($_SESSION['picfuse_user-csrf'])) && Auth::user()->email === $_SESSION['picfuse_user-csrf']) {
			$USERTOKENS = new UserToken($this->db);
			$otp = substr(str_shuffle(NUM_CHARS), 0, 4);

			try {
				if (!empty($email_title) && !empty($email_body))
					if ($this->sendOTPMail($email, $otp, $email_title, $email_body)) {
						$_SESSION['picfuse_user-csrf'] = Auth::user()->email;
						$USERTOKENS->create(['user_id' => Auth::user()->id, 'reason' => 'E-Mail Change', 'token' => $otp]);
						return $this->response(['message' => 'OTP sent to given E-Mail.', 'email' => $email, 'user' => Auth::user()->id], 201);
					}
			} catch (Exception $e) {
				/*return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);*/
				return $this->response(['message' => 'An error occurred: Unable to send OTP to given E-Mail'], 422);
			}
		}
		return $this->response(['message' => 'Unauthorized request. Please try again.'], 422);
	}

	/**
	 * @return bool|string
	 */
	public function basicUpdate(): bool|string
	{
		$this->data['message'] = 'Given data is invalid';
		$USER = new User($this->db);
		extract($this->request);

		if (!empty($name) && !empty($phone) && !empty($email)) {
			if (!preg_match_all(NAME_REGEX, $name)) {
				$this->data['errors']['name'] = 'Please input your name using a valid format (eg. John Doe, John Wood Doe).';
				$this::$error_count++;
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->data['errors']['email'] = "Please input a valid E-Mail Address.";
				$this::$error_count++;
			}

			if (!self::$error_count) {
				if (Auth::user()->email_verified_at) {
					$user_id = Auth::user()->id;
					$query = $USER->where(['id <>' => $user_id, 'email <>' => $email])->result();

					if (!$query->num_rows || $email === Auth::user()->email) {

						try {
							Auth::user()->update(['name' => $name, 'phone' => $phone]);

							if ($email !== Auth::user()->email) {
								$USERTOKENS = new UserToken($this->db);
								$otp = substr(str_shuffle(NUM_CHARS), 0, 4);
								try {
									$email_title = 'Registered E-Mail Change.';
									$email_body = 'You are about to change the E-Mail Address associated with your account';

									if ($this->sendOTPMail($email, $otp, $email_title, $email_body))
										try {
											$_SESSION['picfuse_user-csrf'] = Auth::user()->email;
											$USERTOKENS->create(['user_id' => Auth::user()->id, 'reason' => 'E-Mail Change', 'token' => $otp]);
											return $this->response([
												'message' => 'Although other details were updated.<br>We noticed a change in your E-Mail. An OTP has been sent to the given E-Mail. Verification is needed to complete this action. Please wait...',
												'email' => $email,
												'user' => Auth::user()->id,
												'action' => 'complete-update-email',
												'email_title' => $email_title,
												'email_body' => $email_body,
											], 201);
										} catch (Exception $e) {
											/*return $this->response(['message' => 'OTP Sent to the specified E-Mail Address with errors. Please try again'], 501);*/
											return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 511);
										}
									return $this->response(['message' => 'Although other details were updated.<br>The new E-Mail Address will not change as there was an error sending an OTP. Please try again.'], 422);
								} catch (Exception $e) {
									return $this->response(['message' => 'Other details updated but the E-Mail was not changed.<br>Unable to send OTP to new E-Mail.<br>Mailer Error occurred: ' . $e->getMessage()], 422);
								}
							}
							return $this->response(['message' => 'Personal Details successfully updated. Please wait...']);
						} catch (Exception $e) {
							/*return $this->response(['message' => 'An error occurred: ' . $e->getMessage()], 422);*/
							return $this->response(['message' => 'An error occurred: Unable to update details. Please try again'], 422);
						}
					}
					return $this->response(['message' => 'E-Mail already exists.'], 422);
				}
				return $this->response(['message' => 'Please <a href="./">activate</a> your account to continue.'], 422);
			}
			return $this->response($this->data, 422);
		} else {
			$this->data['message'] = 'Please fill in all fields';

			if (empty($name))
				$this->data['errors']['name'] = 'The Name field is required.';

			if (empty($phone))
				$this->data['errors']['phone'] = 'The Phone field is required.';

			if (empty($email))
				$this->data['errors']['email'] = 'The E-Mail field is required.';
		}
		return $this->response($this->data, 422);
	}

	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function verifyOTP(): bool|string
	{
		$USER = new User($this->db);
		$FUSION = new Fusion($this->db);
		$USERTOKENS = new UserToken($this->db);

		extract($this->request);

		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				if (!empty($otp)) {
					$current_email = $_SESSION['picfuse_user-csrf'];

					if (!empty($current_email) && Auth::user()->email === $current_email) {
						$otp = mysqli_real_escape_string($this->db, $otp);
						$query = $USERTOKENS->where(['user_id' => Auth::user()->id, 'token' => $otp])->result("ORDER BY `id` DESC LIMIT 1");

						if ($query->num_rows) {
							$token_object = $query->fetch_object();
							$start = new DateTime($token_object->created_at);
							$diff = $start->diff(new DateTime());

							if (!$diff->days)
								$elapsed_minutes = $diff->h * 60;
							else {
								$elapsed_minutes = $diff->days * 24 * 60;
								$elapsed_minutes += $diff->h * 60;
							}
							$elapsed_minutes += $diff->i;

							if ($elapsed_minutes < 6 && !((int) $token_object->used))
								if ($token_object->token === $otp)
									try {
										$query = $FUSION->where(['user_id' => Auth::user()->id])->result();
										$user_fuse = $query->fetch_object();
										$USERTOKENS->update(['used' => true], 'token', $otp);
										return $this->response(['message' => 'OTP Confirmation Successful. Please wait...', 'fuse' => $user_fuse->url, 'user' => Auth::user()->id], 201);
									} catch (Exception $e) {
										return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
									} else
									return $this->response(['message' => 'OTP does not match. Please try again'], 422);
							else if ((int) $token_object->used)
								return $this->response(['message' => 'OTP already used. Please try again'], 422);
							return $this->response(['message' => 'OTP expired. Please try again'], 422);
						}
						return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
					}
					return $this->response(['message' => 'An error occurred: OTP Resource not available. Please try again.'], 422);
				}
				return $this->response(['message' => 'OTP not given. Please try again.'], 422);
			}
		return $this->response(['message' => 'User not found or invalid.'], 422);
	}

	/**
	 * @return bool|string
	 */
	public function emailUpdate(): bool|string
	{
		extract($this->request);
		$current_email = Auth::user()->email;

		if (!empty($email) && !empty($picfuseArray))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = mysqli_real_escape_string($this->db, $email);
				try {
					if (Auth::user(true)->update(['email' => $email])) {
						try {
							if (PicFuseAuth::updatePicFuse($this->db, $this->request))
								return $this->response(['message' => 'Please note that your E-Mail has changed. Your new login E-Mail is: ' . $email, 'redirect' => './'], 308);
						} catch (Exception $e) {
							/*return $this->response(['message' => 'An error occurred: ' . $e->getMessage()], 422);*/
							return $this->response(['message' => 'An error occurred updating E-Mail and Associated resources. Please try again.'], 422);
						}
					}
					return $this->response(['message' => 'An error occurred updating E-Mail. Please try again.'], 422);
				} catch (Exception $e) {
					/*return $this->response(['message' => 'An error occurred: ' . $e->getMessage()], 422);*/
					return $this->response(['message' => 'An error occurred: Init update E-Mail failed. Please try again.'], 422);
				}
			} else
				return $this->response(['message' => 'Invalid E-Mail.'], 422);
		return $this->response(['message' => 'User not found.'], 422);
	}

	/**
	 * @return bool|string
	 */
	public function initDeleteUserAccount(): bool|string
	{
		if (Auth::user()->email_verified_at) {
			$email = Auth::user()->email;
			$USERTOKENS = new UserToken($this->db);
			$otp = substr(str_shuffle(NUM_CHARS), 0, 4);

			try {
				$email_title = 'Account Deletion.';
				$email_body = 'You are about to delete your account and entire data from FinalProject platform';

				if ($this->sendOTPMail($email, $otp, $email_title, $email_body))
					try {
						$_SESSION['picfuse_user-csrf'] = $email;
						$USERTOKENS->create(['user_id' => Auth::user()->id, 'reason' => 'Account Deletion', 'token' => $otp]);
						return $this->response([
							'message' => 'Action in progress. Please wait...',
							'email' => $email,
							'user' => Auth::user()->id,
							'action' => 'complete-delete-user-account',
							'email_title' => $email_title,
							'email_body' => $email_body,
						], 201);
					} catch (Exception $e) {
						/*return $this->response(['message' => 'OTP Sent to the specified E-Mail Address with errors. Please try again'], 501);*/
						return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 511);
					}
				return $this->response(['message' => 'There was an error sending an OTP. Please try again.'], 422);
			} catch (Exception $e) {
				return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);
			}
		}
		return $this->response(['message' => 'You must <a href="' . view('index') . '">activate your account</a> first.'], 422);
	}

	/**
	 * @return bool|string
	 */
	public function completeDeleteUserAccount(): bool|string
	{
		Auth::user(true)->update(['is_deleted' => true]);
		unset($_SESSION['picfuse_user-csrf'], $_SESSION['picfuse_user']);
		return $this->response(['message' => 'Your Account has been successfully deleted. Please wait...', 'redirect' => './'], 308);
	}
}

$request = array_merge($_REQUEST, $_FILES); // Merge the contents of the super globals [_REQUEST & _FILES] into one array
$auth = new UserSettingsController($db_connection, $request); // Instantiate the AccountController class

extract($request); // Import variables into the current symbol table from the _REQUEST super global

/* Get the requests and channel them to the right methods/functions */
if (!empty($action)) {
	if (!empty($_POST)) {
		if ($action === 'basic-settings')
			$data = $auth->basicUpdate();

		if ($action === 'verify-otp')
			try {
				$data = $auth->verifyOTP();
			} catch (Exception $e) {
				http_response_code(422);
				$data = json_encode(['message' => $e->getMessage()]);
			}

		if ($action === 'resend-otp-email')
			$data = $auth->resendOTPMail();

		if ($action === 'complete-update-email')
			$data = $auth->emailUpdate();

		if ($action === 'init-delete-user-account')
			$data = $auth->initDeleteUserAccount();

		if ($action === 'complete-delete-user-account')
			$data = $auth->completeDeleteUserAccount();
	}
}
echo $data;
