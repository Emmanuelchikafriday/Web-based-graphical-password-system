<?php

namespace Helpers;

use Auth;
use Exception;
use Models\Fusion;
use Models\User;
use mysqli;

class PicFuseAuth
{
	private static function response($data, int $code = 200): bool|string
	{
		http_response_code($code);
		return json_encode($data);
	}

	/**
	 * Verify selected image segments
	 * @param $request
	 * @return bool|string
	 */
	public static function verifyPicFuse($request): bool|string
	{
		$USER = new User(DB['connection']);
		$FUSION = new Fusion(DB['connection']);

		extract($request);

		if (!empty($picfuseArray)) {
			$fuse = '';
			$fuse_array = explode(',', $picfuseArray);

			if (empty($user) && Auth::user()->check())
				$user = Auth::user()->id;

			if (!empty($_SESSION['picfuse_user-csrf']))
				if (!empty($user)) {
					$query = $USER->where(['id' => $user])->result();

					if ($query->num_rows) {
						$user = $query->fetch_object();

						if ($_SESSION['picfuse_user-csrf'] === $user->email) {
							# code...
							$query = $FUSION->where(['user_id' => $user->id])->result();
							$user_fuse = $query->fetch_object();

							foreach ($fuse_array as $value) {
								$new_value = $user_fuse->collector . $value;
								$fuse .= $new_value;
							}
							$fuse .= '$' . $_SESSION['picfuse_user-csrf'];
							if (password_verify($fuse, $user_fuse->fused)) {
								unset($_SESSION['picfuse_user-csrf']);
								$_SESSION['picfuse_user'] = $user;

								if (!empty($other_actions))
									return PicFuseAuth::response(['message' => 'Sequence matched. Please wait.'], 201);
								return PicFuseAuth::response(['message' => 'Login Successful.', 'redirect' => './'], 308);
							}
							return PicFuseAuth::response(['message' => 'Wrong Image sequence. Please try again.'], 422);
						}
						return PicFuseAuth::response(['message' => 'Sequence mismatch.'], 422);

					}
					return PicFuseAuth::response(['message' => 'User does not exist.'], 422);
				} else
					return PicFuseAuth::response(['message' => 'User not given.'], 422);
			return PicFuseAuth::response(['message' => 'Sequence Mismatch.'], 419);
		}
		return PicFuseAuth::response(['message' => 'Unmatched request.'], 419);
	}

	/**
	 * @param $db
	 * @param $request
	 * @return bool
	 * @throws Exception;
	 */
	public static function updatePicFuse($db, $request): bool
	{
		$USER = new User($db, true);
		$FUSION = new Fusion($db);

		extract($request);

		if (!empty($email) && !empty($picfuseArray)) {
			$chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
			$rand = substr(str_shuffle($chars), 0, 10);
			$fuse_array = explode(',', $picfuseArray);

			$query = $USER->where(['email' => $email])->result();
			$user = $query->fetch_object();
			$user_id = $user->id;

			$fuse = '';
			foreach ($fuse_array as $value) {
				$new_value = $rand . $value;
				$fuse .= $new_value;
			}
			$fuse .= '$' . $email;
			$fused = password_hash($fuse, PASSWORD_DEFAULT);

			try {
				$FUSION->update(['fused' => $fused, 'collector' => $rand], 'user_id', $user_id);
				$_SESSION['picfuse_user'] = $user;
				return true;
			} catch (Exception $e) {
				throw (new Exception($e->getMessage()));
			}
		}
		return false;
	}
}
