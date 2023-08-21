<?php
namespace Base;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

abstract class File
{
	public static string $invalid_message = 'Given file is invalid.';
	
	/**
	 * @param string $dir
	 * @param int $id
	 * @param int $max_size
	 * @param array $allowed_ext
	 * @param array $allowed_type
	 * @return array
	 */
	
	#[ArrayShape(['max_size' => "int", 'allowed_ext' => "array|string[]", 'allowed_type' => "array|string[]", 'path' => "string", 'db_path' => "string", 'rand' => "string"])]
	public static function uploadSettings(string $dir, int $id, int $max_size = 20, array $allowed_ext = ['jpg', 'png', 'gif', 'bmp'], array $allowed_type = ['webp', 'jpeg', 'jpg', 'png', 'bmp']):array
	{
		// TODO: HTTP wrapper does not support writeable connections
		$chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		return [
			'max_size' => $max_size,
			'allowed_ext' => $allowed_ext,
			'allowed_type' => $allowed_type,
			'path' => '../../../../' . default_storage_path . $dir . '/' . $id . '/',
			'db_path' => ROOT_URL . default_storage_path . $dir . '/' . $id . '/',
			'rand' => substr(str_shuffle($chars), 0, 10),
		];
	}
	
	/**
	 * Checks if given input is an actual file.
	 * @param $file
	 * @return bool
	 */
	protected static function isFile($file):bool
	{
		return !empty($file['name']) && !empty($file['tmp_name']) && !empty($file['size']) && is_file($file['tmp_name']);
	}
	
	/**
	 * Returns number of errors for given file if any; returns false if no error.
	 * @param $file
	 * @return string|int|bool
	 */
	#[Pure(true)] public static function fileHasError($file):string|int|bool
	{
		return self::isFile($file) ? ($file['error'] > 0 ? (int) $file['error'] : false) : self::$invalid_message;
	}
	
	/**
	 * Get basename of file
	 * @param $file
	 * @return string
	 */
	#[Pure(true)] public static function getFileName($file):string
	{
		return self::isFile($file) ? basename($file['name']) : self::$invalid_message;
	}
	
	/**
	 * Get file temporary upload name.
	 * @param $file
	 * @return string
	 */
	#[Pure(true)] public static function getFileTmpName($file):string
	{
		return self::isFile($file) ? $file['tmp_name'] : self::$invalid_message;
	}
	
	/**
	 * Get type of file.
	 * @param $file
	 * @return bool|string
	 */
	public static function getFileType($file):bool|string
	{
		if (self::isFile($file)) {
			$split_filetype = explode('/', $file['type']);
			return end($split_filetype);
		}
		return self::$invalid_message;
	}
	
	/**
	 * Get file extension.
	 * @param $file
	 * @return bool|string
	 */
	public static function getFileExtension($file):bool|string
	{
		if (self::isFile($file)) {
			$split_filename = explode('.', self::getFileName($file));
			return end($split_filename);
		}
		return self::$invalid_message;
	}
	
	/**
	 * Get filesize (bytes).
	 * @param $file
	 * @return string|int|bool
	 */
	#[Pure(true)] public static function getFileSize($file):string|int|bool
	{
		return self::isFile($file) ? (int) filesize($file['tmp_name']) : self::$invalid_message;
	}
	
	/**
	 * Get computed filesize in Megabytes(MB).
	 * @param $file
	 * @return float|string
	 */
	#[Pure(true)] public static function getComputedFileSize($file):float|string
	{
		return is_int(self::getFileSize($file)) ? round(self::getFileSize($file) / (1024 * 1024), 2) : self::$invalid_message;
	}
}
