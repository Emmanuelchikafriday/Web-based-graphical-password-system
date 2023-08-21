<?php

namespace Controllers\Auth;

require dirname(__DIR__, 3) . '/Base/Controller.php';

require path('app/Base/File.php');
require path('app/Base/Mail.php');

require path('app/Http/Helpers/PicFuseAuth.php');

use App\Base\Mail;
use Base\Controller;
use Base\File;
use DateTime;
use Exception;
use Models\Fusion;
use Models\User;
use Models\UserAccount;
use Models\UserToken;
use PHPMailer\PHPMailer\PHPMailer;

class PatternRecoveryController extends Controller
{
	/**
	 * @return bool|string
	 */
	public function sendOTPMail():bool|string
	{
		$USER = new User($this->db);
		$USERTOKENS = new UserToken($this->db);
		
		extract($this->request);
		
		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$otp = substr(str_shuffle(NUM_CHARS), 0, 4);
				$query = $USER->where(['email' => $email])->result();
				
				
				if ($query->num_rows) {
					$user = $query->fetch_object();
					$user_info = ['name' => $user->name, 'token' => $otp];
					$mailer = new PHPMailer(true);
					
					try {
						Mail::mailPrepare($mailer);
						$mailer->setFrom('info@farmy.ng', 'PicFuse');
						$mailer->addAddress($email);
						$mailer->isHTML();
						
						$mailer->Subject = 'Account Recovery';
						$mailer->Body = Mail::recoveryMail($user_info);
						
						if ($mailer->send())
							try {
								$_SESSION['picfuse_user-csrf'] = $user->email;
								$USERTOKENS->create(['user_id' => $user->id, 'reason' => 'Account Recovery', 'token' => $otp, 'created_at' => now()]);
								return $this->response(['message' => 'OTP Sent to the specified E-Mail Address. Please Wait..', 'email' => $email]);
							} catch (Exception $e) {
								/*return $this->response(['message' => 'OTP Sent to the specified E-Mail Address with errors. Please try again'], 501);*/
								return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);
							}
						return $this->response(['message' => 'Error occurred sending OTP to your E-Mail Address'], 501);
					} catch (Exception $e) {
						/*return $this->response(['message' => 'Mailer Error occurred: ' . $mailer->ErrorInfo], 422);*/
						return $this->response(['message' => 'Mailer Error occurred: ' . $e->getMessage()], 422);
					}
				} else
					return $this->response(['message' => 'User not found.'], 422);
			}
		return $this->response(['message' => 'Please enter a valid E-Mail Address'], 422);
	}
	
	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function verifyOTP():bool|string
	{
		$USER = new User($this->db);
		$USERTOKENS = new UserToken($this->db);
		
		extract($this->request);
		
		if (!empty($email))
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				if (!empty($otp)) {
					$current_email = $_SESSION['picfuse_user-csrf'];
					
					if (!empty($current_email) && $email === $current_email) {
						$query = $USER->where(['email' => $current_email])->result();
						
						if ($query->num_rows) {
							$user = $query->fetch_object();
							$query = $USERTOKENS->where(['user_id' => $user->id, 'token' => $otp])->result("ORDER BY `id` DESC LIMIT 1");
							
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
											return $this->response(['message' => 'Confirmation Complete. Please wait.', 'redirect' => './recreate-pattern']);
											/*return $this->response(['message' => 'An error occurred. Please try again'], 422);*/
										} catch (Exception $e) {
											return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
										}
									else
										return $this->response(['message' => 'OTP does not match. Please try again'], 422);
								return $this->response(['message' => 'OTP expired. Please try again'], 422);
							}
							return $this->response(['message' => 'OTP Resource not available. Please try again.'], 422);
						}
						return $this->response(['message' => 'An error occurred: User not found. Please try again'], 422);
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
	public function recoverPattern():bool|string
	{
		$USER = new User($this->db);
		$FUSION = new Fusion($this->db);
		
		$this->data['message'] = 'Given data is invalid';
		extract($this->request);
		
		$email = $_SESSION['picfuse_user-csrf'];
		
		if (!empty($email) && !empty($picfuseArray)) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				return $this->response(['message' => 'Invalid User']);
			
			$query = $USER->where(['email' => $email])->result();
			$user_exists = $query->num_rows;
			
			if ($user_exists && !empty($picfuse_auth)) {
				$user = $query->fetch_object();
				try {
					$fuse_array = explode(',', $picfuseArray);
					$user_id = $user->id;
					
					$upload_settings = File::uploadSettings(
						'user_data/picfuse',
						$user_id,
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
									
									if ($FUSION->where(['user_id' => $user_id])->result()->num_rows) {
										unset($_SESSION['picfuse_user-csrf']);
										$FUSION->update(['url' => $picfuse_url, 'fused' => $fused, 'collector' => $rand], 'user_id', $user_id);
										return $this->response(['message' => 'Account recovery successful. You will now be redirected to the login page', 'redirect' => './login'], 308);
									} else
										$this->data['message'] = 'An error occurred: User Info not found.';
								} catch (Exception $exception) {
									/*$this->db->query("DELETE FROM `users` WHERE `id` = '$user_id'");*/
									$this->data['message'] = 'An error occurred: Unable to generate PicFuse lock.';
								}
						} catch (Exception $exception) {
							/*$this->db->query("DELETE FROM `users` WHERE `id` = '$user_id'");*/
							$this->data['message'] = 'An error occurred: Unable to create User resource.';
						}
					} /*else
						$this->db->query("DELETE FROM `users` WHERE `id` = '$user_id'");*/
				} catch (Exception $exception) {
					$this->data['message'] = 'An error occurred: Unable to create User.';
				}
			} else
				$this->data['message'] = 'User not found.';
		}
		return $this->response($this->data, 422);
	}
}

$request = array_merge($_REQUEST, $_FILES);
$auth = new PatternRecoveryController($db_connection, $request);

extract($_REQUEST);

if (!empty($action)) {
	if (!empty($_POST)) {
		if ($action === 'initiate-recovery')
			$data = $auth->sendOTPMail();
		
		if ($action === 'verify-otp')
			try {
				$data = $auth->verifyOTP();
			} catch (Exception $e) {
				http_response_code(422);
				$data = json_encode(['message' => $e->getMessage()]);
			}
		
		if ($action === 'complete-recover-pattern')
			$data = $auth->recoverPattern();
	}
}
echo $data;
