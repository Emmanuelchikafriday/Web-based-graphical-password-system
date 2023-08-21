<?php
namespace Controllers;

require dirname(__DIR__, 2) . '/Base/Controller.php';

require path('app/Http/Helpers/PicFuseAuth.php');

use Auth;
use Base\Controller;
use Exception;
use Helpers\PicFuseAuth;

class AccountController extends Controller
{
	/**
	 * Initiate the withdrawal process
	 * @return bool|string
	 */
	public function initiateWithdrawal():bool|string
	{
		$this->data['message'] = 'Given data is invalid';
		
		if (Auth::user()->check()) { // If User is logged in
			extract($this->request); // Import variables into the current symbol table from the _REQUEST super global
			
			if (!empty($amount)) { // If the user inputted the amount to be withdrawn
				$available_amount = Auth::user()->user_account()->account_balance; // Fetch the current available balance [For the current logged-in user]
				
				if ((floatval(number_format($amount, 2, thousands_separator : ''))) < floatval($available_amount)) { // If the amount to be withdrawn is less than the available balance
					$user_fuse = Auth::user()->fusion(); // Fetch the current logged-in users' Authentication info
					
					$_SESSION['init_withdrawal_amount'] = $amount; // Store the inputted amount to session
					$_SESSION['picfuse_user-csrf'] = Auth::user()->email; // Store the users' E-Mail to session
					return $this->response(['message' => 'Authentication Required. Please Wait...', 'fuse' => $user_fuse->url, 'user' => Auth::user()->id]);
				}
				return $this->response(['message' => 'Insufficient funds.'], 422); // Return message: Insufficient funds
			}
		}
		return $this->response(['message' => 'Unauthorized Request.'], 422); // Return message: Unauthorized request
	}
	
	/**
	 * Initiate the deposit process
	 * @return bool|string
	 */
	public function initiateDeposit():bool|string
	{
		$this->data['message'] = 'Given data is invalid';
		
		if (Auth::user()->check()) {
			extract($this->request);
			
			if (!empty($amount))
				if ((floatval(number_format($amount, 2, thousands_separator : ''))) < floatval('60000')) {
					$user_fuse = Auth::user()->fusion();
					
					$_SESSION['init_deposit_amount'] = $amount;
					$_SESSION['picfuse_user-csrf'] = Auth::user()->email;
					return $this->response(['message' => 'Authentication Required. Please Wait...', 'fuse' => $user_fuse->url, 'user' => Auth::user()->id]);
				}
				return $this->response(['message' => 'You cannot deposit above 60,000.00 in a single transaction.'], 422);
		}
		return $this->response(['message' => 'Unauthorized Request.'], 422);
	}
	
	/**
	 * Segmentation selection verification
	 * @return bool|string
	 */
	public function verifyPicFuse():bool|string
	{
		return PicFuseAuth::verifyPicFuse($this->request);
	}
	
	/**
	 * Complete withdrawal process
	 * @return bool|string
	 */
	public function verifyWithdrawal():bool|string
	{
		if (!empty($_SESSION['init_withdrawal_amount'])) {
            $amount = mysqli_real_escape_string($this->db, $_SESSION['init_withdrawal_amount']);
			$amount = floatval(number_format($amount, 2, thousands_separator : ''));
			$new_balance = floatval(Auth::user()->user_account()->account_balance) - $amount;
			unset($_SESSION['init_withdrawal_amount'], $_SESSION['picfuse_user-csrf']);

			try {
				Auth::user()->transactions()->create(['amount' => $amount, 'transaction_balance' => $new_balance, 'transaction_type' => 'withdrawal']);
				Auth::user()->user_account()->update(['account_balance' => $new_balance]);
				return $this->response(['message' => 'Withdrawal successful. You wil be redirected to your dashboard', 'redirect' => './'], 308);
			} catch (Exception $e) {
				/*return $this->response(['message' => $e->getMessage()], 422);*/
				return $this->response(['message' => 'An error occurred: Failed to update balance. Please try again.'], 422);
			}
		}
		return $this->response(['message' => 'Unauthorized request'], 422);
	}

	/**
	 *  Complete deposit process
	 * @return bool|string
	 */
	public function verifyDeposit():bool|string
	{
		if (!empty($_SESSION['init_deposit_amount'])) {
            $amount = mysqli_real_escape_string($this->db, $_SESSION['init_deposit_amount']);
			$amount = floatval(number_format($amount, 2, thousands_separator : ''));
			$new_balance = floatval(Auth::user()->user_account()->account_balance) + $amount;
			unset($_SESSION['init_deposit_amount'], $_SESSION['picfuse_user-csrf']);
			
			
			try {
				Auth::user()->transactions()->create(['amount' => $amount, 'transaction_balance' => $new_balance, 'transaction_type' => 'deposit']);
				Auth::user()->user_account()->update(['account_balance' => $new_balance]);
				return $this->response(['message' => 'Deposit successful. You wil be redirected to your dashboard', 'redirect' => './'], 308);
			} catch (Exception $e) {
				/*return $this->response(['message' => $e->getMessage()], 422);*/
				return $this->response(['message' => 'An error occurred: Failed to update balance. Please try again.'], 422);
			}
		}
		return $this->response(['message' => 'Unauthorized request'], 422);
	}
}

$request = array_merge($_REQUEST, $_FILES); // Merge the contents of the super globals [_REQUEST & _FILES] into one array
$auth = new AccountController($db_connection, $request); // Instantiate the AccountController class

extract($_REQUEST); // Import variables into the current symbol table from the _REQUEST super global

/* Get the requests and channel them to the right methods/functions */
if (!empty($action)) {
	if (!empty($_POST)) {
		if ($action === 'init-deposit')
			$data = $auth->initiateDeposit();
		
		if ($action === 'init-withdrawal')
			$data = $auth->initiateWithdrawal();
		
		if ($action === 'verify-picfuse')
			$data = $auth->verifyPicFuse();
		
		if ($action === 'complete-verify-withdrawal')
			$data = $auth->verifyWithdrawal();
		
		if ($action === 'complete-verify-deposit')
			$data = $auth->verifyDeposit();
	}
}
echo $data;
