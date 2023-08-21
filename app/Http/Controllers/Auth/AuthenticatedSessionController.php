<?php

namespace Controllers\Auth;

require dirname(__DIR__, 3) . '/Base/Controller.php';

require path('app/Base/File.php');
require path('app/Base/Mail.php');

require path('app/Http/Helpers/PicFuseAuth.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

use App\Base\Mail;
use Auth;
use Base\Controller;
use Base\File;
use DateTime;
use Exception;
use Helpers\PicFuseAuth;
use Models\Fusion;
use Models\User;
use Models\UserAccount;
use Models\UserToken;
use mysqli_result;
use PHPMailer\PHPMailer\PHPMailer;

class AuthenticatedSessionController extends Controller
{
	/**
	 * @return bool|string
	 */
	public function sendOTPMail(): bool|string
	{
		$USERTOKENS = new UserToken($this->db);

		extract($this->request);

		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$otp = substr(str_shuffle(NUM_CHARS), 0, 4);
				$user = ['name' => Auth::user()->name, 'token' => $otp];

				$mailer = new PHPMailer(true);

				try {
					Mail::mailPrepare($mailer);
					$mailer->setFrom('info@farmy.ng', 'PicFuse');
					$mailer->addAddress($email);
					$mailer->isHTML();

					$mailer->Subject = 'Email Confirmation';
					$mailer->Body = Mail::activationMail($user);

					if ($mailer->send())
						try {
							$USERTOKENS->create(['user_id' => Auth::user()->id, 'reason' => 'E-Mail Verification', 'token' => $otp, 'created_at' => now()]);
							return $this->response(['message' => 'OTP Sent to the specified E-Mail Address.', 'email' => $email]);
						} catch (Exception $e) {
							/*return $this->response(['message' => 'OTP Sent to the specified E-Mail Address with errors. Please try again'], 501);*/
							return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);
						}
					return $this->response(['message' => 'Error occurred sending OTP to your E-Mail Address'], 501);
				} catch (Exception $e) {
					/*return $this->response(['message' => 'Mailer Error occurred: ' . $mailer->ErrorInfo], 422);*/
					return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);
				}
			}
		return $this->response(['message' => 'Please enter a valid E-Mail Address'], 422);
	}

	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function verifyOTP(): bool|string
	{
		$FUSION = new Fusion($this->db);
		$USERTOKENS = new UserToken($this->db);

		extract($this->request);

		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				if (!empty($otp)) {
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
									$USERTOKENS->update(['used' => true], 'token', $otp);
									/*$this->db->query("UPDATE `user_tokens` SET `used` = true WHERE `token` = '$otp'");*/
									if ($email === Auth::user()->email)
										if ($this->confirmEmail()) {
											$chars = '0123456789';
											$rand = '3873' . substr(str_shuffle($chars), 0, 12);
											$USERACCOUNT = new UserAccount($this->db);
											$USERACCOUNT->create(['user_id' => Auth::user()->id, 'account_tier_id' => 1, 'account_number' => $rand, 'account_balance' => 60000]);
											return $this->response(['message' => 'Account Verified successfully.']);
										} else
											return $this->response(['message' => 'An error occurred. Please try again'], 501);
									else {
										$query = $FUSION->where(['user_id' => Auth::user()->id])->result();
										if ($query->num_rows) {
											$user_fuse = $query->fetch_object();
											$_SESSION['picfuse_user-csrf'] = Auth::user()->email;
											return $this->response(['message' => 'We noticed a change in the E-Mail. Your Authentication is needed to complete this action', 'fuse' => $user_fuse->url, 'user' => Auth::user()->id], 201);
										}
										return $this->response(['message' => 'User data not found.'], 422);
									}
								} catch (Exception $e) {
									return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
								} else
								return $this->response(['message' => 'OTP does not match. Please try again'], 422);
						return $this->response(['message' => 'OTP expired. Please try again'], 422);
					}
					return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
				}
				return $this->response(['message' => 'OTP not given. Please try again.'], 422);
			}
		return $this->response(['message' => 'User not found or invalid.'], 422);
	}

	/**
	 * @return bool|string
	 */
	public function verifyEmail(): bool|string
	{
		extract($this->request);

		if (!empty($email) && !empty($picfuseArray))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = mysqli_real_escape_string($this->db, $email);
				if ($this->confirmEmail($email)) {
					try {
						if (PicFuseAuth::updatePicFuse($this->db, $this->request)) {

							$chars = '0123456789';
							$rand = '3873' . substr(str_shuffle($chars), 0, 6) . substr(str_shuffle($chars), 0, 6);
							$USERACCOUNT = new UserAccount($this->db);
							$USERACCOUNT->create(['user_id' => Auth::user()->id, 'account_tier_id' => 1, 'account_number' => $rand, 'account_balance' => 60000]);

							return $this->response(['message' => 'Please note that your E-Mail has changed. Your new login E-Mail is: ' . $email, 'redirect' => './'], 308);
						}
					} catch (Exception $e) {
						/*return $this->response(['message' => 'An error occurred: ' . $e->getMessage()], 422);*/
						return $this->response(['message' => 'An error occurred updating E-Mail and Associated resources. Please try again.'], 422);
					}
				}
				return $this->response(['message' => 'An error occurred updating E-Mail. Please try again.'], 422);
			} else
				return $this->response(['message' => 'Invalid E-Mail.'], 422);
		return $this->response(['message' => 'User not found.'], 422);
	}

	/**
	 * @param $new_email
	 * @return bool|mysqli_result
	 */
	private function confirmEmail($new_email = NULL): mysqli_result|bool
	{
		$current_email = Auth::user()->email;
		$email = $new_email ?? $current_email;
		return Auth::user()->update(['email' => $email, 'email_verified_at' => now()]);
		/*return $this->db->query("UPDATE `users` SET `email` = '$email', `email_verified_at` = NOW() WHERE `email` = '$current_email'");*/
	}

	/**
	 * @return bool|string
	 */
	public function authenticatedUser(): bool|string
	{
		$USER = new User($this->db);
		$FUSION = new Fusion($this->db);

		$this->data['message'] = 'Given data is invalid';
		extract($this->request);

		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = mysqli_real_escape_string($this->db, $email);
				try {
					$query = $USER->where(['email' => $email, 'is_active' => true, 'is_deleted' => false])->result();

					if ($query->num_rows) {
						$user = $query->fetch_object();
						$query = $FUSION->where(['user_id' => $user->id])->result();
						$user_fuse = $query->fetch_object();

						if ($query->num_rows) {
							$_SESSION['picfuse_user-csrf'] = $user->email;
							return $this->response(['message' => 'User found. Please Wait...', 'fuse' => $user_fuse->url, 'user' => $user->id]);
						}
						return $this->response(['message' => 'It seems there was a glitch getting this user. Please contact the support team for assistance.'], 422);
					} else {
						if ($USER->where(['email' => $email, 'is_active' => false, 'is_deleted' => false])->result()->num_rows)
							return $this->response(['message' => 'This account has been suspended. Please contact the support team for assistance.'], 422);
						return $this->response(['message' => 'User not found. Try again.'], 422);
					}
				} catch (Exception $e) {
					/*return $this->response(['message' => 'An error occurred: ' . $e->getMessage()], 422);*/
					return $this->response(['message' => 'An error occurred on te server, please try again. Contact the support team if this continues.'], 422);
				}
			} else
				$this->data['errors']['email'] = "Please input a valid E-Mail Address.";
		else
			$this->data['message'] = 'Please enter your E-Mail Address.';
		return $this->response($this->data, 422);
	}

	/**
	 * @return bool|string
	 */
	public function verifyPicFuse(): bool|string
	{
		return PicFuseAuth::verifyPicFuse($this->request);
	}

	/**
	 * @return bool|string
	 */
	public function registerNewUser(): bool|string
	{
		$USER = new User($this->db);
		$FUSION = new Fusion($this->db);

		$this->data['message'] = 'Given data is invalid';
		extract($this->request);

		if (!empty($name) && !empty($phone) && !empty($email) && !empty($picfuseArray)) {
			if (!preg_match_all(NAME_REGEX, $name)) {
				$this->data['errors']['name'] = 'Please input your name using a valid format (eg. John Doe, John Wood Doe).';
				$this::$error_count++;
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->data['errors']['email'] = "Please input a valid E-Mail Address.";
				$this::$error_count++;
			}

			$name = mysqli_real_escape_string($this->db, $name);
			$phone = mysqli_real_escape_string($this->db, $phone);
			$phone = mysqli_real_escape_string($this->db, $phone);

			if (!$this::$error_count) {
				$user_exists = !!($USER->where(['email' => $email])->result()->num_rows);

				if (!$user_exists && !empty($picfuse_auth)) {

					try {
						$fuse_array = explode(',', $picfuseArray);
						$USER->create(['name' => $name, 'phone' => $phone, 'email' => $email]);
						$new_user_id = mysqli_insert_id($this->db);

						$upload_settings = File::uploadSettings(
							'user_data/picfuse',
							$new_user_id,
							5,
							['jpg', 'png'],
							['jpeg', 'jpg', 'png']
						);

						$tmp_name = File::getFileTmpName($picfuse_auth);
						$name = File::getFileName($picfuse_auth);
						$size = File::getComputedFileSize($picfuse_auth);
						$type = File::getFileType($picfuse_auth);
						$ext = File::getFileExtension($picfuse_auth);

						if ($size === File::$invalid_message || $name === File::$invalid_message || $tmp_name === File::$invalid_message || $ext === File::$invalid_message || $type === File::$invalid_message) {
							$this->data['message'] = File::$invalid_message . ' Please upload a valid image type (png or jpg).';
							$this::$error_count++;
						} elseif (!in_array($type, $upload_settings['allowed_type']) || !in_array($ext, $upload_settings['allowed_ext'])) {
							$this->data['message'] = 'Please upload a valid image type (png or jpg).';
							$this::$error_count++;
						} elseif ($size > $upload_settings['max_size']) {
							$this->data['message'] = 'Image exceeds allowed upload size of 5MB.';
							$this::$error_count++;
						}

						if (!$this::$error_count) {
							$final_name = $upload_settings['rand'] . '_' . date('Y_m_d__H_i_s') . '.' . $ext;
							$picfuse_url = $upload_settings['db_path'] . $final_name;
							$picfuse = $upload_settings['path'] . $final_name;
							$rand = $upload_settings['rand'];

							try {
								if (!file_exists($upload_settings['path']))
									mkdir($upload_settings['path']);

								if (move_uploaded_file($tmp_name, $picfuse))
									try {
										$fuse = '';
										foreach ($fuse_array as $value) {
											$new_value = $rand . $value;
											$fuse .= $new_value;
										}
										$fuse .= '$' . $email;
										$fused = password_hash($fuse, PASSWORD_DEFAULT);

										if (!$FUSION->where(['user_id' => $new_user_id])->result()->num_rows)
											$FUSION->create([
												'user_id' => $new_user_id,
												'url' => $picfuse_url,
												'fused' => $fused,
												'collector' => $rand,
											]);
										else
											$FUSION->update(['url' => $picfuse_url, 'fused' => $fused, 'collector' => $rand], 'user_id', $new_user_id);
										/*$this->db->query("UPDATE `fusion` SET `url` = '$picfuse_url', `fused` = '$fused', `collector` = '$rand' WHERE `user_id` = '$new_user_id'");*/
										return $this->response(['message' => 'Registration Successful.', 'redirect' => './login'], 308);
									} catch (Exception $exception) {
										/*$this->db->query("DELETE FROM `users` WHERE `id` = '$new_user_id'");*/
										$USER->delete(['id' => $new_user_id]);
										$this->data['message'] = 'An error occurred: Unable to generate PicFuse lock.';
									}
							} catch (Exception $exception) {
								/*$this->db->query("DELETE FROM `users` WHERE `id` = '$new_user_id'");*/
								$USER->delete(['id' => $new_user_id]);
								$this->data['message'] = 'An error occurred: Unable to create User resource.';
							}
						} else
							/*$this->db->query("DELETE FROM `users` WHERE `id` = '$new_user_id'");*/
							$USER->delete(['id' => $new_user_id]);
					} catch (Exception $exception) {
						$this->data['message'] = 'An error occurred: ' . $exception->getMessage();
						/*$this->data['message'] = 'An error occurred: Unable to create User.';*/
					}
				} else
					$this->data['errors']['email'] = 'E-Mail Address Already exists.';
			}
		} else
			$this->data['message'] = 'Please fill in all fields.';
		return $this->response($this->data, 422);
	}
}

$request = array_merge($_REQUEST, $_FILES);
$auth = new AuthenticatedSessionController($db_connection, $request);

extract($request);

if (!empty($action)) {
	if (!empty($_POST)) {
		if ($action === 'login')
			$data = $auth->authenticatedUser();

		if ($action === 'register')
			$data = $auth->registerNewUser();

		if ($action === 'verify-picfuse')
			$data = $auth->verifyPicFuse();

		if ($action === 'verify-email')
			$data = $auth->sendOTPMail();

		if ($action === 'verify-otp')
			try {
				$data = $auth->verifyOTP();
			} catch (Exception $e) {
				http_response_code(422);
				$data = json_encode(['message' => $e->getMessage()]);
			}

		if ($action === 'complete-verify-email')
			$data = $auth->verifyEmail();
	}
}
echo $data;
